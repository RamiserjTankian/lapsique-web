import { spawn, spawnSync } from 'node:child_process';
import { mkdtempSync, mkdirSync, readFileSync, rmSync, writeFileSync, copyFileSync } from 'node:fs';
import { createServer } from 'node:net';
import { basename, dirname, join, resolve } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import process from 'node:process';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, '..');
const svgDir = join(root, 'svg');
const pngDir = join(root, 'png');
const videoDir = join(root, 'video');
const previewDir = join(root, 'previews');
const fontDir = join(root, 'fonts');
const animationFile = join(root, 'animation', 'lapsique-logo-reveal.html');
const animationJobs = [
  {
    id: 'lapsique-logo-reveal',
    file: join(root, 'animation', 'lapsique-logo-reveal.html'),
    mp4: 'lapsique-logo-reveal-glass-bg-4k.mp4',
    mov: 'lapsique-logo-reveal-glass-bg-alpha.mov',
    webm: 'lapsique-logo-reveal-glass-bg-alpha.webm',
    startPreview: 'lapsique-logo-reveal-start.png',
    midPreview: 'lapsique-logo-reveal-mid.png',
    finalPreview: 'lapsique-logo-reveal-final.png',
    alpha: true,
  },
  {
    id: 'lapsique-logo-liquid-glass-glitch',
    file: join(root, 'animation', 'lapsique-logo-liquid-glass-glitch.html'),
    mp4: 'lapsique-logo-liquid-glass-glitch-4k.mp4',
    mov: 'lapsique-logo-liquid-glass-glitch-alpha.mov',
    webm: 'lapsique-logo-liquid-glass-glitch-alpha.webm',
    startPreview: 'lapsique-logo-liquid-glass-glitch-start.png',
    midPreview: 'lapsique-logo-liquid-glass-glitch-mid.png',
    finalPreview: 'lapsique-logo-liquid-glass-glitch-final.png',
    alpha: false,
  },
];

const chromePath = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const width = 1080;
const height = 1920;
const outputWidth = 2160;
const outputHeight = 3840;
const fps = 30;

const variants = [
  'lapsique-logo-black-transparent.svg',
  'lapsique-logo-black-on-white.svg',
  'lapsique-logo-white-transparent.svg',
  'lapsique-logo-gold-beige.svg',
  'lapsique-logo-white-gold-transparent.svg',
];

function ensureDirs() {
  [pngDir, videoDir, previewDir].forEach((dir) => mkdirSync(dir, { recursive: true }));
}

function fontCss({ embedded = false } = {}) {
  const syneSource = embedded
    ? `data:font/truetype;base64,${readFileSync(join(fontDir, 'Syne-ExtraBold.ttf')).toString('base64')}`
    : `${pathToFileURL(join(fontDir, 'Syne-ExtraBold.ttf')).href}`;
  const plexSource = embedded
    ? `data:font/truetype;base64,${readFileSync(join(fontDir, 'IBMPlexMono-SemiBold.ttf')).toString('base64')}`
    : `${pathToFileURL(join(fontDir, 'IBMPlexMono-SemiBold.ttf')).href}`;

  return `@font-face{font-family:'Syne';src:url('${syneSource}') format('truetype');font-weight:800;font-style:normal;font-display:block;}
@font-face{font-family:'IBM Plex Mono';src:url('${plexSource}') format('truetype');font-weight:600;font-style:normal;font-display:block;}`;
}

function embedFontsInSvgFiles() {
  for (const variant of variants) {
    const svgPath = join(svgDir, variant);
    let svg = readFileSync(svgPath, 'utf8');
    svg = svg.replace(/\s*<style id="lapsique-logo-fonts">[\s\S]*?<\/style>/, '');
    svg = svg.replace(
      /(<svg[^>]*>)/,
      `$1\n  <style id="lapsique-logo-fonts">${fontCss({ embedded: true })}</style>`,
    );
    writeFileSync(svgPath, svg);
  }
}

function getFreePort() {
  return new Promise((resolvePort, reject) => {
    const server = createServer();
    server.once('error', reject);
    server.listen(0, () => {
      const { port } = server.address();
      server.close(() => resolvePort(port));
    });
  });
}

async function waitForJson(url, timeoutMs = 10000) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    try {
      const response = await fetch(url);
      if (response.ok) {
        return await response.json();
      }
    } catch {
      await new Promise((resolveWait) => setTimeout(resolveWait, 80));
    }
  }
  throw new Error(`Timed out waiting for ${url}`);
}

class CdpClient {
  constructor(wsUrl) {
    this.nextId = 1;
    this.pending = new Map();
    this.events = new Map();
    this.ws = new WebSocket(wsUrl);
  }

