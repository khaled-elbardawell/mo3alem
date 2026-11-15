import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import { createReservedMp4TrackMetadata } from "../../resources/js/screen-recorder/export-config.js";
import { createSavePickerOptions, saveFileToHandle } from "../../resources/js/screen-recorder/exporter.js";
import {
  addOverlay,
  commitHistory,
  createHistory,
  createProjectManifest,
  deleteRange,
  estimateRecordingBytes,
  initializeTimeline,
  keepRange,
  mapOutputTimeToSource,
  normalizeProjectManifest,
  removeOverlay,
  redoHistory,
  safeRecordingDuration,
  selectRecorderMimeType,
  splitAt,
  timelineDuration,
  undoHistory,
  updateOverlay
} from "../../resources/js/screen-recorder/state.js";

const recorderInterfaceSource = readFileSync(new URL("../../resources/js/screen-recorder/index.js", import.meta.url), "utf8");

function manifestWithDuration(duration = 20) {
  return initializeTimeline(createProjectManifest({ id: "project-1", createdAt: "2026-11-14T10:00:00.000Z" }), duration);
}

test("manifest v1 initializes a non-destructive source timeline", () => {
  const manifest = manifestWithDuration(20);

  assert.equal(manifest.version, 1);
  assert.equal(manifest.status, "editing");
  assert.equal(timelineDuration(manifest), 20);
  assert.deepEqual(manifest.timeline.segments.map(({ sourceStart, sourceEnd }) => [sourceStart, sourceEnd]), [[0, 20]]);
});

test("split and delete preserve source-time mapping while joining remaining output", () => {
  let manifest = splitAt(manifestWithDuration(20), 8);

  assert.equal(manifest.timeline.segments.length, 2);
  assert.equal(mapOutputTimeToSource(manifest, 11).sourceTime, 11);

  manifest = deleteRange(manifest, 5, 12);

  assert.equal(timelineDuration(manifest), 13);
  assert.deepEqual(manifest.timeline.segments.map(({ sourceStart, sourceEnd }) => [sourceStart, sourceEnd]), [[0, 5], [12, 20]]);
  assert.equal(mapOutputTimeToSource(manifest, 6).sourceTime, 13);
});

test("quick trim keeps only the selected range", () => {
  const manifest = keepRange(manifestWithDuration(20), 4, 13);

  assert.equal(timelineDuration(manifest), 9);
  assert.deepEqual(manifest.timeline.segments.map(({ sourceStart, sourceEnd }) => [sourceStart, sourceEnd]), [[4, 13]]);
});

test("visual overlays keep their position, timing, and editable properties", () => {
  const added = addOverlay(manifestWithDuration(20), "text", 7);
  const overlay = added.manifest.timeline.overlays[0];

  assert.equal(overlay.start, 7);
  assert.equal(overlay.end, 12);
  assert.equal(overlay.type, "text");

  const updated = updateOverlay(added.manifest, added.overlayId, {
    text: "عنوان الدرس",
    start: 3,
    end: 9,
    x: 0.1,
    y: 0.4
  });

  assert.deepEqual(
    updated.timeline.overlays.map(({ text, start, end, x, y }) => ({ text, start, end, x, y })),
    [{ text: "عنوان الدرس", start: 3, end: 9, x: 0.1, y: 0.4 }]
  );
  assert.deepEqual(removeOverlay(updated, added.overlayId).timeline.overlays, []);

  const addedAtEnd = addOverlay(manifestWithDuration(20), "arrow", 20).manifest.timeline.overlays[0];
  assert.deepEqual({ start: addedAtEnd.start, end: addedAtEnd.end }, { start: 15, end: 20 });
});

test("the editor keeps a focused video timeline and edits additions in the dialog", () => {
  assert.doesNotMatch(recorderInterfaceSource, /overlaySettingsSlot/);
  assert.doesNotMatch(recorderInterfaceSource, /function renderClipList\(\)/);
  assert.match(recorderInterfaceSource, /function openCreateOverlayDialog\(type, overlayId = null\)/);
  assert.match(recorderInterfaceSource, /openCreateOverlayDialog\(overlay\.type, overlay\.id\)/);
  assert.match(recorderInterfaceSource, /function createOverlayFromDialog\(event\)/);
  assert.match(recorderInterfaceSource, /overlayCreateForm\.addEventListener\("submit", createOverlayFromDialog\)/);
  assert.match(recorderInterfaceSource, /updateOverlay\(manifest, editedOverlayId, changes\)/);
  assert.doesNotMatch(recorderInterfaceSource, /function renderAudioWaveform\(\)/);
  assert.match(recorderInterfaceSource, /function renderTimelineRuler\(total\)/);
  assert.match(recorderInterfaceSource, /function setTimelineZoom\(value\)/);
  assert.doesNotMatch(recorderInterfaceSource, /elements\.textOverlayTrack/);
  assert.doesNotMatch(recorderInterfaceSource, /elements\.visualOverlayTrack/);
});

