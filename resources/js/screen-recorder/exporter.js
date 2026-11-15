import { canEncodeAudio, canEncodeVideo } from "mediabunny";
import { getProjectFile, removeProjectFile, storageEstimate } from "./storage.js";
import { DEFAULT_VIDEO_BITRATE, formatBytes, timelineDuration } from "./state.js";

let profilePromise;

export class ExportCanceledError extends Error {
  constructor() {
    super("تم إلغاء التصدير.");
    this.name = "ExportCanceledError";
  }
}

export function prepareExportProfile() {
  if (!profilePromise) profilePromise = detectProfile();

  return profilePromise;
}

export function estimateExportBytes(manifest) {
  return Math.ceil(timelineDuration(manifest) * (DEFAULT_VIDEO_BITRATE + 192_000) / 8 * 1.08);
}

export async function exportProject(manifest, callbacks = {}, preparedProfile = null) {
  const profile = preparedProfile || await prepareExportProfile();
  const suggestedName = `${safeFileName(manifest.title || "تسجيل-الشاشة")}.${profile.extension}`;
  let destinationHandle = null;

  if (typeof showSaveFilePicker === "function") {
    try {
      destinationHandle = await showSaveFilePicker(createSavePickerOptions(profile, suggestedName));
    } catch (error) {
      if (error?.name === "AbortError") throw new ExportCanceledError();
      throw new Error("تعذر فتح نافذة الحفظ المحلية. أعد المحاولة وتأكد من السماح للمتصفح بالوصول إلى الملفات.", { cause: error });
    }
  } else {
    callbacks.onFallback?.();
  }

  const estimate = await storageEstimate();
  const expectedBytes = estimateExportBytes(manifest);

  if (estimate.available && estimate.available < expectedBytes + 268_435_456) {
    throw new Error(`المساحة المحلية غير كافية. يحتاج التصدير قرابة ${formatBytes(expectedBytes)}.`);
  }

  const outputFileName = `export-${Date.now()}.${profile.extension}`;
  const worker = new Worker(new URL("./export-worker.js", import.meta.url), { type: "module" });
  let completed = false;
  let rejectExport = null;

  const cancel = async () => {
    if (completed) return;

    completed = true;
    worker.postMessage({ type: "cancel" });
    worker.terminate();
    await removeProjectFile(manifest.id, outputFileName).catch(() => {});
    callbacks.onCanceled?.();
    rejectExport?.(new ExportCanceledError());
  };

  callbacks.onReadyToCancel?.(cancel);

  return new Promise((resolve, reject) => {
    rejectExport = reject;
    worker.addEventListener("message", async (event) => {
      const message = event.data || {};

      if (message.type === "progress") {
        callbacks.onProgress?.(Math.min(0.96, message.progress * 0.96), "video");
        return;
      }

      if (message.type === "audio-progress") {
        callbacks.onAudioProgress?.(message.progress);
        return;
      }

      if (message.type === "error") {
        completed = true;
        worker.terminate();
        await removeProjectFile(manifest.id, outputFileName);
        reject(new Error(message.message));
        return;
      }

      if (message.type === "canceled") {
        completed = true;
        worker.terminate();
        await removeProjectFile(manifest.id, outputFileName);
        reject(new ExportCanceledError());
        return;
      }

      if (message.type !== "complete") return;

      try {
        callbacks.onProgress?.(0.98, "saving");
        const file = await getProjectFile(manifest.id, outputFileName);

        if (destinationHandle) {
          await saveFileToHandle(file, destinationHandle);
        } else {
          downloadFile(file, suggestedName);
        }

        completed = true;
        worker.terminate();
        callbacks.onProgress?.(1, "complete");
        await removeProjectFile(manifest.id, outputFileName);
        resolve({
          ...message,
          savedAs: destinationHandle?.name || suggestedName,
          saveMethod: destinationHandle ? "picker" : "download",
          profile
        });
      } catch (error) {
        completed = true;
        worker.terminate();
        await removeProjectFile(manifest.id, outputFileName);
        reject(error);
      }
    });

    worker.addEventListener("error", async (event) => {
      completed = true;
      worker.terminate();
      await removeProjectFile(manifest.id, outputFileName);
      reject(event.error || new Error("تعذر تشغيل معالج تصدير الفيديو."));
    });

    worker.postMessage({
      type: "export",
      manifest,
      profile,
      outputFileName
    });
  });
}

export function createSavePickerOptions(profile, suggestedName) {
  return {
    id: "screen-recorder-export",
    startIn: "downloads",
    suggestedName,
    excludeAcceptAllOption: true,
    types: [{
      description: profile.format === "mp4" ? "فيديو MP4" : "فيديو WebM",
      accept: { [profile.mimeType]: [`.${profile.extension}`] }
    }]
  };
}

export async function saveFileToHandle(file, destinationHandle) {
  const writable = await destinationHandle.createWritable();
  let isClosed = false;

  try {
    await writable.write(file);
    await writable.close();
    isClosed = true;
  } catch (error) {
    if (!isClosed && typeof writable.abort === "function") {
      await writable.abort().catch(() => {});
    }

    throw new Error("تعذر حفظ الفيديو في المسار المختار. تأكد من صلاحية المجلد والمساحة المتوفرة.", { cause: error });
  }

  const savedFile = await destinationHandle.getFile();

  if (savedFile.size !== file.size) {
    throw new Error("لم يكتمل حفظ الفيديو في المسار المختار. لم يتطابق حجم الملف النهائي مع الملف المُصدّر.");
  }

  return savedFile;
}

async function detectProfile() {
  const [avc, aac] = await Promise.all([
    canEncodeVideo("avc", { width: 1920, height: 1080, bitrate: DEFAULT_VIDEO_BITRATE, frameRate: 30 }),
    canEncodeAudio("aac", { sampleRate: 48_000, numberOfChannels: 2, bitrate: 192_000 })
  ]);

  if (avc && aac) {
    return {
      format: "mp4",
      extension: "mp4",
      mimeType: "video/mp4",
      videoCodec: "avc",
      audioCodec: "aac"
    };
  }

  const [vp9, opus] = await Promise.all([
    canEncodeVideo("vp9", { width: 1920, height: 1080, bitrate: DEFAULT_VIDEO_BITRATE, frameRate: 30 }),
    canEncodeAudio("opus", { sampleRate: 48_000, numberOfChannels: 2, bitrate: 192_000 })
  ]);

  if (!vp9 || !opus) throw new Error("لا يدعم هذا الجهاز ترميز MP4 أو WebM المطلوب.");

  return {
    format: "webm",
    extension: "webm",
    mimeType: "video/webm",
    videoCodec: "vp9",
    audioCodec: "opus"
  };
}

function safeFileName(value) {
  return String(value)
    .trim()
    .replace(/[\\/:*?"<>|]+/g, "-")
    .replace(/\s+/g, "-")
    .slice(0, 80) || "تسجيل-الشاشة";
}

function downloadFile(file, fileName) {
  const url = URL.createObjectURL(file);
  const anchor = document.createElement("a");

  anchor.href = url;
  anchor.download = fileName;
  anchor.hidden = true;
  document.body.append(anchor);
  anchor.click();
  anchor.remove();
  window.setTimeout(() => URL.revokeObjectURL(url), 30_000);
}
