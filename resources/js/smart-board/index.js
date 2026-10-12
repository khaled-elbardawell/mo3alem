import {
  boardKeyboardCommand,
  calendarMonthGrid,
  calculatePointerGeometry,
  clamp,
  commitHistory,
  createDefaultBoard,
  createHistory,
  createWidget,
  formatDuration,
  migrateState,
  randomDiceValue,
  redoHistory,
  reduceBoard,
  shouldRunSelectionAction,
  shouldWidgetScroll,
  shiftCalendarMonth,
  undoHistory
} from "./state.js";
import { decodeImageSource, optimizeBackgroundImage, safeBoardBackgroundImage } from "./image.js";
import { loadDraft, saveDraft } from "./storage.js";

const joinRoot = document.querySelector("[data-smart-board-join]");
const boardRoot = document.querySelector("[data-smart-board]");

if (joinRoot) setupStudentPoll(joinRoot);
if (boardRoot) setupBoard(boardRoot);

async function setupBoard(root) {
  const config = JSON.parse(document.getElementById("smartBoardConfig")?.dataset.config || "{}");
  const elements = {
    stage: document.getElementById("smartBoardStage"),
    widgets: document.getElementById("smartBoardWidgets"),
    drawingCanvas: document.getElementById("smartBoardDrawingCanvas"),
    drawingToolbar: document.getElementById("smartBoardDrawingToolbar"),
    drawingColor: document.getElementById("smartBoardDrawingColor"),
    drawingWidth: document.getElementById("smartBoardDrawingWidth"),
    drawingWidthValue: document.getElementById("smartBoardDrawingWidthValue"),
    dock: document.getElementById("smartBoardDock"),
    dockTools: document.getElementById("smartBoardDockTools"),
    dockToggle: document.getElementById("toggleBoardDockBtn"),
    moreToggle: document.getElementById("smartBoardMoreBtn"),
    moreMenu: document.getElementById("smartBoardMoreMenu"),
    title: document.getElementById("boardTitleInput"),
    status: document.getElementById("boardSaveStatus"),
    toolbar: document.getElementById("selectionToolbar"),
    toast: document.getElementById("smartBoardToast"),
    undo: document.getElementById("undoBoardBtn"),
    redo: document.getElementById("redoBoardBtn"),
    imageInput: document.getElementById("boardImageInput"),
    settingsDialog: document.getElementById("widgetSettingsDialog"),
    settingsContent: document.getElementById("widgetSettingsContent"),
    settingsTitle: document.getElementById("widgetSettingsTitle"),
    appearanceDialog: document.getElementById("appearanceDialog"),
    appearanceApply: document.getElementById("applyAppearanceBtn"),
    appearanceImageInput: document.getElementById("boardBackgroundImageInput"),
    appearanceImagePreview: document.getElementById("boardBackgroundImagePreview"),
    appearanceImageEmpty: document.getElementById("boardBackgroundImageEmpty"),
    appearanceImageName: document.getElementById("boardBackgroundImageName"),
    libraryDialog: document.getElementById("boardsLibraryDialog"),
    libraryContent: document.getElementById("boardsLibraryContent"),
    sessionDialog: document.getElementById("sessionDialog"),
    sessionContent: document.getElementById("sessionDialogContent")
  };

  let boardId = config.board?.id || null;
  let boardVersion = config.board?.version || null;
  let selectedId = null;
  let presentation = false;
  let interactionMode = "move";
  let imageTargetId = null;
  let saveTimer = null;
  let draftTimer = null;
  let qrGenerationTimer = null;
  let selectionToolbarTimer = null;
  let selectionActionsEnabledAt = 0;
  let armedSelectionAction = null;
  let pendingAppearanceImage = "";
  let pendingAppearancePreviewImage = "";
  let pendingAppearanceBackground = "plain";
  let appearanceImageProcessing = null;
  let appearanceImageVersion = 0;
  const draft = config.board ? null : await loadDraft().catch(() => null);
  let history = createHistory(migrateState(config.board?.state || draft || createDefaultBoard()));

  elements.title.value = config.board?.title || history.present.board.title;
  setDockCollapsed(localStorage.getItem("mo3alem-smart-board-dock-collapsed") === "1");
  setupBoardDrawing();
  render();

  if (!localStorage.getItem("mo3alem-smart-board-onboarded")) {
    document.getElementById("onboardingDialog")?.showModal();
    document.getElementById("onboardingDialog")?.addEventListener("close", () => {
      localStorage.setItem("mo3alem-smart-board-onboarded", "1");
    }, { once: true });
  }

  elements.dock.querySelectorAll("[data-add-widget]").forEach((button) => {
    button.addEventListener("click", () => {
      const type = button.dataset.widgetType;
      setMoreMenuOpen(false);
      if (type === "image") {
        imageTargetId = null;
        elements.imageInput.click();
        return;
      }
      const widget = createWidget(type, history.present.widgets.length);
      commit(reduceBoard(history.present, { type: "add", widget }));
      selectedId = widget.id;
      renderWidget(widget.id);
      if (["text", "randomizer", "groups", "embed", "qr", "poll"].includes(type)) openSettings(widget.id);
    });
  });

  elements.imageInput.addEventListener("change", async () => {
    const file = elements.imageInput.files?.[0];
    elements.imageInput.value = "";
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) return notify("حجم الصورة يجب ألا يتجاوز 5MB.", true);

    const src = await readFile(file);
    if (imageTargetId) {
      updateWidget(imageTargetId, { config: { ...findWidget(imageTargetId).config, src, alt: file.name, localAsset: true } });
    } else {
      const widget = createWidget("image", history.present.widgets.length);
      widget.config = { src, alt: file.name, localAsset: true };
      commit(reduceBoard(history.present, { type: "add", widget }));
      selectedId = widget.id;
      renderWidget(widget.id);
    }
  });

  elements.widgets.addEventListener("click", (event) => {
    const widgetElement = event.target.closest("[data-widget-id]");
    if (!widgetElement) return;
    selectedId = widgetElement.dataset.widgetId;
    if (!presentation && interactionMode === "move") {
      widgetElement.style.zIndex = String(bringWidgetToFront(selectedId));
    }
    const control = event.target.closest("[data-widget-control]");
    if (control) {
      event.stopPropagation();
      handleWidgetControl(findWidget(selectedId), control);
    }
    renderSelection();
  });

  elements.widgets.addEventListener("pointerdown", startPointerInteraction);
  document.addEventListener("keydown", handleKeyboard);

  document.querySelectorAll("[data-board-mode]").forEach((button) => {
    button.addEventListener("click", () => setInteractionMode(button.dataset.boardMode));
  });
  elements.dockToggle.addEventListener("click", () => {
    const collapsed = !elements.dock.classList.contains("is-collapsed");
    setDockCollapsed(collapsed);
    localStorage.setItem("mo3alem-smart-board-dock-collapsed", collapsed ? "1" : "0");
  });
  elements.moreToggle.addEventListener("click", (event) => {
    event.stopPropagation();
    setMoreMenuOpen(elements.moreMenu.hidden);
  });
  elements.moreMenu.addEventListener("click", (event) => {
    event.stopPropagation();
    if (event.target.closest("[data-close-more]")) setMoreMenuOpen(false);
  });
  document.addEventListener("click", (event) => {
    if (!event.target.closest("#smartBoardDock")) setMoreMenuOpen(false);
  });
  window.addEventListener("resize", () => {
    if (!elements.moreMenu.hidden) syncMoreMenuTools();
  });
  elements.drawingToolbar.addEventListener("click", (event) => {
    const tool = event.target.closest("[data-drawing-tool]")?.dataset.drawingTool;
    if (tool) updateDrawingSettings({ tool });
  });
  elements.drawingColor.addEventListener("input", () => updateDrawingSettings({ color: safeColor(elements.drawingColor.value) }));
  elements.drawingWidth.addEventListener("input", () => updateDrawingSettings({ width: clamp(Number(elements.drawingWidth.value), 1, 24) }));
  document.getElementById("clearBoardDrawingBtn").addEventListener("click", () => {
    if (!history.present.board.drawing.strokes.length) return;
    commit(reduceBoard(history.present, { type: "drawing", changes: { strokes: [] } }));
    updateHistoryButtons();
    renderBoardDrawing();
  });

  elements.toolbar.addEventListener("pointerdown", (event) => {
    const button = event.target.closest("button[data-selection-action]");
    armedSelectionAction = event.isPrimary && event.button === 0 ? button?.dataset.selectionAction || null : null;
  });
  elements.stage.addEventListener("pointerdown", (event) => {
    if (!event.target.closest("#selectionToolbar")) armedSelectionAction = null;
  }, true);
  elements.toolbar.addEventListener("pointercancel", () => {
    armedSelectionAction = null;
  });
  elements.toolbar.addEventListener("pointerleave", (event) => {
    if (event.buttons) armedSelectionAction = null;
  });
  elements.toolbar.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-selection-action]");
    const action = button?.dataset.selectionAction;
    const actionWasExplicitlyPressed = shouldRunSelectionAction(armedSelectionAction, action);
    armedSelectionAction = null;

    if (performance.now() < selectionActionsEnabledAt || !actionWasExplicitlyPressed) {
      event.preventDefault();
      event.stopPropagation();
      return;
    }
    runSelectionAction(action);
  });
  elements.toolbar.addEventListener("keydown", (event) => {
    if (!["Enter", " "].includes(event.key) || event.repeat) return;
    const action = event.target.closest("button[data-selection-action]")?.dataset.selectionAction;
    if (!action) return;
    event.preventDefault();
    runSelectionAction(action);
  });

  function runSelectionAction(action) {
    if (!selectedId) return;
    const widget = findWidget(selectedId);
    if (!widget) return;
    if (action === "edit") openSettings(selectedId);
    if (action === "duplicate") duplicateSelectedWidget();
    if (action === "lock") updateWidget(selectedId, { locked: !widget.locked });
    if (action === "front") bringWidgetToFront(selectedId, true);
    if (action === "delete") {
      const removedId = selectedId;
      commit(reduceBoard(history.present, { type: "remove", id: selectedId }));
      selectedId = null;
      removeRenderedWidget(removedId);
    }
  }

  elements.undo.addEventListener("click", () => {
    history = undoHistory(history);
    selectedId = null;
    afterHistoryChange();
  });
  elements.redo.addEventListener("click", () => {
    history = redoHistory(history);
    selectedId = null;
    afterHistoryChange();
  });

  elements.title.addEventListener("input", () => {
    history.present = reduceBoard(history.present, { type: "title", title: elements.title.value.trim() || "سبورتي الذكية" });
    schedulePersistence();
  });

  document.getElementById("saveBoardBtn").addEventListener("click", saveBoard);
  document.getElementById("boardsLibraryBtn").addEventListener("click", openLibrary);
  document.getElementById("newBoardBtn").addEventListener("click", createNewBoard);
  document.getElementById("clearBoardBtn").addEventListener("click", clearBoard);
  document.getElementById("presentationModeBtn").addEventListener("click", togglePresentation);
  document.getElementById("fullscreenBoardBtn").addEventListener("click", toggleFullscreen);
  document.getElementById("boardAppearanceBtn").addEventListener("click", () => {
    setMoreMenuOpen(false);
    openAppearance();
  });
  elements.appearanceApply.addEventListener("click", applyAppearance);
  elements.appearanceImageInput.addEventListener("change", handleAppearanceImage);
  elements.appearanceImagePreview.addEventListener("error", () => {
    elements.appearanceImagePreview.hidden = true;
    elements.appearanceImageEmpty.hidden = false;
  });
  document.getElementById("removeBoardBackgroundImageBtn").addEventListener("click", removeAppearanceImage);
  document.getElementById("boardBackgroundColor").addEventListener("input", () => {
    selectAppearanceBackground("color");
    updateAppearanceControls();
    previewAppearanceSelection();
  });
  document.getElementById("boardBackgroundOverlay").addEventListener("input", () => {
    updateAppearanceOverlayOutput();
    previewAppearanceSelection();
  });
  document.getElementById("boardBackgroundSize").addEventListener("change", previewAppearanceSelection);
  document.getElementById("boardAspectRatio").addEventListener("change", previewAppearanceSelection);
  elements.appearanceDialog.querySelectorAll('[name="board_background"]').forEach((input) => {
    input.addEventListener("change", () => {
      if (!input.checked) return;
      pendingAppearanceBackground = input.value;
      if (input.value.startsWith("image-") || input.value === "custom-image") {
        document.getElementById("boardBackgroundSize").value = "contain";
      }
      updateAppearanceControls();
      previewAppearanceSelection();
    });
  });
  elements.appearanceDialog.addEventListener("close", () => {
    appearanceImageVersion += 1;
    appearanceImageProcessing = null;
    elements.appearanceApply.disabled = false;
    releaseAppearancePreviewImage();
    renderBoardAppearance();
  });
  document.getElementById("applyWidgetSettingsBtn").addEventListener("click", applySettings);
  document.addEventListener("fullscreenchange", updateFullscreenIcon);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !elements.moreMenu.hidden) {
      setMoreMenuOpen(false);
      return;
    }
    if (event.key === "Escape" && interactionMode === "draw") return setInteractionMode("move");
    if (event.key === "Escape" && presentation) togglePresentation();
  });

  window.setInterval(updateLiveWidgets, 250);

  function commit(nextState) {
    history = commitHistory(history, nextState);
    schedulePersistence();
  }

  function afterHistoryChange() {
    render();
    schedulePersistence();
  }

  function createNewBoard() {
    const hasContent = history.present.widgets.length || history.present.board.drawing.strokes.length;
    if (hasContent && !window.confirm("بدء سبورة جديدة؟ سيتم استبدال المسودة الحالية، ولن تُحذف السبورة المحفوظة من حسابك.")) return;

    clearTimeout(saveTimer);
    clearTimeout(draftTimer);
    boardId = null;
    boardVersion = null;
    selectedId = null;
    history = createHistory(createDefaultBoard());
    elements.title.value = history.present.board.title;
    setInteractionMode("move");
    setMoreMenuOpen(false);
    render();
    schedulePersistence();
    notify("تم إنشاء سبورة جديدة.");
  }

  function clearBoard() {
    const hasContent = history.present.widgets.length || history.present.board.drawing.strokes.length;
    if (!hasContent) return notify("السبورة نظيفة بالفعل.");
    if (!window.confirm("تنظيف السبورة بالكامل؟ سيتم حذف كل العناصر والرسومات، ويمكنك التراجع بعد ذلك.")) return;

    commit(reduceBoard(history.present, { type: "clear" }));
    selectedId = null;
    setInteractionMode("move");
    setMoreMenuOpen(false);
    render();
    notify("تم تنظيف السبورة.");
  }

  function schedulePersistence() {
    elements.status.textContent = "جارٍ الحفظ…";
    clearTimeout(draftTimer);
    draftTimer = window.setTimeout(async () => {
      await saveDraft(history.present).catch(() => {});
      elements.status.textContent = boardId ? "محفوظ محليًا، بانتظار المزامنة" : "محفوظة على هذا الجهاز";
    }, 350);

    if (config.authenticated && config.verified && boardId) {
      clearTimeout(saveTimer);
      saveTimer = window.setTimeout(saveBoard, 1500);
    }
  }

  function render() {
    renderBoardAppearance();
    elements.widgets.innerHTML = history.present.widgets.map(widgetTemplate).join("");
    updateHistoryButtons();
    elements.title.value = history.present.board.title || elements.title.value;
    renderSelection();
    renderDrawingControls();
    renderBoardDrawing();
    requestQrGeneration();
  }

  function renderWidget(id) {
    const widget = findWidget(id);
    const currentElement = elements.widgets.querySelector(`[data-widget-id="${cssEscape(id)}"]`);
    if (!widget) return;

    const template = document.createElement("template");
    template.innerHTML = widgetTemplate(widget).trim();
    if (currentElement) currentElement.replaceWith(template.content.firstElementChild);
    else elements.widgets.append(template.content.firstElementChild);
    updateHistoryButtons();
    renderSelection();
    updateLiveWidgets();
    if (widget.type === "qr") requestQrGeneration();
  }

  function removeRenderedWidget(id) {
    elements.widgets.querySelector(`[data-widget-id="${cssEscape(id)}"]`)?.remove();
    updateHistoryButtons();
    renderSelection();
  }

  function updateHistoryButtons() {
    elements.undo.disabled = history.past.length === 0;
    elements.redo.disabled = history.future.length === 0;
  }

  function renderSelection() {
    document.querySelectorAll("[data-widget-id]").forEach((element) => {
      element.classList.toggle("is-selected", !presentation && element.dataset.widgetId === selectedId);
    });
    const widget = findWidget(selectedId);
    const widgetElement = widget ? document.querySelector(`[data-widget-id="${cssEscape(widget.id)}"]`) : null;
    elements.toolbar.hidden = !widgetElement || presentation || interactionMode === "draw";
    if (!widgetElement || presentation || interactionMode === "draw") return;
    elements.toolbar.style.left = `${clamp(widget.x + widget.width / 2, 10, 90)}%`;
    elements.toolbar.style.top = `${Math.max(widget.y - 1, 1)}%`;
    const lockIcon = elements.toolbar.querySelector('[data-selection-action="lock"] i');
    lockIcon.className = `fa-solid ${widget.locked ? "fa-lock" : "fa-lock-open"}`;
  }

  function widgetTemplate(widget) {
    const locked = widget.locked ? " is-locked" : "";
    const contentOverflow = shouldWidgetScroll(widget) ? ' data-content-overflow="scroll"' : "";
    return `<article class="smart-board-widget${locked}" data-widget-id="${escapeHtml(widget.id)}"
      data-widget-type="${escapeHtml(widget.type)}"${contentOverflow} style="left:${widget.x}%;top:${widget.y}%;width:${widget.width}%;height:${widget.height}%;z-index:${widget.zIndex}"
      tabindex="0" aria-label="${widgetLabel(widget.type)}">
      <div class="smart-board-widget-handle" data-drag-handle>
        <span><i class="fa-solid ${widgetIcon(widget.type)}" aria-hidden="true"></i> ${widgetLabel(widget.type)}</span>
        ${widget.locked ? '<i class="fa-solid fa-lock" aria-label="مقفلة"></i>' : '<i class="fa-solid fa-grip" aria-hidden="true"></i>'}
      </div>
      <div class="smart-board-widget-content">${widgetContent(widget)}</div>
      ${widget.locked ? "" : '<button class="smart-board-resize-handle" data-resize-handle aria-label="تغيير حجم الأداة"></button>'}
    </article>`;
  }

  function widgetContent(widget) {
    const value = widget.config;
    if (widget.type === "text") {
      const fontWeight = value.fontWeight === "400" ? "400" : "900";
      const fontStyle = value.fontStyle === "italic" ? "italic" : "normal";
      const textDecoration = value.textDecoration === "underline" ? "underline" : "none";
      const align = ["right", "center", "left"].includes(value.align) ? value.align : "center";
      return `<div class="smart-board-text-widget" style="--widget-font-size:${clamp(value.fontSize, 14, 64)}px;--widget-line-height:${clamp(Number(value.lineHeight) || 1.5, 1, 2)};text-align:${align};color:${safeColor(value.color)};background:${safeColor(value.backgroundColor || "#ffffff")};font-family:${safeTextFont(value.fontFamily)};font-weight:${fontWeight};font-style:${fontStyle};text-decoration:${textDecoration}">${escapeHtml(value.content).replaceAll("\n", "<br>")}</div>`;
    }
    if (widget.type === "clock") return '<time class="smart-board-clock" data-live-clock></time>';
    if (widget.type === "calendar") return calendarWidget(value);
    if (widget.type === "timer") return timeWidget("timer", widget, currentTimerSeconds(widget));
    if (widget.type === "stopwatch") return timeWidget("stopwatch", widget, currentStopwatchSeconds(widget));
    if (widget.type === "traffic-light") {
      return `<div class="smart-board-traffic" aria-label="إشارة المرور">${["red", "yellow", "green"].map((color) =>
        `<button class="${value.active === color ? "is-active" : ""}" data-widget-control="traffic" data-value="${color}" aria-label="${color}"></button>`
      ).join("")}</div>`;
    }
    if (widget.type === "scoreboard") {
      return `<div class="smart-board-scoreboard">${(value.teams || []).map((team, index) =>
        `<div><strong>${escapeHtml(team.name)}</strong><span>${Number(team.score) || 0}</span><p><button data-widget-control="score" data-index="${index}" data-delta="1">+</button><button data-widget-control="score" data-index="${index}" data-delta="-1">−</button></p></div>`
      ).join("")}</div>`;
    }
    if (widget.type === "dice") {
      return `<div class="smart-board-dice">
        <div class="smart-board-dice-stage">${diceFace(value.value)}</div>
        <div class="smart-board-dice-footer">
          <div class="smart-board-dice-result"><span>النتيجة</span><strong>${Number(value.value) || 1}</strong></div>
          <button data-widget-control="dice"><i class="fa-solid fa-dice" aria-hidden="true"></i> ارمِ النرد</button>
        </div>
      </div>`;
    }
    if (widget.type === "randomizer") return `<div class="smart-board-randomizer"><strong>${escapeHtml(value.selected || "جاهز للاختيار")}</strong><button data-widget-control="randomize"><i class="fa-solid fa-shuffle"></i> اختيار اسم</button></div>`;
    if (widget.type === "groups") {
      return `<div class="smart-board-groups">${value.groups?.length ? value.groups.map((group, index) =>
        `<section><strong>المجموعة ${index + 1}</strong><span>${group.map(escapeHtml).join("، ")}</span></section>`).join("") : '<p>أضف الأسماء ثم أنشئ المجموعات.</p>'}<button data-widget-control="make-groups">إنشاء المجموعات</button></div>`;
    }
    if (widget.type === "image") return value.src
      ? `<img class="smart-board-image" src="${escapeHtml(value.src)}" alt="${escapeHtml(value.alt || "صورة")}">`
      : '<button class="smart-board-empty-action" data-widget-control="choose-image"><i class="fa-solid fa-image"></i> اختر صورة</button>';
    if (widget.type === "embed") {
      const source = toEmbedUrl(value.url);
      return source
        ? `<iframe class="smart-board-embed" src="${escapeHtml(source)}" title="${escapeHtml(value.title || "محتوى تعليمي")}" sandbox="allow-scripts allow-same-origin allow-presentation" allow="autoplay; fullscreen" loading="lazy"></iframe>`
        : '<button class="smart-board-empty-action" data-widget-control="edit"><i class="fa-solid fa-link"></i> أضف رابط الفيديو أو العرض</button>';
    }
    if (widget.type === "qr") return `<div class="smart-board-qr" data-qr-widget="${escapeHtml(widget.id)}">${value.svg || '<i class="fa-solid fa-qrcode"></i><span>جارٍ إنشاء الرمز…</span>'}</div>`;
    if (widget.type === "poll") {
      const counts = new Map((value.session?.poll?.options || []).map((option) => [option.key, option.count]));
      return `<div class="smart-board-poll"><h3>${escapeHtml(value.question)}</h3><div class="smart-board-poll-results">${value.options.map((option) =>
        `<p><span>${escapeHtml(option.label)}</span><strong>${counts.get(option.key) || 0}</strong></p>`).join("")}</div><div class="smart-board-poll-actions"><button data-widget-control="poll-session"><i class="fa-solid fa-users"></i> ${value.session ? "عرض رمز المشاركة" : "بدء الاستطلاع"}</button>${value.session ? '<button data-widget-control="poll-refresh"><i class="fa-solid fa-rotate"></i> تحديث النتائج</button>' : ""}</div></div>`;
    }
    return "";
  }

  function timeWidget(type, widget, seconds) {
    const running = widget.config.running;
    return `<div class="smart-board-time-widget"><strong data-live-${type}="${escapeHtml(widget.id)}">${formatDuration(seconds)}</strong><div><button data-widget-control="${type}-toggle"><i class="fa-solid ${running ? "fa-pause" : "fa-play"}"></i> ${running ? "إيقاف" : "بدء"}</button><button data-widget-control="${type}-reset" aria-label="إعادة الضبط" title="إعادة الضبط"><i class="fa-solid fa-rotate-left"></i></button></div></div>`;
  }

  function handleWidgetControl(widget, control) {
    const action = control.dataset.widgetControl;
    if (action === "edit") return openSettings(widget.id);
    if (action === "choose-image") {
      imageTargetId = widget.id;
      return elements.imageInput.click();
    }
    if (action === "traffic") return updateWidget(widget.id, { config: { ...widget.config, active: control.dataset.value } });
    if (action === "score") {
      const teams = structuredClone(widget.config.teams);
      teams[Number(control.dataset.index)].score += Number(control.dataset.delta);
      return updateWidget(widget.id, { config: { ...widget.config, teams } });
    }
    if (action === "dice") return rollDice(widget, control);
    if (action === "randomize") {
      const names = widget.config.names || [];
      if (!names.length) return notify("أضف أسماء أولًا.", true);
      return updateWidget(widget.id, { config: { ...widget.config, selected: names[Math.floor(Math.random() * names.length)] } });
    }
    if (action === "make-groups") {
      const shuffled = [...(widget.config.names || [])].sort(() => Math.random() - 0.5);
      const count = clamp(widget.config.groupCount || 2, 2, 10);
      const groups = Array.from({ length: count }, () => []);
      shuffled.forEach((name, index) => groups[index % count].push(name));
      return updateWidget(widget.id, { config: { ...widget.config, groups } });
    }
    if (["calendar-previous", "calendar-next"].includes(action)) {
      const visibleMonth = shiftCalendarMonth(
        widget.config.year,
        widget.config.month,
        action === "calendar-next" ? 1 : -1
      );

      return updateWidget(widget.id, { config: { ...widget.config, ...visibleMonth } });
    }
    if (action === "timer-toggle") {
      const remaining = currentTimerSeconds(widget);
      updateWidget(widget.id, { config: { ...widget.config, remaining, running: !widget.config.running, endsAt: widget.config.running ? null : Date.now() + remaining * 1000 } });
    }
    if (action === "timer-reset") updateWidget(widget.id, { config: { ...widget.config, remaining: widget.config.duration, running: false, endsAt: null } });
    if (action === "stopwatch-toggle") {
      const elapsed = currentStopwatchSeconds(widget);
      updateWidget(widget.id, { config: { ...widget.config, elapsed, running: !widget.config.running, startedAt: widget.config.running ? null : Date.now() } });
    }
    if (action === "stopwatch-reset") updateWidget(widget.id, { config: { ...widget.config, elapsed: 0, running: false, startedAt: null } });
    if (action === "poll-session") openPollSession(widget);
    if (action === "poll-refresh") refreshPollResults(widget.id, control);
  }

  function updateWidget(id, changes, shouldRender = true) {
    commit(reduceBoard(history.present, { type: "update", id, changes }));
    if (shouldRender) renderWidget(id);
  }

  function findWidget(id) {
    return history.present.widgets.find((widget) => widget.id === id);
  }

  function bringWidgetToFront(id, shouldRender = false) {
    commit(reduceBoard(history.present, { type: "front", id }));
    const zIndex = findWidget(id)?.zIndex || 1;
    history.present.widgets.forEach((widget) => {
      const element = elements.widgets.querySelector(`[data-widget-id="${cssEscape(widget.id)}"]`);
      if (element) element.style.zIndex = String(widget.zIndex);
    });
    if (shouldRender) renderSelection();

    return zIndex;
  }

  function startPointerInteraction(event) {
    if (presentation || interactionMode !== "move") return;
    const widgetElement = event.target.closest("[data-widget-id]");
    if (!widgetElement) return;
    const widget = findWidget(widgetElement.dataset.widgetId);
    if (!widget || widget.locked) return;
    const resizing = Boolean(event.target.closest("[data-resize-handle]"));
    const dragging = Boolean(event.target.closest("[data-drag-handle]"));
    if (!resizing && !dragging) return;
    event.preventDefault();
    clearTimeout(selectionToolbarTimer);
    selectionActionsEnabledAt = performance.now() + 300;
    selectedId = widget.id;
    widgetElement.style.zIndex = String(bringWidgetToFront(widget.id));
    const rectangle = elements.stage.getBoundingClientRect();
    const pointerStart = { x: event.clientX, y: event.clientY };
    let latestPointer = pointerStart;
    let animationFrame = null;

    widgetElement.classList.add(resizing ? "is-resizing" : "is-dragging");
    elements.toolbar.hidden = true;
    widgetElement.setPointerCapture?.(event.pointerId);

    const geometryAt = (pointer) => calculatePointerGeometry(widget, {
      deltaX: pointer.x - pointerStart.x,
      deltaY: pointer.y - pointerStart.y,
      stageWidth: rectangle.width,
      stageHeight: rectangle.height
    }, resizing);

    const paint = () => {
      animationFrame = null;
      const geometry = geometryAt(latestPointer);

      if (resizing) {
        widgetElement.style.left = `${geometry.x}%`;
        widgetElement.style.width = `${geometry.width}%`;
        widgetElement.style.height = `${geometry.height}%`;
        return;
      }

      const translateX = ((geometry.x - widget.x) / 100) * rectangle.width;
      const translateY = ((geometry.y - widget.y) / 100) * rectangle.height;
      widgetElement.style.transform = `translate3d(${translateX}px, ${translateY}px, 0)`;
    };

    const move = (moveEvent) => {
      if (moveEvent.pointerId !== event.pointerId) return;
      latestPointer = { x: moveEvent.clientX, y: moveEvent.clientY };
      if (animationFrame === null) animationFrame = window.requestAnimationFrame(paint);
    };

    const end = (endEvent) => {
      if (endEvent.pointerId !== event.pointerId) return;
      latestPointer = { x: endEvent.clientX, y: endEvent.clientY };
      if (animationFrame !== null) window.cancelAnimationFrame(animationFrame);
      const changes = geometryAt(latestPointer);

      widgetElement.removeEventListener("pointermove", move);
      widgetElement.removeEventListener("pointerup", end);
      widgetElement.removeEventListener("pointercancel", end);
      widgetElement.classList.remove("is-dragging", "is-resizing");
      widgetElement.style.transform = "";
      if (widgetElement.hasPointerCapture?.(event.pointerId)) widgetElement.releasePointerCapture(event.pointerId);
      commit(reduceBoard(history.present, { type: "update", id: widget.id, changes }));
      const updatedWidget = findWidget(widget.id);
      widgetElement.style.left = `${updatedWidget.x}%`;
      widgetElement.style.top = `${updatedWidget.y}%`;
      widgetElement.style.width = `${updatedWidget.width}%`;
      widgetElement.style.height = `${updatedWidget.height}%`;
      updateHistoryButtons();
      selectionActionsEnabledAt = performance.now() + 300;
      selectionToolbarTimer = window.setTimeout(renderSelection, 0);
    };

    widgetElement.addEventListener("pointermove", move);
    widgetElement.addEventListener("pointerup", end);
    widgetElement.addEventListener("pointercancel", end);
  }

  function handleKeyboard(event) {
    if (event.target.closest?.("input,textarea,select,[contenteditable='true'],dialog[open]")) return;
    const command = boardKeyboardCommand(event, Boolean(selectedId));

    if (command === "undo" || command === "redo") {
      event.preventDefault();
      history = command === "redo" ? redoHistory(history) : undoHistory(history);
      selectedId = null;
      return afterHistoryChange();
    }
    if (command === "duplicate") {
      event.preventDefault();
      return duplicateSelectedWidget();
    }
    if (command === "delete") {
      event.preventDefault();
      return runSelectionAction("delete");
    }
    if (!selectedId) return;
    const widget = findWidget(selectedId);
    if (!widget || widget.locked) return;
    const movements = { ArrowRight: [-0.5, 0], ArrowLeft: [0.5, 0], ArrowUp: [0, -0.5], ArrowDown: [0, 0.5] };
    if (movements[event.key]) {
      event.preventDefault();
      const [dx, dy] = movements[event.key];
      updateWidget(widget.id, { x: widget.x + dx, y: widget.y + dy });
    }
  }

  function duplicateSelectedWidget() {
    if (!selectedId || !findWidget(selectedId)) return;
    commit(reduceBoard(history.present, { type: "duplicate", id: selectedId }));
    selectedId = history.present.widgets.at(-1)?.id || selectedId;
    renderWidget(selectedId);
  }

  function openSettings(id) {
    const widget = findWidget(id);
    if (!widget) return;
    selectedId = id;
    elements.settingsTitle.textContent = `إعدادات ${widgetLabel(widget.type)}`;
    elements.settingsContent.innerHTML = settingsTemplate(widget);
    elements.settingsDialog.classList.toggle("is-text-editor", widget.type === "text");
    if (widget.type === "text") setupTextEditor(widget);
    elements.settingsDialog.showModal();
  }

  function settingsTemplate(widget) {
    const value = widget.config;
    if (widget.type === "text") return textEditorTemplate(value);
    if (widget.type === "clock") {
      return `${selectField("شكل الساعة", "mode", [["analog", "عقارب"], ["digital", "رقمية"]], value.mode || "analog")}${selectField("تنسيق الساعة الرقمية", "hourFormat", [["12", "12 ساعة"], ["24", "24 ساعة"]], value.hourFormat || "12")}${selectField("إظهار الثواني", "showSeconds", [["no", "بدون ثوانٍ"], ["yes", "إظهار الثواني"]], value.showSeconds ? "yes" : "no")}`;
    }
    if (widget.type === "timer") return field("المدة بالدقائق", "minutes", Math.max(1, Math.round(value.duration / 60)), "number");
    if (widget.type === "scoreboard") return field("أسماء الفرق، كل فريق في سطر", "teams", value.teams.map((team) => team.name).join("\n"), "textarea");
    if (["randomizer", "groups"].includes(widget.type)) {
      return `${field("الأسماء، كل اسم في سطر", "names", value.names.join("\n"), "textarea")}${widget.type === "groups" ? field("عدد المجموعات", "groupCount", value.groupCount, "number") : ""}<button class="smart-board-inline-action" type="button" id="loadSavedNamesBtn" aria-controls="savedNamesPicker" aria-expanded="false"><i class="fa-solid fa-list"></i> استيراد قائمة محفوظة</button><div class="smart-board-saved-list-picker" id="savedNamesPicker" hidden><p data-saved-list-status role="status"></p><div class="smart-board-saved-list-options" data-saved-list-options></div></div>`;
    }
    if (widget.type === "image") return '<button class="smart-board-inline-action" type="button" id="replaceImageBtn"><i class="fa-solid fa-image"></i> استبدال الصورة</button>';
    if (widget.type === "embed") return field("رابط YouTube أو Vimeo أو Canva أو Google Slides", "url", value.url, "url");
    if (widget.type === "qr") return field("الرابط أو النص", "content", value.content, "textarea");
    if (widget.type === "poll") return `${field("السؤال", "question", value.question)}${field("الخيارات، كل خيار في سطر", "options", value.options.map((option) => option.label).join("\n"), "textarea")}`;
    return '<p class="text-sm font-bold text-slate-500">يمكن التحكم بهذه الأداة مباشرة من السبورة.</p>';
  }

  function textEditorTemplate(value) {
    const fontSize = clamp(Number(value.fontSize) || 28, 14, 64);
    const lineHeight = clamp(Number(value.lineHeight) || 1.5, 1, 2);
    const fontFamily = safeTextFontKey(value.fontFamily);
    const fontWeight = value.fontWeight === "400" ? "400" : "900";
    const fontStyle = value.fontStyle === "italic" ? "italic" : "normal";
    const textDecoration = value.textDecoration === "underline" ? "underline" : "none";
    const align = ["right", "center", "left"].includes(value.align) ? value.align : "center";

    return `<div class="smart-board-rich-editor" data-text-editor>
      <div class="smart-board-rich-toolbar" role="toolbar" aria-label="تنسيق النص بالكامل">
        <div class="smart-board-rich-toolbar-group" aria-label="نمط الخط">
          <button type="button" data-text-toggle="fontWeight" class="${fontWeight === "900" ? "is-active" : ""}" aria-pressed="${fontWeight === "900"}" title="عريض"><i class="fa-solid fa-bold"></i></button>
          <button type="button" data-text-toggle="fontStyle" class="${fontStyle === "italic" ? "is-active" : ""}" aria-pressed="${fontStyle === "italic"}" title="مائل"><i class="fa-solid fa-italic"></i></button>
          <button type="button" data-text-toggle="textDecoration" class="${textDecoration === "underline" ? "is-active" : ""}" aria-pressed="${textDecoration === "underline"}" title="تحته خط"><i class="fa-solid fa-underline"></i></button>
        </div>
        <div class="smart-board-rich-toolbar-group" aria-label="محاذاة النص">
          <button type="button" data-text-align="right" class="${align === "right" ? "is-active" : ""}" aria-pressed="${align === "right"}" title="محاذاة لليمين"><i class="fa-solid fa-align-right"></i></button>
          <button type="button" data-text-align="center" class="${align === "center" ? "is-active" : ""}" aria-pressed="${align === "center"}" title="توسيط"><i class="fa-solid fa-align-center"></i></button>
          <button type="button" data-text-align="left" class="${align === "left" ? "is-active" : ""}" aria-pressed="${align === "left"}" title="محاذاة لليسار"><i class="fa-solid fa-align-left"></i></button>
        </div>
        <label class="smart-board-rich-select"><span>الخط</span><select name="fontFamily" data-text-setting><option value="interface" ${fontFamily === "interface" ? "selected" : ""}>خط المنصة</option><option value="readable" ${fontFamily === "readable" ? "selected" : ""}>واضح</option><option value="traditional" ${fontFamily === "traditional" ? "selected" : ""}>تقليدي</option><option value="mono" ${fontFamily === "mono" ? "selected" : ""}>كتابة</option></select></label>
        <label class="smart-board-rich-color" title="لون النص"><span>النص</span><input name="color" data-text-setting type="color" value="${safeColor(value.color)}"></label>
        <label class="smart-board-rich-color" title="لون الخلفية"><span>الخلفية</span><input name="backgroundColor" data-text-setting type="color" value="${safeColor(value.backgroundColor || "#ffffff")}"></label>
      </div>
      <div class="smart-board-rich-canvas" data-rich-text-editor contenteditable="true" role="textbox" aria-multiline="true" spellcheck="true"></div>
      <div class="smart-board-rich-editor-options">
        <label><span>حجم الخط</span><input name="fontSize" data-text-setting type="range" min="14" max="64" value="${fontSize}"><output data-font-size-output>${fontSize}</output></label>
        <label><span>تباعد الأسطر</span><select name="lineHeight" data-text-setting>${[1, 1.25, 1.5, 1.75, 2].map((option) => `<option value="${option}" ${Number(lineHeight) === option ? "selected" : ""}>${option}</option>`).join("")}</select></label>
      </div>
      <input type="hidden" name="content">
      <input type="hidden" name="fontWeight" value="${fontWeight}">
      <input type="hidden" name="fontStyle" value="${fontStyle}">
      <input type="hidden" name="textDecoration" value="${textDecoration}">
      <input type="hidden" name="align" value="${align}">
      <p class="smart-board-rich-editor-note"><i class="fa-solid fa-shield-halved"></i> يتم حفظ النص والتنسيق بشكل آمن دون HTML خام.</p>
    </div>`;
  }

  function setupTextEditor(widget) {
    const shell = elements.settingsContent.querySelector("[data-text-editor]");
    const editor = shell?.querySelector("[data-rich-text-editor]");
    if (!shell || !editor) return;

    const contentInput = shell.querySelector('[name="content"]');
    editor.textContent = String(widget.config.content || "");
    contentInput.value = editor.innerText;

    const syncPreview = () => {
      const fontSize = clamp(Number(shell.querySelector('[name="fontSize"]').value), 14, 64);
      const lineHeight = clamp(Number(shell.querySelector('[name="lineHeight"]').value), 1, 2);
      const fontWeight = shell.querySelector('[name="fontWeight"]').value;
      const fontStyle = shell.querySelector('[name="fontStyle"]').value;
      const textDecoration = shell.querySelector('[name="textDecoration"]').value;
      const align = shell.querySelector('[name="align"]').value;

      editor.style.fontSize = `${fontSize}px`;
      editor.style.fontFamily = safeTextFont(shell.querySelector('[name="fontFamily"]').value);
      editor.style.fontWeight = fontWeight;
      editor.style.fontStyle = fontStyle;
      editor.style.textDecoration = textDecoration;
      editor.style.textAlign = align;
      editor.style.color = safeColor(shell.querySelector('[name="color"]').value);
      editor.style.backgroundColor = safeColor(shell.querySelector('[name="backgroundColor"]').value);
      editor.style.lineHeight = String(lineHeight);
      shell.querySelector("[data-font-size-output]").value = String(fontSize);

      shell.querySelectorAll("[data-text-toggle]").forEach((button) => {
        const activeValues = { fontWeight: "900", fontStyle: "italic", textDecoration: "underline" };
        const active = shell.querySelector(`[name="${button.dataset.textToggle}"]`).value === activeValues[button.dataset.textToggle];
        button.classList.toggle("is-active", active);
        button.setAttribute("aria-pressed", String(active));
      });
      shell.querySelectorAll("[data-text-align]").forEach((button) => {
        const active = button.dataset.textAlign === align;
        button.classList.toggle("is-active", active);
        button.setAttribute("aria-pressed", String(active));
      });
    };

    shell.querySelectorAll("[data-text-toggle]").forEach((button) => {
      button.addEventListener("click", () => {
        const input = shell.querySelector(`[name="${button.dataset.textToggle}"]`);
        const values = {
          fontWeight: ["400", "900"],
          fontStyle: ["normal", "italic"],
          textDecoration: ["none", "underline"]
        }[button.dataset.textToggle];
        input.value = input.value === values[1] ? values[0] : values[1];
        syncPreview();
      });
    });
    shell.querySelectorAll("[data-text-align]").forEach((button) => {
      button.addEventListener("click", () => {
        shell.querySelector('[name="align"]').value = button.dataset.textAlign;
        syncPreview();
      });
    });
    shell.querySelectorAll("[data-text-setting]").forEach((input) => input.addEventListener("input", syncPreview));
    editor.addEventListener("input", () => { contentInput.value = editor.innerText.slice(0, 5000); });
    editor.addEventListener("paste", (event) => {
      event.preventDefault();
      insertPlainText(editor, event.clipboardData?.getData("text/plain") || "");
      contentInput.value = editor.innerText.slice(0, 5000);
    });
    editor.addEventListener("drop", (event) => event.preventDefault());
    syncPreview();
  }

  elements.settingsDialog.addEventListener("click", async (event) => {
    if (event.target.closest("#replaceImageBtn")) {
      imageTargetId = selectedId;
      elements.settingsDialog.close();
      elements.imageInput.click();
    }
    const savedListButton = event.target.closest("[data-saved-list-id]");
    if (savedListButton) {
      event.preventDefault();
      await importSavedNames(savedListButton);
      return;
    }
    if (event.target.closest("#loadSavedNamesBtn")) {
      event.preventDefault();
      await showSavedNamesPicker();
    }
  });

  function applySettings(event) {
    event.preventDefault();
    const widget = findWidget(selectedId);
    if (!widget) return;
    const data = new FormData(elements.settingsDialog.querySelector("form"));
    const value = { ...widget.config };
    if (widget.type === "text") {
      value.content = String(data.get("content") || "").slice(0, 5000);
      value.fontSize = clamp(Number(data.get("fontSize")), 14, 64);
      value.fontFamily = safeTextFontKey(data.get("fontFamily"));
      value.fontWeight = data.get("fontWeight") === "400" ? "400" : "900";
      value.fontStyle = data.get("fontStyle") === "italic" ? "italic" : "normal";
      value.textDecoration = data.get("textDecoration") === "underline" ? "underline" : "none";
      value.align = ["right", "center", "left"].includes(String(data.get("align"))) ? String(data.get("align")) : "center";
      value.color = safeColor(data.get("color"));
      value.backgroundColor = safeColor(data.get("backgroundColor"));
      value.lineHeight = clamp(Number(data.get("lineHeight")) || 1.5, 1, 2);
    } else if (widget.type === "clock") {
      value.mode = data.get("mode") === "digital" ? "digital" : "analog";
      value.hourFormat = data.get("hourFormat") === "24" ? "24" : "12";
      value.showSeconds = data.get("showSeconds") === "yes";
    } else if (widget.type === "timer") {
      value.duration = clamp(Number(data.get("minutes")) * 60, 60, 7200);
      value.remaining = value.duration;
      value.running = false;
      value.endsAt = null;
    } else if (widget.type === "scoreboard") {
      value.teams = lines(data.get("teams")).slice(0, 6).map((name, index) => ({ name, score: value.teams[index]?.score || 0 }));
    } else if (["randomizer", "groups"].includes(widget.type)) {
      value.names = lines(data.get("names")).slice(0, 2000);
      if (widget.type === "groups") value.groupCount = clamp(Number(data.get("groupCount")), 2, 10);
    } else if (widget.type === "embed") {
      value.url = String(data.get("url") || "").trim();
      if (value.url && !toEmbedUrl(value.url)) return notify("هذا الرابط غير مدعوم. استخدم YouTube أو Vimeo أو Canva أو Google Slides.", true);
    } else if (widget.type === "qr") {
      value.content = String(data.get("content") || "").trim().slice(0, 2000);
      value.svg = "";
    } else if (widget.type === "poll") {
      value.question = String(data.get("question") || "").trim().slice(0, 180);
      value.options = lines(data.get("options")).slice(0, 8).map((label, index) => ({ key: `option-${index + 1}`, label }));
      if (value.options.length < 2) return notify("أضف خيارين على الأقل.", true);
      value.session = null;
    }
    updateWidget(widget.id, { config: value });
    elements.settingsDialog.close();
  }

  async function showSavedNamesPicker() {
    if (!config.authenticated) return notify("سجّل الدخول لاستيراد قوائمك.", true);
    const picker = elements.settingsContent.querySelector("#savedNamesPicker");
    const status = picker?.querySelector("[data-saved-list-status]");
    const options = picker?.querySelector("[data-saved-list-options]");
    const trigger = elements.settingsContent.querySelector("#loadSavedNamesBtn");
    if (!picker || !status || !options || !trigger) return;

    picker.hidden = false;
    picker.setAttribute("aria-busy", "true");
    trigger.disabled = true;
    trigger.setAttribute("aria-expanded", "true");
    status.textContent = "جارٍ تحميل القوائم المحفوظة…";
    options.replaceChildren();

    try {
      const response = await fetch(config.routes.savedLists, { headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (!response.ok) throw new Error(firstError(payload) || "تعذر تحميل القوائم المحفوظة.");
      const savedLists = Array.isArray(payload.data) ? payload.data : [];
      if (!savedLists.length) {
        status.textContent = "لا توجد قوائم محفوظة حتى الآن.";
        return;
      }

      status.textContent = "اختر القائمة التي تريد استيرادها:";
      options.innerHTML = savedLists.map(savedListOptionTemplate).join("");
    } catch (error) {
      status.textContent = error.message;
      notify(error.message, true);
    } finally {
      picker.setAttribute("aria-busy", "false");
      trigger.disabled = false;
    }
  }

  async function importSavedNames(button) {
    const listId = Number(button.dataset.savedListId);
    if (!Number.isInteger(listId) || listId < 1) return;
    const picker = elements.settingsContent.querySelector("#savedNamesPicker");
    const status = picker?.querySelector("[data-saved-list-status]");
    const title = String(button.dataset.savedListTitle || "القائمة");
    const optionButtons = picker?.querySelectorAll("[data-saved-list-id]") || [];
    optionButtons.forEach((option) => { option.disabled = true; });
    button.classList.add("is-loading");
    if (status) status.textContent = `جارٍ تحميل قائمة «${title}»…`;

    try {
      const response = await fetch(`${config.routes.savedLists}/${encodeURIComponent(listId)}`, { headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (!response.ok) throw new Error(firstError(payload) || "تعذر تحميل القائمة.");
      const names = Array.isArray(payload.data?.names)
        ? payload.data.names.filter((name) => typeof name === "string").map((name) => name.trim()).filter(Boolean).slice(0, 2000)
        : [];
      const textarea = elements.settingsContent.querySelector('[name="names"]');
      if (textarea) textarea.value = names.join("\n");
      picker.hidden = true;
      elements.settingsContent.querySelector("#loadSavedNamesBtn")?.setAttribute("aria-expanded", "false");
      notify(`تم تحميل قائمة «${title}».`);
    } catch (error) {
      if (status) status.textContent = error.message;
      notify(error.message, true);
    } finally {
      optionButtons.forEach((option) => { option.disabled = false; });
      button.classList.remove("is-loading");
    }
  }

  function openAppearance() {
    const board = history.present.board;
    appearanceImageVersion += 1;
    appearanceImageProcessing = null;
    elements.appearanceApply.disabled = false;
    releaseAppearancePreviewImage();
    selectAppearanceBackground(board.background || "plain");
    pendingAppearanceImage = board.backgroundImage || "";
    pendingAppearancePreviewImage = pendingAppearanceImage;
    elements.appearanceImageInput.value = "";
    document.getElementById("boardBackgroundColor").value = safeColor(board.backgroundColor || "#ffffff");
    document.getElementById("boardBackgroundSize").value = board.backgroundSize || "cover";
    document.getElementById("boardBackgroundOverlay").value = String(board.backgroundOverlay ?? 18);
    document.getElementById("boardAspectRatio").value = board.aspectRatio;
    updateAppearanceImagePreview();
    updateAppearanceControls();
    updateAppearanceOverlayOutput();
    elements.appearanceDialog.showModal();
  }

  async function applyAppearance(event) {
    event.preventDefault();
    if (appearanceImageProcessing) await appearanceImageProcessing;
    if (pendingAppearanceBackground === "custom-image" && !pendingAppearanceImage) {
      return notify("انتظر حتى يكتمل تجهيز صورة الخلفية.", true);
    }

    const currentBoard = history.present.board;
    const backgroundImageChanged = pendingAppearanceImage !== (currentBoard.backgroundImage || "");
    const aspectRatio = document.getElementById("boardAspectRatio").value;
    commit(reduceBoard(history.present, {
      type: "appearance",
      changes: {
        background: pendingAppearanceBackground,
        backgroundColor: safeColor(document.getElementById("boardBackgroundColor").value),
        backgroundImage: pendingAppearanceImage,
        backgroundImageAssetId: backgroundImageChanged ? null : currentBoard.backgroundImageAssetId,
        backgroundImageLocal: backgroundImageChanged
          ? pendingAppearanceImage.startsWith("data:image/")
          : currentBoard.backgroundImageLocal,
        backgroundSize: document.getElementById("boardBackgroundSize").value === "contain" ? "contain" : "cover",
        backgroundSizeCustomized: true,
        backgroundOverlay: clamp(Number(document.getElementById("boardBackgroundOverlay").value), 0, 70),
        showGrid: false,
        aspectRatio
      }
    }));
    renderBoardAppearance();
    updateHistoryButtons();
    elements.appearanceDialog.close();
  }

  async function handleAppearanceImage() {
    const file = elements.appearanceImageInput.files?.[0];
    if (!file) return;
    if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
      elements.appearanceImageInput.value = "";
      return notify("اختر صورة JPG أو PNG أو WebP فقط.", true);
    }
    if (file.size > 5 * 1024 * 1024) {
      elements.appearanceImageInput.value = "";
      return notify("حجم صورة الخلفية يجب ألا يتجاوز 5MB.", true);
    }

    const imageVersion = ++appearanceImageVersion;
    releaseAppearancePreviewImage();
    pendingAppearanceImage = "";
    pendingAppearancePreviewImage = URL.createObjectURL(file);
    selectAppearanceBackground("custom-image");
    document.getElementById("boardBackgroundSize").value = "contain";
    elements.appearanceImageName.textContent = `جارٍ تجهيز ${file.name}…`;
    elements.appearanceApply.disabled = true;
    updateAppearanceImagePreview();
    updateAppearanceControls();
    previewAppearanceSelection();

    appearanceImageProcessing = optimizeBackgroundImage(file)
      .catch(() => readFile(file))
      .then(async (source) => {
        if (imageVersion !== appearanceImageVersion) return;
        await decodeImageSource(source);
        if (imageVersion !== appearanceImageVersion) return;
        releaseAppearancePreviewImage();
        pendingAppearanceImage = source;
        pendingAppearancePreviewImage = source;
        elements.appearanceImageName.textContent = file.name;
        updateAppearanceImagePreview();
        previewAppearanceSelection();
      })
      .catch(() => {
        if (imageVersion !== appearanceImageVersion) return;
        releaseAppearancePreviewImage();
        pendingAppearanceImage = "";
        selectAppearanceBackground("plain");
        updateAppearanceImagePreview();
        updateAppearanceControls();
        previewAppearanceSelection();
        notify("تعذر تجهيز صورة الخلفية.", true);
      })
      .finally(() => {
        if (imageVersion !== appearanceImageVersion) return;
        appearanceImageProcessing = null;
        elements.appearanceApply.disabled = false;
      });
  }

  function removeAppearanceImage() {
    appearanceImageVersion += 1;
    appearanceImageProcessing = null;
    elements.appearanceApply.disabled = false;
    releaseAppearancePreviewImage();
    pendingAppearanceImage = "";
    elements.appearanceImageInput.value = "";
    selectAppearanceBackground("plain");
    updateAppearanceImagePreview();
    updateAppearanceControls();
    previewAppearanceSelection();
  }

  function updateAppearanceImagePreview() {
    const source = safeBoardBackgroundImage(pendingAppearancePreviewImage || pendingAppearanceImage);
    elements.appearanceImagePreview.hidden = !source;
    elements.appearanceImageEmpty.hidden = Boolean(source);
    if (source) elements.appearanceImagePreview.src = source;
    else elements.appearanceImagePreview.removeAttribute("src");
    if (source && !elements.appearanceImageInput.files?.[0]) elements.appearanceImageName.textContent = "صورة الخلفية الحالية";
    if (!source) elements.appearanceImageName.textContent = "لم تُرفع صورة بعد";
    document.getElementById("removeBoardBackgroundImageBtn").hidden = !source;
  }

  function releaseAppearancePreviewImage() {
    if (pendingAppearancePreviewImage.startsWith("blob:")) URL.revokeObjectURL(pendingAppearancePreviewImage);
    pendingAppearancePreviewImage = "";
  }

  function updateAppearanceControls() {
    const usesImage = pendingAppearanceBackground.startsWith("image-") || pendingAppearanceBackground === "custom-image";
    document.getElementById("boardImageDisplaySettings").hidden = !usesImage;
    document.getElementById("boardCustomColorField").hidden = pendingAppearanceBackground !== "color";
  }

  function selectAppearanceBackground(background) {
    const input = elements.appearanceDialog.querySelector(`[name="board_background"][value="${background}"]`)
      || elements.appearanceDialog.querySelector('[name="board_background"][value="plain"]');
    pendingAppearanceBackground = input.value;
    input.checked = true;
  }

  function updateAppearanceOverlayOutput() {
    const input = document.getElementById("boardBackgroundOverlay");
    document.getElementById("boardBackgroundOverlayValue").value = `${input.value}%`;
  }

  function renderBoardAppearance() {
    applyBoardAppearanceStyles(history.present.board);
  }

  function previewAppearanceSelection() {
    applyBoardAppearanceStyles({
      ...history.present.board,
      background: pendingAppearanceBackground,
      backgroundColor: safeColor(document.getElementById("boardBackgroundColor").value),
      backgroundImage: pendingAppearancePreviewImage || pendingAppearanceImage,
      backgroundSize: document.getElementById("boardBackgroundSize").value,
      backgroundOverlay: Number(document.getElementById("boardBackgroundOverlay").value),
      showGrid: false,
      aspectRatio: document.getElementById("boardAspectRatio").value
    });
  }

  function applyBoardAppearanceStyles(board) {
    root.dataset.background = board.background || "plain";
    root.classList.toggle("has-board-grid", board.showGrid === true);
    elements.stage.style.aspectRatio = board.aspectRatio === "auto" ? "" : board.aspectRatio;
    elements.stage.style.setProperty("--board-background-color", safeColor(board.backgroundColor || "#ffffff"));
    elements.stage.style.setProperty("--board-background-size", board.backgroundSize === "contain" ? "contain" : "cover");
    elements.stage.style.setProperty("--board-background-overlay", String(clamp(Number(board.backgroundOverlay ?? 18), 0, 70) / 100));
    const presetImage = elements.appearanceDialog
      .querySelector(`[name="board_background"][value="${board.background}"]`)
      ?.closest("label")
      ?.querySelector("[data-background-image]")
      ?.dataset.backgroundImage || "";
    const customImage = safeBoardBackgroundImage(board.backgroundImage);
    elements.stage.style.setProperty("--board-preset-background-image", presetImage ? `url("${presetImage.replaceAll('"', "%22")}")` : "none");
    elements.stage.style.setProperty("--board-custom-background-image", customImage ? `url("${customImage.replaceAll('"', "%22")}")` : "none");
  }

  async function saveBoard() {
    if (!config.authenticated) {
      elements.libraryContent.innerHTML = `<div class="smart-board-auth-prompt"><i class="fa-solid fa-cloud"></i><h3>احفظ سبورتك على كل أجهزتك</h3><p>المسودة محفوظة على هذا الجهاز. سجّل الدخول لحفظها في حسابك.</p><a href="${escapeHtml(config.routes.login)}">تسجيل الدخول</a><a class="secondary" href="${escapeHtml(config.routes.register)}">إنشاء حساب</a></div>`;
      return elements.libraryDialog.showModal();
    }
    if (!config.verified) return notify("فعّل بريدك الإلكتروني قبل الحفظ السحابي.", true);
    clearTimeout(saveTimer);
    elements.status.textContent = "جارٍ الحفظ على حسابك…";

    try {
      const pendingImages = history.present.widgets.filter((widget) => widget.type === "image" && widget.config.localAsset && widget.config.src?.startsWith("data:"));
      const pendingBackgroundImage = history.present.board.backgroundImageLocal
        && history.present.board.backgroundImage?.startsWith("data:image/")
        ? history.present.board.backgroundImage
        : null;
      const cloudState = structuredClone(history.present);
      cloudState.widgets.forEach((widget) => {
        if (widget.type === "image" && widget.config.localAsset) widget.config = { ...widget.config, src: "" };
      });
      if (pendingBackgroundImage) {
        cloudState.board.backgroundImage = "";
        cloudState.board.backgroundImageLocal = false;
      }
      const endpoint = boardId ? `${config.routes.boards}/${boardId}` : config.routes.boards;
      const response = await jsonFetch(endpoint, {
        method: boardId ? "PATCH" : "POST",
        body: { title: history.present.board.title, state: cloudState, ...(boardId ? { version: boardVersion } : {}) }
      });
      if (response.status === 409) return handleConflict(await response.json());
      const payload = await response.json();
      if (!response.ok) throw new Error(firstError(payload) || "تعذر حفظ السبورة.");
      boardId = payload.data.id;
      boardVersion = payload.data.version;

      for (const widget of pendingImages) {
        const upload = await uploadImage(widget.config.src);
        history.present = reduceBoard(history.present, {
          type: "update", id: widget.id,
          changes: { config: { ...widget.config, src: upload.url, assetId: upload.id, localAsset: false } }
        });
      }
      if (pendingBackgroundImage) {
        const upload = await uploadImage(pendingBackgroundImage, "board-background");
        await decodeImageSource(upload.url).catch(() => {});
        history.present = reduceBoard(history.present, {
          type: "appearance",
          changes: {
            backgroundImage: upload.url,
            backgroundImageAssetId: upload.id,
            backgroundImageLocal: false
          }
        });
        renderBoardAppearance();
      }
      if (pendingImages.length || pendingBackgroundImage) {
        const update = await jsonFetch(`${config.routes.boards}/${boardId}`, {
          method: "PATCH",
          body: { title: history.present.board.title, state: history.present, version: boardVersion }
        });
        const updated = await update.json();
        if (!update.ok) throw new Error(firstError(updated) || "حُفظت السبورة دون بعض الصور.");
        boardVersion = updated.data.version;
      }
      await saveDraft(history.present);
      elements.status.textContent = "تم الحفظ على حسابك";
    } catch (error) {
      elements.status.textContent = "تعذر الحفظ السحابي؛ المسودة آمنة محليًا";
      notify(error.message, true);
    }
  }

  async function uploadImage(dataUrl, baseName = "board-image") {
    const blob = await (await fetch(dataUrl)).blob();
    const extension = { "image/jpeg": "jpg", "image/png": "png", "image/webp": "webp" }[blob.type] || "webp";
    const form = new FormData();
    form.append("image", blob, `${baseName}.${extension}`);
    const response = await fetch(`${config.routes.boards}/${boardId}/assets`, {
      method: "POST",
      headers: { Accept: "application/json", "X-CSRF-TOKEN": config.csrfToken },
      body: form
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(firstError(payload) || "تعذر رفع الصورة.");
    return payload.data;
  }

  function handleConflict(payload) {
    const loadServer = window.confirm("عُدّلت السبورة من جهاز آخر. اضغط موافق لتحميل نسخة الخادم، أو إلغاء لحفظ عملك كنسخة جديدة.");
    if (loadServer) {
      history = createHistory(migrateState(payload.data.state));
      boardVersion = payload.data.version;
      elements.title.value = payload.data.title;
      render();
      notify("تم تحميل النسخة الأحدث.");
    } else {
      boardId = null;
      boardVersion = null;
      saveBoard();
    }
  }

  async function openLibrary() {
    if (!config.authenticated) return saveBoard();
    elements.libraryContent.innerHTML = '<p class="smart-board-loading"><i class="fa-solid fa-spinner fa-spin"></i> جارٍ تحميل سبوراتك…</p>';
    elements.libraryDialog.showModal();
    try {
      const response = await fetch(config.routes.boards, { headers: { Accept: "application/json" } });
      const boards = (await response.json()).data || [];
      elements.libraryContent.innerHTML = boards.length
        ? boards.map((board) => `<a class="smart-board-library-item" href="/tools/smart-board?board=${board.id}"><i class="fa-solid fa-chalkboard"></i><span><strong>${escapeHtml(board.title)}</strong><small>آخر تعديل ${new Date(board.updated_at).toLocaleDateString("ar")}</small></span><i class="fa-solid fa-arrow-left"></i></a>`).join("")
        : '<div class="smart-board-empty-library"><i class="fa-regular fa-folder-open"></i><p>لم تحفظ أي سبورة بعد.</p></div>';
    } catch {
      elements.libraryContent.innerHTML = '<p class="text-red-700">تعذر تحميل السبورات.</p>';
    }
  }

  async function openPollSession(widget) {
    if (widget.config.session) {
      showSession(widget);
      return;
    }
    elements.sessionContent.innerHTML = '<p class="smart-board-loading"><i class="fa-solid fa-spinner fa-spin"></i> جارٍ إنشاء الجلسة…</p>';
    elements.sessionDialog.showModal();
    try {
      const response = await jsonFetch(config.routes.sessions, {
        method: "POST",
        body: { smart_board_id: boardId, poll_key: widget.id, poll: { question: widget.config.question, options: widget.config.options } }
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(firstError(payload) || "تعذر إنشاء الجلسة.");
      updateWidget(widget.id, { config: { ...widget.config, session: payload.data } });
      showSession(findWidget(widget.id));
    } catch (error) {
      elements.sessionContent.innerHTML = `<p class="text-red-700">${escapeHtml(error.message)}</p>`;
    }
  }

  async function showSession(widget) {
    const session = widget.config.session;
    elements.sessionContent.innerHTML = `<div class="smart-board-session-share"><p>اطلب من الطلاب مسح الرمز أو فتح الرابط:</p><strong>${escapeHtml(session.code)}</strong><div class="smart-board-session-qr" id="sessionQr"></div><a href="${escapeHtml(session.join_url || `/board/join/${session.code}`)}" target="_blank" rel="noopener">فتح صفحة الطالب</a></div>`;
    elements.sessionDialog.showModal();
    const svg = await generateQr(session.join_url || `${location.origin}/board/join/${session.code}`).catch(() => "");
    const target = document.getElementById("sessionQr");
    if (target) target.innerHTML = svg;
  }

  async function refreshPollResults(widgetId, control) {
    const widget = findWidget(widgetId);
    const session = widget?.config.session;
    if (!session?.code) return;

    control.disabled = true;
    control.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جارٍ التحديث…';

    try {
      const response = await fetch(`${config.routes.sessions}/${session.code}`, { headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (!response.ok) throw new Error(firstError(payload) || "تعذر تحديث نتائج الاستطلاع.");
      history.present = reduceBoard(history.present, {
        type: "update",
        id: widgetId,
        changes: { config: { ...widget.config, session: { ...session, ...payload.data } } }
      });
      renderWidget(widgetId);
    } catch (error) {
      control.disabled = false;
      control.innerHTML = '<i class="fa-solid fa-rotate"></i> تحديث النتائج';
      notify(error.message, true);
    }
  }

  function togglePresentation() {
    presentation = !presentation;
    if (presentation) setInteractionMode("move");
    root.classList.toggle("is-presenting", presentation);
    selectedId = null;
    document.getElementById("presentationModeBtn").innerHTML = presentation
      ? '<i class="fa-solid fa-pen-to-square"></i><span>عودة للتحرير</span>'
      : '<i class="fa-solid fa-display"></i><span>وضع العرض</span>';
    renderSelection();
  }

  async function toggleFullscreen() {
    if (document.fullscreenElement) await document.exitFullscreen();
    else await root.requestFullscreen();
  }

  function updateFullscreenIcon() {
    document.querySelector("#fullscreenBoardBtn i").className = `fa-solid ${document.fullscreenElement ? "fa-compress" : "fa-expand"}`;
  }

  function updateLiveWidgets() {
    document.querySelectorAll("[data-live-clock]").forEach((element) => {
      const widget = findWidget(element.closest("[data-widget-id]")?.dataset.widgetId);
      const now = new Date();
      if (widget?.config.mode === "analog") {
        const second = (now.getSeconds() + now.getMilliseconds() / 1000) * 6;
        const minute = now.getMinutes() * 6 + second / 60;
        const hour = (now.getHours() % 12) * 30 + minute / 12;
        let face = element.querySelector(".smart-board-analog-face");
        if (!face || Boolean(face.querySelector(".second")) !== Boolean(widget.config.showSeconds)) {
          element.innerHTML = `<span class="smart-board-analog-face"><span class="smart-board-clock-minute-marks" aria-hidden="true"></span><span class="smart-board-clock-numbers" aria-hidden="true">${analogClockNumbers()}</span><i class="hour"></i><i class="minute"></i>${widget.config.showSeconds ? '<i class="second"></i>' : ""}<b></b></span>`;
          face = element.querySelector(".smart-board-analog-face");
        }
        face.style.setProperty("--hour", `${hour}deg`);
        face.style.setProperty("--minute", `${minute}deg`);
        face.style.setProperty("--second", `${second}deg`);
        element.classList.add("is-analog");
      } else {
        element.classList.remove("is-analog");
        const time = now.toLocaleTimeString("ar", { hour: "2-digit", minute: "2-digit", hour12: widget?.config.hourFormat !== "24", ...(widget?.config.showSeconds ? { second: "2-digit" } : {}) });
        element.innerHTML = `<span class="smart-board-digital-clock-value">${escapeHtml(time)}</span>`;
      }
    });
    document.querySelectorAll("[data-live-timer]").forEach((element) => {
      const widget = findWidget(element.dataset.liveTimer);
      if (!widget) return;
      const seconds = currentTimerSeconds(widget);
      element.textContent = formatDuration(seconds);
      if (widget.config.running && seconds <= 0) {
        beep();
        updateWidget(widget.id, { config: { ...widget.config, remaining: 0, running: false, endsAt: null } });
      }
    });
    document.querySelectorAll("[data-live-stopwatch]").forEach((element) => {
      const widget = findWidget(element.dataset.liveStopwatch);
      if (widget) element.textContent = formatDuration(currentStopwatchSeconds(widget));
    });
  }

  function currentTimerSeconds(widget) {
    return widget.config.running && widget.config.endsAt
      ? Math.max(0, Math.ceil((widget.config.endsAt - Date.now()) / 1000))
      : Number(widget.config.remaining) || 0;
  }

  function currentStopwatchSeconds(widget) {
    return (Number(widget.config.elapsed) || 0) + (widget.config.running && widget.config.startedAt
      ? Math.floor((Date.now() - widget.config.startedAt) / 1000)
      : 0);
  }

  function setInteractionMode(mode) {
    interactionMode = mode === "draw" && !presentation ? "draw" : "move";
    root.dataset.interactionMode = interactionMode;
    if (interactionMode === "draw") selectedId = null;

    document.querySelectorAll("[data-board-mode]").forEach((button) => {
      const active = button.dataset.boardMode === interactionMode;
      button.classList.toggle("is-active", active);
      button.setAttribute("aria-pressed", String(active));
    });

    renderSelection();
    renderDrawingControls();
  }

  function rollDice(widget, control) {
    const dice = control.closest(".smart-board-dice");
    if (!dice || dice.classList.contains("is-rolling")) return;

    dice.classList.add("is-rolling");
    dice.setAttribute("aria-busy", "true");
    control.disabled = true;
    playDiceRollSound();

    const duration = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? 120 : 680;
    window.setTimeout(() => {
      const currentWidget = findWidget(widget.id);
      if (!currentWidget) return;
      updateWidget(widget.id, { config: { ...currentWidget.config, value: randomDiceValue() } });
    }, duration);
  }

  function setDockCollapsed(collapsed) {
    const label = collapsed ? "تكبير شريط الأدوات" : "تصغير شريط الأدوات";
    elements.dock.classList.toggle("is-collapsed", collapsed);
    elements.dockToggle.setAttribute("aria-expanded", String(!collapsed));
    elements.dockToggle.setAttribute("aria-label", label);
    elements.dockToggle.title = label;
    elements.dockToggle.querySelector("i").className = collapsed
      ? "fa-solid fa-angles-up"
      : "fa-solid fa-angles-down";
    if (collapsed) setMoreMenuOpen(false);
  }

  function setMoreMenuOpen(open) {
    if (open) syncMoreMenuTools();
    elements.moreMenu.hidden = !open;
    elements.moreToggle.classList.toggle("is-active", open);
    elements.moreToggle.setAttribute("aria-expanded", String(open));
  }

  function syncMoreMenuTools() {
    const visibleDockTypes = new Set(
      [...elements.dockTools.querySelectorAll("[data-dock-priority][data-widget-type]")]
        .filter((button) => window.getComputedStyle(button).display !== "none")
        .map((button) => button.dataset.widgetType)
    );

    elements.moreMenu.querySelectorAll(".smart-board-more-tool[data-widget-type]").forEach((moreTool) => {
      moreTool.hidden = visibleDockTypes.has(moreTool.dataset.widgetType);
    });
  }

  function updateDrawingSettings(changes) {
    history.present = reduceBoard(history.present, { type: "drawing", changes });
    schedulePersistence();
    renderDrawingControls();
  }

  function renderDrawingControls() {
    const drawing = history.present.board.drawing;
    root.dataset.interactionMode = interactionMode;
    elements.drawingToolbar.hidden = interactionMode !== "draw" || presentation;
    elements.drawingColor.value = safeColor(drawing.color);
    elements.drawingWidth.value = String(drawing.width);
    elements.drawingWidthValue.value = String(drawing.width);
    elements.drawingToolbar.querySelectorAll("[data-drawing-tool]").forEach((button) => {
      const active = button.dataset.drawingTool === drawing.tool;
      button.classList.toggle("is-active", active);
      button.setAttribute("aria-pressed", String(active));
    });
  }

  function setupBoardDrawing() {
    elements.drawingCanvas.addEventListener("pointerdown", startBoardStroke);
    new ResizeObserver(renderBoardDrawing).observe(elements.stage);
  }

  function startBoardStroke(event) {
    if (interactionMode !== "draw" || presentation || (event.button !== 0 && event.pointerType === "mouse")) return;
    event.preventDefault();

    const drawing = history.present.board.drawing;
    const stroke = { color: drawing.color, width: drawing.width, mode: drawing.tool, points: [] };
    const pointerId = event.pointerId;
    elements.drawingCanvas.setPointerCapture?.(pointerId);

    const addPoints = (pointEvent) => {
      const rectangle = elements.drawingCanvas.getBoundingClientRect();
      const events = pointEvent.getCoalescedEvents?.() || [pointEvent];

      events.forEach((sample) => {
        const point = [
          clamp((sample.clientX - rectangle.left) / rectangle.width, 0, 1),
          clamp((sample.clientY - rectangle.top) / rectangle.height, 0, 1)
        ];
        const previous = stroke.points.at(-1);
        if (previous && Math.hypot(point[0] - previous[0], point[1] - previous[1]) < 0.0005) return;
        stroke.points.push(point);
        drawStrokeSegment(elements.drawingCanvas, stroke);
      });
    };

    const move = (moveEvent) => {
      if (moveEvent.pointerId === pointerId) addPoints(moveEvent);
    };

    const end = (endEvent) => {
      if (endEvent.pointerId !== pointerId) return;
      addPoints(endEvent);
      elements.drawingCanvas.removeEventListener("pointermove", move);
      elements.drawingCanvas.removeEventListener("pointerup", end);
      elements.drawingCanvas.removeEventListener("pointercancel", end);
      if (elements.drawingCanvas.hasPointerCapture?.(pointerId)) elements.drawingCanvas.releasePointerCapture(pointerId);
      if (!stroke.points.length) return;

      const strokes = [...history.present.board.drawing.strokes, stroke].slice(-200);
      commit(reduceBoard(history.present, { type: "drawing", changes: { strokes } }));
      updateHistoryButtons();
      renderBoardDrawing();
    };

    addPoints(event);
    elements.drawingCanvas.addEventListener("pointermove", move);
    elements.drawingCanvas.addEventListener("pointerup", end);
    elements.drawingCanvas.addEventListener("pointercancel", end);
  }

  function renderBoardDrawing() {
    const canvas = elements.drawingCanvas;
    const rectangle = canvas.getBoundingClientRect();
    const width = Math.max(1, Math.round(rectangle.width * devicePixelRatio));
    const height = Math.max(1, Math.round(rectangle.height * devicePixelRatio));
    if (canvas.width !== width) canvas.width = width;
    if (canvas.height !== height) canvas.height = height;
    drawStrokes(canvas, history.present.board.drawing.strokes);
  }

  function drawStrokes(canvas, strokes) {
    const context = canvas.getContext("2d");
    context.clearRect(0, 0, canvas.width, canvas.height);
    strokes.forEach((stroke) => drawStroke(canvas, stroke));
  }

  function drawStroke(canvas, stroke) {
    if (!stroke.points?.length) return;
    const context = canvas.getContext("2d");
    prepareStrokeContext(context, stroke);
    context.beginPath();
    stroke.points.forEach(([x, y], index) => {
      const px = x * canvas.width;
      const py = y * canvas.height;
      if (index === 0) context.moveTo(px, py);
      else context.lineTo(px, py);
    });
    if (stroke.points.length === 1) {
      const [x, y] = stroke.points[0];
      context.lineTo(x * canvas.width + 0.01, y * canvas.height + 0.01);
    }
    context.stroke();
    resetStrokeContext(context);
  }

  function drawStrokeSegment(canvas, stroke) {
    const points = stroke.points;
    if (!points.length) return;
    const context = canvas.getContext("2d");
    const from = points[Math.max(0, points.length - 2)];
    const to = points.at(-1);
    prepareStrokeContext(context, stroke);
    context.beginPath();
    context.moveTo(from[0] * canvas.width, from[1] * canvas.height);
    context.lineTo(to[0] * canvas.width + (points.length === 1 ? 0.01 : 0), to[1] * canvas.height + (points.length === 1 ? 0.01 : 0));
    context.stroke();
    resetStrokeContext(context);
  }

  function prepareStrokeContext(context, stroke) {
    const width = clamp(Number(stroke.width) || 4, 1, 24);
    context.lineCap = "round";
    context.lineJoin = "round";
    context.strokeStyle = safeColor(stroke.color);
    context.globalCompositeOperation = stroke.mode === "eraser" ? "destination-out" : "source-over";
    context.globalAlpha = stroke.mode === "highlight" ? 0.3 : 1;
    context.lineWidth = (stroke.mode === "eraser" ? Math.max(12, width * 3) : width) * devicePixelRatio;
  }

  function resetStrokeContext(context) {
    context.globalCompositeOperation = "source-over";
    context.globalAlpha = 1;
  }

  function requestQrGeneration() {
    clearTimeout(qrGenerationTimer);
    const pending = history.present.widgets.filter((widget) => widget.type === "qr" && widget.config.content && !widget.config.svg);
    if (!pending.length) return;
    qrGenerationTimer = window.setTimeout(async () => {
      const updatedWidgetIds = [];
      for (const widget of pending) {
        const svg = await generateQr(widget.config.content).catch(() => "");
        if (svg) {
          history.present = reduceBoard(history.present, { type: "update", id: widget.id, changes: { config: { ...widget.config, svg } } });
          updatedWidgetIds.push(widget.id);
        }
      }
      updatedWidgetIds.forEach(renderWidget);
      schedulePersistence();
    }, 250);
  }

  async function generateQr(content) {
    const isUrl = /^https?:\/\//i.test(content);
    const response = await jsonFetch(config.routes.qrRender, {
      method: "POST",
      body: {
        mode: "static",
        content_type: isUrl ? "url" : "text",
        payload: isUrl ? { url: content } : { text: content },
        design: { style: "rounded", foreground_color: "#312e81", eye_color: "#6d28d9", background_color: "#ffffff", frame: "none", center_type: "none", center_text: null }
      }
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(firstError(payload));
    return payload.svg;
  }

  function jsonFetch(url, options) {
    return fetch(url, {
      method: options.method,
      credentials: "same-origin",
      headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": config.csrfToken },
      body: JSON.stringify(options.body)
    });
  }

  function notify(message, error = false) {
    elements.toast.textContent = message;
    elements.toast.classList.toggle("is-error", error);
    elements.toast.classList.add("is-visible");
    window.setTimeout(() => elements.toast.classList.remove("is-visible"), 3200);
  }
}

async function setupStudentPoll(root) {
  const content = document.getElementById("studentPollContent");
  const status = document.getElementById("studentPollStatus");
  const responseEndpoint = root.dataset.responseEndpoint;
  let session = JSON.parse(root.dataset.session || "{}");
  let selected = null;
  const tokenKey = `mo3alem-participant-${session.code}`;
  let participantToken = localStorage.getItem(tokenKey);
  if (!participantToken) {
    participantToken = globalThis.crypto?.randomUUID?.() + "-" + Math.random().toString(36).slice(2);
    localStorage.setItem(tokenKey, participantToken);
  }

  render();
  content.addEventListener("click", async (event) => {
    const button = event.target.closest("[data-poll-option]");
    if (!button) return;
    button.disabled = true;
    try {
      const response = await fetch(responseEndpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          Accept: "application/json", "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        body: JSON.stringify({ poll_key: session.poll_key, option_key: button.dataset.pollOption, participant_token: participantToken })
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(firstError(payload) || "تعذر إرسال الإجابة.");
      session = payload.data;
      selected = button.dataset.pollOption;
      status.textContent = "تم تسجيل إجابتك، ويمكنك تغييرها.";
      render();
    } catch (error) {
      status.textContent = error.message;
      button.disabled = false;
    }
  });

  function render() {
    if (session.status !== "active" || !session.poll) {
      content.innerHTML = '<div class="smart-board-join-closed"><i class="fa-solid fa-circle-pause"></i><h1>الاستطلاع غير متاح الآن</h1><p>انتظر تعليمات معلمك.</p></div>';
      return;
    }
    content.innerHTML = `<h1>${escapeHtml(session.poll.question)}</h1><div class="smart-board-student-options">${session.poll.options.map((option) =>
      `<button class="${selected === option.key ? "is-selected" : ""}" data-poll-option="${escapeHtml(option.key)}"><span>${escapeHtml(option.label)}</span><small>${option.count} إجابة</small></button>`
    ).join("")}</div>`;
  }
}

function field(label, name, value, type = "text") {
  const safeValue = escapeHtml(String(value ?? ""));
  if (type === "textarea") return `<label class="smart-board-field">${label}<textarea name="${name}" rows="5">${safeValue}</textarea></label>`;
  return `<label class="smart-board-field">${label}<input name="${name}" type="${type}" value="${safeValue}" ${type === "range" ? 'min="1" max="20"' : ""}></label>`;
}

function calendarWidget(value) {
  const visibleMonth = shiftCalendarMonth(value.year, value.month, 0);
  const monthLabel = new Intl.DateTimeFormat("ar-EG-u-ca-gregory-nu-latn", {
    month: "long",
    year: "numeric"
  }).format(new Date(visibleMonth.year, visibleMonth.month, 1));
  const weekDays = ["اثن", "ثلا", "أرب", "خمي", "جمع", "سبت", "أحد"];
  const days = calendarMonthGrid(visibleMonth.year, visibleMonth.month);

  return `<div class="smart-board-calendar">
    <div class="smart-board-calendar-heading">
      <button type="button" data-widget-control="calendar-previous" aria-label="الشهر السابق" title="الشهر السابق"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
      <strong>${escapeHtml(monthLabel)}</strong>
      <button type="button" data-widget-control="calendar-next" aria-label="الشهر التالي" title="الشهر التالي"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
    </div>
    <div class="smart-board-calendar-weekdays" aria-hidden="true">${weekDays.map((day) => `<span>${day}</span>`).join("")}</div>
    <div class="smart-board-calendar-days" role="grid" aria-label="${escapeHtml(monthLabel)}">${days.map((day) =>
      `<span class="smart-board-calendar-day${day.isCurrentMonth ? "" : " is-outside"}${day.isToday ? " is-today" : ""}" role="gridcell"${day.isToday ? ' aria-current="date"' : ""}><b>${day.day}</b></span>`).join("")}</div>
  </div>`;
}

function selectField(label, name, options, selected) {
  return `<label class="smart-board-field">${label}<select name="${name}">${options.map(([value, text]) =>
    `<option value="${value}" ${value === selected ? "selected" : ""}>${text}</option>`).join("")}</select></label>`;
}

function lines(value) {
  return String(value || "").split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
}

function widgetLabel(type) {
  return {
    text: "نص", clock: "ساعة", calendar: "تقويم", timer: "مؤقت", stopwatch: "ساعة إيقاف",
    "traffic-light": "إشارة المرور", scoreboard: "لوحة النتائج",
    dice: "النرد", randomizer: "الاختيار العشوائي", groups: "المجموعات",
    image: "صورة", embed: "فيديو أو عرض", qr: "رمز QR", poll: "استطلاع"
  }[type] || "أداة";
}

function widgetIcon(type) {
  return {
    text: "fa-font", clock: "fa-clock", calendar: "fa-calendar-days", timer: "fa-hourglass-half", stopwatch: "fa-stopwatch",
    "traffic-light": "fa-traffic-light", scoreboard: "fa-ranking-star",
    dice: "fa-dice", randomizer: "fa-shuffle", groups: "fa-users-rectangle",
    image: "fa-image", embed: "fa-circle-play", qr: "fa-qrcode", poll: "fa-square-poll-vertical"
  }[type] || "fa-shapes";
}

function diceFace(value) {
  const result = Math.round(clamp(Number(value) || 1, 1, 6));
  const positions = {
    1: [5],
    2: [1, 9],
    3: [1, 5, 9],
    4: [1, 3, 7, 9],
    5: [1, 3, 5, 7, 9],
    6: [1, 3, 4, 6, 7, 9]
  }[result];
  const coordinates = [22, 50, 78];

  return `<div class="smart-board-die" data-value="${result}" role="img" aria-label="نتيجة النرد ${result}">${positions.map((position) => {
    const index = position - 1;
    const x = coordinates[index % 3];
    const y = coordinates[Math.floor(index / 3)];

    return `<span class="smart-board-die-pip" style="--pip-x:${x}%;--pip-y:${y}%" aria-hidden="true"></span>`;
  }).join("")}</div>`;
}

function analogClockNumbers() {
  return Array.from({ length: 12 }, (_, index) => {
    const number = index + 1;
    const angle = (number * 30 - 90) * (Math.PI / 180);
    const x = 50 + Math.cos(angle) * 39;
    const y = 50 + Math.sin(angle) * 39;

    return `<span style="--clock-x:${x}%;--clock-y:${y}%">${number.toLocaleString("ar")}</span>`;
  }).join("");
}

function toEmbedUrl(value) {
  try {
    const url = new URL(value);
    const host = url.hostname.replace(/^www\./, "");
    if (host === "youtu.be") return `https://www.youtube-nocookie.com/embed/${url.pathname.slice(1)}`;
    if (["youtube.com", "m.youtube.com"].includes(host)) {
      const id = url.searchParams.get("v") || url.pathname.split("/").filter(Boolean).at(-1);
      return id ? `https://www.youtube-nocookie.com/embed/${id}` : null;
    }
    if (host === "vimeo.com") return `https://player.vimeo.com/video/${url.pathname.split("/").filter(Boolean).at(-1)}`;
    if (host.endsWith("canva.com") || host === "docs.google.com") return url.toString();
  } catch {}
  return null;
}

function safeColor(value) {
  return /^#[\da-f]{6}$/i.test(String(value)) ? String(value) : "#312e81";
}

function safeTextFontKey(value) {
  return ["interface", "readable", "traditional", "mono"].includes(String(value)) ? String(value) : "interface";
}

function safeTextFont(value) {
  return {
    interface: "inherit",
    readable: "Tahoma, Arial, sans-serif",
    traditional: "Arial, Tahoma, sans-serif",
    mono: "'Courier New', monospace"
  }[safeTextFontKey(value)];
}

function insertPlainText(editor, text) {
  const selection = window.getSelection();
  if (!selection?.rangeCount || !editor.contains(selection.anchorNode)) {
    editor.append(document.createTextNode(text));
    return;
  }

  const range = selection.getRangeAt(0);
  range.deleteContents();
  const node = document.createTextNode(text);
  range.insertNode(node);
  range.setStartAfter(node);
  range.collapse(true);
  selection.removeAllRanges();
  selection.addRange(range);
}

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>'"]/g, (character) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;"
  })[character]);
}

function savedListOptionTemplate(savedList) {
  const id = Number(savedList?.id);
  if (!Number.isInteger(id) || id < 1) return "";
  const title = String(savedList?.title || "قائمة بلا اسم").slice(0, 120);
  const namesCount = Math.max(0, Number(savedList?.names_count) || 0);

  return `<button type="button" data-saved-list-id="${id}" data-saved-list-title="${escapeHtml(title)}"><span><strong>${escapeHtml(title)}</strong><small>${new Intl.NumberFormat("en-GB").format(namesCount)} اسم</small></span><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>`;
}

function cssEscape(value) {
  return globalThis.CSS?.escape ? CSS.escape(value) : value.replace(/[^a-zA-Z0-9_-]/g, "\\$&");
}

function readFile(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(reader.error);
    reader.readAsDataURL(file);
  });
}

function firstError(payload) {
  return payload?.message || Object.values(payload?.errors || {}).flat()[0] || "حدث خطأ غير متوقع.";
}

function beep() {
  try {
    const context = new AudioContext();
    const oscillator = context.createOscillator();
    oscillator.frequency.value = 760;
    oscillator.connect(context.destination);
    oscillator.start();
    oscillator.stop(context.currentTime + 0.35);
  } catch {}
}

function playDiceRollSound() {
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;

    const context = new AudioContextClass();
    void context.resume();
    const master = context.createGain();
    master.gain.setValueAtTime(0.3, context.currentTime);
    master.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.75);
    master.connect(context.destination);

    [0, 0.07, 0.15, 0.24, 0.34, 0.46, 0.59].forEach((offset, index) => {
      const duration = 0.035 + (index % 2) * 0.012;
      const buffer = context.createBuffer(1, Math.ceil(context.sampleRate * duration), context.sampleRate);
      const samples = buffer.getChannelData(0);
      for (let sample = 0; sample < samples.length; sample += 1) {
        samples[sample] = (Math.random() * 2 - 1) * (1 - sample / samples.length);
      }

      const source = context.createBufferSource();
      const filter = context.createBiquadFilter();
      const gain = context.createGain();
      const startsAt = context.currentTime + offset;
      filter.type = "bandpass";
      filter.frequency.value = 650 + index * 95;
      filter.Q.value = 1.2;
      gain.gain.setValueAtTime(0.0001, startsAt);
      gain.gain.exponentialRampToValueAtTime(0.55 - index * 0.035, startsAt + 0.006);
      gain.gain.exponentialRampToValueAtTime(0.0001, startsAt + duration);
      source.buffer = buffer;
      source.connect(filter);
      filter.connect(gain);
      gain.connect(master);
      source.start(startsAt);
    });

    window.setTimeout(() => context.close().catch(() => {}), 900);
  } catch {}
}
