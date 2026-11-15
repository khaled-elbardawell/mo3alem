import {
  ALL_FORMATS,
  AudioSample,
  AudioSampleSink,
  AudioSampleSource,
  BlobSource,
  CanvasSource,
  Input,
  Mp4OutputFormat,
  Output,
  StreamTarget,
  VideoSampleSink,
  WebMOutputFormat
} from "mediabunny";
import { createReservedMp4TrackMetadata } from "./export-config.js";
import { mapOutputTimeToSource, timelineDuration } from "./state.js";

let canceled = false;
let activeOutput = null;

self.addEventListener("message", async (event) => {
  if (event.data?.type === "cancel") {
    canceled = true;
    await activeOutput?.cancel?.().catch(() => {});
    return;
  }

  if (event.data?.type !== "export") return;

  canceled = false;

  try {
    const result = await exportProject(event.data);
    self.postMessage({ type: "complete", ...result });
  } catch (error) {
    await activeOutput?.cancel?.().catch(() => {});
    self.postMessage({
      type: canceled ? "canceled" : "error",
      message: canceled ? "تم إلغاء التصدير." : readableError(error)
    });
  } finally {
    activeOutput = null;
  }
});

async function exportProject({ manifest, profile, outputFileName }) {
  const projectDirectory = await getProjectDirectory(manifest.id);
  const screenFile = await getSourceFile(projectDirectory, manifest.sources.screen);
  const cameraFile = await getSourceFile(projectDirectory, manifest.sources.camera, false);
  const microphoneFile = await getSourceFile(projectDirectory, manifest.sources.microphone, false);
  const screenInput = createInput(screenFile);
  const cameraInput = cameraFile ? createInput(cameraFile) : null;
  const microphoneInput = microphoneFile ? createInput(microphoneFile) : null;
  const screenVideoTrack = await screenInput.getPrimaryVideoTrack();

  if (!screenVideoTrack) throw new Error("ملف تسجيل الشاشة لا يحتوي مسار فيديو صالحًا.");

  const sourceWidth = await screenVideoTrack.getDisplayWidth();
  const sourceHeight = await screenVideoTrack.getDisplayHeight();
  const outputSize = fitOutputSize(sourceWidth, sourceHeight, manifest.exportPreset);
  const duration = timelineDuration(manifest);
  const frameRate = Math.min(30, Number(manifest.exportPreset?.frameRate) || 30);
  const trackMetadata = createReservedMp4TrackMetadata(duration, frameRate);
  const outputHandle = await projectDirectory.getFileHandle(outputFileName, { create: true });
  const writable = await outputHandle.createWritable();
  const output = new Output({
    format: profile.format === "mp4" ? new Mp4OutputFormat({ fastStart: "reserve" }) : new WebMOutputFormat(),
    target: new StreamTarget(writable, { chunked: true, chunkSize: 8 * 1024 * 1024 })
  });
  const canvas = new OffscreenCanvas(outputSize.width, outputSize.height);
  const context = canvas.getContext("2d", { alpha: false, desynchronized: true });
  const videoSource = new CanvasSource(canvas, {
    codec: profile.videoCodec,
    bitrate: 8_000_000,
    keyFrameInterval: 2
  });
  const screenAudioTrack = await screenInput.getPrimaryAudioTrack();
  const microphoneAudioTrack = microphoneInput ? await microphoneInput.getPrimaryAudioTrack() : null;
  const hasAudio = Boolean(screenAudioTrack || microphoneAudioTrack);
  const audioSource = hasAudio
    ? new AudioSampleSource({ codec: profile.audioCodec, bitrate: 192_000 })
    : null;

  output.addVideoTrack(videoSource, {
    name: "تسجيل الشاشة",
    ...(profile.format === "mp4" ? trackMetadata.video : {})
  });
  if (audioSource) {
    output.addAudioTrack(audioSource, {
      name: "الصوت المختلط",
      ...(profile.format === "mp4" ? trackMetadata.audio : {})
    });
  }
  output.setMetadataTags({ title: manifest.title, artist: "معلم" });
  activeOutput = output;

  const screenVideoSink = new VideoSampleSink(screenVideoTrack);
  const cameraTrack = cameraInput ? await cameraInput.getPrimaryVideoTrack() : null;
  const cameraVideoSink = cameraTrack ? new VideoSampleSink(cameraTrack) : null;
  const screenAudioSink = screenAudioTrack ? new AudioSampleSink(screenAudioTrack) : null;
  const microphoneAudioSink = microphoneAudioTrack ? new AudioSampleSink(microphoneAudioTrack) : null;
  await output.start();
  await Promise.all([
    encodeVideo({
      manifest,
      duration,
      canvas,
      context,
      videoSource,
      screenVideoSink,
      cameraVideoSink,
      outputSize
    }),
    audioSource ? encodeAudio({
      manifest,
      duration,
      audioSource,
      screenAudioSink,
      microphoneAudioSink
    }) : Promise.resolve()
  ]);
  assertNotCanceled();
  await output.finalize();

  screenInput.dispose();
  cameraInput?.dispose();
  microphoneInput?.dispose();

  const outputFile = await outputHandle.getFile();

  return {
    outputFileName,
    size: outputFile.size,
    duration,
    format: profile.format,
    mimeType: profile.mimeType,
    width: outputSize.width,
    height: outputSize.height
  };
}

