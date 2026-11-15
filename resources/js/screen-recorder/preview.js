import { mapOutputTimeToSource, timelineDuration } from "./state.js";
import { getProjectFileUrl } from "./storage.js";

export class PreviewController {
  constructor({ canvas, screenVideo, cameraVideo, microphoneAudio, onTimeUpdate, onEnded }) {
    this.canvas = canvas;
    this.context = canvas.getContext("2d", { alpha: false });
    this.screenVideo = screenVideo;
    this.cameraVideo = cameraVideo;
    this.microphoneAudio = microphoneAudio;
    this.onTimeUpdate = onTimeUpdate;
    this.onEnded = onEnded;
    this.urls = [];
    this.manifest = null;
    this.outputTime = 0;
    this.playing = false;
    this.startedAt = 0;
    this.startedOutputTime = 0;
    this.lastSegmentId = null;
    this.selectedOverlayId = null;
    this.frame = null;
    this.blurCanvas = document.createElement("canvas");
  }

  async load(manifest) {
    this.destroySources();
    this.manifest = manifest;
    const screenUrl = await getProjectFileUrl(manifest.id, manifest.sources.screen.fileName);
    this.urls.push(screenUrl);
    this.screenVideo.src = screenUrl;
    this.screenVideo.preload = "auto";

    if (manifest.sources.camera?.fileName) {
      const cameraUrl = await getProjectFileUrl(manifest.id, manifest.sources.camera.fileName);
      this.urls.push(cameraUrl);
      this.cameraVideo.src = cameraUrl;
      this.cameraVideo.preload = "auto";
    } else {
      this.cameraVideo.removeAttribute("src");
    }

    if (manifest.sources.microphone?.fileName) {
      const microphoneUrl = await getProjectFileUrl(manifest.id, manifest.sources.microphone.fileName);
      this.urls.push(microphoneUrl);
      this.microphoneAudio.src = microphoneUrl;
      this.microphoneAudio.preload = "auto";
    } else {
      this.microphoneAudio.removeAttribute("src");
    }

    await Promise.all([
      waitForMetadata(this.screenVideo, manifest.duration),
      this.cameraVideo.src ? waitForMetadata(this.cameraVideo, manifest.duration) : Promise.resolve(),
      this.microphoneAudio.src ? waitForMetadata(this.microphoneAudio, manifest.duration) : Promise.resolve()
    ]);
    this.resizeCanvas();
    await this.seek(0);
    this.startRenderLoop();
  }

  setManifest(manifest) {
    this.manifest = manifest;
    this.outputTime = Math.min(this.outputTime, timelineDuration(manifest));
    this.syncVolumes();
    this.render();
  }

  setSelectedOverlay(overlayId) {
    this.selectedOverlayId = overlayId;
    this.render();
  }

  async play() {
    if (!this.manifest || !timelineDuration(this.manifest)) return;

    if (this.outputTime >= timelineDuration(this.manifest) - 0.03) await this.seek(0);

    this.playing = true;
    this.startedAt = performance.now();
    this.startedOutputTime = this.outputTime;
    await this.syncMediaToOutputTime(true);
  }

  pause() {
    this.playing = false;
    this.screenVideo.pause();
    this.cameraVideo.pause();
    this.microphoneAudio.pause();
  }

  async toggle() {
    if (this.playing) {
      this.pause();
      return false;
    }

    await this.play();
    return true;
  }

  async seek(outputTime) {
    this.pause();
    this.outputTime = Math.max(0, Math.min(Number(outputTime) || 0, timelineDuration(this.manifest)));
    await this.syncMediaToOutputTime(false);
    this.onTimeUpdate?.(this.outputTime);
    this.render();
  }

  destroy() {
    this.pause();
    cancelAnimationFrame(this.frame);
    this.destroySources();
  }

  resizeCanvas() {
    const width = Math.max(2, Math.min(1920, this.screenVideo.videoWidth || 1280));
    const height = Math.max(2, Math.min(1080, this.screenVideo.videoHeight || 720));

    this.canvas.width = width;
    this.canvas.height = height;
  }