  async open() {
    await new Promise((resolveOpen, reject) => {
      this.ws.addEventListener('open', resolveOpen, { once: true });
      this.ws.addEventListener('error', reject, { once: true });
    });

    this.ws.addEventListener('message', (event) => {
      const message = JSON.parse(event.data);
      if (message.id && this.pending.has(message.id)) {
        const { resolveMessage, rejectMessage } = this.pending.get(message.id);
        this.pending.delete(message.id);
        if (message.error) {
          rejectMessage(new Error(message.error.message));
        } else {
          resolveMessage(message.result ?? {});
        }
        return;
      }

      const listeners = this.events.get(message.method) ?? [];
      listeners.forEach((listener) => listener(message.params ?? {}));
    });
  }

  send(method, params = {}) {
    const id = this.nextId++;
    this.ws.send(JSON.stringify({ id, method, params }));
    return new Promise((resolveMessage, rejectMessage) => {
      this.pending.set(id, { resolveMessage, rejectMessage });
    });
  }

  once(method) {
    return new Promise((resolveEvent) => {
      const listeners = this.events.get(method) ?? [];
      listeners.push(resolveEvent);
      this.events.set(method, listeners);
    }).then((params) => {
      const listeners = this.events.get(method) ?? [];
      this.events.set(method, listeners.filter((listener) => listener !== params));
      return params;
    });
  }

  close() {
    this.ws.close();
  }
}

async function createPage(port) {
  const response = await fetch(`http://127.0.0.1:${port}/json/new?about:blank`, { method: 'PUT' });
  if (!response.ok) {
    throw new Error(`Could not create Chrome target: ${response.status}`);
  }
  const target = await response.json();
  const client = new CdpClient(target.webSocketDebuggerUrl);
  await client.open();
  await client.send('Page.enable');
  await client.send('Runtime.enable');
  return client;
}

async function navigate(client, url, viewportWidth, viewportHeight, transparent = true) {
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: viewportWidth,
    height: viewportHeight,
    deviceScaleFactor: 1,
    mobile: false,
  });

  await client.send('Emulation.setDefaultBackgroundColorOverride', {
    color: transparent ? { r: 0, g: 0, b: 0, a: 0 } : { r: 255, g: 255, b: 255, a: 1 },
  });

  const loaded = client.once('Page.loadEventFired');
  await client.send('Page.navigate', { url });
  await loaded;
  await client.send('Runtime.evaluate', {
    expression: 'document.fonts ? document.fonts.ready : Promise.resolve()',
    awaitPromise: true,
  });
  await client.send('Runtime.evaluate', {
    expression: `new Promise((resolve, reject) => {
      const deadline = Date.now() + 10000;
      const check = () => {
        const syneReady = document.fonts && document.fonts.check('800 36px Syne');
        const plexReady = document.fonts && document.fonts.check('600 22px "IBM Plex Mono"');
        if (syneReady && plexReady) {
          resolve(true);
          return;
        }
        if (Date.now() > deadline) {
          reject(new Error('Logo fonts did not load before export'));
          return;
        }
        setTimeout(check, 50);
      };
      check();
    })`,
    awaitPromise: true,
  });
}

async function captureScreenshot(client, outFile, clip = null) {
  const result = await client.send('Page.captureScreenshot', {
    format: 'png',
    fromSurface: true,
    captureBeyondViewport: false,
    ...(clip ? { clip } : {}),
  });
  writeFileSync(outFile, Buffer.from(result.data, 'base64'));
}

function svgPreviewHtml(svgContent, viewportWidth) {
  return `<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    ${fontCss()}

    html, body {
      margin: 0;
      width: ${viewportWidth}px;
      background: transparent;
      overflow: hidden;
    }

    svg {
      display: block;
      width: ${viewportWidth}px;
      height: auto;
    }
  </style>
</head>
<body>${svgContent}</body>
</html>`;
}

async function exportPngVariants(client) {
  const pngWidth = 2160;

  for (const variant of variants) {
    const svgPath = join(svgDir, variant);
    const svg = readFileSync(svgPath, 'utf8');
    const viewBox = svg.match(/viewBox="([\d.]+) ([\d.]+) ([\d.]+) ([\d.]+)"/);
    const viewBoxWidth = viewBox ? Number(viewBox[3]) : 292;
    const viewBoxHeight = viewBox ? Number(viewBox[4]) : 104;
    const pngHeight = Math.round(pngWidth * (viewBoxHeight / viewBoxWidth));
    const tempHtml = join(process.cwd(), `.tmp-${variant}.html`);
    writeFileSync(tempHtml, svgPreviewHtml(svg, pngWidth));
    const url = pathToFileURL(tempHtml).href;
    await navigate(client, url, pngWidth, pngHeight, true);
    await captureScreenshot(
      client,
      join(pngDir, variant.replace(/\.svg$/, '.png')),
      { x: 0, y: 0, width: pngWidth, height: pngHeight, scale: 1 },
    );
    rmSync(tempHtml, { force: true });
  }
}