async function encodeVideo({ manifest, duration, canvas, context, videoSource, screenVideoSink, cameraVideoSink, outputSize }) {
  const frameRate = Math.min(30, Number(manifest.exportPreset?.frameRate) || 30);
  const frameDuration = 1 / frameRate;
  const frameCount = Math.max(1, Math.ceil(duration * frameRate));
  const timestamps = sourceTimestamps(manifest, frameCount, frameDuration);
  const screenIterator = screenVideoSink.samplesAtTimestamps(timestamps);
  const cameraIterator = cameraVideoSink
    ? cameraVideoSink.samplesAtTimestamps(sourceTimestamps(manifest, frameCount, frameDuration))
    : null;
  const blurCanvas = new OffscreenCanvas(2, 2);

  for (let frameIndex = 0; frameIndex < frameCount; frameIndex += 1) {
    assertNotCanceled();

    const outputTime = frameIndex * frameDuration;
    const [screenResult, cameraResult] = await Promise.all([
      screenIterator.next(),
      cameraIterator ? cameraIterator.next() : Promise.resolve({ value: null })
    ]);
    const screenSample = screenResult.value;
    const cameraSample = cameraResult.value;

    context.fillStyle = "#050816";
    context.fillRect(0, 0, canvas.width, canvas.height);

    if (screenSample) {
      drawScreen(context, screenSample, manifest.timeline.crop, outputSize);
    }

    if (cameraSample && manifest.timeline.camera.visible) {
      drawCamera(context, cameraSample, manifest.timeline.camera, outputSize);
    }

    drawOverlays(context, canvas, blurCanvas, manifest.timeline.overlays, outputTime);
    await videoSource.add(outputTime, frameDuration, { keyFrame: frameIndex % (frameRate * 2) === 0 });
    screenSample?.close();
    cameraSample?.close();

    if (frameIndex % Math.max(1, Math.floor(frameCount / 200)) === 0) {
      self.postMessage({ type: "progress", progress: frameIndex / frameCount, stage: "video" });
    }
  }
}