  startRenderLoop() {
    cancelAnimationFrame(this.frame);

    const update = async () => {
      if (this.playing) {
        this.outputTime = this.startedOutputTime + (performance.now() - this.startedAt) / 1000;
        const total = timelineDuration(this.manifest);

        if (this.outputTime >= total) {
          this.outputTime = total;
          this.pause();
          this.onEnded?.();
        } else {
          await this.syncMediaToOutputTime(true);
        }

        this.onTimeUpdate?.(this.outputTime);
      }

      this.render();
      this.frame = requestAnimationFrame(update);
    };

    this.frame = requestAnimationFrame(update);
  }

  async syncMediaToOutputTime(shouldPlay) {
    const mapping = mapOutputTimeToSource(this.manifest, this.outputTime);

    if (!mapping) return;

    const mustSeek = mapping.segment.id !== this.lastSegmentId
      || Math.abs(this.screenVideo.currentTime - mapping.sourceTime) > 0.22;

    if (mustSeek) {
      await Promise.all([
        seekMedia(this.screenVideo, mapping.sourceTime),
        this.cameraVideo.src ? seekMedia(this.cameraVideo, mapping.sourceTime) : Promise.resolve(),
        this.microphoneAudio.src ? seekMedia(this.microphoneAudio, mapping.sourceTime) : Promise.resolve()
      ]);
      this.lastSegmentId = mapping.segment.id;
    }

    this.syncVolumes();

    if (shouldPlay) {
      await Promise.all([
        this.screenVideo.play().catch(() => {}),
        this.cameraVideo.src ? this.cameraVideo.play().catch(() => {}) : Promise.resolve(),
        this.microphoneAudio.src ? this.microphoneAudio.play().catch(() => {}) : Promise.resolve()
      ]);
    }
  }

  syncVolumes() {
    if (!this.manifest) return;

    const audio = this.manifest.timeline.audio;
    this.screenVideo.muted = Boolean(audio.systemMuted);
    this.screenVideo.volume = Math.min(1, Math.max(0, Number(audio.systemGain) || 0));
    this.microphoneAudio.muted = Boolean(audio.microphoneMuted);
    this.microphoneAudio.volume = Math.min(1, Math.max(0, Number(audio.microphoneGain) || 0));
  }

  render() {
    if (!this.manifest || !this.screenVideo.videoWidth) return;

    const { context, canvas } = this;
    context.fillStyle = "#050816";
    context.fillRect(0, 0, canvas.width, canvas.height);
    drawScreen(context, this.screenVideo, this.manifest.timeline.crop, canvas);

    if (this.cameraVideo.videoWidth && this.manifest.timeline.camera.visible) {
      drawCamera(context, this.cameraVideo, this.manifest.timeline.camera, canvas);
    }

    drawOverlays(
      context,
      canvas,
      this.blurCanvas,
      this.manifest.timeline.overlays,
      this.outputTime,
      this.selectedOverlayId
    );
  }

  destroySources() {
    [this.screenVideo, this.cameraVideo, this.microphoneAudio].forEach((media) => {
      media.pause();
      media.removeAttribute("src");
      media.load();
    });
    this.urls.forEach((url) => URL.revokeObjectURL(url));
    this.urls = [];
  }
}

export async function generateThumbnails(sourceUrl, duration, count = 12) {
  const video = document.createElement("video");
  const canvas = document.createElement("canvas");
  const context = canvas.getContext("2d");
  const thumbnails = [];

  video.src = sourceUrl;
  video.muted = true;
  video.preload = "auto";
  await waitForMetadata(video, duration);
  canvas.width = 160;
  canvas.height = 90;

  for (let index = 0; index < count; index += 1) {
    const time = duration * (index / Math.max(1, count - 1));
    await seekMedia(video, Math.min(time, Math.max(0, video.duration - 0.05)));
    context.fillStyle = "#0f172a";
    context.fillRect(0, 0, canvas.width, canvas.height);
    drawContainedImage(context, video, canvas.width, canvas.height);
    thumbnails.push({ time, dataUrl: canvas.toDataURL("image/jpeg", 0.58) });
  }

  video.removeAttribute("src");
  video.load();

  return thumbnails;
}

export function hitTest(manifest, outputTime, x, y) {
  const overlays = [...manifest.timeline.overlays].reverse();

  for (const overlay of overlays) {
    if (outputTime >= overlay.start && outputTime <= overlay.end && contains(overlay, x, y)) {
      return { type: "overlay", id: overlay.id, rectangle: overlay };
    }
  }

  if (manifest.sources.camera && manifest.timeline.camera.visible && contains(manifest.timeline.camera, x, y)) {
    return { type: "camera", id: "camera", rectangle: manifest.timeline.camera };
  }

  return null;
}

