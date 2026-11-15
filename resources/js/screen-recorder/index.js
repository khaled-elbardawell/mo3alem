import { CaptureSession, createAudioMeter, detectCaptureCapabilities } from "./capture.js";
import { ExportCanceledError, estimateExportBytes, exportProject, prepareExportProfile } from "./exporter.js";
import { generateThumbnails, hitTest, PreviewController } from "./preview.js";
import {
  addOverlay,
  commitHistory,
  createHistory,
  createProjectManifest,
  deleteRange,
  estimateRecordingBytes,
  formatBytes,
  formatDuration,
  initializeTimeline,
  keepRange,
  normalizeProjectManifest,
  redoHistory,
  removeOverlay,
  safeRecordingDuration,
  splitAt,
  timelineDuration,
  undoHistory,
  updateOverlay,
  updateTimelineSettings
} from "./state.js";
import {
  deleteProject,
  getProjectFileUrl,
  listProjects,
  persistLocalStorage,
  saveProject,
  storageEstimate,
  supportsLocalProjectStorage
} from "./storage.js";

const root = document.querySelector("[data-screen-recorder]");
const THUMBNAIL_VERSION = 2;

if (root) initializeScreenRecorder(root);

async function initializeScreenRecorder(container) {
  const elements = collectElements(container);
  const config = JSON.parse(container.dataset.config || "{}");
  const capabilities = detectCaptureCapabilities();
  let currentManifest = null;
  let history = null;
  let captureSession = null;
  let preview = null;
  let recordingFinishing = false;
  let recordingStarted = false;
  let paused = false;
  let selectionStart = null;
  let selectionEnd = null;
  let selectedOverlayId = null;
  let pendingOverlayType = null;
  let pendingOverlayId = null;
  let timelineZoom = 1;
  let saveTimer = null;
  let toastTimer = null;
  let cancelExport = null;
  let exportProfile = null;
  let stopMicrophoneMeter = () => {};
  let stopSystemMeter = () => {};
  let microphoneLevel = 0;
  let systemLevel = 0;
  let lastWaveformSample = -1;
  let drag = null;

  bindEvents();

  if (!isSupported(capabilities) || !supportsLocalProjectStorage()) {
    showUnsupported(capabilities);
    return;
  }

  await persistLocalStorage().catch(() => false);
  prepareExportProfile()
    .then((profile) => {
      exportProfile = profile;
      elements.previewQuality.textContent = `${profile.format.toUpperCase()} حتى 1080p / 30fps`;
      elements.exportFormat.textContent = profile.format === "mp4" ? "MP4 عالي التوافق" : "WebM عالي الجودة";
      elements.startExport.disabled = false;
    })
    .catch(() => {
      elements.previewQuality.textContent = "التصدير غير مدعوم على هذا الجهاز";
      elements.exportFormat.textContent = "غير مدعوم";
      elements.exportVideo.disabled = true;
      elements.startExport.disabled = true;
    });
  await Promise.all([refreshStorageSummary(), refreshProjects()]);

  async function startRecording() {
    elements.startRecording.disabled = true;
    recordingStarted = false;
    recordingFinishing = false;
    paused = false;
    lastWaveformSample = -1;
    const options = {
      microphone: elements.recordMicrophone.checked,
      camera: elements.recordCamera.checked
    };

    try {
      const estimate = await storageEstimate();
      const safeDuration = safeRecordingDuration(estimate.available, options, config.storageReserveBytes);

      if (safeDuration < 60) {
        throw new Error("المساحة المحلية المتاحة لا تكفي لتسجيل دقيقة واحدة بأمان.");
      }

      currentManifest = createProjectManifest({
        title: `تسجيل شاشة ${new Intl.DateTimeFormat("ar", { dateStyle: "medium", timeStyle: "short" }).format(new Date())}`
      });
      currentManifest.waveform = [];
      await saveProject(currentManifest);
      captureSession = new CaptureSession({
        projectId: currentManifest.id,
        options,
        maxDurationSeconds: Math.min(config.maxDurationSeconds || 3600, safeDuration),
        callbacks: {
          onSourceUnavailable(source) {
            showToast(source === "camera" ? "تعذر تشغيل الكاميرا؛ سيستمر التسجيل بدونها." : "تعذر تشغيل الميكروفون؛ سيستمر التسجيل بدونه.", "warning");
          },
          onPrepared(streams) {
            elements.liveScreen.srcObject = streams.screen;
            elements.liveCamera.hidden = !streams.camera;
            elements.liveCamera.srcObject = streams.camera;
            elements.systemAudioStatus.textContent = streams.hasSystemAudio ? "صوت النظام متاح" : "صوت النظام غير متاح للمصدر المختار";
            stopMicrophoneMeter = createAudioMeter(streams.microphone, (level) => {
              microphoneLevel = level;
              elements.microphoneMeter.style.width = `${Math.round(level * 100)}%`;
            });
            stopSystemMeter = createAudioMeter(streams.screen, (level) => {
              systemLevel = level;
            });
          },
          onStarted() {
            recordingStarted = true;
            currentManifest.status = "recording";
            currentManifest.sources = Object.fromEntries(captureSession.recorders.map((item) => [item.name, {
              fileName: item.fileName,
              mimeType: item.mimeType,
              size: 0,
              offsetSeconds: 0
            }]));
            saveProject(currentManifest);
            postMetric("screen_recording_started");
          },
          onTick(elapsed, remaining) {
            elements.recordingTime.textContent = formatDuration(elapsed);
            elements.recordingRemaining.textContent = `متبقي ${formatDuration(remaining)}`;

            if (elapsed - lastWaveformSample >= 0.5) {
              currentManifest.waveform.push(Math.max(microphoneLevel, systemLevel));
              lastWaveformSample = elapsed;
            }
          },
          onPaused() {
            elements.recordingState.textContent = "التسجيل متوقف مؤقتًا";
            elements.recordingHint.textContent = "اضغط استئناف للمتابعة";
            elements.recordingDot.classList.remove("animate-pulse");
          },
          onResumed() {
            elements.recordingState.textContent = "جارٍ التسجيل";
            elements.recordingHint.textContent = "يُحفظ التسجيل محليًا كل ثانية";
            elements.recordingDot.classList.add("animate-pulse");
          },
          onWarning(type) {
            showToast(type === "one-minute" ? "تبقت دقيقة واحدة قبل إيقاف التسجيل تلقائيًا." : "تبقت خمس دقائق على الحد الأقصى للتسجيل.", "warning");
          },
          onLimitReached() {
            finishRecording("limit");
          },
          onScreenEnded() {
            finishRecording("screen-ended");
          },
          onLowStorage() {
            showToast("المساحة المحلية منخفضة؛ تم إنهاء التسجيل لحماية الملف.", "error");
            finishRecording("low-storage");
          },
          onWriteError(error) {
            showToast("تعذر حفظ جزء من التسجيل محليًا.", "error");
            console.error(error);
            finishRecording("write-error");
          },
          onRecorderError(error) {
            console.error(error);
            finishRecording("recorder-error");
          }
        }
      });

      await captureSession.prepare();
      showPanel("recording");
      await runCountdown();
      captureSession.begin();
    } catch (error) {
      await captureSession?.abort?.();
      await postMetric("screen_recording_failed");
      showPanel("start");
      showToast(readableCaptureError(error), "error");
      await refreshProjects();
    } finally {
      elements.startRecording.disabled = false;
    }
  }

  async function finishRecording(reason = "user") {
    if (!captureSession || recordingFinishing) return;

    recordingFinishing = true;
    elements.stopRecording.disabled = true;
    elements.pauseRecording.disabled = true;
    elements.recordingState.textContent = "جارٍ إنهاء التسجيل…";

    try {
      const result = await captureSession.stop(reason);
      stopMicrophoneMeter();
      stopSystemMeter();
      elements.liveScreen.srcObject = null;
      elements.liveCamera.srcObject = null;

      if (!recordingStarted || !result || result.duration < 0.5) {
        await deleteProject(currentManifest.id);
        showPanel("start");
        showToast("لم يتم إنشاء تسجيل قابل للتحرير.", "warning");
        return;
      }

      currentManifest.sources = {
        screen: result.sources.screen || null,
        microphone: result.sources.microphone || null,
        camera: result.sources.camera || null
      };
      currentManifest.hasSystemAudio = result.hasSystemAudio;
      currentManifest = initializeTimeline(currentManifest, result.duration);
      await saveProject(currentManifest);
      await openEditor(currentManifest);
    } catch (error) {
      await postMetric("screen_recording_failed");
      showToast(error.message || "تعذر إنهاء التسجيل المحلي.", "error");
      showPanel("start");
    } finally {
      captureSession = null;
      recordingFinishing = false;
      elements.stopRecording.disabled = false;
      elements.pauseRecording.disabled = false;
      await Promise.all([refreshProjects(), refreshStorageSummary()]);
    }
  }

  async function openEditor(project) {
    currentManifest = normalizeProjectManifest(project);

    if (!currentManifest.sources.screen?.fileName || timelineDuration(currentManifest) <= 0) {
      showToast("هذا المشروع غير مكتمل ولا يحتوي تسجيلًا صالحًا للتحرير.", "warning");
      return;
    }

    history = createHistory(currentManifest);
    currentManifest = history.present;
    selectionStart = 0;
    selectionEnd = timelineDuration(currentManifest);
    selectedOverlayId = null;
    preview?.destroy();
    preview = new PreviewController({
      canvas: elements.editorCanvas,
      screenVideo: elements.screenSource,
      cameraVideo: elements.cameraSource,
      microphoneAudio: elements.microphoneSource,
      onTimeUpdate: updatePlayheadUi,
      onEnded: () => updatePlayButton(false)
    });
    showPanel("editor");

    try {
      await preview.load(currentManifest);
      syncEditorUi();
      await ensureThumbnails();
    } catch (error) {
      showToast(error.message || "تعذر فتح ملفات المشروع المحلي.", "error");
      showPanel("start");
    }
  }

  async function ensureThumbnails() {
    if (currentManifest.thumbnailVersion === THUMBNAIL_VERSION && currentManifest.thumbnails?.length) {
      renderTimeline();
      return;
    }

    const url = await getProjectFileUrl(currentManifest.id, currentManifest.sources.screen.fileName);

    try {
      currentManifest.thumbnails = await generateThumbnails(url, currentManifest.duration, 12);
      currentManifest.thumbnailVersion = THUMBNAIL_VERSION;
      history.present = currentManifest;
      await saveProject(currentManifest);
      renderTimeline();
    } finally {
      URL.revokeObjectURL(url);
    }
  }

  function applyEdit(transform, options = {}) {
    const next = transform(history.present);

    if (!next || next === history.present) return;

    history = commitHistory(history, next);
    currentManifest = history.present;
    if (Object.hasOwn(options, "selectedOverlayId")) {
      selectedOverlayId = options.selectedOverlayId;
    }
    preview.setManifest(currentManifest);
    preview.setSelectedOverlay(selectedOverlayId);
    syncEditorUi();
    scheduleSave();
  }

  function handleEditAction(action) {
    const playhead = Number(elements.playhead.value);

    if (action === "split") applyEdit((manifest) => splitAt(manifest, playhead));

    if (action === "apply-trim") {
      if (!hasValidSelection()) {
        showToast("حرّك مؤشري البداية والنهاية لتحديد الجزء المطلوب.", "warning");
        return;
      }

      applyEdit((manifest) => keepRange(manifest, selectionStart, selectionEnd));
      selectionStart = 0;
      selectionEnd = timelineDuration(currentManifest);
      showToast("تم تطبيق القص وحذف ما يقع خارج المؤشرين. يمكنك التراجع إذا أردت.");
    }

    if (action === "delete-range") {
      const totalBeforeDelete = timelineDuration(currentManifest);

      if (!hasValidSelection()) {
        showToast("حرّك مؤشري البداية والنهاية لتحديد الجزء الذي تريد حذفه.", "warning");
        return;
      }

      if (selectionStart <= 0 && selectionEnd >= totalBeforeDelete) {
        showToast("لا يمكن حذف التسجيل كاملًا. حرّك البداية أو النهاية أولًا.", "warning");
        return;
      }

      applyEdit((manifest) => deleteRange(manifest, selectionStart, selectionEnd));
      selectionStart = 0;
      selectionEnd = timelineDuration(currentManifest);
      showToast("تم حذف الجزء المحدد. يمكنك التراجع إذا أردت.");
    }

    renderSelection();
    const total = timelineDuration(currentManifest);
    elements.playhead.max = String(total);
    elements.playhead.value = String(Math.min(playhead, total));
    preview.seek(Number(elements.playhead.value));
  }

  function hasValidSelection() {
    return selectionStart != null && selectionEnd != null && selectionEnd - selectionStart >= 1 / 30;
  }

  function syncEditorUi() {
    const total = timelineDuration(currentManifest);
    elements.projectTitle.value = currentManifest.title;
    elements.playhead.max = String(Math.max(0.01, total));
    elements.playhead.value = String(Math.min(Number(elements.playhead.value) || 0, total));
    elements.totalTime.textContent = formatDuration(total);
    elements.undo.disabled = history.past.length === 0;
    elements.redo.disabled = history.future.length === 0;
    elements.timelineUndo.disabled = history.past.length === 0;
    elements.timelineRedo.disabled = history.future.length === 0;
    elements.cameraSettings.hidden = !currentManifest.sources.camera;
    elements.cameraVisible.checked = currentManifest.timeline.camera.visible;
    elements.cameraSize.value = Math.round(currentManifest.timeline.camera.width * 100);
    elements.cameraShape.value = currentManifest.timeline.camera.shape;
    elements.cameraMirror.checked = currentManifest.timeline.camera.mirror;
    elements.systemGain.value = Math.round(currentManifest.timeline.audio.systemGain * 100);
    elements.systemGainOutput.textContent = `${elements.systemGain.value}%`;
    elements.systemMuted.checked = currentManifest.timeline.audio.systemMuted;
    elements.microphoneGain.value = Math.round(currentManifest.timeline.audio.microphoneGain * 100);
    elements.microphoneGainOutput.textContent = `${elements.microphoneGain.value}%`;
    elements.microphoneMuted.checked = currentManifest.timeline.audio.microphoneMuted;

    Object.entries(currentManifest.timeline.crop).forEach(([field, value]) => {
      const input = container.querySelector(`[data-crop-field="${field}"]`);
      if (input) input.value = Math.round(value * 100);
    });

    renderTimeline();
    renderSelection();
    renderOverlaySettings();
    updatePlayheadUi(Number(elements.playhead.value));
  }

  function renderTimeline() {
    elements.timelineSegments.replaceChildren();
    elements.timelineShell.dir = "ltr";
    const total = timelineDuration(currentManifest);
    const thumbnails = currentManifest.thumbnails || [];

    elements.timelineContent.style.width = `${timelineZoom * 100}%`;
    elements.timelineTotalTime.textContent = formatDuration(total);
    renderTimelineRuler(total);

    currentManifest.timeline.segments.forEach((segment, index) => {
      const duration = segment.sourceEnd - segment.sourceStart;
      const segmentElement = document.createElement("div");
      segmentElement.className = "relative isolate min-w-12 overflow-hidden rounded-lg border border-violet-300 bg-violet-200";
      segmentElement.style.flex = `0 0 ${Math.max(2, duration / total * 100)}%`;
      segmentElement.title = `المقطع ${index + 1}: ${formatDuration(duration)}`;

      const matchingThumbnails = thumbnails.filter((thumbnail) => thumbnail.time >= segment.sourceStart && thumbnail.time <= segment.sourceEnd);
      const imageRow = document.createElement("div");
      imageRow.className = "absolute inset-0 flex opacity-75";
      matchingThumbnails.slice(0, 5).forEach((thumbnail) => {
        const image = document.createElement("img");
        image.src = thumbnail.dataUrl;
        image.alt = "";
        image.className = "h-full min-w-0 flex-1 object-cover";
        imageRow.append(image);
      });
      segmentElement.append(imageRow);

      const waveform = document.createElement("div");
      waveform.className = "absolute inset-x-1 bottom-1 flex h-6 items-end gap-px opacity-75";
      const startWave = Math.floor(segment.sourceStart * 2);
      const endWave = Math.max(startWave + 1, Math.floor(segment.sourceEnd * 2));
      const levels = (currentManifest.waveform || []).slice(startWave, endWave);
      const sampleStep = Math.max(1, Math.ceil(levels.length / 48));
      levels.filter((_, levelIndex) => levelIndex % sampleStep === 0).forEach((level) => {
        const bar = document.createElement("i");
        bar.className = "block min-w-px flex-1 rounded-t bg-white";
        bar.style.height = `${Math.max(8, level * 100)}%`;
        waveform.append(bar);
      });
      segmentElement.append(waveform);

      const label = document.createElement("span");
      label.className = "absolute top-1 left-1 z-10 rounded bg-slate-950/70 px-1.5 py-0.5 text-[10px] font-black text-white";
      label.textContent = `${index + 1}`;
      segmentElement.append(label);
      elements.timelineSegments.append(segmentElement);
    });

    if (!elements.timelineSegments.children.length) {
      const empty = document.createElement("p");
      empty.className = "grid w-full place-items-center text-sm font-bold text-slate-500";
      empty.textContent = "حُذف كامل التسجيل. استخدم التراجع لاستعادته.";
      elements.timelineSegments.append(empty);
    }

  }

  function renderTimelineRuler(total) {
    elements.timelineRuler.replaceChildren();
    elements.timelineGrid.replaceChildren();
    const divisions = total <= 30 ? 6 : total <= 120 ? 8 : 10;

    for (let index = 0; index <= divisions; index += 1) {
      const ratio = index / divisions;
      const mark = document.createElement("span");
      mark.className = `absolute bottom-1 font-mono ${index === 0 ? "" : index === divisions ? "-translate-x-full" : "-translate-x-1/2"}`;
      mark.style.left = `${ratio * 100}%`;
      mark.textContent = formatDuration(total * ratio);
      elements.timelineRuler.append(mark);

      const gridLine = document.createElement("i");
      gridLine.className = "h-full w-px bg-slate-200/70";
      elements.timelineGrid.append(gridLine);
    }
  }

  function setTimelineZoom(value) {
    timelineZoom = clamp(Number(value) || 1, 1, 3);
    elements.timelineZoom.value = String(timelineZoom);

    if (currentManifest) {
      elements.timelineContent.style.width = `${timelineZoom * 100}%`;
      renderTimelineRuler(timelineDuration(currentManifest));
    }
  }

  function renderSelection() {
    const total = timelineDuration(currentManifest);

    selectionStart = Math.max(0, Math.min(Number(selectionStart) || 0, total));
    const requestedEnd = Number(selectionEnd);
    selectionEnd = Math.max(selectionStart, Math.min(Number.isFinite(requestedEnd) ? requestedEnd : total, total));
    elements.selectionStart.max = String(Math.max(0.01, total));
    elements.selectionEnd.max = String(Math.max(0.01, total));
    elements.selectionStart.value = String(selectionStart);
    elements.selectionEnd.value = String(selectionEnd);
    elements.selectionStartOutput.textContent = formatDuration(selectionStart);
    elements.selectionEndOutput.textContent = formatDuration(selectionEnd);

    if (!hasValidSelection() || !total) {
      elements.timelineSelection.classList.add("hidden");
      elements.selectionLabel.textContent = "حرّك البداية والنهاية";
      return;
    }

    elements.timelineSelection.classList.remove("hidden");
    elements.timelineSelection.style.left = `${selectionStart / total * 100}%`;
    elements.timelineSelection.style.width = `${(selectionEnd - selectionStart) / total * 100}%`;
    elements.selectionLabel.textContent = `الجزء المحدد: ${formatDuration(selectionEnd - selectionStart)}`;
  }

  function updatePlayheadUi(outputTime) {
    const total = currentManifest ? timelineDuration(currentManifest) : 0;
    const value = Math.max(0, Math.min(Number(outputTime) || 0, total));
    elements.playhead.value = String(value);
    elements.playheadTime.textContent = formatDuration(value);
    elements.timelineCurrentTime.textContent = formatDuration(value);
    elements.timelinePlayhead.style.left = `${total ? value / total * 100 : 0}%`;
  }

  function renderOverlaySettings() {
    const overlay = currentManifest.timeline.overlays.find((item) => item.id === selectedOverlayId);

    preview.setSelectedOverlay(overlay?.id || null);
    renderOverlayList();
  }

  function renderOverlayList() {
    elements.overlayList.replaceChildren();
    const total = timelineDuration(currentManifest);
    const overlays = currentManifest.timeline.overlays || [];
    elements.overlayCount.textContent = overlays.length === 0
      ? "لا توجد إضافات"
      : overlays.length === 1 ? "إضافة واحدة" : `${overlays.length} إضافات`;

    if (!overlays.length || !total) {
      const empty = document.createElement("p");
      empty.className = "rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm font-bold text-slate-500";
      empty.textContent = "لا توجد إضافات بعد. اختر نصًا أو سهمًا أو تمويهًا.";
      elements.overlayList.append(empty);
      return;
    }

    overlays.forEach((overlay) => {
      const start = clamp(overlay.start, 0, total);
      const end = clamp(overlay.end, start, total);
      const item = elements.overlayItemTemplate.content.firstElementChild.cloneNode(true);
      const icon = item.querySelector("[data-overlay-icon] i");
      const name = item.querySelector("[data-overlay-name]");
      const time = item.querySelector("[data-overlay-time]");
      const editButton = item.querySelector("[data-overlay-edit]");
      const deleteButton = item.querySelector("[data-overlay-delete]");
      const iconClasses = {
        text: "fa-solid fa-font",
        arrow: "fa-solid fa-arrow-left-long",
        blur: "fa-solid fa-eye-slash"
      };
      const typeLabels = { text: "نص", arrow: "سهم", blur: "تمويه" };

      icon.className = iconClasses[overlay.type] || "fa-solid fa-shapes";
      name.textContent = overlay.type === "text" ? overlay.text : typeLabels[overlay.type] || "عنصر";
      time.textContent = `${formatDuration(start)} – ${formatDuration(end)} · ${formatDuration(end - start)}`;
      item.title = `${name.textContent} · ${formatDuration(start)} – ${formatDuration(end)}`;
      editButton.setAttribute("aria-label", `تعديل ${typeLabels[overlay.type] || "العنصر"}`);
      deleteButton.setAttribute("aria-label", `حذف ${typeLabels[overlay.type] || "العنصر"}`);

      if (overlay.id === selectedOverlayId) {
        item.classList.remove("border-slate-200", "bg-white");
        item.classList.add("border-violet-400", "bg-violet-50", "ring-2", "ring-violet-100");
      }

      editButton.addEventListener("click", () => {
        selectedOverlayId = overlay.id;
        preview.seek(start);
        renderOverlaySettings();
        openCreateOverlayDialog(overlay.type, overlay.id);
      });
      deleteButton.addEventListener("click", () => deleteOverlayById(overlay.id));
      elements.overlayList.append(item);
    });
  }

  function deleteOverlayById(overlayId) {
    const overlay = currentManifest.timeline.overlays.find((item) => item.id === overlayId);

    if (!overlay) return;

    const nextSelectedOverlayId = selectedOverlayId === overlayId ? null : selectedOverlayId;
    applyEdit((manifest) => removeOverlay(manifest, overlayId), { selectedOverlayId: nextSelectedOverlayId });
    showToast(`تم حذف ${overlay.type === "arrow" ? "السهم" : overlay.type === "blur" ? "التمويه" : "النص"}.`);
  }

  function scheduleSave() {
    elements.saveStatus.textContent = "جارٍ الحفظ محليًا…";
    elements.saveStatus.classList.remove("text-emerald-600");
    elements.saveStatus.classList.add("text-amber-600");
    window.clearTimeout(saveTimer);
    saveTimer = window.setTimeout(async () => {
      await saveProject(currentManifest);
      elements.saveStatus.textContent = "محفوظ محليًا";
      elements.saveStatus.classList.add("text-emerald-600");
      elements.saveStatus.classList.remove("text-amber-600");
      refreshProjects();
    }, 450);
  }

  async function refreshProjects() {
    const projects = await listProjects();
    elements.projectList.replaceChildren();
    elements.projectsEmpty.hidden = projects.length > 0;

    projects.forEach((project) => {
      const card = document.createElement("article");
      card.className = "rounded-2xl border border-slate-200 bg-slate-50 p-4";
      const heading = document.createElement("div");
      heading.className = "flex items-start justify-between gap-3";
      const text = document.createElement("div");
      const title = document.createElement("h3");
      title.className = "line-clamp-2 font-black text-slate-900";
      title.textContent = project.title || "تسجيل شاشة";
      const details = document.createElement("p");
      details.className = "mt-1 text-xs font-bold text-slate-500";
      details.textContent = project.status === "editing"
        ? `${formatDuration(timelineDuration(normalizeProjectManifest(project)))} · ${formatLocalDate(project.updatedAt)}`
        : `مشروع غير مكتمل · ${formatLocalDate(project.updatedAt)}`;
      text.append(title, details);
      heading.append(text);
      card.append(heading);
      const actions = document.createElement("div");
      actions.className = "mt-3 flex gap-2";

      if (project.status === "editing") {
        const openButton = document.createElement("button");
        openButton.type = "button";
        openButton.className = "min-h-9 flex-1 rounded-xl bg-violet-700 px-3 text-sm font-black text-white hover:bg-violet-800";
        openButton.textContent = "استكمال التحرير";
        openButton.addEventListener("click", () => openEditor(project));
        actions.append(openButton);
      }

      const deleteButton = document.createElement("button");
      deleteButton.type = "button";
      deleteButton.className = "grid size-9 place-items-center rounded-xl bg-rose-100 text-rose-700 hover:bg-rose-200";
      deleteButton.setAttribute("aria-label", `حذف ${project.title || "المشروع"}`);
      deleteButton.innerHTML = '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>';
      deleteButton.addEventListener("click", async () => {
        if (!window.confirm("حذف هذا المشروع وملفاته من الجهاز نهائيًا؟")) return;

        await deleteProject(project.id);
        await Promise.all([refreshProjects(), refreshStorageSummary()]);
        showToast("حُذف المشروع المحلي.");
      });
      actions.append(deleteButton);
      card.append(actions);
      elements.projectList.append(card);
    });
  }

  async function refreshStorageSummary() {
    const estimate = await storageEstimate();
    const options = { camera: elements.recordCamera.checked, microphone: elements.recordMicrophone.checked };
    const safeDuration = safeRecordingDuration(estimate.available, options, config.storageReserveBytes);
    const cappedDuration = Math.min(config.maxDurationSeconds || 3600, safeDuration);
    const usedPercentage = estimate.quota ? estimate.usage / estimate.quota * 100 : 0;

    elements.storageAvailable.textContent = estimate.quota ? formatBytes(estimate.available) : "غير معروف";
    elements.storageBar.style.width = `${Math.max(2, Math.min(100, usedPercentage))}%`;
    elements.safeDuration.textContent = formatDuration(cappedDuration);
    elements.estimatedSize.textContent = formatBytes(estimateRecordingBytes(cappedDuration, options));
    elements.startRecording.disabled = cappedDuration < 60;
  }

  function openExportDialog() {
    if (!currentManifest || timelineDuration(currentManifest) <= 0) {
      showToast("لا يوجد محتوى قابل للتصدير.", "warning");
      return;
    }

    elements.exportReady.classList.remove("hidden");
    elements.exportWorking.classList.add("hidden");
    elements.exportSize.textContent = formatBytes(estimateExportBytes(currentManifest));
    elements.startExport.disabled = !exportProfile;
    elements.exportMessage.textContent = "يتم تركيب أجزاء الفيديو والإضافات ثم ترميز الملف. يمكنك متابعة العمل في نافذة أخرى.";
    elements.exportDialog.showModal();
  }

  async function handleExport() {
    if (!currentManifest || !exportProfile || timelineDuration(currentManifest) <= 0) {
      showToast("التصدير غير متاح حاليًا على هذا الجهاز.", "warning");
      return;
    }

    elements.exportReady.classList.add("hidden");
    elements.exportWorking.classList.remove("hidden");
    elements.exportProgress.style.width = "0%";
    elements.exportPercent.textContent = "0%";
    elements.exportStage.textContent = `الحجم المتوقع ${formatBytes(estimateExportBytes(currentManifest))}`;
    elements.cancelExport.disabled = false;
    elements.startExport.disabled = true;

    try {
      const result = await exportProject(currentManifest, {
        onReadyToCancel(callback) {
          cancelExport = callback;
        },
        onProgress(progress, stage) {
          const percentage = Math.round(progress * 100);
          elements.exportProgress.style.width = `${percentage}%`;
          elements.exportPercent.textContent = `${percentage}%`;
          elements.exportStage.textContent = stage === "saving" ? "جارٍ الحفظ إلى جهازك…" : "جارٍ تركيب الفيديو وترميزه…";
        },
        onFallback() {
          elements.exportMessage.textContent = "سيُنزّل الملف تلقائيًا عند اكتمال التصدير.";
        }
      }, exportProfile);
      await postMetric("screen_recording_exported");
      elements.exportDialog.close();
      showToast(result.saveMethod === "picker"
        ? `اكتمل حفظ ${result.savedAs} في المسار الذي اخترته.`
        : `اكتمل تنزيل ${result.savedAs}.`);
    } catch (error) {
      elements.exportDialog.close();

      if (!(error instanceof ExportCanceledError)) {
        await postMetric("screen_recording_failed");
        showToast(error.message || "تعذر تصدير الفيديو.", "error");
      } else {
        showToast("تم إلغاء التصدير.", "warning");
      }
    } finally {
      cancelExport = null;
      elements.startExport.disabled = false;
    }
  }

  function handleCanvasPointerDown(event) {
    if (!currentManifest) return;

    const point = canvasPoint(event);
    const target = hitTest(currentManifest, Number(elements.playhead.value), point.x, point.y);

    if (!target) {
      selectedOverlayId = null;
      renderOverlaySettings();
      return;
    }

    selectedOverlayId = target.type === "overlay" ? target.id : null;
    renderOverlaySettings();
    elements.editorCanvas.setPointerCapture(event.pointerId);
    drag = {
      target,
      pointerId: event.pointerId,
      offsetX: point.x - target.rectangle.x,
      offsetY: point.y - target.rectangle.y,
      x: target.rectangle.x,
      y: target.rectangle.y
    };
  }

  function handleCanvasPointerMove(event) {
    if (!drag || drag.pointerId !== event.pointerId) return;

    const point = canvasPoint(event);
    drag.x = clamp(point.x - drag.offsetX, 0, 1 - drag.target.rectangle.width);
    drag.y = clamp(point.y - drag.offsetY, 0, 1 - drag.target.rectangle.height);
    const draft = structuredClone(history.present);

    if (drag.target.type === "camera") {
      draft.timeline.camera.x = drag.x;
      draft.timeline.camera.y = drag.y;
    } else {
      const overlay = draft.timeline.overlays.find((item) => item.id === drag.target.id);
      if (overlay) {
        overlay.x = drag.x;
        overlay.y = drag.y;
      }
    }

    currentManifest = draft;
    preview.setManifest(draft);
  }

  function handleCanvasPointerUp(event) {
    if (!drag || drag.pointerId !== event.pointerId) return;

    const finishedDrag = drag;
    drag = null;

    if (finishedDrag.target.type === "camera") {
      applyEdit((manifest) => updateTimelineSettings(manifest, "camera", { x: finishedDrag.x, y: finishedDrag.y }));
    } else {
      applyEdit((manifest) => updateOverlay(manifest, finishedDrag.target.id, { x: finishedDrag.x, y: finishedDrag.y }));
    }
  }

  function bindEvents() {
    elements.startRecording.addEventListener("click", startRecording);
    elements.recordMicrophone.addEventListener("change", refreshStorageSummary);
    elements.recordCamera.addEventListener("change", refreshStorageSummary);
    elements.stopRecording.addEventListener("click", () => finishRecording("user"));
    elements.pauseRecording.addEventListener("click", () => {
      paused = !paused;
      if (paused) captureSession.pause();
      else captureSession.resume();
      elements.pauseRecording.querySelector("i").className = `fa-solid ${paused ? "fa-play" : "fa-pause"}`;
      elements.pauseRecording.querySelector("span").textContent = paused ? "استئناف" : "إيقاف مؤقت";
    });
    elements.backToProjects.addEventListener("click", async () => {
      preview?.pause();
      await saveProject(currentManifest);
      showPanel("start");
      await Promise.all([refreshProjects(), refreshStorageSummary()]);
    });
    elements.playPreview.addEventListener("click", async () => updatePlayButton(await preview.toggle()));
    elements.playhead.addEventListener("input", () => preview.seek(Number(elements.playhead.value)));
    elements.timelineShell.addEventListener("click", (event) => {
      if (event.target.closest("[data-overlay-item]")) return;
      const rectangle = elements.timelineShell.getBoundingClientRect();
      const ratio = clamp((event.clientX - rectangle.left) / rectangle.width, 0, 1);
      preview.seek(timelineDuration(currentManifest) * ratio);
    });
    elements.timelineZoom.addEventListener("input", () => setTimelineZoom(elements.timelineZoom.value));
    elements.timelineZoomOut.addEventListener("click", () => setTimelineZoom(timelineZoom - 0.25));
    elements.timelineZoomIn.addEventListener("click", () => setTimelineZoom(timelineZoom + 0.25));
    elements.timelineUndo.addEventListener("click", () => elements.undo.click());
    elements.timelineRedo.addEventListener("click", () => elements.redo.click());
    elements.selectionStart.addEventListener("input", () => {
      selectionStart = Math.min(Number(elements.selectionStart.value), Math.max(0, selectionEnd - 1 / 30));
      renderSelection();
      preview.seek(selectionStart);
    });
    elements.selectionEnd.addEventListener("input", () => {
      selectionEnd = Math.max(Number(elements.selectionEnd.value), selectionStart + 1 / 30);
      renderSelection();
      preview.seek(selectionEnd);
    });
    container.querySelectorAll("[data-edit-action]").forEach((button) => {
      button.addEventListener("click", () => handleEditAction(button.dataset.editAction));
    });
    container.querySelectorAll("[data-add-overlay]").forEach((button) => {
      button.addEventListener("click", () => openCreateOverlayDialog(button.dataset.addOverlay));
    });
    elements.undo.addEventListener("click", () => {
      history = undoHistory(history);
      currentManifest = history.present;
      preview.setManifest(currentManifest);
      selectedOverlayId = null;
      syncEditorUi();
      scheduleSave();
    });
    elements.redo.addEventListener("click", () => {
      history = redoHistory(history);
      currentManifest = history.present;
      preview.setManifest(currentManifest);
      selectedOverlayId = null;
      syncEditorUi();
      scheduleSave();
    });
    elements.projectTitle.addEventListener("change", () => {
      const title = elements.projectTitle.value.trim().slice(0, 80) || "تسجيل شاشة";
      applyEdit((manifest) => ({ ...structuredClone(manifest), title, updatedAt: new Date().toISOString(), revision: manifest.revision + 1 }));
    });
    container.querySelectorAll("[data-crop-field]").forEach((input) => {
      input.addEventListener("change", () => {
        const crop = Object.fromEntries([...container.querySelectorAll("[data-crop-field]")]
          .map((field) => [field.dataset.cropField, Number(field.value) / 100]));
        applyEdit((manifest) => updateTimelineSettings(manifest, "crop", crop));
      });
    });
    elements.resetCrop.addEventListener("click", () => applyEdit((manifest) => updateTimelineSettings(manifest, "crop", { x: 0, y: 0, width: 1, height: 1 })));
    elements.cameraVisible.addEventListener("change", () => applyEdit((manifest) => updateTimelineSettings(manifest, "camera", { visible: elements.cameraVisible.checked })));
    elements.cameraSize.addEventListener("change", () => applyEdit((manifest) => updateTimelineSettings(manifest, "camera", {
      width: Number(elements.cameraSize.value) / 100,
      height: Number(elements.cameraSize.value) / 100 * 9 / 16
    })));
    elements.cameraShape.addEventListener("change", () => applyEdit((manifest) => updateTimelineSettings(manifest, "camera", { shape: elements.cameraShape.value })));
    elements.cameraMirror.addEventListener("change", () => applyEdit((manifest) => updateTimelineSettings(manifest, "camera", { mirror: elements.cameraMirror.checked })));
    elements.systemGain.addEventListener("change", () => applyAudioSettings());
    elements.systemMuted.addEventListener("change", () => applyAudioSettings());
    elements.microphoneGain.addEventListener("change", () => applyAudioSettings());
    elements.microphoneMuted.addEventListener("change", () => applyAudioSettings());
    [elements.systemGain, elements.microphoneGain].forEach((input) => input.addEventListener("input", () => {
      elements.systemGainOutput.textContent = `${elements.systemGain.value}%`;
      elements.microphoneGainOutput.textContent = `${elements.microphoneGain.value}%`;
    }));
    [elements.overlayCreateStart, elements.overlayCreateEnd].forEach((input) => input.addEventListener("input", updateCreateOverlayTimeOutputs));
    elements.overlayCreateBlur.addEventListener("input", () => {
      elements.overlayCreateBlurOutput.textContent = elements.overlayCreateBlur.value;
    });
    elements.overlayCreateForm.addEventListener("submit", createOverlayFromDialog);
    elements.overlayCreateClose.addEventListener("click", closeCreateOverlayDialog);
    elements.overlayCreateCancel.addEventListener("click", closeCreateOverlayDialog);
    elements.overlayCreateDialog.addEventListener("cancel", () => {
      pendingOverlayType = null;
      pendingOverlayId = null;
    });
    elements.editorCanvas.addEventListener("pointerdown", handleCanvasPointerDown);
    elements.editorCanvas.addEventListener("pointermove", handleCanvasPointerMove);
    elements.editorCanvas.addEventListener("pointerup", handleCanvasPointerUp);
    elements.editorCanvas.addEventListener("pointercancel", handleCanvasPointerUp);
    elements.exportVideo.addEventListener("click", openExportDialog);
    elements.startExport.addEventListener("click", handleExport);
    elements.closeExport.addEventListener("click", () => elements.exportDialog.close());
    elements.cancelExport.addEventListener("click", async () => {
      elements.cancelExport.disabled = true;
      await cancelExport?.();
      elements.exportDialog.close();
    });
    document.addEventListener("keydown", handleKeyboardShortcuts);
    window.addEventListener("beforeunload", (event) => {
      if (!captureSession || !recordingStarted) return;
      event.preventDefault();
      event.returnValue = "";
    });
  }

  function openCreateOverlayDialog(type, overlayId = null) {
    const total = timelineDuration(currentManifest);

    if (!total) {
      showToast("لا يوجد فيديو لإضافة عنصر إليه.", "warning");
      return;
    }

    const typeSettings = {
      text: { label: "نص", icon: "fa-solid fa-font" },
      arrow: { label: "سهم", icon: "fa-solid fa-arrow-left-long" },
      blur: { label: "تمويه", icon: "fa-solid fa-eye-slash" }
    };
    const settings = typeSettings[type];
    const overlay = overlayId
      ? currentManifest.timeline.overlays.find((item) => item.id === overlayId && item.type === type)
      : null;

    if (!settings || (overlayId && !overlay)) return;

    pendingOverlayType = type;
    pendingOverlayId = overlay?.id || null;
    const requestedStart = clamp(Number(elements.playhead.value), 0, total);
    const defaultStart = requestedStart >= total ? Math.max(0, total - Math.min(5, total)) : requestedStart;
    const start = overlay ? clamp(overlay.start, 0, total) : defaultStart;
    const end = overlay ? clamp(overlay.end, start, total) : Math.min(total, start + Math.min(5, total));
    elements.overlayCreateMode.textContent = overlay ? "تعديل الإضافة" : "إضافة جديدة";
    elements.overlayCreateTitle.textContent = `${overlay ? "تعديل" : "إضافة"} ${settings.label}`;
    elements.overlayCreateSubmit.textContent = overlay ? "حفظ التعديلات" : "إضافة إلى الفيديو";
    elements.overlayCreateIcon.querySelector("i").className = settings.icon;
    elements.overlayCreateTextField.classList.toggle("hidden", type !== "text");
    elements.overlayCreateDirectionField.classList.toggle("hidden", type !== "arrow");
    elements.overlayCreateBlurField.classList.toggle("hidden", type !== "blur");
    elements.overlayCreateColorField.classList.toggle("hidden", type === "blur");
    elements.overlayCreateText.value = overlay?.text || "نص توضيحي";
    elements.overlayCreateDirection.value = overlay?.direction || "left";
    elements.overlayCreateBlur.value = String(overlay?.blur || 16);
    elements.overlayCreateBlurOutput.textContent = String(overlay?.blur || 16);
    elements.overlayCreateColor.value = overlay?.background || "#6d28d9";
    elements.overlayCreateStart.max = String(total);
    elements.overlayCreateEnd.max = String(total);
    elements.overlayCreateStart.value = String(start);
    elements.overlayCreateEnd.value = String(end);
    elements.overlayCreateError.classList.add("hidden");
    updateCreateOverlayTimeOutputs();
    elements.overlayCreateDialog.showModal();

    if (type === "text") {
      window.requestAnimationFrame(() => {
        elements.overlayCreateText.focus();
        elements.overlayCreateText.select();
      });
    }
  }

  function updateCreateOverlayTimeOutputs() {
    elements.overlayCreateStartOutput.textContent = formatDuration(Number(elements.overlayCreateStart.value));
    elements.overlayCreateEndOutput.textContent = formatDuration(Number(elements.overlayCreateEnd.value));
  }

  function createOverlayFromDialog(event) {
    event.preventDefault();

    if (!pendingOverlayType || !history) return;

    const total = timelineDuration(currentManifest);
    const start = clamp(Number(elements.overlayCreateStart.value), 0, total);
    const end = clamp(Number(elements.overlayCreateEnd.value), 0, total);
    const text = elements.overlayCreateText.value.trim();
    let errorMessage = "";

    if (end - start < 1 / 30) errorMessage = "اختر وقت نهاية يأتي بعد وقت البداية.";
    if (pendingOverlayType === "text" && !text) errorMessage = "اكتب النص الذي تريد إظهاره على الفيديو.";

    if (errorMessage) {
      elements.overlayCreateError.textContent = errorMessage;
      elements.overlayCreateError.classList.remove("hidden");
      return;
    }

    const changes = {
      text,
      start,
      end,
      background: elements.overlayCreateColor.value,
      direction: elements.overlayCreateDirection.value,
      blur: Number(elements.overlayCreateBlur.value)
    };

    if (pendingOverlayId) {
      const editedOverlayId = pendingOverlayId;
      applyEdit((manifest) => updateOverlay(manifest, editedOverlayId, changes), { selectedOverlayId: editedOverlayId });
      closeCreateOverlayDialog();
      showToast("تم حفظ تعديلات الإضافة.");
      return;
    }

    const added = addOverlay(history.present, pendingOverlayType, start);
    const configuredManifest = updateOverlay(added.manifest, added.overlayId, changes);
    applyEdit(() => configuredManifest, { selectedOverlayId: added.overlayId });
    closeCreateOverlayDialog();
    showToast("تمت إضافة العنصر إلى الفيديو. يمكنك سحبه لتغيير مكانه.");
  }

  function closeCreateOverlayDialog() {
    pendingOverlayType = null;
    pendingOverlayId = null;
    elements.overlayCreateDialog.close();
  }

  function applyAudioSettings() {
    applyEdit((manifest) => updateTimelineSettings(manifest, "audio", {
      systemGain: Number(elements.systemGain.value) / 100,
      systemMuted: elements.systemMuted.checked,
      microphoneGain: Number(elements.microphoneGain.value) / 100,
      microphoneMuted: elements.microphoneMuted.checked
    }));
  }

  function handleKeyboardShortcuts(event) {
    if (!history || elements.editorPanel.classList.contains("hidden")) return;
    const target = event.target;
    const isEditing = ["INPUT", "TEXTAREA", "SELECT"].includes(target?.tagName) || target?.isContentEditable;

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "z" && !isEditing) {
      event.preventDefault();
      if (event.shiftKey) elements.redo.click();
      else elements.undo.click();
    }

    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "y" && !isEditing) {
      event.preventDefault();
      elements.redo.click();
    }

    if (event.code === "Space" && !isEditing) {
      event.preventDefault();
      elements.playPreview.click();
    }

    if ((event.key === "Delete" || event.key === "Backspace") && selectedOverlayId && !isEditing) {
      event.preventDefault();
      deleteOverlayById(selectedOverlayId);
    }
  }

  function showPanel(panel) {
    elements.startPanel.classList.toggle("hidden", panel !== "start");
    elements.recordingPanel.classList.toggle("hidden", panel !== "recording");
    elements.editorPanel.classList.toggle("hidden", panel !== "editor");
  }

  function showUnsupported(currentCapabilities) {
    elements.startPanel.classList.add("hidden");
    elements.unsupportedPanel.classList.remove("hidden");
    const missing = [];
    if (!currentCapabilities.desktopChromium) missing.push("Chrome أو Edge على الكمبيوتر");
    if (!currentCapabilities.secureContext) missing.push("اتصال HTTPS آمن");
    if (!currentCapabilities.displayMedia) missing.push("واجهة تسجيل الشاشة");
    if (!currentCapabilities.webCodecs) missing.push("معالجة WebCodecs");
    if (!currentCapabilities.opfs) missing.push("التخزين المحلي OPFS");
    elements.unsupportedMessage.textContent = `المطلوب: ${missing.join("، ") || "متصفح حديث متوافق"}.`;
  }

  async function runCountdown() {
    elements.countdownOverlay.hidden = false;

    for (let value = 3; value >= 1; value -= 1) {
      elements.countdownValue.textContent = String(value);
      await delay(800);
    }

    elements.countdownValue.textContent = "ابدأ";
    await delay(450);
    elements.countdownOverlay.hidden = true;
  }

  function updatePlayButton(isPlaying) {
    const icon = elements.playPreview.querySelector("i");
    icon.className = `fa-solid ${isPlaying ? "fa-pause" : "fa-play"}`;
    elements.playPreview.setAttribute("aria-label", isPlaying ? "إيقاف المعاينة" : "تشغيل المعاينة");
  }

  function canvasPoint(event) {
    const rectangle = elements.editorCanvas.getBoundingClientRect();
    return {
      x: clamp((event.clientX - rectangle.left) / rectangle.width, 0, 1),
      y: clamp((event.clientY - rectangle.top) / rectangle.height, 0, 1)
    };
  }

  function showToast(message, tone = "success") {
    window.clearTimeout(toastTimer);
    elements.toast.textContent = message;
    elements.toast.classList.remove("bg-slate-950", "bg-rose-700", "bg-amber-600");
    elements.toast.classList.add(tone === "error" ? "bg-rose-700" : tone === "warning" ? "bg-amber-600" : "bg-slate-950");
    elements.toast.classList.remove("pointer-events-none", "translate-y-4", "opacity-0");
    toastTimer = window.setTimeout(() => {
      elements.toast.classList.add("pointer-events-none", "translate-y-4", "opacity-0");
    }, 4200);
  }

  function postMetric(event) {
    return fetch(config.metricsEndpoint, {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
      },
      body: JSON.stringify({ event })
    }).catch(() => null);
  }
}