async function encodeAudio({ manifest, duration, audioSource, screenAudioSink, microphoneAudioSink }) {
  const sampleRate = 48_000;
  const channels = 2;
  const maximumFrames = 2048;
  let outputTime = 0;

  while (outputTime < duration - 0.00001) {
    assertNotCanceled();

    const mapping = mapOutputTimeToSource(manifest, outputTime);

    if (!mapping) break;

    const segmentRemaining = mapping.segment.sourceEnd - mapping.sourceTime;
    const remainingDuration = duration - outputTime;
    const frameCount = Math.max(1, Math.min(
      maximumFrames,
      Math.floor(segmentRemaining * sampleRate),
      Math.ceil(remainingDuration * sampleRate)
    ));
    const screenFrames = screenAudioSink
      ? await readAudioFrames(screenAudioSink, mapping.sourceTime, frameCount, sampleRate, channels)
      : new Float32Array(frameCount * channels);
    const microphoneFrames = microphoneAudioSink
      ? await readAudioFrames(microphoneAudioSink, mapping.sourceTime, frameCount, sampleRate, channels)
      : new Float32Array(frameCount * channels);
    const mixed = mixAudio(
      screenFrames,
      microphoneFrames,
      manifest.timeline.audio.systemMuted ? 0 : manifest.timeline.audio.systemGain,
      manifest.timeline.audio.microphoneMuted ? 0 : manifest.timeline.audio.microphoneGain
    );
    const sample = new AudioSample({
      data: mixed,
      format: "f32",
      numberOfChannels: channels,
      sampleRate,
      timestamp: outputTime
    });

    await audioSource.add(sample);
    sample.close();
    outputTime += frameCount / sampleRate;

    if (Math.floor(outputTime * 10) % 20 === 0) {
      self.postMessage({ type: "audio-progress", progress: Math.min(1, outputTime / duration) });
    }
  }
}

async function readAudioFrames(sink, sourceTime, frameCount, outputSampleRate, outputChannels) {
  const output = new Float32Array(frameCount * outputChannels);
  let writtenFrames = 0;
  let cursorTime = sourceTime;
  let guard = 0;

  while (writtenFrames < frameCount && guard < 12) {
    guard += 1;
    const sample = await sink.getSample(cursorTime + 0.000001);

    if (!sample) break;

    const sampleEnd = sample.timestamp + sample.duration;

    if (sampleEnd <= cursorTime + 0.000001) {
      sample.close();
      cursorTime += 1 / outputSampleRate;
      continue;
    }

    const raw = new Float32Array(sample.allocationSize({ planeIndex: 0, format: "f32" }) / 4);
    sample.copyTo(raw, { planeIndex: 0, format: "f32" });
    const availableSeconds = sampleEnd - cursorTime;
    const framesToCopy = Math.min(frameCount - writtenFrames, Math.max(1, Math.floor(availableSeconds * outputSampleRate)));

    for (let frame = 0; frame < framesToCopy; frame += 1) {
      const absoluteTime = cursorTime + frame / outputSampleRate;
      const sourceFrame = Math.min(
        sample.numberOfFrames - 1,
        Math.max(0, Math.floor((absoluteTime - sample.timestamp) * sample.sampleRate))
      );

      for (let channel = 0; channel < outputChannels; channel += 1) {
        const sourceChannel = Math.min(channel, sample.numberOfChannels - 1);
        output[(writtenFrames + frame) * outputChannels + channel]
          = raw[sourceFrame * sample.numberOfChannels + sourceChannel] || 0;
      }
    }

    writtenFrames += framesToCopy;
    cursorTime += framesToCopy / outputSampleRate;
    sample.close();
  }

  return output;
}

function mixAudio(systemFrames, microphoneFrames, systemGain, microphoneGain) {
  const mixed = new Float32Array(systemFrames.length);
  const safeSystemGain = Math.max(0, Math.min(2, Number(systemGain) || 0));
  const safeMicrophoneGain = Math.max(0, Math.min(2, Number(microphoneGain) || 0));

  for (let index = 0; index < mixed.length; index += 1) {
    const value = systemFrames[index] * safeSystemGain + microphoneFrames[index] * safeMicrophoneGain;
    mixed[index] = Math.tanh(value);
  }

  return mixed;
}

function drawScreen(context, sample, crop, outputSize) {
  const sourceX = Math.round(sample.displayWidth * crop.x);
  const sourceY = Math.round(sample.displayHeight * crop.y);
  const sourceWidth = Math.max(2, Math.round(sample.displayWidth * crop.width));
  const sourceHeight = Math.max(2, Math.round(sample.displayHeight * crop.height));
  const fit = contain(sourceWidth, sourceHeight, outputSize.width, outputSize.height);

  sample.draw(
    context,
    sourceX,
    sourceY,
    sourceWidth,
    sourceHeight,
    fit.x,
    fit.y,
    fit.width,
    fit.height
  );
}

