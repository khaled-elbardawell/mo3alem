import {
  DEFAULT_AUDIO_BITRATE,
  DEFAULT_CAMERA_BITRATE,
  DEFAULT_VIDEO_BITRATE,
  MAX_RECORDING_SECONDS,
  selectRecorderMimeType
} from "./state.js";
import { createProjectFileWriter, storageEstimate } from "./storage.js";

const LOW_STORAGE_BYTES = 268_435_456;

export function detectCaptureCapabilities() {
  const userAgent = navigator.userAgent || "";
  const isMobile = navigator.userAgentData?.mobile === true || /Android|iPhone|iPad|Mobile/i.test(userAgent);
  const isChromium = /Chrome|Chromium|Edg\//i.test(userAgent) && !/OPR\//i.test(userAgent);

  return {
    secureContext: globalThis.isSecureContext,
    displayMedia: typeof navigator.mediaDevices?.getDisplayMedia === "function",
    userMedia: typeof navigator.mediaDevices?.getUserMedia === "function",
    mediaRecorder: typeof globalThis.MediaRecorder === "function",
    webCodecs: ["VideoEncoder", "VideoDecoder", "AudioEncoder", "AudioDecoder"]
      .every((name) => typeof globalThis[name] === "function"),
    opfs: typeof navigator.storage?.getDirectory === "function",
    filePicker: typeof globalThis.showSaveFilePicker === "function",
    desktopChromium: isChromium && !isMobile
  };
}

export class CaptureSession {
  constructor({ projectId, options, maxDurationSeconds = MAX_RECORDING_SECONDS, callbacks = {} }) {
    this.projectId = projectId;
    this.options = options;
    this.maxDurationSeconds = Math.min(MAX_RECORDING_SECONDS, maxDurationSeconds);
    this.callbacks = callbacks;
    this.streams = {};
    this.recorders = [];
    this.writers = [];
    this.writeQueues = [];
    this.elapsedBeforeResume = 0;
    this.runningStartedAt = null;
    this.startedAt = null;
    this.tickTimer = null;
    this.storageTimer = null;
    this.warningFiveMinutes = false;
    this.warningOneMinute = false;
    this.stopping = false;
  }

  async start() {
    await this.prepare();
    this.begin();
  }

  async prepare() {
    this.streams.screen = await navigator.mediaDevices.getDisplayMedia({
      video: {
        width: { ideal: 1920, max: 1920 },
        height: { ideal: 1080, max: 1080 },
        frameRate: { ideal: 30, max: 30 },
        displaySurface: "monitor"
      },
      audio: {
        echoCancellation: false,
        noiseSuppression: false,
        autoGainControl: false
      },
      preferCurrentTab: false,
      selfBrowserSurface: "exclude",
      systemAudio: "include",
      surfaceSwitching: "include"
    });

    if (this.options.microphone) {
      try {
        this.streams.microphone = await navigator.mediaDevices.getUserMedia({
          audio: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
            sampleRate: 48_000
          },
          video: false
        });
      } catch (error) {
        this.callbacks.onSourceUnavailable?.("microphone", error);
      }
    }

    if (this.options.camera) {
      try {
        this.streams.camera = await navigator.mediaDevices.getUserMedia({
          audio: false,
          video: {
            width: { ideal: 1280, max: 1280 },
            height: { ideal: 720, max: 720 },
            frameRate: { ideal: 30, max: 30 },
            facingMode: "user"
          }
        });
      } catch (error) {
        this.callbacks.onSourceUnavailable?.("camera", error);
      }
    }

    const screenTrack = this.streams.screen.getVideoTracks()[0];
    screenTrack.addEventListener("ended", () => this.callbacks.onScreenEnded?.(), { once: true });

    await this.prepareRecorders();