function drawScreen(context, video, crop, canvas) {
  const sourceX = video.videoWidth * crop.x;
  const sourceY = video.videoHeight * crop.y;
  const sourceWidth = video.videoWidth * crop.width;
  const sourceHeight = video.videoHeight * crop.height;
  const fit = contain(sourceWidth, sourceHeight, canvas.width, canvas.height);

  context.drawImage(video, sourceX, sourceY, sourceWidth, sourceHeight, fit.x, fit.y, fit.width, fit.height);
}

function drawCamera(context, video, camera, canvas) {
  const rectangle = pixelRectangle(canvas, camera);
  const crop = cover(video.videoWidth, video.videoHeight, rectangle.width, rectangle.height);

  context.save();
  roundedPath(context, rectangle, camera.shape);
  context.clip();
  context.translate(camera.mirror ? rectangle.x + rectangle.width : rectangle.x, rectangle.y);
  context.scale(camera.mirror ? -1 : 1, 1);
  context.drawImage(video, crop.x, crop.y, crop.width, crop.height, 0, 0, rectangle.width, rectangle.height);
  context.restore();
  context.save();
  roundedPath(context, rectangle, camera.shape);
  context.strokeStyle = "rgba(255,255,255,.92)";
  context.lineWidth = Math.max(2, canvas.width / 420);
  context.stroke();
  context.restore();
}

function drawOverlays(context, canvas, blurCanvas, overlays, outputTime, selectedId) {
  overlays.forEach((overlay) => {
    if (outputTime < overlay.start || outputTime > overlay.end) return;

    const rectangle = pixelRectangle(canvas, overlay);

    if (overlay.type === "blur") {
      blurCanvas.width = Math.max(2, rectangle.width);
      blurCanvas.height = Math.max(2, rectangle.height);
      const blurContext = blurCanvas.getContext("2d");
      blurContext.drawImage(canvas, rectangle.x, rectangle.y, rectangle.width, rectangle.height, 0, 0, rectangle.width, rectangle.height);
      context.save();
      context.filter = `blur(${overlay.blur}px)`;
      context.drawImage(blurCanvas, rectangle.x, rectangle.y, rectangle.width, rectangle.height);
      context.restore();
    }

    if (overlay.type === "text") {
      context.save();
      context.fillStyle = overlay.background;
      context.globalAlpha = 0.92;
      context.beginPath();
      context.roundRect(rectangle.x, rectangle.y, rectangle.width, rectangle.height, Math.max(8, rectangle.height * 0.12));
      context.fill();
      context.globalAlpha = 1;
      context.fillStyle = overlay.color;
      context.font = `800 ${Math.max(16, overlay.fontSize * canvas.width / 1280)}px Tajawal, sans-serif`;
      context.textAlign = "center";
      context.textBaseline = "middle";
      context.direction = "rtl";
      context.fillText(overlay.text, rectangle.x + rectangle.width / 2, rectangle.y + rectangle.height / 2, rectangle.width - 20);
      context.restore();
    }

    if (overlay.type === "arrow") drawArrow(context, rectangle, overlay.background, overlay.direction, canvas.width);

    if (overlay.id === selectedId) {
      context.save();
      context.strokeStyle = "#fbbf24";
      context.lineWidth = Math.max(2, canvas.width / 500);
      context.setLineDash([10, 8]);
      context.strokeRect(rectangle.x, rectangle.y, rectangle.width, rectangle.height);
      context.restore();
    }
  });
}

