#!/usr/bin/env python3
"""
Recursively scan both Google Drive accounts for edited aftermovies & reels.
Discards Sony/camera default filenames (C0001.MP4, DJI_*, etc.) and raw footage paths.
Bluepoint: only your My Drive (owned files); skips "shared with me" via DriveFS metadata.
Never scans Shared drives — only .../My Drive.

Usage:
  python3 scripts/collect_drive_media.py --dry-run
  python3 scripts/collect_drive_media.py --dry-run --verbose
  python3 scripts/collect_drive_media.py --copy

Then (PHP/ffmpeg only):
  LANDING_VIDEOS_SOURCE=/Users/redasoft/Documents/Lapsique-Media-Local php artisan landing:videos-import --force
"""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import re
import shutil
import sqlite3
import subprocess
import sys
from dataclasses import dataclass, field
from datetime import datetime, timezone
from pathlib import Path

VIDEO_EXT = {".mp4", ".mov", ".m4v", ".MP4", ".MOV", ".M4V"}

DEFAULT_LAPSQUE = Path(
    "/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@lapsique.media"
    "/My Drive/LAPSIQUE.MEDIA/2.- CONTENIDO"
)
DEFAULT_BLUEPOINT = Path(
    "/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@bluepointrs.com/My Drive"
)
# Bluepoint: do NOT scan all of My Drive (~1400 root folders). Use portfolio + Ramiro folders.
DEFAULT_BLUEPOINT_ROOTS = (
    DEFAULT_BLUEPOINT / "PHOTOS" / "PORTAFOLIO",
    DEFAULT_BLUEPOINT
    / "PHOTOS"
    / "ETHEREAL"
    / "1.- Enero 08-03-2025"
    / "1.- Ramiro ",
)
# Only these subfolders under PORTAFOLIO (skip FOTOS / huge client archives).
BLUEPOINT_PORTAFOLIO_SUBDIRS = frozenset(
    {"REELS", "DJ SETS", "MULTI-CAM", "REAL ESTATE"}
)
MAX_DIR_CHILDREN = 400  # skip cloud-placeholder trees (65535 entries)
DRIVEFS_BASE = Path.home() / "Library/Application Support/Google/DriveFS"
DRIVEFS_ITEM_ID_XATTR = "com.google.drivefs.item-id#S"

DEFAULT_DEST = Path("/Users/redasoft/Documents/Lapsique-Media-Local")
PROGRESS_EVERY_DEFAULT = 500

# Skip these top-level folders under 2.- CONTENIDO (not deliverables).
LAPSIQUE_SKIP_TOP = frozenset(
    {
        "2.- franquicia santino",
        "3.- stock",
        "4.- recovers",
        "6.- procesar",
        "8.- carpetas para corregir santino - on heaven",
    }
)

RAW_PATH_MARKERS = (
    "clips para aftermovie",
    "sony log clips",
    "/dron/",
    "/recover/",
    "/camaras/",
    "/cameras/",
    "/audio/",
    "/audios/",
    "fixed camera",
    "ronin camera",
    "/behind angled camera",
    "/ronin camera",
)

# Sony / camera / drone default naming (not edited deliverables).
CAMERA_PREFIX = re.compile(
    r"^(C\d{3,5}|DSC\d+|MVI_\d+|GOPR\d+|MAH\d+|DJI_|IMG_\d{4,}|GOPR)",
    re.I,
)
CAMERA_DATE_RAM = re.compile(r"^20\d{6}_[A-Z]{3}\d", re.I)

AFTERMOVIE_DIR = re.compile(r"/\d+\.\-\s*aftermovies?\b", re.I)
REEL_DIR = re.compile(r"/\d+\.\-\s*reels?\b", re.I)
CLIPS_DIR = re.compile(r"/\d+\.\-\s*clips(\s|/|$)", re.I)

# Aftermovies: usually one MP4 ~50–180 MB with a human export name.
AFTERMOVIE_SIZE_MIN = 35 * 1024 * 1024
AFTERMOVIE_SIZE_MAX = 220 * 1024 * 1024
REEL_SIZE_MIN = 2 * 1024 * 1024
REEL_SIZE_MAX = 180 * 1024 * 1024