function drawCamera(context, sample, camera, outputSize) {
  const x = Math.round(camera.x * outputSize.width);
  const y = Math.round(camera.y * outputSize.height);
  const width = Math.round(camera.width * outputSize.width);
  const height = Math.round(camera.height * outputSize.height);

  context.save();
  roundedClip(context, x, y, width, height, camera.shape);
  context.clip();
  context.translate(camera.mirror ? x + width : x, y);
  context.scale(camera.mirror ? -1 : 1, 1);
  const source = cover(sample.displayWidth, sample.displayHeight, width, height);
  sample.draw(context, source.x, source.y, source.width, source.height, 0, 0, width, height);
  context.restore();

  context.save();
  context.strokeStyle = "rgba(255,255,255,.9)";
  context.lineWidth = Math.max(3, outputSize.width / 480);
  roundedClip(context, x, y, width, height, camera.shape);
  context.stroke();
  context.restore();
}

function cover(sourceWidth, sourceHeight, targetWidth, targetHeight) {
  const scale = Math.max(targetWidth / sourceWidth, targetHeight / sourceHeight);
  const width = targetWidth / scale;
  const height = targetHeight / scale;

  return {
    x: (sourceWidth - width) / 2,
    y: (sourceHeight - height) / 2,
    width,
    height
  };
}

function drawOverlays(context, canvas, blurCanvas, overlays, outputTime) {
  for (const overlay of overlays) {
    if (outputTime < overlay.start || outputTime > overlay.end) continue;

    if (overlay.type === "blur") drawBlur(context, canvas, blurCanvas, overlay);
    if (overlay.type === "text") drawText(context, canvas, overlay);
    if (overlay.type === "arrow") drawArrow(context, canvas, overlay);
  }
}

function drawBlur(context, canvas, blurCanvas, overlay) {
  const rectangle = pixelRectangle(canvas, overlay);

  blurCanvas.width = Math.max(2, rectangle.width);
  blurCanvas.height = Math.max(2, rectangle.height);
  const blurContext = blurCanvas.getContext("2d");
  blurContext.clearRect(0, 0, blurCanvas.width, blurCanvas.height);
  blurContext.drawImage(canvas, rectangle.x, rectangle.y, rectangle.width, rectangle.height, 0, 0, rectangle.width, rectangle.height);
  context.save();
  context.filter = `blur(${overlay.blur}px)`;
  context.drawImage(blurCanvas, rectangle.x, rectangle.y, rectangle.width, rectangle.height);
  context.restore();
}

function drawText(context, canvas, overlay) {
  const rectangle = pixelRectangle(canvas, overlay);
  const radius = Math.max(12, rectangle.height * 0.12);

  context.save();
  context.globalAlpha = 0.92;
  context.fillStyle = overlay.background;
  context.beginPath();
  context.roundRect(rectangle.x, rectangle.y, rectangle.width, rectangle.height, radius);
  context.fill();
  context.globalAlpha = 1;
  context.fillStyle = overlay.color;
  context.font = `800 ${Math.max(18, overlay.fontSize)}px Tajawal, sans-serif`;
  context.textAlign = "center";
  context.textBaseline = "middle";
  context.direction = "rtl";
  context.fillText(overlay.text, rectangle.x + rectangle.width / 2, rectangle.y + rectangle.height / 2, rectangle.width - 28);
  context.restore();
}

function drawArrow(context, canvas, overlay) {
  const rectangle = pixelRectangle(canvas, overlay);
  const points = arrowPoints(rectangle, overlay.direction);
  const head = Math.max(16, Math.min(rectangle.width, rectangle.height) * 0.25);

  context.save();
  context.strokeStyle = overlay.background;
  context.fillStyle = overlay.background;
  context.lineWidth = Math.max(6, canvas.width / 240);
  context.lineCap = "round";
  context.beginPath();
  context.moveTo(points.start.x, points.start.y);
  context.lineTo(points.end.x, points.end.y);
  context.stroke();
  const angle = Math.atan2(points.end.y - points.start.y, points.end.x - points.start.x);
  context.beginPath();
  context.moveTo(points.end.x, points.end.y);
  context.lineTo(points.end.x - head * Math.cos(angle - Math.PI / 6), points.end.y - head * Math.sin(angle - Math.PI / 6));
  context.lineTo(points.end.x - head * Math.cos(angle + Math.PI / 6), points.end.y - head * Math.sin(angle + Math.PI / 6));
  context.closePath();
  context.fill();
  context.restore();
}