async function exportAnimationFrames(client, framesDir, job) {
  const url = `${pathToFileURL(job.file).href}?manual=1&bg=transparent`;
  await navigate(client, url, width, height, true);
  await client.send('Runtime.evaluate', {
    expression: 'new Promise((resolve) => { const check = () => window.logoReady ? resolve(true) : setTimeout(check, 20); check(); })',
    awaitPromise: true,
  });
  const { result: durationResult } = await client.send('Runtime.evaluate', {
    expression: 'window.logoDuration',
    returnByValue: true,
  });
  const durationMs = Number(durationResult.value);
  const frameCount = Math.ceil((durationMs / 1000) * fps);

  for (let frame = 0; frame < frameCount; frame += 1) {
    const timeMs = Math.round((frame / (frameCount - 1)) * durationMs);
    await client.send('Runtime.evaluate', {
      expression: `window.renderAt(${timeMs})`,
      awaitPromise: true,
    });
    await captureScreenshot(client, join(framesDir, `frame-${String(frame).padStart(4, '0')}.png`));
  }

  copyFileSync(join(framesDir, 'frame-0000.png'), join(previewDir, job.startPreview));
  copyFileSync(join(framesDir, `frame-${String(Math.floor(frameCount / 2)).padStart(4, '0')}.png`), join(previewDir, job.midPreview));
  copyFileSync(join(framesDir, `frame-${String(frameCount - 1).padStart(4, '0')}.png`), join(previewDir, job.finalPreview));

  return durationMs;
}

function runFfmpeg(args) {
  const result = spawnSync('ffmpeg', ['-y', '-hide_banner', '-loglevel', 'error', ...args], {
    stdio: 'inherit',
  });
  if (result.status !== 0) {
    throw new Error(`ffmpeg failed with status ${result.status}`);
  }
}

function waitForProcessExit(child, timeoutMs = 2500) {
  if (child.exitCode !== null || child.signalCode !== null) {
    return Promise.resolve();
  }

  return new Promise((resolveWait) => {
    const timeout = setTimeout(resolveWait, timeoutMs);
    child.once('exit', () => {
      clearTimeout(timeout);
      resolveWait();
    });
  });
}

function exportVideos(framesDir, job, durationMs) {
  const input = join(framesDir, 'frame-%04d.png');

  if (job.alpha) {
    runFfmpeg([
      '-framerate', String(fps),
      '-i', input,
      '-vf', `scale=${outputWidth}:${outputHeight}:flags=lanczos`,
      '-c:v', 'prores_ks',
      '-profile:v', '4444',
      '-pix_fmt', 'yuva444p10le',
      join(videoDir, job.mov),
    ]);

    runFfmpeg([
      '-framerate', String(fps),
      '-i', input,
      '-vf', `scale=${outputWidth}:${outputHeight}:flags=lanczos`,
      '-c:v', 'libvpx-vp9',
      '-pix_fmt', 'yuva420p',
      '-auto-alt-ref', '0',
      '-b:v', '0',
      '-crf', '24',
      join(videoDir, job.webm),
    ]);
  }

  runFfmpeg([
    '-framerate', String(fps),
    '-i', input,
    '-vf', `scale=${outputWidth}:${outputHeight}:flags=lanczos,format=yuv420p`,
    '-c:v', 'libx264',
    '-preset', 'slow',
    '-b:v', '34M',
    '-minrate', '34M',
    '-maxrate', '34M',
    '-bufsize', '68M',
    '-x264-params', 'nal-hrd=cbr:force-cfr=1',
    '-movflags', '+faststart',
    '-pix_fmt', 'yuv420p',
    '-r', String(fps),
    join(videoDir, job.mp4),
  ]);
}

async function main() {
  ensureDirs();
  embedFontsInSvgFiles();

  const port = await getFreePort();
  const userDataDir = mkdtempSync(join(process.env.TMPDIR || '/tmp', 'lapsique-logo-chrome-'));
  const framesDir = mkdtempSync(join(process.env.TMPDIR || '/tmp', 'lapsique-logo-frames-'));
  const chrome = spawn(chromePath, [
    '--headless=new',
    '--disable-gpu',
    '--no-first-run',
    '--no-default-browser-check',
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${userDataDir}`,
    'about:blank',
  ], { stdio: 'ignore' });

  try {
    await waitForJson(`http://127.0.0.1:${port}/json/version`);
    const client = await createPage(port);
    await exportPngVariants(client);
    const requestedJobs = new Set(process.argv.slice(2));
    const jobs = requestedJobs.size
      ? animationJobs.filter((job) => requestedJobs.has(job.id))
      : animationJobs;
    for (const job of jobs) {
      const jobFramesDir = join(framesDir, job.id);
      mkdirSync(jobFramesDir, { recursive: true });
      const durationMs = await exportAnimationFrames(client, jobFramesDir, job);
      exportVideos(jobFramesDir, job, durationMs);
    }
    client.close();
  } finally {
    chrome.kill('SIGTERM');
    await waitForProcessExit(chrome);
    rmSync(userDataDir, { recursive: true, force: true, maxRetries: 5, retryDelay: 100 });
    rmSync(framesDir, { recursive: true, force: true });
  }

  console.log('Lapsique logo assets exported.');
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