@dataclass
class MediaFile:
    account: str
    kind: str
    source: Path
    size: int
    reason: str

    def dest_relative(self) -> str:
        safe = re.sub(r"[^a-zA-Z0-9._-]+", "_", self.source.name)
        digest = hashlib.md5(str(self.source).encode()).hexdigest()[:8]
        return f"{self.account}/{self.kind}/{digest}_{safe}"


@dataclass
class ScanStats:
    paths: int = 0
    rejected: dict[str, int] = field(default_factory=dict)

    def reject(self, reason: str) -> None:
        self.rejected[reason] = self.rejected.get(reason, 0) + 1


def is_video(path: Path) -> bool:
    return path.suffix in VIDEO_EXT


def is_raw_path(path_lower: str) -> bool:
    for marker in RAW_PATH_MARKERS:
        if marker in path_lower:
            return True
    if CLIPS_DIR.search(path_lower) and not REEL_DIR.search(path_lower) and not AFTERMOVIE_DIR.search(path_lower):
        return True
    return False


def is_camera_filename(name: str) -> bool:
    if CAMERA_PREFIX.match(name):
        return True
    if CAMERA_DATE_RAM.match(name):
        return True
    stem = Path(name).stem
    if re.fullmatch(r"C\d{3,5}", stem, re.I):
        return True
    return False


def has_editorial_name(name: str) -> bool:
    lower = name.lower()
    if "aftermovie" in lower or "after movie" in lower:
        return True
    if re.search(r"\breel\b", lower) or lower.startswith("reel "):
        return True
    if re.search(r"\bad\s*[\d_]", lower):
        return True
    # At least one word-like token (not only digits/underscores)
    if re.search(r"[a-z]{3,}", lower):
        return True
    if " " in name and not is_camera_filename(name):
        return True
    return False


def classify(path: Path, size: int, stats: ScanStats) -> MediaFile | None:
    lower = str(path).lower()
    name = path.name

    if not is_video(path):
        return None

    if is_raw_path(lower):
        stats.reject("raw_path")
        return None

    if is_camera_filename(name):
        # Allow only if filename explicitly says aftermovie/reel (rare re-exports)
        if not re.search(r"aftermovie|after movie|\breel\b", name, re.I):
            stats.reject("camera_filename")
            return None

    explicit_aftermovie = (
        "aftermovie" in name.lower()
        or "after movie" in name.lower()
        or AFTERMOVIE_DIR.search(lower)
    )
    explicit_reel = (
        REEL_DIR.search(lower)
        or re.search(r"\breel\b", name, re.I)
        or re.search(r"\bad\s*[\d_]", name, re.I)
        or re.search(r"\bdrop\b", name, re.I)
    )

    ext = path.suffix.lower()

    if explicit_aftermovie:
        if ext != ".mp4" and ext != ".mov":
            stats.reject("aftermovie_wrong_ext")
            return None
        if size < AFTERMOVIE_SIZE_MIN or size > AFTERMOVIE_SIZE_MAX:
            stats.reject("aftermovie_size")
            return None
        return MediaFile("", "aftermovie", path, size, "explicit_aftermovie")

    if explicit_reel and not CLIPS_DIR.search(lower):
        if size < REEL_SIZE_MIN or size > REEL_SIZE_MAX:
            stats.reject("reel_size")
            return None
        return MediaFile("", "reel", path, size, "explicit_reel")

    # Heuristic: large MP4 export (~80–100 MB), human name, not drops/clips
    if (
        ext == ".mp4"
        and AFTERMOVIE_SIZE_MIN <= size <= AFTERMOVIE_SIZE_MAX
        and has_editorial_name(name)
        and not is_camera_filename(name)
        and "/dron/" not in lower
        and not re.search(r"\bdrop\b", name, re.I)
    ):
        return MediaFile("", "aftermovie", path, size, "heuristic_export_mp4")

    stats.reject("no_match")
    return None


class ScanProgress:
    def __init__(self, every: int, label: str) -> None:
        self.every = max(1, every)
        self.label = label
        self.stats = ScanStats()

    def tick(self, n: int = 1) -> None:
        self.stats.paths += n
        if self.stats.paths % self.every == 0:
            print(f"  {self.label}… {self.stats.paths} paths", flush=True)