function roundedClip(context, x, y, width, height, shape) {
  context.beginPath();

  if (shape === "circle") {
    context.ellipse(x + width / 2, y + height / 2, width / 2, height / 2, 0, 0, Math.PI * 2);
  } else {
    context.roundRect(x, y, width, height, shape === "square" ? 0 : Math.min(width, height) * 0.12);
  }
}

function pixelRectangle(canvas, rectangle) {
  return {
    x: Math.round(rectangle.x * canvas.width),
    y: Math.round(rectangle.y * canvas.height),
    width: Math.max(2, Math.round(rectangle.width * canvas.width)),
    height: Math.max(2, Math.round(rectangle.height * canvas.height))
  };
}

function arrowPoints(rectangle, direction) {
  const centerX = rectangle.x + rectangle.width / 2;
  const centerY = rectangle.y + rectangle.height / 2;
  const points = {
    right: [{ x: rectangle.x, y: centerY }, { x: rectangle.x + rectangle.width, y: centerY }],
    left: [{ x: rectangle.x + rectangle.width, y: centerY }, { x: rectangle.x, y: centerY }],
    up: [{ x: centerX, y: rectangle.y + rectangle.height }, { x: centerX, y: rectangle.y }],
    down: [{ x: centerX, y: rectangle.y }, { x: centerX, y: rectangle.y + rectangle.height }]
  }[direction] || [{ x: rectangle.x + rectangle.width, y: centerY }, { x: rectangle.x, y: centerY }];

  return { start: points[0], end: points[1] };
}

function* sourceTimestamps(manifest, frameCount, frameDuration) {
  for (let index = 0; index < frameCount; index += 1) {
    yield mapOutputTimeToSource(manifest, index * frameDuration)?.sourceTime || 0;
  }
}

function contain(sourceWidth, sourceHeight, boxWidth, boxHeight) {
  const scale = Math.min(boxWidth / sourceWidth, boxHeight / sourceHeight, 1);
  const width = Math.round(sourceWidth * scale);
  const height = Math.round(sourceHeight * scale);

  return {
    x: Math.round((boxWidth - width) / 2),
    y: Math.round((boxHeight - height) / 2),
    width,
    height
  };
}

function fitOutputSize(width, height, preset) {
  const maximumWidth = Math.min(1920, Number(preset?.width) || 1920);
  const maximumHeight = Math.min(1080, Number(preset?.height) || 1080);
  const scale = Math.min(maximumWidth / width, maximumHeight / height, 1);

  return {
    width: Math.max(2, Math.floor(width * scale / 2) * 2),
    height: Math.max(2, Math.floor(height * scale / 2) * 2)
  };
}

function createInput(file) {
  return new Input({ source: new BlobSource(file), formats: ALL_FORMATS });
}

async function getSourceFile(directory, source, required = true) {
  if (!source?.fileName) {
    if (required) throw new Error("ملف مصدر التسجيل مفقود.");
    return null;
  }

  try {
    const handle = await directory.getFileHandle(source.fileName);
    return handle.getFile();
  } catch (error) {
    if (!required && error?.name === "NotFoundError") return null;
    throw error;
  }
}

async function getProjectDirectory(projectId) {
  const root = await navigator.storage.getDirectory();
  const recorderDirectory = await root.getDirectoryHandle("screen-recorder", { create: true });

  return recorderDirectory.getDirectoryHandle(projectId);
}

function assertNotCanceled() {
  if (canceled) throw new DOMException("تم إلغاء التصدير.", "AbortError");
}

function readableError(error) {
  if (error instanceof Error && error.message) return error.message;

  return "تعذر تصدير الفيديو على هذا الجهاز.";
}
