export const PROJECT_VERSION = 1;
export const MAX_RECORDING_SECONDS = 60 * 60;
export const MIN_SEGMENT_SECONDS = 1 / 30;
export const DEFAULT_VIDEO_BITRATE = 8_000_000;
export const DEFAULT_CAMERA_BITRATE = 1_500_000;
export const DEFAULT_AUDIO_BITRATE = 128_000;

const clamp = (value, minimum, maximum) => Math.min(maximum, Math.max(minimum, Number(value) || 0));
const roundTime = (value) => Math.round(value * 1000) / 1000;
const copy = (value) => structuredClone(value);

export function projectId() {
  return globalThis.crypto?.randomUUID?.()
    || `recording-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export function createProjectManifest({ id = projectId(), title = "تسجيل شاشة جديد", createdAt = new Date().toISOString() } = {}) {
  return {
    version: PROJECT_VERSION,
    id,
    title,
    createdAt,
    updatedAt: createdAt,
    status: "preflight",
    duration: 0,
    revision: 0,
    sources: {
      screen: null,
      microphone: null,
      camera: null
    },
    timeline: {
      segments: [],
      crop: { x: 0, y: 0, width: 1, height: 1 },
      camera: {
        visible: true,
        x: 0.72,
        y: 0.68,
        width: 0.24,
        height: 0.27,
        shape: "rounded",
        mirror: true
      },
      audio: {
        systemGain: 1,
        microphoneGain: 1,
        systemMuted: false,
        microphoneMuted: false
      },
      overlays: []
    },
    editHistory: [],
    exportPreset: {
      width: 1920,
      height: 1080,
      frameRate: 30,
      format: "mp4"
    }
  };
}

export function initializeTimeline(manifest, duration) {
  const next = copy(manifest);
  const normalizedDuration = Math.max(0, Number(duration) || 0);

  next.duration = roundTime(normalizedDuration);
  next.status = "editing";
  next.timeline.segments = normalizedDuration > 0
    ? [{ id: projectId(), sourceStart: 0, sourceEnd: roundTime(normalizedDuration) }]
    : [];

  return touch(next, "initialize");
}

export function normalizeProjectManifest(value) {
  const fallback = createProjectManifest({ id: value?.id || projectId() });
  const timeline = value?.timeline || {};
  const normalized = {
    ...fallback,
    ...value,
    version: PROJECT_VERSION,
    sources: { ...fallback.sources, ...(value?.sources || {}) },
    timeline: {
      ...fallback.timeline,
      ...timeline,
      crop: normalizeRectangle(timeline.crop || fallback.timeline.crop),
      camera: {
        ...fallback.timeline.camera,
        ...(timeline.camera || {}),
        ...normalizeRectangle(timeline.camera || fallback.timeline.camera)
      },
      audio: { ...fallback.timeline.audio, ...(timeline.audio || {}) },
      segments: normalizeSegments(timeline.segments || [], value?.duration || 0),
      overlays: (timeline.overlays || []).map(normalizeOverlay).filter(Boolean)
    },
    editHistory: Array.isArray(value?.editHistory) ? value.editHistory.slice(-30) : []
  };

  normalized.duration = Math.max(0, Number(normalized.duration) || 0);
  normalized.revision = Math.max(0, Number(normalized.revision) || 0);

  return normalized;
}

export function normalizeRectangle(rectangle) {
  const x = clamp(rectangle?.x, 0, 0.95);
  const y = clamp(rectangle?.y, 0, 0.95);
  const width = clamp(rectangle?.width || 1, 0.05, 1 - x);
  const height = clamp(rectangle?.height || 1, 0.05, 1 - y);

  return { x, y, width, height };
}

export function normalizeSegments(segments, duration = Infinity) {
  const maximum = Math.max(0, Number(duration) || 0) || Infinity;

  return segments
    .map((segment) => ({
      id: segment.id || projectId(),
      sourceStart: roundTime(clamp(segment.sourceStart, 0, maximum)),
      sourceEnd: roundTime(clamp(segment.sourceEnd, 0, maximum))
    }))
    .filter((segment) => segment.sourceEnd - segment.sourceStart >= MIN_SEGMENT_SECONDS)
    .sort((first, second) => first.sourceStart - second.sourceStart);
}

export function normalizeOverlay(overlay) {
  if (!overlay || !["text", "arrow", "blur"].includes(overlay.type)) return null;

  return {
    id: overlay.id || projectId(),
    type: overlay.type,
    start: Math.max(0, Number(overlay.start) || 0),
    end: Math.max(Number(overlay.end) || 5, Number(overlay.start) || 0),
    ...normalizeRectangle(overlay),
    text: String(overlay.text || "نص توضيحي").slice(0, 160),
    color: /^#[\da-f]{6}$/i.test(overlay.color || "") ? overlay.color : "#ffffff",
    background: /^#[\da-f]{6}$/i.test(overlay.background || "") ? overlay.background : "#6d28d9",
    fontSize: clamp(overlay.fontSize || 42, 18, 96),
    direction: ["left", "right", "up", "down"].includes(overlay.direction) ? overlay.direction : "left",
    blur: clamp(overlay.blur || 16, 4, 40)
  };
}

export function timelineDuration(manifest) {
  return roundTime((manifest?.timeline?.segments || [])
    .reduce((total, segment) => total + Math.max(0, segment.sourceEnd - segment.sourceStart), 0));
}

export function mapOutputTimeToSource(manifest, outputTime) {
  const segments = manifest?.timeline?.segments || [];
  let remaining = clamp(outputTime, 0, timelineDuration(manifest));

  for (let index = 0; index < segments.length; index += 1) {
    const segment = segments[index];
    const duration = segment.sourceEnd - segment.sourceStart;

    if (remaining <= duration || index === segments.length - 1) {
      return {
        segment,
        segmentIndex: index,
        sourceTime: roundTime(Math.min(segment.sourceEnd, segment.sourceStart + remaining)),
        segmentOffset: roundTime(Math.min(duration, remaining))
      };
    }

    remaining -= duration;
  }

  return null;
}

export function splitAt(manifest, outputTime) {
  const mapping = mapOutputTimeToSource(manifest, outputTime);

  if (!mapping) return manifest;

  const { segment, segmentIndex, sourceTime } = mapping;

  if (
    sourceTime - segment.sourceStart < MIN_SEGMENT_SECONDS
    || segment.sourceEnd - sourceTime < MIN_SEGMENT_SECONDS
  ) {
    return manifest;
  }

  const next = copy(manifest);
  next.timeline.segments.splice(segmentIndex, 1,
    { ...segment, id: projectId(), sourceEnd: sourceTime },
    { ...segment, id: projectId(), sourceStart: sourceTime });

  return touch(next, "split");
}

export function deleteRange(manifest, start, end) {
  const total = timelineDuration(manifest);
  const rangeStart = clamp(Math.min(start, end), 0, total);
  const rangeEnd = clamp(Math.max(start, end), 0, total);

  if (rangeEnd - rangeStart < MIN_SEGMENT_SECONDS) return manifest;

  const kept = [];
  let outputCursor = 0;

  for (const segment of manifest.timeline.segments) {
    const segmentDuration = segment.sourceEnd - segment.sourceStart;
    const outputStart = outputCursor;
    const outputEnd = outputStart + segmentDuration;

    if (rangeStart > outputStart) {
      const keepEnd = Math.min(rangeStart, outputEnd);
      const length = keepEnd - outputStart;

      if (length >= MIN_SEGMENT_SECONDS) {
        kept.push({ ...segment, id: projectId(), sourceEnd: roundTime(segment.sourceStart + length) });
      }
    }

    if (rangeEnd < outputEnd) {
      const removedFromSegment = Math.max(0, rangeEnd - outputStart);
      const keepStart = segment.sourceStart + removedFromSegment;

      if (segment.sourceEnd - keepStart >= MIN_SEGMENT_SECONDS) {
        kept.push({ ...segment, id: projectId(), sourceStart: roundTime(keepStart) });
      }
    }

    outputCursor = outputEnd;
  }

  const next = copy(manifest);
  next.timeline.segments = mergeAdjacentSegments(kept);

  return touch(next, "delete-range");
}

export function trimStart(manifest, outputTime) {
  return deleteRange(manifest, 0, outputTime);
}

export function trimEnd(manifest, outputTime) {
  return deleteRange(manifest, outputTime, timelineDuration(manifest));
}

export function keepRange(manifest, start, end) {
  const total = timelineDuration(manifest);
  const rangeStart = clamp(Math.min(start, end), 0, total);
  const rangeEnd = clamp(Math.max(start, end), 0, total);

  if (rangeEnd - rangeStart < MIN_SEGMENT_SECONDS) return manifest;

  const withoutEnding = trimEnd(manifest, rangeEnd);

  return trimStart(withoutEnding, rangeStart);
}

export function addOverlay(manifest, type, outputTime = 0) {
  const duration = timelineDuration(manifest);
  const requestedStart = clamp(outputTime, 0, duration);
  const start = duration > 0 && requestedStart >= duration ? Math.max(0, duration - 5) : requestedStart;
  const overlay = normalizeOverlay({
    id: projectId(),
    type,
    start,
    end: Math.min(duration, start + 5),
    x: type === "arrow" ? 0.2 : 0.3,
    y: type === "arrow" ? 0.35 : 0.2,
    width: type === "text" ? 0.4 : 0.25,
    height: type === "text" ? 0.14 : 0.2
  });
  const next = copy(manifest);
  next.timeline.overlays.push(overlay);

  return { manifest: touch(next, `add-${type}`), overlayId: overlay.id };
}

export function updateOverlay(manifest, overlayId, changes) {
  const next = copy(manifest);
  const index = next.timeline.overlays.findIndex((overlay) => overlay.id === overlayId);

  if (index < 0) return manifest;

  next.timeline.overlays[index] = normalizeOverlay({ ...next.timeline.overlays[index], ...changes });

  return touch(next, "update-overlay");
}

export function removeOverlay(manifest, overlayId) {
  const next = copy(manifest);
  next.timeline.overlays = next.timeline.overlays.filter((overlay) => overlay.id !== overlayId);

  return touch(next, "remove-overlay");
}

export function updateTimelineSettings(manifest, section, changes) {
  if (!["crop", "camera", "audio"].includes(section)) return manifest;

  const next = copy(manifest);
  next.timeline[section] = { ...next.timeline[section], ...changes };

  if (["crop", "camera"].includes(section)) {
    next.timeline[section] = { ...next.timeline[section], ...normalizeRectangle(next.timeline[section]) };
  }

  return touch(next, `update-${section}`);
}

export function createHistory(manifest) {
  return { past: [], present: normalizeProjectManifest(manifest), future: [] };
}

export function commitHistory(history, manifest) {
  if (manifest === history.present || JSON.stringify(manifest) === JSON.stringify(history.present)) return history;

  return {
    past: [...history.past.slice(-49), history.present],
    present: manifest,
    future: []
  };
}

export function undoHistory(history) {
  if (!history.past.length) return history;

  return {
    past: history.past.slice(0, -1),
    present: history.past.at(-1),
    future: [history.present, ...history.future].slice(0, 50)
  };
}

export function redoHistory(history) {
  if (!history.future.length) return history;

  return {
    past: [...history.past, history.present].slice(-50),
    present: history.future[0],
    future: history.future.slice(1)
  };
}

export function estimateRecordingBytes(durationSeconds, { camera = false, microphone = false } = {}) {
  const bitsPerSecond = DEFAULT_VIDEO_BITRATE
    + DEFAULT_AUDIO_BITRATE
    + (camera ? DEFAULT_CAMERA_BITRATE : 0)
    + (microphone ? DEFAULT_AUDIO_BITRATE : 0);

  return Math.ceil((Math.max(0, durationSeconds) * bitsPerSecond) / 8 * 1.12);
}

export function safeRecordingDuration(availableBytes, options = {}, reserveBytes = 268_435_456) {
  const usableBytes = Math.max(0, availableBytes - reserveBytes);
  const minuteBytes = estimateRecordingBytes(60, options);

  if (!minuteBytes) return 0;

  return Math.min(MAX_RECORDING_SECONDS, Math.floor(usableBytes / minuteBytes) * 60);
}

export function selectRecorderMimeType(kind = "video", isSupported = (type) => MediaRecorder.isTypeSupported(type)) {
  const candidates = kind === "audio"
    ? ["audio/webm;codecs=opus", "audio/webm"]
    : ["video/webm;codecs=vp9,opus", "video/webm;codecs=vp8,opus", "video/webm"];

  return candidates.find((candidate) => isSupported(candidate)) || "";
}

export function formatDuration(seconds) {
  const value = Math.max(0, Math.floor(Number(seconds) || 0));
  const hours = Math.floor(value / 3600);
  const minutes = Math.floor((value % 3600) / 60);
  const remainingSeconds = value % 60;
  const parts = [minutes, remainingSeconds].map((part) => String(part).padStart(2, "0"));

  return hours ? `${String(hours).padStart(2, "0")}:${parts.join(":")}` : parts.join(":");
}

export function formatBytes(bytes) {
  const value = Math.max(0, Number(bytes) || 0);

  if (value < 1024) return `${Math.round(value)} بايت`;

  const units = ["ك.ب", "م.ب", "ج.ب", "ت.ب"];
  let index = -1;
  let size = value;

  do {
    size /= 1024;
    index += 1;
  } while (size >= 1024 && index < units.length - 1);

  return `${size >= 10 ? size.toFixed(0) : size.toFixed(1)} ${units[index]}`;
}

function mergeAdjacentSegments(segments) {
  return segments.reduce((merged, segment) => {
    const previous = merged.at(-1);

    if (previous && Math.abs(previous.sourceEnd - segment.sourceStart) < MIN_SEGMENT_SECONDS) {
      previous.sourceEnd = segment.sourceEnd;
      return merged;
    }

    merged.push(segment);
    return merged;
  }, []);
}

function touch(manifest, action) {
  manifest.updatedAt = new Date().toISOString();
  manifest.revision = (Number(manifest.revision) || 0) + 1;
  manifest.editHistory = [
    ...(manifest.editHistory || []).slice(-29),
    { action, at: manifest.updatedAt }
  ];

  return manifest;
}