def get_drive_item_id(path: Path) -> str | None:
    try:
        out = subprocess.run(
            ["xattr", "-p", DRIVEFS_ITEM_ID_XATTR, str(path)],
            capture_output=True,
            text=True,
            timeout=3,
        )
        if out.returncode == 0:
            return out.stdout.strip()
    except (OSError, subprocess.TimeoutExpired):
        pass
    return None


def find_drivefs_metadata_db(drive_root: Path) -> Path | None:
    """Pick the DriveFS sqlite DB that indexes this Cloud Storage account."""
    probes: list[Path] = [drive_root]
    try:
        for child in sorted(drive_root.iterdir()):
            if child.is_dir() and not child.name.startswith("."):
                probes.append(child)
                if len(probes) >= 8:
                    break
    except OSError:
        pass

    item_ids: list[str] = []
    for probe in probes:
        iid = get_drive_item_id(probe)
        if iid:
            item_ids.append(iid)

    if not item_ids:
        return None

    for db_path in sorted(DRIVEFS_BASE.glob("*/metadata_sqlite_db")):
        try:
            conn = sqlite3.connect(f"file:{db_path}?mode=ro", uri=True)
            for iid in item_ids:
                row = conn.execute("SELECT 1 FROM items WHERE id = ?", (iid,)).fetchone()
                if row:
                    conn.close()
                    return db_path
            conn.close()
        except sqlite3.Error:
            continue
    return None


def load_non_owned_item_ids(metadata_db: Path) -> frozenset[str]:
    """IDs of files/folders shared with this account (not owned by you)."""
    conn = sqlite3.connect(f"file:{metadata_db}?mode=ro", uri=True)
    try:
        rows = conn.execute("SELECT id FROM items WHERE is_owner = 0").fetchall()
    finally:
        conn.close()
    return frozenset(r[0] for r in rows)


def is_shared_with_me(path: Path, non_owned_ids: frozenset[str]) -> bool:
    iid = get_drive_item_id(path)
    return bool(iid and iid in non_owned_ids)


def prune_dirnames(
    parent: Path,
    dirnames: list[str],
    lapsique_contenido: Path | None,
    bluepoint_portafolio: Path | None = None,
) -> None:
    kept = []
    portafolio_resolved = bluepoint_portafolio.resolve() if bluepoint_portafolio else None
    for d in dirnames:
        dl = d.lower()
        if dl.startswith(".") or dl in {"node_modules", ".trash"}:
            continue
        if lapsique_contenido and parent.resolve() == lapsique_contenido.resolve():
            if dl in LAPSIQUE_SKIP_TOP:
                continue
        if portafolio_resolved and parent.resolve() == portafolio_resolved:
            if d not in BLUEPOINT_PORTAFOLIO_SUBDIRS:
                continue
        if "fotos editadas" in dl and "reel" not in dl and "aftermovie" not in dl:
            continue
        kept.append(d)
    dirnames[:] = kept


def skip_heavy_dir(path: Path) -> bool:
    try:
        return sum(1 for _ in path.iterdir()) > MAX_DIR_CHILDREN
    except OSError:
        return True


def scan_recursive(
    account: str,
    root: Path,
    max_depth: int,
    found: list[MediaFile],
    progress: ScanProgress,
    lapsique_contenido: Path | None = None,
    non_owned_ids: frozenset[str] | None = None,
    bluepoint_portafolio: Path | None = None,
) -> None:
    root = root.resolve()
    if not root.is_dir():
        print(f"  skip missing: {root}", file=sys.stderr)
        return

    if "shared drives" in str(root).lower():
        print(f"  skip (not My Drive): {root}", file=sys.stderr)
        return

    root_len = len(root.parts)
    progress.tick()

    for dirpath, dirnames, filenames in os.walk(root, topdown=True):
        current = Path(dirpath)
        if current != root:
            progress.tick()
        depth = len(current.parts) - root_len
        if depth > max_depth:
            dirnames.clear()
            continue

        prune_dirnames(current, dirnames, lapsique_contenido, bluepoint_portafolio)

        if non_owned_ids:
            pruned_dirs: list[str] = []
            for d in dirnames:
                sub = current / d
                if is_shared_with_me(sub, non_owned_ids):
                    progress.stats.reject("shared_with_me")
                    continue
                if skip_heavy_dir(sub):
                    progress.stats.reject("heavy_dir_skipped")
                    continue
                pruned_dirs.append(d)
            dirnames[:] = pruned_dirs
        elif dirnames:
            pruned_dirs = []
            for d in dirnames:
                if skip_heavy_dir(current / d):
                    progress.stats.reject("heavy_dir_skipped")
                    continue
                pruned_dirs.append(d)
            dirnames[:] = pruned_dirs

        for fname in filenames:
            progress.tick()
            path = current / fname
            try:
                if not path.is_file():
                    continue
                if non_owned_ids and is_shared_with_me(path, non_owned_ids):
                    progress.stats.reject("shared_with_me")
                    continue
                size = path.stat().st_size
                if size < 100_000:
                    progress.stats.reject("too_small")
                    continue
                hit = classify(path, size, progress.stats)
                if hit:
                    hit.account = account
                    found.append(hit)
            except OSError:
                progress.stats.reject("os_error")
                continue