test("export writes and closes the selected local file before reporting success", async () => {
  const sourceFile = new Blob(["playable-video-data"], { type: "video/mp4" });
  let writtenFile = null;
  let closed = false;

  const destinationHandle = {
    async createWritable() {
      return {
        async write(file) {
          writtenFile = file;
        },
        async close() {
          closed = true;
        }
      };
    },
    async getFile() {
      assert.equal(closed, true);
      return new Blob([await writtenFile.arrayBuffer()], { type: writtenFile.type });
    }
  };

  const savedFile = await saveFileToHandle(sourceFile, destinationHandle);

  assert.equal(writtenFile, sourceFile);
  assert.equal(savedFile.size, sourceFile.size);
});

test("the export picker starts in Downloads and keeps its own remembered directory", () => {
  const options = createSavePickerOptions({
    format: "mp4",
    extension: "mp4",
    mimeType: "video/mp4"
  }, "lesson.mp4");

  assert.equal(options.id, "screen-recorder-export");
  assert.equal(options.startIn, "downloads");
  assert.equal(options.suggestedName, "lesson.mp4");
  assert.equal(options.excludeAcceptAllOption, true);
});

test("reserved MP4 export declares a safe packet count for every track", () => {
  const metadata = createReservedMp4TrackMetadata(60, 30);

  assert.ok(metadata.video.maximumPacketCount >= Math.ceil(60 * 30 * 4 / 3));
  assert.ok(metadata.audio.maximumPacketCount >= Math.ceil(60 / 0.01 * 4 / 3));
  assert.ok(createReservedMp4TrackMetadata(0).video.maximumPacketCount > 0);
  assert.ok(createReservedMp4TrackMetadata(0).audio.maximumPacketCount > 0);
});

test("history supports undo and redo after an edit", () => {
  const original = manifestWithDuration(20);
  const edited = deleteRange(original, 4, 9);
  let history = commitHistory(createHistory(original), edited);

  assert.equal(timelineDuration(history.present), 15);
  history = undoHistory(history);
  assert.equal(timelineDuration(history.present), 20);
  history = redoHistory(history);
  assert.equal(timelineDuration(history.present), 15);
});

test("legacy manifests migrate to v1 and clamp unsafe rectangles", () => {
  const migrated = normalizeProjectManifest({
    id: "legacy",
    version: 0,
    duration: 10,
    timeline: {
      segments: [{ sourceStart: -2, sourceEnd: 14 }],
      crop: { x: -1, y: 0.9, width: 3, height: 3 },
      overlays: [{ type: "unknown" }]
    }
  });

  assert.equal(migrated.version, 1);
  assert.deepEqual(migrated.timeline.segments.map(({ sourceStart, sourceEnd }) => [sourceStart, sourceEnd]), [[0, 10]]);
  assert.deepEqual(
    { ...migrated.timeline.crop, height: Number(migrated.timeline.crop.height.toFixed(3)) },
    { x: 0, y: 0.9, width: 1, height: 0.1 }
  );
  assert.deepEqual(migrated.timeline.overlays, []);
});

test("codec selection and storage estimates choose safe local limits", () => {
  const supported = new Set(["video/webm;codecs=vp8,opus", "audio/webm"]);
  const isSupported = (type) => supported.has(type);

  assert.equal(selectRecorderMimeType("video", isSupported), "video/webm;codecs=vp8,opus");
  assert.equal(selectRecorderMimeType("audio", isSupported), "audio/webm");
  assert.equal(selectRecorderMimeType("video", () => false), "");

  const oneMinute = estimateRecordingBytes(60, { camera: true, microphone: true });
  assert.equal(safeRecordingDuration(268_435_456 + oneMinute * 3, { camera: true, microphone: true }), 180);
  assert.equal(safeRecordingDuration(Number.MAX_SAFE_INTEGER), 3600);
});