    this.callbacks.onPrepared?.({
      screen: this.streams.screen,
      microphone: this.streams.microphone || null,
      camera: this.streams.camera || null,
      hasSystemAudio: this.streams.screen.getAudioTracks().length > 0
    });
  }

  begin() {
    if (this.startedAt || !this.recorders.length) return;

    this.startedAt = performance.now();
    this.runningStartedAt = this.startedAt;
    this.recorders.forEach(({ recorder }) => recorder.start(1000));
    this.startMonitoring();

    this.callbacks.onStarted?.({
      screen: this.streams.screen,
      microphone: this.streams.microphone || null,
      camera: this.streams.camera || null,
      hasSystemAudio: this.streams.screen.getAudioTracks().length > 0
    });
  }

  pause() {
    if (this.stopping || !this.runningStartedAt) return;

    this.recorders.forEach(({ recorder }) => {
      if (recorder.state === "recording") recorder.pause();
    });
    this.elapsedBeforeResume += (performance.now() - this.runningStartedAt) / 1000;
    this.runningStartedAt = null;
    this.callbacks.onPaused?.(this.elapsedSeconds());
  }

  resume() {
    if (this.stopping || this.runningStartedAt) return;

    this.recorders.forEach(({ recorder }) => {
      if (recorder.state === "paused") recorder.resume();
    });
    this.runningStartedAt = performance.now();
    this.callbacks.onResumed?.(this.elapsedSeconds());
  }

  async stop(reason = "user") {
    if (this.stopping) return null;

    this.stopping = true;
    this.clearMonitoring();

    if (this.runningStartedAt) {
      this.elapsedBeforeResume += (performance.now() - this.runningStartedAt) / 1000;
      this.runningStartedAt = null;
    }

    const recorderStops = this.recorders.map(({ recorder }) => stopRecorder(recorder));
    await Promise.all(recorderStops);
    await Promise.all(this.writeQueues);
    await Promise.all(this.writers.map((writer) => writer.close()));
    this.stopTracks();

    const sources = {};

    for (const item of this.recorders) {
      sources[item.name] = {
        fileName: item.fileName,
        mimeType: item.mimeType,
        size: item.writer.size,
        offsetSeconds: 0
      };
    }

    const result = {
      reason,
      duration: Math.min(this.maxDurationSeconds, this.elapsedSeconds()),
      sources,
      hasSystemAudio: this.streams.screen?.getAudioTracks().length > 0
    };

    this.callbacks.onStopped?.(result);

    return result;
  }

  async abort() {
    this.stopping = true;
    this.clearMonitoring();

    await Promise.all(this.recorders.map(({ recorder }) => stopRecorder(recorder)));
    await Promise.allSettled(this.writeQueues);
    await Promise.allSettled(this.writers.map((writer) => writer.abort()));
    this.stopTracks();
  }

  elapsedSeconds() {
    const running = this.runningStartedAt ? (performance.now() - this.runningStartedAt) / 1000 : 0;

    return this.elapsedBeforeResume + running;
  }

  async prepareRecorders() {
    await this.addRecorder("screen", "screen.webm", this.streams.screen, {
      mimeType: selectRecorderMimeType("video"),
      videoBitsPerSecond: DEFAULT_VIDEO_BITRATE,
      audioBitsPerSecond: DEFAULT_AUDIO_BITRATE
    });

    if (this.streams.microphone) {
      await this.addRecorder("microphone", "microphone.webm", this.streams.microphone, {
        mimeType: selectRecorderMimeType("audio"),
        audioBitsPerSecond: DEFAULT_AUDIO_BITRATE
      });
    }

    if (this.streams.camera) {
      await this.addRecorder("camera", "camera.webm", this.streams.camera, {
        mimeType: selectRecorderMimeType("video"),
        videoBitsPerSecond: DEFAULT_CAMERA_BITRATE
      });
    }
  }

  async addRecorder(name, fileName, stream, recorderOptions) {
    const writer = await createProjectFileWriter(this.projectId, fileName);
    const recorder = new MediaRecorder(stream, compactRecorderOptions(recorderOptions));
    const queueIndex = this.writeQueues.length;

    this.writers.push(writer);
    this.writeQueues.push(Promise.resolve());
    recorder.addEventListener("dataavailable", (event) => {
      if (!event.data?.size) return;

      this.writeQueues[queueIndex] = this.writeQueues[queueIndex]
        .then(() => writer.write(event.data))
        .catch((error) => this.callbacks.onWriteError?.(error));
    });
    recorder.addEventListener("error", (event) => this.callbacks.onRecorderError?.(event.error));
    this.recorders.push({ name, fileName, mimeType: recorder.mimeType, recorder, writer });
  }

  startMonitoring() {
    this.tickTimer = window.setInterval(() => {
      const elapsed = this.elapsedSeconds();
      const remaining = this.maxDurationSeconds - elapsed;

      this.callbacks.onTick?.(elapsed, remaining);

      if (remaining <= 300 && !this.warningFiveMinutes) {
        this.warningFiveMinutes = true;
        this.callbacks.onWarning?.("five-minutes", remaining);
      }

      if (remaining <= 60 && !this.warningOneMinute) {
        this.warningOneMinute = true;
        this.callbacks.onWarning?.("one-minute", remaining);
      }

      if (remaining <= 0) this.callbacks.onLimitReached?.();
    }, 250);

    this.storageTimer = window.setInterval(async () => {
      const estimate = await storageEstimate();

      this.callbacks.onStorage?.(estimate);

      if (estimate.available && estimate.available < LOW_STORAGE_BYTES) {
        this.callbacks.onLowStorage?.(estimate);
      }
    }, 30_000);
  }

  clearMonitoring() {
    window.clearInterval(this.tickTimer);
    window.clearInterval(this.storageTimer);
  }

  stopTracks() {
    Object.values(this.streams).forEach((stream) => {
      stream?.getTracks().forEach((track) => track.stop());
    });
  }
}

export function createAudioMeter(stream, onLevel) {
  if (!stream?.getAudioTracks().length) return () => {};

  const audioContext = new AudioContext();
  const source = audioContext.createMediaStreamSource(stream);
  const analyser = audioContext.createAnalyser();
  const samples = new Uint8Array(analyser.frequencyBinCount);
  let frame;

  analyser.fftSize = 256;
  source.connect(analyser);

  const update = () => {
    analyser.getByteFrequencyData(samples);
    const average = samples.reduce((total, value) => total + value, 0) / samples.length / 255;
    onLevel(Math.min(1, average * 2.4));
    frame = requestAnimationFrame(update);
  };

  update();

  return () => {
    cancelAnimationFrame(frame);
    source.disconnect();
    analyser.disconnect();
    audioContext.close();
  };
}

function compactRecorderOptions(options) {
  return Object.fromEntries(Object.entries(options).filter(([, value]) => value !== "" && value != null));
}

function stopRecorder(recorder) {
  if (recorder.state === "inactive") return Promise.resolve();

  return new Promise((resolve) => {
    recorder.addEventListener("stop", resolve, { once: true });

    try {
      recorder.requestData();
    } catch (_) {
      // Some Chromium builds reject requestData immediately after pause; stop still flushes the data.
    }

    recorder.stop();
  });
}