def copy_file(src: Path, dest: Path, force: bool) -> bool:
    dest.parent.mkdir(parents=True, exist_ok=True)
    if dest.exists() and not force:
        try:
            if dest.stat().st_size == src.stat().st_size:
                return False
        except OSError:
            pass
    shutil.copy2(src, dest)
    return True


def print_rejected(stats: ScanStats, verbose: bool) -> None:
    if not stats.rejected:
        return
    print("\nDiscarded (reason → count):")
    for reason, count in sorted(stats.rejected.items(), key=lambda x: -x[1]):
        print(f"  {reason}: {count}")
    if not verbose and stats.rejected.get("no_match", 0) > 50:
        print("  (use --verbose on dry-run to see kept files only)")


def main() -> int:
    parser = argparse.ArgumentParser(description="Collect aftermovies & reels from both Google Drives")
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--copy", action="store_true")
    parser.add_argument("--force", action="store_true")
    parser.add_argument("--verbose", action="store_true")
    parser.add_argument("--limit", type=int, default=0)
    parser.add_argument("--max-depth", type=int, default=7, help="Lapsique 2.- CONTENIDO depth")
    parser.add_argument("--bluepoint-max-depth", type=int, default=6, help="Bluepoint My Drive depth")
    parser.add_argument("--progress-every", type=int, default=PROGRESS_EVERY_DEFAULT)
    parser.add_argument("--dest", type=Path, default=DEFAULT_DEST)
    parser.add_argument("--lapsique", type=Path, default=DEFAULT_LAPSQUE)
    parser.add_argument("--bluepoint", type=Path, default=DEFAULT_BLUEPOINT)
    parser.add_argument(
        "--bluepoint-root",
        action="append",
        type=Path,
        dest="bluepoint_roots",
        help="Extra Bluepoint folder to scan (repeatable). Default: PORTAFOLIO + 1.- Ramiro",
    )
    parser.add_argument(
        "--bluepoint-full-my-drive",
        action="store_true",
        help="Scan entire Bluepoint My Drive (slow; not recommended)",
    )
    parser.add_argument(
        "--bluepoint-drivefs-db",
        type=Path,
        default=None,
        help="DriveFS metadata_sqlite_db (auto-detected if omitted)",
    )
    parser.add_argument(
        "--include-shared",
        action="store_true",
        help="Include Bluepoint items shared with you (default: My Drive owned only)",
    )
    scope = parser.add_mutually_exclusive_group()
    scope.add_argument("--lapsique-only", action="store_true")
    scope.add_argument("--bluepoint-only", action="store_true")
    args = parser.parse_args()

    if not args.dry_run and not args.copy:
        parser.error("Use --dry-run or --copy")

    found: list[MediaFile] = []
    combined_rejected: dict[str, int] = {}

    if not args.bluepoint_only:
        print(f"Scanning lapsique (recursive): {args.lapsique}")
        lp = ScanProgress(args.progress_every, "lapsique")
        before = len(found)
        scan_recursive("lapsique", args.lapsique, args.max_depth, found, lp, args.lapsique)
        print(f"  → {len(found) - before} kept ({lp.stats.paths} paths)")
        for k, v in lp.stats.rejected.items():
            combined_rejected[k] = combined_rejected.get(k, 0) + v

    if not args.lapsique_only:
        non_owned: frozenset[str] | None = None
        if not args.include_shared:
            db = args.bluepoint_drivefs_db
            if db is None:
                db = find_drivefs_metadata_db(args.bluepoint)
            if db and db.is_file():
                non_owned = load_non_owned_item_ids(db)
                print(
                    f"Scanning bluepoint (My Drive only, skip shared): {args.bluepoint}\n"
                    f"  DriveFS index: {db.name} ({len(non_owned)} shared items to skip)"
                )
            else:
                print(
                    f"Scanning bluepoint: {args.bluepoint}\n"
                    "  warn: could not load DriveFS DB — shared folders may be included. "
                    "Pass --bluepoint-drivefs-db or open Google Drive for desktop.",
                    file=sys.stderr,
                )
        else:
            print(f"Scanning bluepoint (includes shared with me): {args.bluepoint}")

        if args.bluepoint_full_my_drive:
            scan_roots = [args.bluepoint]
        else:
            scan_roots = list(args.bluepoint_roots or DEFAULT_BLUEPOINT_ROOTS)

        portafolio_root = next(
            (r for r in scan_roots if r.name.upper() == "PORTAFOLIO"),
            None,
        )
        bp = ScanProgress(args.progress_every, "bluepoint")
        before = len(found)
        for scan_root in scan_roots:
            label = scan_root.name.strip() or str(scan_root)
            print(f"  → {scan_root}")
            if not scan_root.is_dir():
                print(f"    skip missing", file=sys.stderr)
                continue
            scan_recursive(
                "bluepoint",
                scan_root,
                args.bluepoint_max_depth,
                found,
                bp,
                non_owned_ids=non_owned,
                bluepoint_portafolio=portafolio_root,
            )
        print(f"  → {len(found) - before} kept ({bp.stats.paths} paths)")
        for k, v in bp.stats.rejected.items():
            combined_rejected[k] = combined_rejected.get(k, 0) + v

    if args.limit:
        found = found[: args.limit]

    found.sort(key=lambda m: (m.account, m.kind, m.source.name))

    am = sum(1 for m in found if m.kind == "aftermovie")
    reels = sum(1 for m in found if m.kind == "reel")
    total_mb = round(sum(m.size for m in found) / 1024 / 1024, 1)

    print(f"\nKept: {len(found)} ({am} aftermovies, {reels} reels, ~{total_mb} MB)")
    stats = ScanStats(rejected=combined_rejected)
    print_rejected(stats, args.verbose)

    if args.dry_run:
        show = found if args.verbose else found[:80]
        for m in show:
            print(
                f"  [{m.account}/{m.kind}] {m.size/1024/1024:.1f} MB "
                f"({m.reason})  {m.source.name}"
            )
        if not args.verbose and len(found) > 80:
            print(f"  … +{len(found) - 80} more (use --verbose)")
        print(f"\nCopy target: {args.dest.resolve()}")
        return 0

    dest = args.dest.resolve()
    dest.mkdir(parents=True, exist_ok=True)
    copied = skipped = failed = 0
    manifest_entries = []

    for i, m in enumerate(found, 1):
        target = dest / m.dest_relative()
        print(f"[{i}/{len(found)}] {m.source.name}", end=" … ", flush=True)
        try:
            if copy_file(m.source, target, args.force):
                print("ok")
                copied += 1
            else:
                print("skip")
                skipped += 1
            manifest_entries.append(
                {
                    "account": m.account,
                    "kind": m.kind,
                    "reason": m.reason,
                    "source": str(m.source),
                    "dest": str(target),
                    "size": m.size,
                }
            )
        except OSError as e:
            print(f"fail ({e})")
            failed += 1

    manifest = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "destination": str(dest),
        "copied": copied,
        "skipped": skipped,
        "failed": failed,
        "files": manifest_entries,
    }
    (dest / "manifest.json").write_text(json.dumps(manifest, indent=2) + "\n", encoding="utf-8")
    print(f"\nDone → {dest} (copied {copied})")
    return 1 if failed else 0


if __name__ == "__main__":
    raise SystemExit(main())