function drawArrow(context, rectangle, color, direction, canvasWidth) {
  const centerX = rectangle.x + rectangle.width / 2;
  const centerY = rectangle.y + rectangle.height / 2;
  const pair = {
    right: [{ x: rectangle.x, y: centerY }, { x: rectangle.x + rectangle.width, y: centerY }],
    left: [{ x: rectangle.x + rectangle.width, y: centerY }, { x: rectangle.x, y: centerY }],
    up: [{ x: centerX, y: rectangle.y + rectangle.height }, { x: centerX, y: rectangle.y }],
    down: [{ x: centerX, y: rectangle.y }, { x: centerX, y: rectangle.y + rectangle.height }]
  }[direction];
  const [start, end] = pair;
  const angle = Math.atan2(end.y - start.y, end.x - start.x);
  const head = Math.max(14, Math.min(rectangle.width, rectangle.height) * 0.3);

  context.save();
  context.strokeStyle = color;
  context.fillStyle = color;
  context.lineWidth = Math.max(5, canvasWidth / 260);
  context.lineCap = "round";
  context.beginPath();
  context.moveTo(start.x, start.y);
  context.lineTo(end.x, end.y);
  context.stroke();
  context.beginPath();
  context.moveTo(end.x, end.y);
  context.lineTo(end.x - head * Math.cos(angle - Math.PI / 6), end.y - head * Math.sin(angle - Math.PI / 6));
  context.lineTo(end.x - head * Math.cos(angle + Math.PI / 6), end.y - head * Math.sin(angle + Math.PI / 6));
  context.closePath();
  context.fill();
  context.restore();
}

function roundedPath(context, rectangle, shape) {
  context.beginPath();

  if (shape === "circle") {
    context.ellipse(
      rectangle.x + rectangle.width / 2,
      rectangle.y + rectangle.height / 2,
      rectangle.width / 2,
      rectangle.height / 2,
      0,
      0,
      Math.PI * 2
    );
  } else {
    context.roundRect(
      rectangle.x,
      rectangle.y,
      rectangle.width,
      rectangle.height,
      shape === "square" ? 0 : Math.min(rectangle.width, rectangle.height) * 0.12
    );
  }
}

function drawContainedImage(context, image, width, height) {
  const fit = contain(image.videoWidth, image.videoHeight, width, height);
  context.drawImage(image, fit.x, fit.y, fit.width, fit.height);
}

function contain(sourceWidth, sourceHeight, boxWidth, boxHeight) {
  const scale = Math.min(boxWidth / sourceWidth, boxHeight / sourceHeight);
  const width = sourceWidth * scale;
  const height = sourceHeight * scale;

  return { x: (boxWidth - width) / 2, y: (boxHeight - height) / 2, width, height };
}

function cover(sourceWidth, sourceHeight, boxWidth, boxHeight) {
  const scale = Math.max(boxWidth / sourceWidth, boxHeight / sourceHeight);
  const width = boxWidth / scale;
  const height = boxHeight / scale;

  return { x: (sourceWidth - width) / 2, y: (sourceHeight - height) / 2, width, height };
}

function pixelRectangle(canvas, rectangle) {
  return {
    x: rectangle.x * canvas.width,
    y: rectangle.y * canvas.height,
    width: rectangle.width * canvas.width,
    height: rectangle.height * canvas.height
  };
}

function contains(rectangle, x, y) {
  return x >= rectangle.x && y >= rectangle.y
    && x <= rectangle.x + rectangle.width
    && y <= rectangle.y + rectangle.height;
}

async function waitForMetadata(media, expectedDuration = 0) {
  if (media.readyState < 1) {
    await new Promise((resolve, reject) => {
      media.addEventListener("loadedmetadata", resolve, { once: true });
      media.addEventListener("error", () => reject(new Error("تعذر قراءة ملف التسجيل المحلي.")), { once: true });
    });
  }

  if (!Number.isFinite(media.duration)) {
    await seekMedia(media, Math.max(86_400, Number(expectedDuration) || 0));
    await seekMedia(media, 0);
  }
}

function seekMedia(media, time) {
  if (!media.src) return Promise.resolve();

  const target = Number.isFinite(media.duration)
    ? Math.max(0, Math.min(time, Math.max(0, media.duration - 0.001)))
    : Math.max(0, Number(time) || 0);

  if (Math.abs(media.currentTime - target) < 0.015) return Promise.resolve();

  return new Promise((resolve) => {
    const finish = () => {
      window.clearTimeout(timeout);
      media.removeEventListener("seeked", finish);
      media.removeEventListener("error", finish);
      resolve();
    };
    const timeout = window.setTimeout(finish, 1500);

    media.addEventListener("seeked", finish, { once: true });
    media.addEventListener("error", finish, { once: true });

    try {
      media.currentTime = target;
    } catch (_) {
      finish();
    }
  });
}
