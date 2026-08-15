const certificateConfigElement = document.getElementById("certificateAppConfig");

if (certificateConfigElement) {
  const config = JSON.parse(certificateConfigElement.dataset.config || "{}");
  const templates = new Map((config.templates || []).map((template) => [template.key, template]));
  const canvas = document.getElementById("certificateCanvas");
  const canvasSizer = document.getElementById("certificateCanvasSizer");
  const editorShell = document.getElementById("certificateEditorShell");
  const viewport = document.getElementById("certificateViewport");
  const backgroundImage = document.getElementById("certificateBackground");
  const guideX = document.getElementById("certificateGuideX");
  const guideY = document.getElementById("certificateGuideY");
  const layersList = document.getElementById("certificateLayersList");
  const layersCount = document.getElementById("certificateLayersCount");
  const propertiesEmpty = document.getElementById("certificatePropertiesEmpty");
  const propertiesForm = document.getElementById("certificatePropertiesForm");
  const sidebarPanels = document.querySelectorAll("[data-certificate-sidebar-panel]");
  const sidebarTabs = document.querySelectorAll("[data-certificate-sidebar-tab]");
  const textContentInput = document.getElementById("certificateTextContent");
  const fontFamilyInput = document.getElementById("certificateFontFamily");
  const fontSizeInput = document.getElementById("certificateFontSize");
  const fontWeightInput = document.getElementById("certificateFontWeight");
  const textColorInput = document.getElementById("certificateTextColor");
  const textColorValue = document.getElementById("certificateTextColorValue");
  const rotationInput = document.getElementById("certificateRotation");
  const rotationValue = document.getElementById("certificateRotationValue");
  const opacityInput = document.getElementById("certificateOpacity");
  const opacityValue = document.getElementById("certificateOpacityValue");
  const lockedInput = document.getElementById("certificateElementLocked");
  const zoomRange = document.getElementById("certificateZoomRange");
  const zoomValue = document.getElementById("certificateZoomValue");
  const saveIndicator = document.getElementById("certificateSaveIndicator");
  const workingTitle = document.getElementById("certificateWorkingTitle");
  const backgroundInput = document.getElementById("certificateBackgroundInput");
  const backgroundHint = document.getElementById("certificateBackgroundHint");
  const saveDialog = document.getElementById("saveCertificateDialog");
  const saveForm = document.getElementById("saveCertificateForm");
  const saveTitleInput = document.getElementById("certificateSaveTitle");
  const saveStatus = document.getElementById("certificateSaveStatus");
  const confirmSaveButton = document.getElementById("confirmSaveCertificateBtn");
  const guestDialog = document.getElementById("guestCertificateDialog");
  const previewDialog = document.getElementById("certificatePreviewDialog");
  const previewImage = document.getElementById("certificatePreviewImage");
  const undoButton = document.getElementById("certificateUndoBtn");
  const redoButton = document.getElementById("certificateRedoBtn");
  const fullscreenButton = document.getElementById("certificateFullscreenBtn");
  const baseDraftKey = "muallem-certificate-draft-v1";
  const allowedImageTypes = ["image/jpeg", "image/png", "image/webp"];
  const maximumBackgroundSize = 4 * 1024 * 1024;
  const minimumElementWidth = 60;
  const minimumElementHeight = 28;
  const historyLimit = 80;
  const maximumSavedCertificates = Number(config.limits?.savedCertificates) || 5;

  let currentCertificate = config.savedCertificate || null;
  let savedCertificatesCount = Number(config.usage?.savedCertificates) || 0;
  let templateKey = "b6";
  let design = defaultDesign();
  let selectedElementId = null;
  let zoom = 0.7;
  let pointerOperation = null;
  let backgroundDataUrl = null;
  let currentBackgroundFile = null;
  let previewObjectUrl = null;
  let draftTimer = null;
  let historyTimer = null;
  let history = [];
  let historyIndex = -1;
  let isRestoringHistory = false;

  function uniqueId() {
    return globalThis.crypto?.randomUUID?.() || `text-${Date.now()}-${Math.random().toString(16).slice(2)}`;
  }

  function defaultText(overrides = {}) {
    return {
      id: uniqueId(),
      type: "text",
      text: "نص جديد",
      x: 260,
      y: 330,
      width: 600,
      height: 70,
      font_size: 42,
      font_family: "Tajawal",
      font_weight: 700,
      color: "#172b52",
      text_align: "center",
      direction: "rtl",
      rotation: 0,
      opacity: 1,
      locked: false,
      ...overrides
    };
  }

  function defaultDesign() {
    return {
      width: 1120,
      height: 790,
      elements: [
        defaultText({ text: "شهادة تقدير", x: 160, y: 150, width: 800, height: 92, font_size: 58, font_weight: 900 }),
        defaultText({ text: "تُمنح هذه الشهادة إلى", x: 260, y: 270, width: 600, height: 55, font_size: 28, font_weight: 500 }),
        defaultText({ text: "اسم الطالب/ـة", x: 170, y: 330, width: 780, height: 90, font_size: 54, font_weight: 900, color: "#6d28d9" }),
        defaultText({ text: "تقديراً لتميّزه/ـا وجهوده/ـا الرائعة، مع أطيب الأمنيات بمزيد من النجاح.", x: 190, y: 450, width: 740, height: 105, font_size: 27, font_weight: 500 }),
        defaultText({ text: "المعلّم/ـة", x: 760, y: 625, width: 250, height: 55, font_size: 23, font_weight: 700 }),
        defaultText({ text: new Intl.DateTimeFormat("ar", { dateStyle: "long" }).format(new Date()), x: 110, y: 625, width: 310, height: 55, font_size: 21, font_weight: 500 })
      ]
    };
  }

  function numeric(value, fallback) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function normalizeElement(element, index) {
    return defaultText({
      ...element,
      id: typeof element?.id === "string" ? element.id : `${uniqueId()}-${index}`,
      type: "text",
      text: typeof element?.text === "string" && element.text.trim() ? element.text : "نص جديد",
      x: numeric(element?.x, 100),
      y: numeric(element?.y, 100),
      width: numeric(element?.width, 500),
      height: numeric(element?.height, 70),
      font_size: numeric(element?.font_size, 36),
      font_weight: numeric(element?.font_weight, 700),
      rotation: numeric(element?.rotation, 0),
      opacity: numeric(element?.opacity, 1),
      locked: Boolean(element?.locked)
    });
  }

  function normalizeDesign(value) {
    const fallback = defaultDesign();

    return {
      width: Math.round(numeric(value?.width, fallback.width)),
      height: Math.round(numeric(value?.height, fallback.height)),
      elements: Array.isArray(value?.elements)
        ? value.elements.slice(0, 50).map(normalizeElement)
        : fallback.elements
    };
  }

  function clone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  function draftKey(certificate = currentCertificate) {
    return `${baseDraftKey}:${certificate?.id || "new"}`;
  }

  function readDraft(key = draftKey()) {
    try {
      const draft = JSON.parse(localStorage.getItem(key) || "null");

      return draft && Date.now() - Number(draft.updatedAt || 0) < 7 * 24 * 60 * 60 * 1000
        ? draft
        : null;
    } catch (_) {
      return null;
    }
  }

  function persistDraft(pendingSave = false) {
    const previous = readDraft();
    const draft = {
      templateKey,
      design,
      backgroundDataUrl,
      title: saveTitleInput.value.trim() || currentCertificate?.title || "شهادة جديدة",
      pendingSave: pendingSave || previous?.pendingSave || false,
      updatedAt: Date.now()
    };

    try {
      localStorage.setItem(draftKey(), JSON.stringify(draft));
      setSaveIndicator("تم حفظ المسودة على هذا الجهاز", "muted");
    } catch (_) {
      setSaveIndicator("تعذر حفظ صورة القالب محلياً؛ أكمل التصميم ثم نزّله قبل إغلاق الصفحة", "error");
    }
  }

  function scheduleDraft() {
    window.clearTimeout(draftTimer);
    draftTimer = window.setTimeout(() => persistDraft(), 280);
  }

  function setSaveIndicator(message, tone = "muted") {
    saveIndicator.textContent = message;
    saveIndicator.className = "mt-0.5 text-xs font-bold";
    saveIndicator.classList.add(tone === "error" ? "text-red-700" : tone === "success" ? "text-emerald-700" : "text-slate-500");
  }

  function setSaveStatus(message = "", tone = "muted") {
    saveStatus.textContent = message;
    saveStatus.className = "mt-2 min-h-5 text-xs font-bold";
    saveStatus.classList.add(tone === "error" ? "text-red-700" : tone === "success" ? "text-emerald-700" : "text-slate-500");
  }

  function certificateLimitMessage() {
    return `وصلت إلى الحد الأقصى وهو ${maximumSavedCertificates} شهادات محفوظة. احذف شهادة قبل حفظ شهادة جديدة.`;
  }

  function certificateLimitReached() {
    return !currentCertificate && savedCertificatesCount >= maximumSavedCertificates;
  }

  function snapshot() {
    return JSON.stringify({ templateKey, design });
  }

  function pushHistory() {
    if (isRestoringHistory) return;

    const value = snapshot();
    if (history[historyIndex] === value) return;

    history = history.slice(0, historyIndex + 1);
    history.push(value);
    if (history.length > historyLimit) history.shift();
    historyIndex = history.length - 1;
    updateHistoryButtons();
  }

  function scheduleHistory() {
    window.clearTimeout(historyTimer);
    historyTimer = window.setTimeout(pushHistory, 320);
  }

  function markChanged({ immediateHistory = false } = {}) {
    setSaveIndicator("لديك تغييرات غير محفوظة");
    scheduleDraft();
    if (immediateHistory) pushHistory();
    else scheduleHistory();
  }

  function updateHistoryButtons() {
    undoButton.disabled = historyIndex <= 0;
    redoButton.disabled = historyIndex >= history.length - 1;
  }

  function restoreHistory(index) {
    const value = history[index];
    if (!value) return;

    isRestoringHistory = true;
    const state = JSON.parse(value);
    templateKey = state.templateKey;
    design = normalizeDesign(state.design);
    historyIndex = index;
    selectedElementId = null;
    renderEditor();
    updateHistoryButtons();
    scheduleDraft();
    setSaveIndicator("تم استرجاع التعديل");
    isRestoringHistory = false;
  }

  function selectedElement() {
    return design.elements.find((element) => element.id === selectedElementId) || null;
  }

  function backgroundSource() {
    if (templateKey === "custom") {
      return backgroundDataUrl || currentCertificate?.background_url || "";
    }

    return templates.get(templateKey)?.url || templates.get("b6")?.url || "";
  }

  function applyCanvasGeometry() {
    canvas.style.width = `${design.width}px`;
    canvas.style.height = `${design.height}px`;
    canvas.style.transform = `scale(${zoom})`;
    canvasSizer.style.width = `${design.width * zoom}px`;
    canvasSizer.style.height = `${design.height * zoom}px`;
    zoomRange.value = String(Math.round(zoom * 100));
    zoomValue.textContent = `${Math.round(zoom * 100)}%`;
    design.elements.forEach((element) => {
      const actions = elementActions(element.id);
      if (actions) applyElementActionsStyle(actions, element);
    });
  }

  function applyElementStyle(node, element, index = design.elements.indexOf(element)) {
    node.style.left = `${element.x}px`;
    node.style.top = `${element.y}px`;
    node.style.width = `${element.width}px`;
    node.style.height = `${element.height}px`;
    node.style.fontSize = `${element.font_size}px`;
    node.style.fontFamily = `"${element.font_family}", sans-serif`;
    node.style.fontWeight = String(element.font_weight);
    node.style.color = element.color;
    node.style.textAlign = element.text_align;
    node.style.direction = element.direction;
    node.style.transform = `rotate(${element.rotation}deg)`;
    node.style.opacity = String(element.opacity);
    node.style.zIndex = String(index + 2);
    node.classList.toggle("is-selected", element.id === selectedElementId);
    node.classList.toggle("is-locked", element.locked);
    node.querySelector(".certificate-text-content").textContent = element.text;

    const actions = elementActions(element.id);
    if (actions) applyElementActionsStyle(actions, element);
  }

  function applyElementActionsStyle(actions, element) {
    const toolbarGap = 10 / zoom;
    const toolbarHeight = 56 / zoom;
    const toolbarWidth = 156 / zoom;
    const shouldPlaceBelow = element.y < toolbarHeight + toolbarGap;
    const shouldAlignRight = element.x + toolbarWidth > design.width;
    actions.style.setProperty("--certificate-actions-scale", String(1 / zoom));
    actions.style.top = `${shouldPlaceBelow ? element.y + element.height + toolbarGap : element.y - toolbarGap - toolbarHeight}px`;
    actions.style.left = shouldAlignRight ? "auto" : `${element.x}px`;
    actions.style.right = shouldAlignRight ? `${Math.max(0, design.width - element.x - element.width)}px` : "auto";
    actions.classList.toggle("is-visible", element.id === selectedElementId);
    actions.classList.toggle("actions-below", shouldPlaceBelow);
    actions.classList.toggle("actions-align-right", shouldAlignRight);

    const lockButton = actions.querySelector('[data-certificate-element-action="lock"]');
    lockButton.classList.toggle("is-active", element.locked);
    lockButton.setAttribute("aria-pressed", String(element.locked));
    lockButton.setAttribute("aria-label", element.locked ? "فتح قفل العنصر" : "قفل العنصر");
    lockButton.title = element.locked ? "فتح القفل" : "قفل";
    lockButton.querySelector("i").className = `fa-solid ${element.locked ? "fa-lock" : "fa-lock-open"}`;
  }

  function elementNode(elementId) {
    return canvas.querySelector(`[data-certificate-element="${CSS.escape(elementId)}"]`);
  }

  function elementActions(elementId) {
    return canvas.querySelector(`[data-certificate-actions-for="${CSS.escape(elementId)}"]`);
  }

  function renderElements() {
    canvas.querySelectorAll("[data-certificate-element], [data-certificate-actions-for]").forEach((node) => node.remove());

    design.elements.forEach((element, index) => {
      const node = document.createElement("div");
      const content = document.createElement("span");
      const actions = document.createElement("div");
      node.className = "certificate-text-element";
      node.dataset.certificateElement = element.id;
      node.setAttribute("role", "group");
      node.setAttribute("aria-label", `عنصر نص: ${element.text.slice(0, 40)}`);
      content.className = "certificate-text-content w-full";
      actions.className = "certificate-element-actions";
      actions.dataset.certificateActionsFor = element.id;
      actions.setAttribute("role", "toolbar");
      actions.setAttribute("aria-label", "إجراءات العنصر النصي");

      [
        ["lock", "fa-solid fa-lock-open", "قفل العنصر"],
        ["copy", "fa-regular fa-copy", "نسخ العنصر"],
        ["delete", "fa-regular fa-trash-can", "حذف العنصر"]
      ].forEach(([action, icon, label]) => {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "certificate-element-action";
        button.dataset.certificateElementAction = action;
        button.setAttribute("aria-label", label);
        button.title = label.replace(" العنصر", "");
        button.innerHTML = `<i class="${icon}" aria-hidden="true"></i>`;
        if (action === "delete") button.classList.add("is-danger");
        actions.appendChild(button);
      });

      actions.addEventListener("pointerdown", (event) => event.stopPropagation());
      actions.addEventListener("dblclick", (event) => event.stopPropagation());
      actions.querySelector('[data-certificate-element-action="lock"]').addEventListener("click", (event) => {
        event.stopPropagation();
        toggleElementLock(element.id);
      });
      actions.querySelector('[data-certificate-element-action="copy"]').addEventListener("click", (event) => {
        event.stopPropagation();
        selectElement(element.id);
        duplicateSelectedElement();
      });
      actions.querySelector('[data-certificate-element-action="delete"]').addEventListener("click", (event) => {
        event.stopPropagation();
        selectElement(element.id);
        deleteSelectedElement();
      });

      node.appendChild(content);

      ["nw", "ne", "se", "sw"].forEach((handleName) => {
        const handle = document.createElement("span");
        handle.className = "certificate-resize-handle";
        handle.dataset.handle = handleName;
        handle.setAttribute("aria-hidden", "true");
        node.appendChild(handle);
      });

      node.addEventListener("pointerdown", startElementPointerOperation);
      node.addEventListener("dblclick", startInlineEditing);
      canvas.append(node, actions);
      applyElementStyle(node, element, index);
    });
  }

  function renderLayers() {
    layersList.replaceChildren();
    layersCount.textContent = String(design.elements.length);

    [...design.elements].reverse().forEach((element) => {
      const button = document.createElement("button");
      const icon = document.createElement("i");
      const label = document.createElement("span");
      button.type = "button";
      button.className = "flex min-h-10 min-w-0 w-full items-center gap-2 rounded-xl border border-slate-200 px-3 text-right text-sm font-bold text-slate-600 hover:border-violet-200 hover:bg-violet-50 data-selected:border-violet-400 data-selected:bg-violet-50 data-selected:text-violet-800";
      button.toggleAttribute("data-selected", element.id === selectedElementId);
      icon.className = `fa-solid ${element.locked ? "fa-lock" : "fa-font"} w-4 shrink-0 text-center text-xs`;
      label.className = "min-w-0 flex-1 truncate";
      label.textContent = element.text.replace(/\s+/g, " ");
      button.append(icon, label);
      button.addEventListener("click", () => selectElement(element.id));
      layersList.appendChild(button);
    });

    if (!design.elements.length) {
      const empty = document.createElement("p");
      empty.className = "rounded-xl border border-dashed border-slate-200 p-3 text-center text-xs font-bold leading-5 text-slate-500";
      empty.textContent = "لا توجد عناصر نصية بعد.";
      layersList.appendChild(empty);
    }
  }

  function renderProperties() {
    const element = selectedElement();
    propertiesEmpty.classList.toggle("hidden", Boolean(element));
    propertiesEmpty.classList.toggle("grid", !element);
    propertiesForm.classList.toggle("hidden", !element);
    propertiesForm.classList.toggle("grid", Boolean(element));
    if (!element) return;

    textContentInput.value = element.text;
    fontFamilyInput.value = element.font_family;
    fontSizeInput.value = String(element.font_size);
    fontWeightInput.value = String(element.font_weight);
    textColorInput.value = element.color;
    textColorValue.textContent = element.color;
    rotationInput.value = String(element.rotation);
    rotationValue.textContent = `${Math.round(element.rotation)}°`;
    opacityInput.value = String(Math.round(element.opacity * 100));
    opacityValue.textContent = `${Math.round(element.opacity * 100)}%`;
    lockedInput.checked = element.locked;
    propertiesForm.querySelectorAll('[name="certificate_text_align"]').forEach((input) => {
      input.checked = input.value === element.text_align;
    });
  }

  function showSidebarPanel(panelName, { scrollOnMobile = false } = {}) {
    let activePanel = null;

    sidebarPanels.forEach((panel) => {
      const isActive = panel.dataset.certificateSidebarPanel === panelName;
      panel.classList.toggle("hidden", !isActive);
      if (isActive) activePanel = panel;
    });
    sidebarTabs.forEach((tab) => {
      const isActive = tab.dataset.certificateSidebarTab === panelName;
      tab.toggleAttribute("data-selected", isActive);
      tab.setAttribute("aria-pressed", String(isActive));
    });

    if (scrollOnMobile && activePanel && window.matchMedia("(max-width: 1023px)").matches) {
      activePanel.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }

  function renderTemplateSelection() {
    document.querySelectorAll("[data-certificate-template]").forEach((button) => {
      button.toggleAttribute("data-selected", button.dataset.certificateTemplate === templateKey);
    });
  }

  function renderEditor() {
    backgroundImage.src = backgroundSource();
    backgroundImage.classList.toggle("hidden", !backgroundImage.src);
    applyCanvasGeometry();
    renderElements();
    renderLayers();
    renderProperties();
    renderTemplateSelection();
  }

  function selectElement(elementId) {
    selectedElementId = design.elements.some((element) => element.id === elementId) ? elementId : null;
    canvas.querySelectorAll("[data-certificate-element]").forEach((node) => {
      node.classList.toggle("is-selected", node.dataset.certificateElement === selectedElementId);
    });
    canvas.querySelectorAll("[data-certificate-actions-for]").forEach((actions) => {
      actions.classList.toggle("is-visible", actions.dataset.certificateActionsFor === selectedElementId);
    });
    renderLayers();
    renderProperties();
    if (selectedElementId) showSidebarPanel("properties", { scrollOnMobile: true });
  }

  function startElementPointerOperation(event) {
    const node = event.currentTarget;
    const element = design.elements.find((item) => item.id === node.dataset.certificateElement);
    if (!element) return;

    selectElement(element.id);
    if (element.locked || node.classList.contains("is-editing")) return;

    event.preventDefault();
    event.stopPropagation();
    const handle = event.target.closest("[data-handle]")?.dataset.handle || null;
    pointerOperation = {
      mode: handle ? "resize" : "drag",
      handle,
      element,
      node,
      startX: event.clientX,
      startY: event.clientY,
      original: clone(element)
    };
    try {
      node.setPointerCapture?.(event.pointerId);
    } catch (_) {
      // Synthetic pointer events and interrupted gestures may not own an active pointer.
    }
  }

  function startInlineEditing(event) {
    const node = event.currentTarget;
    const element = design.elements.find((item) => item.id === node.dataset.certificateElement);
    if (!element || element.locked) return;

    event.preventDefault();
    event.stopPropagation();
    selectElement(element.id);
    const content = node.querySelector(".certificate-text-content");
    node.classList.add("is-editing");
    content.contentEditable = "true";
    content.focus();

    const selection = window.getSelection();
    const range = document.createRange();
    range.selectNodeContents(content);
    selection.removeAllRanges();
    selection.addRange(range);

    const sync = () => {
      element.text = content.textContent.trim() || "نص جديد";
      textContentInput.value = element.text;
      markChanged();
    };
    const finish = () => {
      sync();
      content.contentEditable = "false";
      node.classList.remove("is-editing");
      renderLayers();
      content.removeEventListener("input", sync);
    };
    content.addEventListener("input", sync);
    content.addEventListener("blur", finish, { once: true });
  }

  function clamp(value, minimum, maximum) {
    return Math.min(Math.max(value, minimum), Math.max(minimum, maximum));
  }

  function moveElement(operation, dx, dy) {
    const { element, original, node } = operation;
    let x = clamp(original.x + dx, 0, design.width - element.width);
    let y = clamp(original.y + dy, 0, design.height - element.height);
    const centeredX = Math.abs(x + element.width / 2 - design.width / 2) <= 8;
    const centeredY = Math.abs(y + element.height / 2 - design.height / 2) <= 8;

    if (centeredX) x = (design.width - element.width) / 2;
    if (centeredY) y = (design.height - element.height) / 2;
    guideX.classList.toggle("is-visible", centeredX);
    guideY.classList.toggle("is-visible", centeredY);
    element.x = Math.round(x * 10) / 10;
    element.y = Math.round(y * 10) / 10;
    node.style.left = `${element.x}px`;
    node.style.top = `${element.y}px`;
    const actions = elementActions(element.id);
    if (actions) applyElementActionsStyle(actions, element);
  }

  function resizeElement(operation, dx, dy) {
    const { element, original, node, handle } = operation;
    let x = original.x;
    let y = original.y;
    let width = original.width;
    let height = original.height;

    if (handle.includes("e")) width = original.width + dx;
    if (handle.includes("s")) height = original.height + dy;
    if (handle.includes("w")) {
      x = original.x + dx;
      width = original.width - dx;
    }
    if (handle.includes("n")) {
      y = original.y + dy;
      height = original.height - dy;
    }

    if (width < minimumElementWidth) {
      if (handle.includes("w")) x -= minimumElementWidth - width;
      width = minimumElementWidth;
    }
    if (height < minimumElementHeight) {
      if (handle.includes("n")) y -= minimumElementHeight - height;
      height = minimumElementHeight;
    }

    x = clamp(x, 0, design.width - minimumElementWidth);
    y = clamp(y, 0, design.height - minimumElementHeight);
    width = clamp(width, minimumElementWidth, design.width - x);
    height = clamp(height, minimumElementHeight, design.height - y);
    Object.assign(element, { x, y, width, height });
    applyElementStyle(node, element);
  }

  document.addEventListener("pointermove", (event) => {
    if (!pointerOperation) return;

    const dx = (event.clientX - pointerOperation.startX) / zoom;
    const dy = (event.clientY - pointerOperation.startY) / zoom;
    if (pointerOperation.mode === "drag") moveElement(pointerOperation, dx, dy);
    else resizeElement(pointerOperation, dx, dy);
  });

  document.addEventListener("pointerup", () => {
    if (!pointerOperation) return;

    pointerOperation = null;
    guideX.classList.remove("is-visible");
    guideY.classList.remove("is-visible");
    renderLayers();
    markChanged({ immediateHistory: true });
  });

  function scaleDesign(width, height) {
    const scaleX = width / design.width;
    const scaleY = height / design.height;
    const fontScale = Math.min(scaleX, scaleY);
    design.elements.forEach((element) => {
      element.x *= scaleX;
      element.y *= scaleY;
      element.width *= scaleX;
      element.height *= scaleY;
      element.font_size = clamp(element.font_size * fontScale, 8, 240);
    });
    design.width = Math.round(width);
    design.height = Math.round(height);
  }

  function selectTemplate(key) {
    const template = templates.get(key);
    if (!template || key === templateKey) return;

    scaleDesign(template.width, template.height);
    templateKey = key;
    selectedElementId = null;
    renderEditor();
    fitCanvas();
    markChanged({ immediateHistory: true });
  }

  function fileToDataUrl(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = () => reject(new Error("تعذر قراءة الصورة."));
      reader.readAsDataURL(file);
    });
  }

  function imageDimensions(source) {
    return new Promise((resolve, reject) => {
      const image = new Image();
      image.onload = () => resolve({ width: image.naturalWidth, height: image.naturalHeight });
      image.onerror = () => reject(new Error("تعذر فتح الصورة."));
      image.src = source;
    });
  }

  async function useUploadedBackground(file) {
    if (!allowedImageTypes.includes(file.type)) {
      backgroundHint.textContent = "اختر صورة PNG أو JPG أو WebP فقط.";
      backgroundHint.className = "mt-2 text-xs font-bold leading-5 text-red-700";
      return;
    }
    if (file.size > maximumBackgroundSize) {
      backgroundHint.textContent = "حجم الصورة أكبر من 4 ميجابايت.";
      backgroundHint.className = "mt-2 text-xs font-bold leading-5 text-red-700";
      return;
    }

    try {
      const dataUrl = await fileToDataUrl(file);
      const dimensions = await imageDimensions(dataUrl);
      if (dimensions.width < 600 || dimensions.height < 400 || dimensions.width > 4000 || dimensions.height > 4000) {
        throw new Error("أبعاد الصورة يجب أن تكون بين 600×400 و4000×4000 بكسل.");
      }

      scaleDesign(dimensions.width, dimensions.height);
      templateKey = "custom";
      backgroundDataUrl = dataUrl;
      currentBackgroundFile = file;
      selectedElementId = null;
      backgroundHint.textContent = `${file.name} — ${dimensions.width}×${dimensions.height}`;
      backgroundHint.className = "mt-2 text-xs font-bold leading-5 text-emerald-700";
      renderEditor();
      fitCanvas();
      markChanged({ immediateHistory: true });
    } catch (error) {
      backgroundHint.textContent = error.message;
      backgroundHint.className = "mt-2 text-xs font-bold leading-5 text-red-700";
    }
  }

  function addText(element = null) {
    const created = element || defaultText({
      x: Math.max(0, (design.width - 600) / 2),
      y: Math.max(0, (design.height - 70) / 2)
    });
    design.elements.push(created);
    selectedElementId = created.id;
    renderElements();
    renderLayers();
    renderProperties();
    showSidebarPanel("properties", { scrollOnMobile: true });
    markChanged({ immediateHistory: true });
    window.setTimeout(() => textContentInput.select(), 40);
  }

  function deleteSelectedElement() {
    if (!selectedElementId) return;
    if (!confirm("هل تريد حذف هذا العنصر النصي؟ لا يمكن التراجع عن الحذف بعد حفظ الشهادة.")) return;

    design.elements = design.elements.filter((element) => element.id !== selectedElementId);
    selectedElementId = null;
    renderElements();
    renderLayers();
    renderProperties();
    markChanged({ immediateHistory: true });
  }

  function duplicateSelectedElement() {
    const element = selectedElement();
    if (!element || design.elements.length >= 50) return;

    addText({
      ...clone(element),
      id: uniqueId(),
      x: clamp(element.x + 22, 0, design.width - element.width),
      y: clamp(element.y + 22, 0, design.height - element.height),
      locked: false
    });
  }

  function toggleElementLock(elementId = selectedElementId) {
    const element = design.elements.find((item) => item.id === elementId);
    if (!element) return;

    selectedElementId = element.id;
    element.locked = !element.locked;
    const node = elementNode(element.id);
    if (node) applyElementStyle(node, element);
    renderLayers();
    renderProperties();
    markChanged({ immediateHistory: true });
  }

  function updateSelectedElement(event) {
    const element = selectedElement();
    if (!element) return;

    if (event.target === textContentInput) element.text = textContentInput.value.trimStart() || "نص جديد";
    if (event.target === fontFamilyInput) element.font_family = fontFamilyInput.value;
    if (event.target === fontSizeInput) element.font_size = clamp(numeric(fontSizeInput.value, element.font_size), 8, 240);
    if (event.target === fontWeightInput) element.font_weight = numeric(fontWeightInput.value, 700);
    if (event.target === textColorInput) element.color = textColorInput.value;
    if (event.target === rotationInput) element.rotation = numeric(rotationInput.value, 0);
    if (event.target === opacityInput) element.opacity = numeric(opacityInput.value, 100) / 100;
    if (event.target === lockedInput) element.locked = lockedInput.checked;
    if (event.target.name === "certificate_text_align") element.text_align = event.target.value;

    const node = elementNode(element.id);
    if (node) applyElementStyle(node, element);
    textColorValue.textContent = element.color;
    rotationValue.textContent = `${Math.round(element.rotation)}°`;
    opacityValue.textContent = `${Math.round(element.opacity * 100)}%`;
    renderLayers();
    markChanged();
  }

  function setZoom(nextZoom) {
    zoom = clamp(nextZoom, 0.25, 1.25);
    applyCanvasGeometry();
  }

  function fitCanvas() {
    const availableWidth = Math.max(280, viewport.clientWidth - 56);
    const availableHeight = Math.max(300, viewport.clientHeight - 56);
    setZoom(Math.min(1, availableWidth / design.width, availableHeight / design.height));
  }

  async function toggleFullscreen() {
    if (document.fullscreenElement) {
      await document.exitFullscreen();
      return;
    }

    await editorShell.requestFullscreen();
  }

  function validateDesign() {
    if (!backgroundSource()) return "اختر قالباً أو ارفع صورة للقالب.";
    if (templateKey === "custom" && !backgroundDataUrl && !currentBackgroundFile && !currentCertificate?.background_url) {
      return "ارفع صورة القالب المخصص.";
    }
    if (!design.elements.length) return "أضف مربع نص واحداً على الأقل.";

    return null;
  }

  function appendNested(formData, prefix, value) {
    if (Array.isArray(value)) {
      value.forEach((item, index) => appendNested(formData, `${prefix}[${index}]`, item));
      return;
    }
    if (value && typeof value === "object") {
      Object.entries(value).forEach(([key, item]) => appendNested(formData, `${prefix}[${key}]`, item));
      return;
    }

    formData.append(prefix, typeof value === "boolean" ? (value ? "1" : "0") : String(value ?? ""));
  }

  function dataUrlToBlob(dataUrl) {
    const [metadata, encoded] = dataUrl.split(",");
    const mimeType = metadata.match(/data:([^;]+)/)?.[1] || "image/png";
    const bytes = atob(encoded);
    const output = new Uint8Array(bytes.length);
    for (let index = 0; index < bytes.length; index += 1) output[index] = bytes.charCodeAt(index);

    return new Blob([output], { type: mimeType });
  }

  async function saveCertificate() {
    if (certificateLimitReached()) {
      setSaveStatus(certificateLimitMessage(), "error");
      return;
    }

    const title = saveTitleInput.value.trim();
    if (!title) {
      setSaveStatus("اكتب اسماً للتصميم.", "error");
      saveTitleInput.focus();
      return;
    }

    const validationMessage = validateDesign();
    if (validationMessage) {
      setSaveStatus(validationMessage, "error");
      return;
    }

    confirmSaveButton.disabled = true;
    setSaveStatus("جارٍ حفظ الشهادة…");
    const oldDraftKey = draftKey();
    const formData = new FormData();
    formData.append("title", title);
    formData.append("template_key", templateKey);
    appendNested(formData, "design", design);

    if (templateKey === "custom" && currentBackgroundFile) {
      formData.append("background", currentBackgroundFile);
    } else if (templateKey === "custom" && backgroundDataUrl && !currentCertificate?.background_url) {
      const blob = dataUrlToBlob(backgroundDataUrl);
      formData.append("background", blob, `certificate-background.${blob.type.split("/")[1] || "png"}`);
    }

    let url = config.routes.store;
    if (currentCertificate) {
      url = `${config.routes.updateBase}/${currentCertificate.id}`;
      formData.append("_method", "PATCH");
      formData.append("version", currentCertificate.version);
    }

    const isCreatingCertificate = !currentCertificate;

    try {
      const response = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: { Accept: "application/json", "X-CSRF-TOKEN": config.csrfToken },
        body: formData
      });
      const body = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(Object.values(body.errors || {}).flat()[0] || body.message || "تعذر حفظ الشهادة.");

      currentCertificate = body.data;
      if (isCreatingCertificate) savedCertificatesCount += 1;
      currentBackgroundFile = null;
      backgroundDataUrl = null;
      if (templateKey === "custom" && currentCertificate.background_url) backgroundImage.src = currentCertificate.background_url;
      localStorage.removeItem(oldDraftKey);
      localStorage.removeItem(draftKey());
      saveTitleInput.value = currentCertificate.title;
      workingTitle.textContent = currentCertificate.title;
      setSaveStatus("تم الحفظ في حسابك.", "success");
      setSaveIndicator("تم حفظ جميع التغييرات", "success");
      window.setTimeout(() => saveDialog.close(), 700);
    } catch (error) {
      setSaveStatus(error.message, "error");
    } finally {
      confirmSaveButton.disabled = false;
    }
  }

  async function openSaveFlow() {
    const validationMessage = validateDesign();
    if (validationMessage) {
      setSaveIndicator(validationMessage, "error");
      return;
    }

    if (!config.authenticated) {
      persistDraft(true);
      guestDialog.showModal();
      return;
    }
    if (!config.verified) {
      persistDraft(true);
      window.location.href = config.routes.verification;
      return;
    }

    if (certificateLimitReached()) {
      setSaveIndicator(certificateLimitMessage(), "error");
      return;
    }

    saveTitleInput.value = currentCertificate?.title || saveTitleInput.value || "شهادة جديدة";
    setSaveStatus();
    saveDialog.showModal();
    window.setTimeout(() => saveTitleInput.select(), 40);
  }

  function loadImage(source) {
    return new Promise((resolve, reject) => {
      const image = new Image();
      image.onload = () => resolve(image);
      image.onerror = () => reject(new Error("تعذر تحميل قالب الشهادة."));
      image.src = source;
    });
  }

  function wrappedLines(context, text, maximumWidth) {
    return text.split("\n").flatMap((paragraph) => {
      const words = paragraph.split(/\s+/).filter(Boolean);
      if (!words.length) return [""];
      const lines = [];
      let line = words.shift();
      words.forEach((word) => {
        const candidate = `${line} ${word}`;
        if (context.measureText(candidate).width <= maximumWidth) line = candidate;
        else {
          lines.push(line);
          line = word;
        }
      });
      lines.push(line);

      return lines;
    });
  }

  async function renderOutputCanvas() {
    const validationMessage = validateDesign();
    if (validationMessage) throw new Error(validationMessage);
    await document.fonts?.ready;
    const output = document.createElement("canvas");
    output.width = design.width;
    output.height = design.height;
    const context = output.getContext("2d");
    const image = await loadImage(backgroundSource());
    context.drawImage(image, 0, 0, output.width, output.height);

    design.elements.forEach((element) => {
      context.save();
      context.translate(element.x + element.width / 2, element.y + element.height / 2);
      context.rotate(element.rotation * Math.PI / 180);
      context.globalAlpha = element.opacity;
      context.beginPath();
      context.rect(-element.width / 2, -element.height / 2, element.width, element.height);
      context.clip();
      context.font = `${element.font_weight} ${element.font_size}px "${element.font_family}"`;
      context.fillStyle = element.color;
      context.textAlign = element.text_align;
      context.textBaseline = "top";
      context.direction = element.direction;
      const lineHeight = element.font_size * 1.25;
      const lines = wrappedLines(context, element.text, Math.max(20, element.width - 16));
      const startY = -element.height / 2 + Math.max(4, (element.height - lines.length * lineHeight) / 2);
      const x = element.text_align === "right"
        ? element.width / 2 - 8
        : element.text_align === "left"
          ? -element.width / 2 + 8
          : 0;
      lines.forEach((line, index) => context.fillText(line, x, startY + index * lineHeight));
      context.restore();
    });

    return output;
  }

  function canvasBlob(output) {
    return new Promise((resolve) => output.toBlob(resolve, "image/png", 1));
  }

  async function outputBlob() {
    const output = await renderOutputCanvas();

    return canvasBlob(output);
  }

  function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  function trackGeneration() {
    fetch(config.routes.metrics, {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": config.csrfToken
      },
      body: JSON.stringify({ event: "certificate_generate" })
    }).catch(() => {});
  }

  async function openPreview() {
    try {
      setSaveIndicator("نجهّز المعاينة النهائية…");
      const blob = await outputBlob();
      if (!blob) throw new Error("تعذر إنشاء صورة الشهادة.");
      if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
      previewObjectUrl = URL.createObjectURL(blob);
      previewImage.src = previewObjectUrl;
      previewDialog.showModal();
      setSaveIndicator("المعاينة جاهزة");
    } catch (error) {
      setSaveIndicator(error.message, "error");
    }
  }

  async function downloadPng() {
    try {
      const blob = await outputBlob();
      if (!blob) throw new Error("تعذر إنشاء صورة الشهادة.");
      downloadBlob(blob, "muallem-certificate.png");
      trackGeneration();
      setSaveIndicator("تم تنزيل الشهادة", "success");
    } catch (error) {
      setSaveIndicator(error.message, "error");
    }
  }

  async function printCertificate() {
    const printWindow = window.open("", "_blank");
    if (!printWindow) {
      setSaveIndicator("اسمح بالنوافذ المنبثقة حتى تتمكن من الطباعة.", "error");
      return;
    }

    try {
      printWindow.opener = null;
      const output = await renderOutputCanvas();
      const dataUrl = output.toDataURL("image/png", 1);
      printWindow.document.open();
      printWindow.document.write(`<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>طباعة الشهادة</title><style>@page{size:A4 landscape;margin:0}html,body{margin:0;width:100%;height:100%;display:grid;place-items:center;background:#fff}img{display:block;max-width:100%;max-height:100%;object-fit:contain}@media print{img{width:100%;height:100%}}</style></head><body><img src="${dataUrl}" alt="الشهادة"></body></html>`);
      printWindow.document.close();
      printWindow.addEventListener("load", () => {
        printWindow.focus();
        printWindow.print();
      }, { once: true });
      trackGeneration();
    } catch (error) {
      printWindow.close();
      setSaveIndicator(error.message, "error");
    }
  }

  function isTypingTarget(target) {
    return target.matches("input, textarea, select, [contenteditable='true']");
  }

  function nudgeSelectedElement(event) {
    const element = selectedElement();
    if (!element || element.locked) return false;
    const amount = event.shiftKey ? 10 : 1;
    if (event.key === "ArrowRight") element.x = clamp(element.x + amount, 0, design.width - element.width);
    else if (event.key === "ArrowLeft") element.x = clamp(element.x - amount, 0, design.width - element.width);
    else if (event.key === "ArrowDown") element.y = clamp(element.y + amount, 0, design.height - element.height);
    else if (event.key === "ArrowUp") element.y = clamp(element.y - amount, 0, design.height - element.height);
    else return false;

    const node = elementNode(element.id);
    if (node) applyElementStyle(node, element);
    markChanged();

    return true;
  }

  document.querySelectorAll("[data-certificate-template]").forEach((button) => {
    button.addEventListener("click", () => selectTemplate(button.dataset.certificateTemplate));
  });
  sidebarTabs.forEach((tab) => {
    if (tab.dataset.certificateSidebarTab === "properties") return;
    tab.addEventListener("click", () => showSidebarPanel(tab.dataset.certificateSidebarTab, { scrollOnMobile: true }));
  });
  backgroundInput.addEventListener("change", () => {
    const file = backgroundInput.files?.[0];
    if (file) useUploadedBackground(file);
  });
  document.getElementById("addCertificateTextBtn").addEventListener("click", () => addText());
  propertiesForm.addEventListener("input", updateSelectedElement);
  propertiesForm.addEventListener("change", updateSelectedElement);
  document.getElementById("deleteCertificateElementBtn").addEventListener("click", deleteSelectedElement);
  document.getElementById("duplicateCertificateElementBtn").addEventListener("click", duplicateSelectedElement);
  document.getElementById("certificateZoomOutBtn").addEventListener("click", () => setZoom(zoom - 0.1));
  document.getElementById("certificateZoomInBtn").addEventListener("click", () => setZoom(zoom + 0.1));
  document.getElementById("certificateFitBtn").addEventListener("click", fitCanvas);
  fullscreenButton.addEventListener("click", toggleFullscreen);
  document.addEventListener("fullscreenchange", () => {
    const isFullscreen = document.fullscreenElement === editorShell;
    fullscreenButton.setAttribute("aria-label", isFullscreen ? "إنهاء ملء الشاشة" : "ملء الشاشة");
    fullscreenButton.querySelector("i").className = `fa-solid ${isFullscreen ? "fa-compress" : "fa-expand"}`;
    window.requestAnimationFrame(fitCanvas);
  });
  zoomRange.addEventListener("input", () => setZoom(Number(zoomRange.value) / 100));
  undoButton.addEventListener("click", () => restoreHistory(historyIndex - 1));
  redoButton.addEventListener("click", () => restoreHistory(historyIndex + 1));
  document.getElementById("saveCertificateBtn").addEventListener("click", openSaveFlow);
  document.getElementById("guestCertificateSavePromptBtn")?.addEventListener("click", openSaveFlow);
  document.getElementById("certificatePreviewBtn").addEventListener("click", openPreview);
  document.getElementById("printCertificateBtn").addEventListener("click", openPreview);
  document.getElementById("downloadCertificatePngBtn").addEventListener("click", downloadPng);
  document.getElementById("printCertificatePreviewBtn").addEventListener("click", printCertificate);
  saveForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    await saveCertificate();
  });
  document.querySelectorAll("[data-close-certificate-save]").forEach((button) => button.addEventListener("click", () => saveDialog.close()));
  document.querySelectorAll("[data-close-certificate-guest]").forEach((button) => button.addEventListener("click", () => guestDialog.close()));
  document.querySelectorAll("[data-close-certificate-preview]").forEach((button) => button.addEventListener("click", () => previewDialog.close()));
  document.getElementById("certificateRegisterLink")?.addEventListener("click", () => persistDraft(true));
  document.getElementById("certificateLoginLink")?.addEventListener("click", () => persistDraft(true));
  document.getElementById("createNewCertificateLink")?.addEventListener("click", () => localStorage.removeItem(draftKey()));
  canvas.addEventListener("pointerdown", (event) => {
    if (event.target === canvas || event.target === backgroundImage) selectElement(null);
  });

  document.addEventListener("keydown", (event) => {
    if (isTypingTarget(event.target)) return;
    const usesCommandKey = event.ctrlKey || event.metaKey;
    if (usesCommandKey && event.key.toLowerCase() === "z") {
      event.preventDefault();
      restoreHistory(historyIndex + (event.shiftKey ? 1 : -1));
      return;
    }
    if (usesCommandKey && event.key.toLowerCase() === "y") {
      event.preventDefault();
      restoreHistory(historyIndex + 1);
      return;
    }
    if (usesCommandKey && event.key.toLowerCase() === "d") {
      event.preventDefault();
      duplicateSelectedElement();
      return;
    }
    if (event.key === "Delete" || event.key === "Backspace") {
      if (selectedElementId) {
        event.preventDefault();
        deleteSelectedElement();
      }
      return;
    }
    if (event.key === "Escape") {
      selectElement(null);
      return;
    }
    if (nudgeSelectedElement(event)) event.preventDefault();
  });

  async function initialize() {
    const certificateDraft = currentCertificate ? readDraft(draftKey(currentCertificate)) : readDraft(`${baseDraftKey}:new`);
    const serverUpdatedAt = currentCertificate ? Date.parse(currentCertificate.updated_at) : 0;
    const useDraft = certificateDraft && (!currentCertificate || certificateDraft.updatedAt > serverUpdatedAt);
    const initial = useDraft ? certificateDraft : currentCertificate;

    if (initial) {
      templateKey = initial.templateKey || initial.template_key || "b6";
      design = normalizeDesign(initial.design);
      backgroundDataUrl = initial.backgroundDataUrl || null;
      saveTitleInput.value = initial.title || "شهادة جديدة";
      workingTitle.textContent = initial.title || "شهادة جديدة";
    }

    renderEditor();
    showSidebarPanel("templates");
    window.requestAnimationFrame(fitCanvas);
    pushHistory();
    if (currentCertificate && !useDraft) setSaveIndicator("تم تحميل النسخة المحفوظة", "success");
    else if (useDraft) setSaveIndicator("تم استرجاع مسودتك الأخيرة", "success");

    if (certificateDraft?.pendingSave && config.authenticated && config.verified && !currentCertificate) {
      if (certificateLimitReached()) {
        setSaveIndicator(certificateLimitMessage(), "error");
      } else {
        saveTitleInput.value = certificateDraft.title || "شهادة جديدة";
        saveDialog.showModal();
      }
    }
  }

  initialize();
}