function collectElements(container) {
  const selectors = {
    unsupportedPanel: "[data-unsupported-panel]",
    unsupportedMessage: "[data-unsupported-message]",
    startPanel: "[data-start-panel]",
    recordingPanel: "[data-recording-panel]",
    editorPanel: "[data-editor-panel]",
    startRecording: "[data-start-recording]",
    recordMicrophone: "[data-record-microphone]",
    recordCamera: "[data-record-camera]",
    storageAvailable: "[data-storage-available]",
    storageBar: "[data-storage-bar]",
    safeDuration: "[data-safe-duration]",
    estimatedSize: "[data-estimated-size]",
    projectList: "[data-project-list]",
    projectsEmpty: "[data-projects-empty]",
    liveScreen: "[data-live-screen]",
    liveCamera: "[data-live-camera]",
    countdownOverlay: "[data-countdown-overlay]",
    countdownValue: "[data-countdown-value]",
    recordingTime: "[data-recording-time]",
    recordingRemaining: "[data-recording-remaining]",
    recordingState: "[data-recording-state]",
    recordingHint: "[data-recording-hint]",
    recordingDot: "[data-recording-dot]",
    systemAudioStatus: "[data-system-audio-status]",
    microphoneMeter: "[data-microphone-meter]",
    pauseRecording: "[data-pause-recording]",
    stopRecording: "[data-stop-recording]",
    backToProjects: "[data-back-to-projects]",
    projectTitle: "[data-project-title]",
    saveStatus: "[data-save-status]",
    undo: "[data-undo]",
    redo: "[data-redo]",
    exportVideo: "[data-export-video]",
    editorCanvas: "[data-editor-canvas]",
    screenSource: "[data-screen-source]",
    cameraSource: "[data-camera-source]",
    microphoneSource: "[data-microphone-source]",
    playPreview: "[data-play-preview]",
    playheadTime: "[data-playhead-time]",
    totalTime: "[data-total-time]",
    previewQuality: "[data-preview-quality]",
    timelineShell: "[data-timeline-shell]",
    timelineViewport: "[data-timeline-viewport]",
    timelineContent: "[data-timeline-content]",
    timelineRuler: "[data-timeline-ruler]",
    timelineGrid: "[data-timeline-grid]",
    timelineZoom: "[data-timeline-zoom]",
    timelineZoomOut: "[data-timeline-zoom-out]",
    timelineZoomIn: "[data-timeline-zoom-in]",
    timelineUndo: "[data-timeline-undo]",
    timelineRedo: "[data-timeline-redo]",
    timelineCurrentTime: "[data-timeline-current-time]",
    timelineTotalTime: "[data-timeline-total-time]",
    timelineSegments: "[data-timeline-segments]",
    timelinePlayhead: "[data-timeline-playhead]",
    timelineSelection: "[data-timeline-selection]",
    selectionLabel: "[data-selection-label]",
    selectionStart: "[data-selection-start]",
    selectionStartOutput: "[data-selection-start-output]",
    selectionEnd: "[data-selection-end]",
    selectionEndOutput: "[data-selection-end-output]",
    playhead: "[data-playhead]",
    overlayList: "[data-overlay-list]",
    overlayCount: "[data-overlay-count]",
    overlayItemTemplate: "[data-overlay-item-template]",
    resetCrop: "[data-reset-crop]",
    cameraSettings: "[data-camera-settings]",
    cameraVisible: "[data-camera-visible]",
    cameraSize: "[data-camera-size]",
    cameraShape: "[data-camera-shape]",
    cameraMirror: "[data-camera-mirror]",
    systemGain: "[data-system-gain]",
    systemGainOutput: "[data-system-gain-output]",
    systemMuted: "[data-system-muted]",
    microphoneGain: "[data-microphone-gain]",
    microphoneGainOutput: "[data-microphone-gain-output]",
    microphoneMuted: "[data-microphone-muted]",
    overlayCreateDialog: "[data-overlay-create-dialog]",
    overlayCreateForm: "[data-overlay-create-form]",
    overlayCreateMode: "[data-overlay-create-mode]",
    overlayCreateTitle: "[data-overlay-create-title]",
    overlayCreateIcon: "[data-overlay-create-icon]",
    overlayCreateTextField: "[data-overlay-create-text-field]",
    overlayCreateText: "[data-overlay-create-text]",
    overlayCreateDirectionField: "[data-overlay-create-direction-field]",
    overlayCreateDirection: "[data-overlay-create-direction]",
    overlayCreateBlurField: "[data-overlay-create-blur-field]",
    overlayCreateBlur: "[data-overlay-create-blur]",
    overlayCreateBlurOutput: "[data-overlay-create-blur-output]",
    overlayCreateColorField: "[data-overlay-create-color-field]",
    overlayCreateColor: "[data-overlay-create-color]",
    overlayCreateStart: "[data-overlay-create-start]",
    overlayCreateStartOutput: "[data-overlay-create-start-output]",
    overlayCreateEnd: "[data-overlay-create-end]",
    overlayCreateEndOutput: "[data-overlay-create-end-output]",
    overlayCreateError: "[data-overlay-create-error]",
    overlayCreateClose: "[data-overlay-create-close]",
    overlayCreateCancel: "[data-overlay-create-cancel]",
    overlayCreateSubmit: "[data-overlay-create-submit]",
    exportDialog: "[data-export-dialog]",
    exportReady: "[data-export-ready]",
    exportWorking: "[data-export-working]",
    exportFormat: "[data-export-format]",
    exportSize: "[data-export-size]",
    startExport: "[data-start-export]",
    closeExport: "[data-close-export]",
    exportMessage: "[data-export-message]",
    exportProgress: "[data-export-progress]",
    exportStage: "[data-export-stage]",
    exportPercent: "[data-export-percent]",
    cancelExport: "[data-cancel-export]",
    toast: "[data-recorder-toast]"
  };

  return Object.fromEntries(Object.entries(selectors).map(([key, selector]) => [key, container.querySelector(selector)]));
}

function isSupported(capabilities) {
  return capabilities.desktopChromium
    && capabilities.secureContext
    && capabilities.displayMedia
    && capabilities.userMedia
    && capabilities.mediaRecorder
    && capabilities.webCodecs
    && capabilities.opfs;
}

function readableCaptureError(error) {
  if (error?.name === "NotAllowedError") return "أُلغي اختيار الشاشة أو رُفض الإذن. يمكنك المحاولة مجددًا.";
  if (error?.name === "NotFoundError") return "لم يعثر المتصفح على شاشة أو نافذة قابلة للتسجيل.";
  if (error?.name === "NotReadableError") return "تعذر قراءة الشاشة المختارة؛ قد تكون مستخدمة في تطبيق آخر.";

  return error?.message || "تعذر بدء تسجيل الشاشة.";
}

function formatLocalDate(value) {
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? "" : new Intl.DateTimeFormat("ar", { dateStyle: "medium", timeStyle: "short" }).format(date);
}

function delay(milliseconds) {
  return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
}

function clamp(value, minimum, maximum) {
  return Math.min(maximum, Math.max(minimum, Number(value) || 0));
}
