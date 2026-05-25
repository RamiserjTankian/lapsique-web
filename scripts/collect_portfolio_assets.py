#!/usr/bin/env python3
"""
Collect portfolio-ready photos and videos.

Photos are sourced from rendered JPG/JPEG files in edited-photo folders only.
RAW files such as .ARW are ignored. Selected photos are resized and compressed
to public/images/portfolio/photos, while video entries are read from the existing
public/videos/reels/manifest.json and get lightweight poster frames.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import math
import os
import re
import subprocess
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path

from PIL import Image, ImageFilter, ImageOps, ImageStat


DEFAULT_ROOTS = (
    Path("/Users/redasoft/Documents/PROYECTOS"),
    Path("/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@lapsique.media/My Drive/1.- FOTOS EDITADAS"),
    Path("/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@lapsique.media/My Drive/LAPSIQUE.MEDIA/2.- CONTENIDO"),
    Path("/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@bluepointrs.com/My Drive/1.- FOTOS EDITADAS"),
    Path("/Users/redasoft/Library/CloudStorage/GoogleDrive-ramiro@bluepointrs.com/My Drive/PHOTOS/PORTAFOLIO"),
)

PHOTO_DEST = Path("public/images/portfolio/photos")
POSTER_DEST = Path("public/images/portfolio/video-posters")
MANIFEST_PATH = Path("database/data/portfolio_assets.json")
REELS_MANIFEST = Path("public/videos/reels/manifest.json")

IMAGE_EXT = {".jpg", ".jpeg"}
RAW_EXT = {".arw", ".raw", ".dng", ".cr2", ".cr3", ".nef", ".raf"}
MAX_DIR_CHILDREN = 900

EDITED_MARKERS = (
    "fotos editadas",
    "foto editadas",
    "edited photos",
    "portafolio",
    "portfolio",
)

SKIP_PATH_MARKERS = (
    "/raw/",
    "/arw/",
    "/sony log clips/",
    "/clips/",
    "/recover/",
    "/recovers/",
    "/node_modules/",
    "/.trash/",
)

CAMERA_PREFIX = re.compile(r"^(DSC\d+|IMG_\d{4,}|DJI_|C\d{3,5}|MVI_\d+)", re.I)


@dataclass
class PhotoCandidate:
    source: Path
    project: str
    width: int
    height: int
    size: int
    sharpness: float
    score: float

    @property
    def orientation(self) -> str:
        if self.height > self.width * 1.12:
            return "vertical"
        if self.width > self.height * 1.12:
            return "horizontal"
        return "square"


def is_edited_photo_path(path: Path) -> bool:
    lower = str(path).lower()
    if any(marker in lower for marker in SKIP_PATH_MARKERS):
        return False
    if path.suffix.lower() in RAW_EXT:
        return False
    return path.suffix.lower() in IMAGE_EXT and any(marker in lower for marker in EDITED_MARKERS)


def project_name(path: Path) -> str:
    parts = path.parts
    for i, part in enumerate(parts):
        lower = part.lower()
        if any(marker in lower for marker in EDITED_MARKERS):
            if i > 0:
                return clean_title(parts[i - 1])
            return clean_title(part)

    for marker in ("PROYECTOS", "LAPSIQUE.MEDIA", "PHOTOS"):
        if marker in parts:
            idx = parts.index(marker)
            if idx + 1 < len(parts):
                return clean_title(parts[idx + 1])

    return clean_title(path.parent.name)


def clean_title(value: str) -> str:
    value = re.sub(r"^\d+\s*(?:[.-]\s*)+", "", value.strip())
    value = value.replace("_", " ").replace("-", " ")
    value = re.sub(r"\s+", " ", value)
    return value.title() or "Proyecto"


def safe_slug(value: str) -> str:
    slug = re.sub(r"[^a-z0-9]+", "-", value.lower()).strip("-")
    return slug or "portfolio"


def skip_heavy_dir(path: Path) -> bool:
    try:
        return sum(1 for _ in path.iterdir()) > MAX_DIR_CHILDREN
    except OSError:
        return True


def measure_sharpness(image: Image.Image) -> float:
    sample = ImageOps.grayscale(image)
    sample.thumbnail((360, 360))
    edges = sample.filter(ImageFilter.FIND_EDGES)
    stat = ImageStat.Stat(edges)
    return float(stat.var[0] if stat.var else 0.0)


def score_image(path: Path) -> PhotoCandidate | None:
    try:
        size = path.stat().st_size
        if size < 250_000:
            return None
        with Image.open(path) as img:
            img.verify()
        with Image.open(path) as img:
            img = ImageOps.exif_transpose(img)
            width, height = img.size
            if width < 1200 or height < 900:
                return None
            sharpness = measure_sharpness(img)
    except Exception:
        return None

    megapixels = (width * height) / 1_000_000
    size_mb = size / 1024 / 1024
    name_bonus = 0 if CAMERA_PREFIX.match(path.name) else 8
    score = (math.log1p(megapixels) * 20) + (min(sharpness, 900) * 0.08) + min(size_mb, 18) + name_bonus

    return PhotoCandidate(
        source=path,
        project=project_name(path),
        width=width,
        height=height,
        size=size,
        sharpness=sharpness,
        score=score,
    )


def scan_photos(roots: list[Path], progress_every: int) -> list[PhotoCandidate]:
    candidates: list[PhotoCandidate] = []
    seen: set[str] = set()
    visited = 0

    for root in roots:
        if not root.is_dir():
            print(f"skip missing: {root}", flush=True)
            continue
        print(f"Scanning photos: {root}", flush=True)
        for dirpath, dirnames, filenames in os.walk(root, topdown=True):
            current = Path(dirpath)
            lower_current = str(current).lower()
            dirnames[:] = [
                d for d in dirnames
                if not d.startswith(".")
                and not any(marker in f"{lower_current}/{d.lower()}/" for marker in SKIP_PATH_MARKERS)
                and not skip_heavy_dir(current / d)
            ]
            in_relevant_tree = any(marker in lower_current for marker in EDITED_MARKERS)
            if not in_relevant_tree and root.name.lower() not in {"proyectos", "my drive"}:
                continue
            for fname in filenames:
                visited += 1
                if progress_every and visited % progress_every == 0:
                    print(f"  visited {visited} files, kept {len(candidates)} candidates", flush=True)
                path = current / fname
                if not is_edited_photo_path(path):
                    continue
                digest = hashlib.md5(str(path).encode()).hexdigest()
                if digest in seen:
                    continue
                seen.add(digest)
                hit = score_image(path)
                if hit:
                    candidates.append(hit)

    return candidates


def select_photos(candidates: list[PhotoCandidate], limit: int) -> list[PhotoCandidate]:
    by_project: dict[str, list[PhotoCandidate]] = {}
    for candidate in sorted(candidates, key=lambda c: c.score, reverse=True):
        by_project.setdefault(candidate.project, []).append(candidate)

    selected: list[PhotoCandidate] = []
    selected_paths: set[Path] = set()

    for per_project in (2, 4, 8):
        for project in sorted(by_project):
            for candidate in by_project[project][:per_project]:
                if candidate.source in selected_paths:
                    continue
                selected.append(candidate)
                selected_paths.add(candidate.source)
                if len(selected) >= limit:
                    return selected

    for candidate in sorted(candidates, key=lambda c: c.score, reverse=True):
        if candidate.source in selected_paths:
            continue
        selected.append(candidate)
        selected_paths.add(candidate.source)
        if len(selected) >= limit:
            break

    return selected


def compress_photo(candidate: PhotoCandidate, dest_dir: Path, index: int, force: bool) -> dict:
    digest = hashlib.md5(str(candidate.source).encode()).hexdigest()[:10]
    stem = f"{index:03d}-{safe_slug(candidate.project)}-{digest}"
    output = dest_dir / f"{stem}.webp"
    dest_dir.mkdir(parents=True, exist_ok=True)

    if force or not output.exists():
        with Image.open(candidate.source) as img:
            img = ImageOps.exif_transpose(img).convert("RGB")
            img.thumbnail((1800, 1800), Image.Resampling.LANCZOS)
            img.save(output, "WEBP", quality=82, method=6)

    return {
        "kind": "photo",
        "title": candidate.project,
        "caption": f"Fotografía editada de {candidate.project}.",
        "project": candidate.project,
        "src": "/" + str(output.relative_to(Path("public"))),
        "source": str(candidate.source),
        "width": candidate.width,
        "height": candidate.height,
        "orientation": candidate.orientation,
        "score": round(candidate.score, 2),
        "tags": ["foto", candidate.project],
    }


def load_video_entries(force_posters: bool) -> list[dict]:
    if not REELS_MANIFEST.is_file():
        return []

    data = json.loads(REELS_MANIFEST.read_text(encoding="utf-8"))
    entries = data.get("entries", [])
    POSTER_DEST.mkdir(parents=True, exist_ok=True)
    resolved: list[dict] = []

    for entry in entries:
        src = entry.get("src")
        title = entry.get("title") or "Video"
        if not src:
            continue
        video_path = Path("public") / src.lstrip("/")
        if not video_path.is_file():
            continue

        slug = safe_slug(Path(src).stem)
        poster = POSTER_DEST / f"{slug}.jpg"
        if force_posters or not poster.exists():
            subprocess.run(
                [
                    "ffmpeg",
                    "-hide_banner",
                    "-loglevel",
                    "error",
                    "-ss",
                    "00:00:01.000",
                    "-i",
                    str(video_path),
                    "-frames:v",
                    "1",
                    "-vf",
                    "scale='min(1280,iw)':-2",
                    "-q:v",
                    "4",
                    "-y",
                    str(poster),
                ],
                check=False,
            )

        lower = title.lower()
        kind_tag = "aftermovie" if "aftermovie" in lower or "after movie" in lower else "reel"
        resolved.append(
            {
                "kind": "video",
                "title": title,
                "caption": "Aftermovie" if kind_tag == "aftermovie" else "Reel vertical",
                "src": src,
                "poster": "/" + str(poster.relative_to(Path("public"))) if poster.exists() else None,
                "orientation": "vertical",
                "tags": ["video", kind_tag],
            }
        )

    return resolved


def write_manifest(photos: list[dict], videos: list[dict]) -> None:
    MANIFEST_PATH.parent.mkdir(parents=True, exist_ok=True)
    manifest = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "photo_count": len(photos),
        "video_count": len(videos),
        "photos": photos,
        "videos": videos,
    }
    MANIFEST_PATH.write_text(json.dumps(manifest, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser(description="Collect compressed public portfolio assets")
    parser.add_argument("--limit", type=int, default=120)
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--force", action="store_true")
    parser.add_argument("--progress-every", type=int, default=1000)
    parser.add_argument("--root", action="append", type=Path, dest="roots")
    args = parser.parse_args()

    roots = args.roots or list(DEFAULT_ROOTS)
    candidates = scan_photos(roots, args.progress_every)
    selected = select_photos(candidates, args.limit)

    print(f"\nPhoto candidates: {len(candidates)}")
    print(f"Selected photos: {len(selected)}")
    projects = sorted({item.project for item in selected})
    print(f"Projects represented: {len(projects)}")

    if args.dry_run:
        for candidate in selected[:40]:
            print(f"  {candidate.score:7.2f} {candidate.width}x{candidate.height} {candidate.project} :: {candidate.source.name}")
        return 0

    photo_entries = [
        compress_photo(candidate, PHOTO_DEST, index, args.force)
        for index, candidate in enumerate(selected, 1)
    ]
    video_entries = load_video_entries(args.force)
    write_manifest(photo_entries, video_entries)

    print(f"Compressed photos: {len(photo_entries)} -> {PHOTO_DEST}")
    print(f"Video entries: {len(video_entries)} -> {POSTER_DEST}")
    print(f"Manifest: {MANIFEST_PATH}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
