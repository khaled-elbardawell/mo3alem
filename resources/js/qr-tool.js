const qrForm = document.getElementById("qrForm");

if (qrForm) {
  const svgNamespace = "http://www.w3.org/2000/svg";
  const configElement = document.getElementById("qrAppConfig");
  const config = JSON.parse(configElement?.dataset.config || "{}");
  const previewImage = document.getElementById("qrPreviewImage");
  const previewLoader = document.getElementById("qrPreviewLoader");
  const previewEmptyState = document.getElementById("qrPreviewEmptyState");
  const previewWaitingBadge = document.getElementById("qrPreviewWaitingBadge");
  const previewWaitingLabel = document.getElementById("qrPreviewWaitingLabel");
  const previewReadyBadge = document.getElementById("qrPreviewReadyBadge");
  const downloadActions = document.getElementById("qrDownloadActions");
  const generateButton = document.getElementById("generateQrBtn");
  const saveButton = document.getElementById("saveQrBtn");
  const saveButtonLabel = document.getElementById("saveQrButtonLabel");
  const workingTitle = document.getElementById("qrWorkingTitle");
  const renameButton = document.getElementById("renameQrTitleBtn");
  const formStatus = document.getElementById("qrFormStatus");
  const contrastWarning = document.getElementById("qrContrastWarning");
  const logoInput = document.getElementById("qrLogo");
  const logoName = document.getElementById("qrLogoName");
  const centerTextInput = document.getElementById("qrCenterText");
  const guestDialog = document.getElementById("guestQrDialog");
  const saveDialog = document.getElementById("saveQrDialog");
  const exportDialog = document.getElementById("qrExportDialog");
  const saveForm = document.getElementById("saveQrForm");
  const saveTitleInput = document.getElementById("qrSaveTitle");
  const saveStatus = document.getElementById("qrSaveStatus");
  const saveDialogTitle = document.getElementById("saveQrDialogTitle");
  const confirmSaveButton = document.getElementById("confirmSaveQrBtn");
  const dynamicSettings = document.getElementById("qrDynamicSettings");
  const dynamicSavedPanel = document.getElementById("qrDynamicSavedPanel");
  const publicUrlInput = document.getElementById("qrPublicUrl");
  const activeInput = document.getElementById("qrIsActive");
  const expiresAtInput = document.getElementById("qrExpiresAt");
  const modeLockMessage = document.getElementById("qrModeLockMessage");
  const contentTypeGrid = document.getElementById("qrContentTypeGrid");
  const dynamicLockedState = document.getElementById("qrDynamicLockedState");
  const guestCloudSaveCard = document.getElementById("guestCloudSaveCard");
  const ribbonActions = document.getElementById("qrRibbonActions");
  const primaryActionButtons = [...document.querySelectorAll("[data-qr-primary-action]")];
  const primaryActionLabels = [...document.querySelectorAll("[data-qr-primary-label]")];
  const primaryActionIcons = [...document.querySelectorAll("[data-qr-primary-icon]")];
  const draftKey = "muallem-qr-draft-v1";
  const templateLayouts = {
    "template-1": { width: 1968, height: 1968, qrX: 460, qrY: 700, qrSize: 1060 },
    "template-2": { width: 1968, height: 1968, qrX: 450, qrY: 430, qrSize: 1050 },
    "template-3": { width: 1968, height: 1968, qrX: 440, qrY: 480, qrSize: 1000 },
    "template-4": { width: 1968, height: 1968, qrX: 430, qrY: 440, qrSize: 1050 },
    "template-5": { width: 1968, height: 1968, qrX: 420, qrY: 420, qrSize: 1100 },
    "template-6": { width: 1968, height: 1968, qrX: 450, qrY: 450, qrSize: 1000 },
    "template-7": { width: 1968, height: 1968, qrX: 450, qrY: 380, qrSize: 1050 },
    "template-8": { width: 1968, height: 1968, qrX: 500, qrY: 150, qrSize: 930 },
    "template-9": { width: 1968, height: 1968, qrX: 480, qrY: 450, qrSize: 1000 },
    "template-10": { width: 1968, height: 1968, qrX: 450, qrY: 350, qrSize: 1050 },
    "template-11": { width: 1968, height: 1968, qrX: 585, qrY: 650, qrSize: 900 }
  };
  const templateDataUrls = new Map();
  const maximumSavedQrCodes = Number(config.limits?.savedQrCodes) || 5;
  let baseQrSvg = "";
  let previewUrl = "";
  let logoDataUrl = null;
  let logoFile = null;
  let currentQrCode = config.savedQrCode || null;
  let savedQrCodesCount = Number(config.usage?.savedQrCodes) || 0;
  let renderTimer = null;
  let renderSequence = 0;
  let pendingDynamicExport = false;
  const sidebarPanels = [...document.querySelectorAll("[data-qr-sidebar-panel]")];
  const sidebarTabs = [...document.querySelectorAll("[data-qr-sidebar-tab]")];
  const panelDetails = {
    content: { kicker: "الخطوة الأساسية", title: "محتوى الرمز" },
    appearance: { kicker: "خصّص التصميم", title: "مظهر الرمز" },
    center: { kicker: "أضف هويتك", title: "وسط الرمز" },
    frames: { kicker: "اختر قالبًا", title: "إطار الرمز" }
  };

  const selectedValue = (name) => qrForm.querySelector(`[name="${name}"]:checked`)?.value;
  const currentMode = () => selectedValue("mode") || "static";

  function setStatus(message = "", tone = "muted") {
    formStatus.textContent = message;
    formStatus.className = "min-h-5 text-sm font-bold";
    formStatus.classList.add(tone === "error" ? "text-red-700" : tone === "success" ? "text-emerald-700" : "text-slate-500");
  }

  function setSaveStatus(message = "", tone = "muted") {
    saveStatus.textContent = message;
    saveStatus.className = "mt-2 min-h-5 text-xs font-bold";
    saveStatus.classList.add(tone === "error" ? "text-red-700" : tone === "success" ? "text-emerald-700" : "text-slate-500");
  }

  function syncQrTitleUi() {
    const isSavedQrCode = Boolean(currentQrCode);
    const title = saveTitleInput.value.trim() || currentQrCode?.title || "رمز QR جديد";

    workingTitle.textContent = title;
    renameButton.classList.toggle("hidden", !isSavedQrCode);
    renameButton.classList.toggle("grid", isSavedQrCode);
    saveButtonLabel.textContent = isSavedQrCode ? "تعديل" : "حفظ";
    saveDialogTitle.textContent = isSavedQrCode ? "تعديل عنوان رمز QR" : "حفظ رمز QR";
    confirmSaveButton.textContent = isSavedQrCode ? "حفظ التعديلات" : "حفظ في حسابي";
  }

  function qrCodeLimitMessage() {
    return `وصلت إلى الحد الأقصى وهو ${maximumSavedQrCodes} رموز QR محفوظة. احذف رمزًا قبل حفظ رمز جديد.`;
  }

  function qrCodeLimitReached() {
    return !currentQrCode && savedQrCodesCount >= maximumSavedQrCodes;
  }

  function activateSidebarPanel(panelName, { scroll = false } = {}) {
    const activePanel = sidebarPanels.find((panel) => panel.dataset.qrSidebarPanel === panelName);
    if (!activePanel) return;

    sidebarPanels.forEach((panel) => {
      const isActive = panel === activePanel;
      panel.classList.toggle("hidden", !isActive);
      panel.classList.toggle("grid", isActive);
    });
    sidebarTabs.forEach((tab) => {
      const isActive = tab.dataset.qrSidebarTab === panelName;
      tab.toggleAttribute("data-selected", isActive);
      tab.setAttribute("aria-selected", String(isActive));
    });

    const details = panelDetails[panelName];
    if (details) {
      document.getElementById("qrSettingsKicker").textContent = details.kicker;
      document.getElementById("qrSettingsTitle").textContent = details.title;
    }

    if (scroll && window.matchMedia("(max-width: 1023px)").matches) {
      document.querySelector('[aria-labelledby="qrSettingsTitle"]')?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }

  function currentPayload() {
    const contentType = selectedValue("content_type");

    if (contentType === "text") {
      return { text: document.getElementById("qrText").value.trim() };
    }

    if (contentType === "wifi") {
      return {
        ssid: document.getElementById("qrWifiSsid").value.trim(),
        password: document.getElementById("qrWifiPassword").value,
        encryption: document.getElementById("qrWifiEncryption").value,
        hidden: document.getElementById("qrWifiHidden").checked
      };
    }

    return { url: document.getElementById("qrUrl").value.trim() };
  }

  function currentDesign() {
    return {
      style: selectedValue("qr_style"),
      foreground_color: document.getElementById("qrForegroundColor").value,
      eye_color: document.getElementById("qrEyeColor").value,
      background_color: document.getElementById("qrBackgroundColor").value,
      frame: selectedValue("qr_frame"),
      center_type: selectedValue("center_type"),
      center_text: centerTextInput.value.trim() || null
    };
  }

  function currentState() {
    return {
      mode: currentMode(),
      qr_code_id: currentQrCode?.id || null,
      content_type: selectedValue("content_type"),
      payload: currentPayload(),
      design: currentDesign(),
      is_active: activeInput?.checked ?? true,
      expires_at: expiresAtInput?.value || null
    };
  }

  function dynamicChangesNeedSave(state = currentState()) {
    if (state.mode !== "dynamic") return false;

    const savedExpiry = currentQrCode?.expires_at ? String(currentQrCode.expires_at).slice(0, 16) : "";

    return !currentQrCode
      || currentQrCode.mode !== "dynamic"
      || currentQrCode.payload?.url !== state.payload.url
      || Boolean(currentQrCode.is_active) !== state.is_active
      || savedExpiry !== (state.expires_at || "");
  }

  function updatePrimaryAction() {
    const dynamic = currentMode() === "dynamic";
    const requiresAccount = dynamic && !config.authenticated;
    const requiresVerification = dynamic && config.authenticated && !config.verified;
    const requiresSave = dynamic && config.verified && dynamicChangesNeedSave();
    const action = requiresAccount
      ? { full: "سجّل الدخول لإنشاء الرمز", short: "دخول للتفعيل", icon: "fa-right-to-bracket" }
      : requiresVerification
        ? { full: "فعّل بريدك للمتابعة", short: "تفعيل البريد", icon: "fa-envelope-circle-check" }
        : requiresSave
          ? { full: "احفظ وفعّل الرمز", short: "حفظ وتفعيل", icon: "fa-floppy-disk" }
          : { full: "تنزيل ومشاركة", short: "تنزيل", icon: "fa-download" };

    primaryActionLabels.forEach((label) => {
      label.textContent = action[label.dataset.qrPrimaryLabel] || action.full;
    });
    primaryActionIcons.forEach((icon) => {
      icon.classList.remove("fa-download", "fa-right-to-bracket", "fa-envelope-circle-check", "fa-floppy-disk");
      icon.classList.add(action.icon);
    });
    primaryActionButtons.forEach((button) => {
      button.setAttribute("aria-label", action.full);
    });

    const hideSaveButton = requiresAccount || requiresVerification;
    saveButton.classList.toggle("hidden", hideSaveButton);
    saveButton.classList.toggle("inline-flex", !hideSaveButton);
    ribbonActions?.classList.toggle("grid-cols-1", hideSaveButton);
    ribbonActions?.classList.toggle("grid-cols-2", !hideSaveButton);
    guestCloudSaveCard?.classList.toggle("lg:block", !dynamic);
  }

  function hasPreviewContent(state) {
    if (state.content_type === "text") return Boolean(state.payload.text);
    if (state.content_type === "wifi") return Boolean(state.payload.ssid);

    return Boolean(state.payload.url);
  }

  function setPreviewReady(isReady) {
    previewWaitingBadge.classList.toggle("hidden", isReady);
    previewWaitingBadge.classList.toggle("inline-flex", !isReady);
    previewReadyBadge.classList.toggle("hidden", !isReady);
    previewReadyBadge.classList.toggle("inline-flex", isReady);
  }

  function showEmptyPreview() {
    renderSequence += 1;
    baseQrSvg = "";
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = "";
    previewImage.removeAttribute("src");
    previewImage.classList.add("hidden");
    previewLoader.classList.add("hidden");
    previewLoader.classList.remove("grid");
    previewEmptyState.classList.remove("hidden");
    previewEmptyState.classList.add("grid");
    dynamicLockedState?.classList.add("hidden");
    dynamicLockedState?.classList.remove("grid");
    downloadActions.classList.add("hidden");
    downloadActions.classList.remove("grid");
    setPreviewReady(false);
    if (previewWaitingLabel) previewWaitingLabel.textContent = "بانتظار المحتوى";
  }

  function showDynamicLockedPreview() {
    renderSequence += 1;
    baseQrSvg = "";
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = "";
    previewImage.removeAttribute("src");
    previewImage.classList.add("hidden");
    previewLoader.classList.add("hidden");
    previewLoader.classList.remove("grid");
    previewEmptyState.classList.add("hidden");
    previewEmptyState.classList.remove("grid");
    dynamicLockedState?.classList.remove("hidden");
    dynamicLockedState?.classList.add("grid");
    downloadActions.classList.add("hidden");
    downloadActions.classList.remove("grid");
    setPreviewReady(false);
    if (previewWaitingLabel) previewWaitingLabel.textContent = "يتطلب تفعيلًا";
  }

  function validateState(state) {
    if (state.content_type === "url") {
      try {
        const url = new URL(state.payload.url);
        if (!["http:", "https:"].includes(url.protocol)) throw new Error();
      } catch (_) {
        return "أدخل رابطًا صحيحًا يبدأ بـ http:// أو https://";
      }
    }

    if (state.content_type === "text" && !state.payload.text) return "اكتب النص الذي تريد تحويله إلى QR.";
    if (state.content_type === "wifi" && !state.payload.ssid) return "أدخل اسم شبكة Wi-Fi.";
    if (state.design.center_type === "text" && !state.design.center_text) return "اكتب النص الذي سيظهر وسط الرمز.";
    if (state.design.center_type === "image" && !logoDataUrl && !currentQrCode?.has_logo) return "اختر صورة لتظهر وسط الرمز.";

    return null;
  }

  function showRelevantPanels() {
    const dynamic = currentMode() === "dynamic";
    if (dynamic) setRadio("content_type", "url");

    document.querySelectorAll("[data-static-content-type]").forEach((label) => {
      label.classList.toggle("hidden", dynamic);
    });
    contentTypeGrid?.classList.toggle("grid-cols-1", dynamic);
    contentTypeGrid?.classList.toggle("grid-cols-3", !dynamic);
    dynamicSettings?.classList.toggle("hidden", !dynamic);
    dynamicSettings?.classList.toggle("grid", dynamic);
    dynamicSavedPanel?.classList.toggle("hidden", !dynamic || !currentQrCode?.public_url);
    dynamicSavedPanel?.classList.toggle("grid", dynamic && Boolean(currentQrCode?.public_url));
    if (publicUrlInput) publicUrlInput.value = currentQrCode?.public_url || "";

    if (currentQrCode) {
      qrForm.querySelectorAll('[name="mode"]').forEach((input) => { input.disabled = true; });
      modeLockMessage?.classList.remove("hidden");
    }

    updatePrimaryAction();

    const contentType = selectedValue("content_type");
    document.querySelectorAll("[data-content-panel]").forEach((panel) => {
      const visible = panel.dataset.contentPanel === contentType;
      panel.classList.toggle("hidden", !visible);
      panel.classList.toggle("grid", visible);
    });

    const centerType = selectedValue("center_type");
    document.querySelectorAll("[data-center-panel]").forEach((panel) => {
      const visible = panel.dataset.centerPanel === centerType;
      panel.classList.toggle("hidden", !visible);
      panel.classList.toggle("grid", visible);
    });

    document.getElementById("qrWifiPassword").disabled = document.getElementById("qrWifiEncryption").value === "nopass";
  }

  function colorLuminance(hex) {
    const channels = hex.slice(1).match(/.{2}/g).map((channel) => parseInt(channel, 16) / 255)
      .map((channel) => channel <= 0.03928 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4);

    return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
  }

  function updateContrastWarning() {
    const design = currentDesign();
    const light = Math.max(colorLuminance(design.foreground_color), colorLuminance(design.background_color));
    const dark = Math.min(colorLuminance(design.foreground_color), colorLuminance(design.background_color));
    contrastWarning.classList.toggle("hidden", (light + 0.05) / (dark + 0.05) >= 4.5);
  }

  function updateColorLabels() {
    document.querySelectorAll("[data-color-value]").forEach((label) => {
      label.textContent = document.getElementById(label.dataset.colorValue).value.toUpperCase();
    });
  }

  function persistDraft(pendingSave = false) {
    const previous = readDraft();
    const draft = {
      ...currentState(),
      title: saveTitleInput.value.trim() || currentQrCode?.title || "رمز QR جديد",
      logoDataUrl,
      pendingSave: pendingSave || previous?.pendingSave || false,
      updatedAt: Date.now()
    };

    try {
      localStorage.setItem(draftKey, JSON.stringify(draft));
    } catch (_) {
      setStatus("تعذر حفظ المسودة محليًا بسبب حجم الصورة، لكن يمكنك متابعة الإنشاء.", "error");
    }
  }

  function readDraft() {
    try {
      const draft = JSON.parse(localStorage.getItem(draftKey) || "null");
      return draft && Date.now() - Number(draft.updatedAt || 0) < 7 * 24 * 60 * 60 * 1000 ? draft : null;
    } catch (_) {
      return null;
    }
  }

  function setRadio(name, value) {
    const input = qrForm.querySelector(`[name="${name}"][value="${value}"]`);
    if (input) input.checked = true;
  }

  function hydrateState(state) {
    if (!state) return;

    saveTitleInput.value = state.title || "رمز QR جديد";
    setRadio("mode", state.mode || "static");
    setRadio("content_type", state.content_type || "url");
    setRadio("qr_style", state.design?.style || "classic");
    setRadio("qr_frame", state.design?.frame || "none");
    setRadio("center_type", state.design?.center_type || "none");
    document.getElementById("qrUrl").value = state.payload?.url || "";
    document.getElementById("qrText").value = state.payload?.text || "";
    document.getElementById("qrWifiSsid").value = state.payload?.ssid || "";
    document.getElementById("qrWifiPassword").value = state.payload?.password || "";
    document.getElementById("qrWifiEncryption").value = state.payload?.encryption || "WPA";
    document.getElementById("qrWifiHidden").checked = Boolean(state.payload?.hidden);
    document.getElementById("qrForegroundColor").value = state.design?.foreground_color || "#111827";
    document.getElementById("qrEyeColor").value = state.design?.eye_color || "#6d28d9";
    document.getElementById("qrBackgroundColor").value = state.design?.background_color || "#ffffff";
    centerTextInput.value = state.design?.center_text || "";
    if (activeInput) activeInput.checked = state.is_active ?? true;
    if (expiresAtInput) expiresAtInput.value = state.expires_at ? String(state.expires_at).slice(0, 16) : "";
    logoDataUrl = state.logoDataUrl || logoDataUrl;
    showRelevantPanels();
    updateColorLabels();
    updateContrastWarning();
  }

  async function fetchLogoDataUrl(url) {
    if (!url) return null;
    const response = await fetch(url, { credentials: "same-origin" });
    if (!response.ok) return null;
    return blobToDataUrl(await response.blob());
  }

  function svgElement(documentNode, name, attributes = {}) {
    const element = documentNode.createElementNS(svgNamespace, name);
    Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, String(value)));
    return element;
  }

  async function loadTemplateDataUrl(frame) {
    if (frame === "none" || templateDataUrls.has(frame)) return templateDataUrls.get(frame) || null;

    const templateInput = qrForm.querySelector(`[name="qr_frame"][value="${frame}"]`);
    const templateUrl = templateInput?.dataset.templateUrl;
    if (!templateUrl) throw new Error("تعذر العثور على صورة القالب.");
    const response = await fetch(templateUrl, { credentials: "same-origin" });
    if (!response.ok) throw new Error("تعذر تحميل صورة القالب.");
    const dataUrl = await blobToDataUrl(await response.blob());
    templateDataUrls.set(frame, dataUrl);

    return dataUrl;
  }

  function appendTemplate(documentNode, root, frame, dimensions) {
    if (frame === "none") return;

    root.appendChild(svgElement(documentNode, "image", {
      x: 0,
      y: 0,
      width: dimensions.width,
      height: dimensions.height,
      href: templateDataUrls.get(frame),
      preserveAspectRatio: "xMidYMid meet"
    }));
  }

  function composeSvg() {
    if (!baseQrSvg) return null;

    const state = currentState();
    const dimensions = templateLayouts[state.design.frame] || { width: 720, height: 720, qrX: 0, qrY: 0, qrSize: 720 };
    const output = document.implementation.createDocument(svgNamespace, "svg", null);
    const root = output.documentElement;
    root.setAttribute("xmlns", svgNamespace);
    root.setAttribute("width", dimensions.width);
    root.setAttribute("height", dimensions.height);
    root.setAttribute("viewBox", `0 0 ${dimensions.width} ${dimensions.height}`);
    appendTemplate(output, root, state.design.frame, dimensions);

    const source = new DOMParser().parseFromString(baseQrSvg, "image/svg+xml").documentElement;
    const qrSvg = output.importNode(source, true);
    qrSvg.setAttribute("x", dimensions.qrX);
    qrSvg.setAttribute("y", dimensions.qrY);
    qrSvg.setAttribute("width", dimensions.qrSize);
    qrSvg.setAttribute("height", dimensions.qrSize);
    root.appendChild(qrSvg);

    if (state.design.center_type !== "none") {
      const center = {
        x: dimensions.qrX + dimensions.qrSize * 0.39,
        y: dimensions.qrY + dimensions.qrSize * 0.39,
        size: dimensions.qrSize * 0.22
      };
      root.appendChild(svgElement(output, "rect", {
        x: center.x - 12,
        y: center.y - 12,
        width: center.size + 24,
        height: center.size + 24,
        rx: 28,
        fill: state.design.background_color
      }));

      if (state.design.center_type === "image" && logoDataUrl) {
        root.appendChild(svgElement(output, "image", {
          x: center.x,
          y: center.y,
          width: center.size,
          height: center.size,
          href: logoDataUrl,
          preserveAspectRatio: "xMidYMid meet"
        }));
      } else if (state.design.center_type === "text") {
        const text = svgElement(output, "text", {
          x: center.x + center.size / 2,
          y: center.y + center.size / 2 + 15,
          "text-anchor": "middle",
          "font-family": "Tajawal, Arial, sans-serif",
          "font-size": Math.min(46, 230 / Math.max(2, state.design.center_text.length)),
          "font-weight": 800,
          fill: state.design.eye_color,
          direction: "rtl"
        });
        text.textContent = state.design.center_text;
        root.appendChild(text);
      }
    }

    return output;
  }

  function serializedOutput() {
    const output = composeSvg();
    return output ? new XMLSerializer().serializeToString(output) : "";
  }

  function updatePreviewImage() {
    const svg = serializedOutput();
    if (!svg) return;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(new Blob([svg], { type: "image/svg+xml" }));
    previewImage.src = previewUrl;
    previewImage.classList.remove("hidden");
    previewLoader.classList.add("hidden");
    previewLoader.classList.remove("grid");
    previewEmptyState.classList.add("hidden");
    previewEmptyState.classList.remove("grid");
    dynamicLockedState?.classList.add("hidden");
    dynamicLockedState?.classList.remove("grid");
    setPreviewReady(true);
  }

  async function renderPreview({ track = false } = {}) {
    const state = currentState();
    if (state.mode === "dynamic" && !currentQrCode) {
      showDynamicLockedPreview();
      setStatus("احفظ الرمز الديناميكي أولًا لتفعيل رابطه الثابت ثم تنزيله.");
      return false;
    }
    if (!hasPreviewContent(state)) {
      showEmptyPreview();
      setStatus();
      return false;
    }

    const validationMessage = validateState(state);
    if (validationMessage) {
      showEmptyPreview();
      setStatus(validationMessage, "error");
      return false;
    }

    const sequence = ++renderSequence;
    dynamicLockedState?.classList.add("hidden");
    dynamicLockedState?.classList.remove("grid");
    previewLoader.classList.remove("hidden");
    previewLoader.classList.add("grid");
    previewEmptyState.classList.add("hidden");
    previewEmptyState.classList.remove("grid");
    if (!baseQrSvg) previewImage.classList.add("hidden");
    generateButton.disabled = true;

    try {
      await loadTemplateDataUrl(state.design.frame);
      const response = await fetch(config.routes.render, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": config.csrfToken
        },
        body: JSON.stringify(state)
      });
      const body = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(Object.values(body.errors || {}).flat()[0] || body.message || "تعذر إنشاء الرمز.");
      if (sequence !== renderSequence) return false;
      baseQrSvg = body.svg;
      updatePreviewImage();
      setStatus(track ? "تم إنشاء الرمز بنجاح. يمكنك الآن تحميله أو حفظه." : "المعاينة محدثة.", track ? "success" : "muted");

      if (track) {
        downloadActions.classList.remove("hidden");
        downloadActions.classList.add("grid");
        fetch(config.routes.metrics, {
          method: "POST",
          credentials: "same-origin",
          keepalive: true,
          headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": config.csrfToken },
          body: JSON.stringify({ event: "qr_generate" })
        }).catch(() => {});
      }

      persistDraft();
      return true;
    } catch (error) {
      setStatus(error.message, "error");
      previewLoader.classList.add("hidden");
      previewLoader.classList.remove("grid");
      if (!baseQrSvg) showEmptyPreview();
      return false;
    } finally {
      generateButton.disabled = false;
    }
  }

  function schedulePreview() {
    showRelevantPanels();
    updateColorLabels();
    updateContrastWarning();
    persistDraft();
    window.clearTimeout(renderTimer);
    if (currentMode() === "dynamic" && !currentQrCode) {
      showDynamicLockedPreview();
      setStatus(config.authenticated && config.verified
        ? "احفظ الرمز لتفعيل رابطه الديناميكي ثم تنزيله."
        : "يلزم حساب مجاني لتفعيل QR الديناميكي.");
      return;
    }
    if (!hasPreviewContent(currentState())) {
      showEmptyPreview();
      setStatus();
      return;
    }
    renderTimer = window.setTimeout(() => renderPreview(), 450);
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

  async function pngBlob() {
    await loadTemplateDataUrl(currentDesign().frame);
    const svg = serializedOutput();
    const output = composeSvg();
    if (!svg || !output) return null;
    const width = Number(output.documentElement.getAttribute("width"));
    const height = Number(output.documentElement.getAttribute("height"));
    const imageUrl = URL.createObjectURL(new Blob([svg], { type: "image/svg+xml" }));
    const image = new Image();
    await new Promise((resolve, reject) => {
      image.onload = resolve;
      image.onerror = reject;
      image.src = imageUrl;
    });
    const canvas = document.createElement("canvas");
    canvas.width = width * 2;
    canvas.height = height * 2;
    const context = canvas.getContext("2d");
    context.scale(2, 2);
    context.drawImage(image, 0, 0, width, height);
    URL.revokeObjectURL(imageUrl);

    return new Promise((resolve) => canvas.toBlob(resolve, "image/png", 1));
  }

  function blobToDataUrl(blob) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }

  function dataUrlToBlob(dataUrl) {
    const [header, encoded] = dataUrl.split(",");
    const mime = header.match(/data:([^;]+)/)?.[1] || "image/png";
    const bytes = atob(encoded);
    const array = new Uint8Array(bytes.length);
    for (let index = 0; index < bytes.length; index += 1) array[index] = bytes.charCodeAt(index);
    return new Blob([array], { type: mime });
  }

  function appendNested(formData, prefix, values) {
    Object.entries(values).forEach(([key, value]) => {
      if (value === null || value === undefined) return;
      formData.append(`${prefix}[${key}]`, typeof value === "boolean" ? (value ? "1" : "0") : value);
    });
  }

  async function saveQrCode() {
    if (qrCodeLimitReached()) {
      setSaveStatus(qrCodeLimitMessage(), "error");
      return;
    }

    const title = saveTitleInput.value.trim();
    if (!title) {
      setSaveStatus("اكتب اسمًا للتصميم.", "error");
      saveTitleInput.focus();
      return;
    }

    const state = currentState();
    const validationMessage = validateState(state);
    if (validationMessage) {
      setSaveStatus(validationMessage, "error");
      return;
    }

    confirmSaveButton.disabled = true;
    setSaveStatus("جارٍ الحفظ…");
    const formData = new FormData();
    formData.append("title", title);
    formData.append("mode", state.mode);
    formData.append("content_type", state.content_type);
    appendNested(formData, "payload", state.payload);
    appendNested(formData, "design", state.design);
    formData.append("is_active", state.is_active ? "1" : "0");
    if (state.expires_at) formData.append("expires_at", state.expires_at);

    if (logoFile) {
      formData.append("logo", logoFile);
    } else if (state.design.center_type === "image" && logoDataUrl && !currentQrCode?.has_logo) {
      const blob = dataUrlToBlob(logoDataUrl);
      formData.append("logo", blob, `qr-logo.${blob.type.split("/")[1] || "png"}`);
    }

    let url = config.routes.store;
    if (currentQrCode) {
      url = `${config.routes.updateBase}/${currentQrCode.id}`;
      formData.append("_method", "PATCH");
      formData.append("version", currentQrCode.version);
      if (state.design.center_type !== "image" && currentQrCode.has_logo) formData.append("remove_logo", "1");
    }

    const isCreatingQrCode = !currentQrCode;

    try {
      const response = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: { Accept: "application/json", "X-CSRF-TOKEN": config.csrfToken },
        body: formData
      });
      const body = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(Object.values(body.errors || {}).flat()[0] || body.message || "تعذر حفظ الرمز.");
      currentQrCode = body.data;
      if (isCreatingQrCode) savedQrCodesCount += 1;
      logoFile = null;
      saveTitleInput.value = currentQrCode.title;
      syncQrTitleUi();
      localStorage.removeItem(draftKey);
      setSaveStatus("تم الحفظ في حسابك.", "success");
      setStatus("تم حفظ الرمز في حسابك ويمكنك تعديله لاحقًا.", "success");
      showRelevantPanels();

      if (currentQrCode.mode === "dynamic") {
        const shouldOpenExport = pendingDynamicExport;
        pendingDynamicExport = false;
        if (shouldOpenExport) saveDialog.close();
        const rendered = await renderPreview({ track: shouldOpenExport });
        if (rendered && shouldOpenExport && !exportDialog.open) exportDialog.showModal();
      }

      if (saveDialog.open) window.setTimeout(() => saveDialog.close(), 700);
    } catch (error) {
      setSaveStatus(error.message, "error");
    } finally {
      confirmSaveButton.disabled = false;
    }
  }

  async function openSaveFlow() {
    const state = currentState();
    const validationMessage = validateState(state);
    if (validationMessage) {
      setStatus(validationMessage, "error");
      return;
    }

    if (state.mode === "static") {
      const rendered = await renderPreview();
      if (!rendered) return;
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

    if (qrCodeLimitReached()) {
      setStatus(qrCodeLimitMessage(), "error");
      return;
    }

    saveTitleInput.value = currentQrCode?.title || saveTitleInput.value || "رمز QR جديد";
    syncQrTitleUi();
    setSaveStatus();
    saveDialog.showModal();
    window.setTimeout(() => saveTitleInput.select(), 50);
  }

  async function openExportFlow() {
    const state = currentState();
    if (state.mode === "dynamic" && !config.authenticated) {
      persistDraft(true);
      guestDialog.showModal();
      return;
    }

    if (state.mode === "dynamic" && !config.verified) {
      persistDraft(true);
      window.location.href = config.routes.verification;
      return;
    }

    const validationMessage = validateState(state);
    if (validationMessage) {
      const hasInvalidCenter = (state.design.center_type === "text" && !state.design.center_text)
        || (state.design.center_type === "image" && !logoDataUrl && !currentQrCode?.has_logo);
      activateSidebarPanel(
        hasInvalidCenter ? "center" : "content",
        { scroll: true }
      );
      setStatus(validationMessage, "error");
      return;
    }

    if (state.mode === "dynamic") {
      if (dynamicChangesNeedSave(state)) {
        pendingDynamicExport = true;
        await openSaveFlow();
        return;
      }
    }

    const rendered = await renderPreview({ track: true });
    if (rendered && !exportDialog.open) exportDialog.showModal();
  }

  qrForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    window.clearTimeout(renderTimer);
    await openExportFlow();
  });

  qrForm.addEventListener("input", schedulePreview);
  qrForm.addEventListener("change", schedulePreview);
  sidebarTabs.forEach((tab) => tab.addEventListener("click", () => activateSidebarPanel(tab.dataset.qrSidebarTab)));
  document.querySelectorAll("[data-open-qr-export]").forEach((button) => button.addEventListener("click", openExportFlow));
  saveButton.addEventListener("click", openSaveFlow);
  renameButton.addEventListener("click", openSaveFlow);
  document.getElementById("createNewQrLink")?.addEventListener("click", () => localStorage.removeItem(draftKey));
  document.getElementById("guestSavePromptBtn")?.addEventListener("click", openSaveFlow);
  document.querySelectorAll("[data-qr-register-link], [data-qr-login-link]").forEach((link) => {
    link.addEventListener("click", () => persistDraft(true));
  });
  document.querySelectorAll("[data-open-dynamic-save]").forEach((button) => button.addEventListener("click", openSaveFlow));

  logoInput.addEventListener("change", async () => {
    const file = logoInput.files?.[0];
    if (!file) return;
    if (!["image/png", "image/jpeg", "image/webp"].includes(file.type) || file.size > 1024 * 1024) {
      setStatus("اختر صورة PNG أو JPG أو WebP بحجم لا يتجاوز 1 ميجابايت.", "error");
      logoInput.value = "";
      return;
    }
    logoFile = file;
    logoDataUrl = await blobToDataUrl(file);
    logoName.textContent = file.name;
    persistDraft();
    if (baseQrSvg) updatePreviewImage();
  });

  saveForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    await saveQrCode();
  });
  saveDialog?.querySelectorAll("[data-close-save-dialog]").forEach((button) => button.addEventListener("click", () => {
    pendingDynamicExport = false;
    saveDialog.close();
  }));
  guestDialog?.querySelectorAll("[data-close-guest-dialog]").forEach((button) => button.addEventListener("click", () => guestDialog.close()));
  exportDialog?.querySelectorAll("[data-close-qr-export]").forEach((button) => button.addEventListener("click", () => exportDialog.close()));
  exportDialog?.querySelector("[data-save-from-qr-export]")?.addEventListener("click", () => {
    exportDialog.close();
    openSaveFlow();
  });

  document.getElementById("downloadQrSvg").addEventListener("click", async () => {
    await loadTemplateDataUrl(currentDesign().frame);
    downloadBlob(new Blob([serializedOutput()], { type: "image/svg+xml" }), "muallem-qr.svg");
  });
  document.getElementById("downloadQrPng").addEventListener("click", async () => {
    const blob = await pngBlob();
    if (blob) downloadBlob(blob, "muallem-qr.png");
  });
  document.getElementById("copyQrImage").addEventListener("click", async () => {
    const blob = await pngBlob();
    if (!blob) return;
    if (navigator.clipboard?.write && window.ClipboardItem) {
      await navigator.clipboard.write([new ClipboardItem({ "image/png": blob })]);
      setStatus("تم نسخ صورة QR.", "success");
    } else {
      downloadBlob(blob, "muallem-qr.png");
      setStatus("المتصفح لا يدعم نسخ الصور؛ تم تنزيلها بدلًا من ذلك.");
    }
  });
  document.getElementById("copyQrPublicUrl")?.addEventListener("click", async () => {
    if (!publicUrlInput?.value) return;
    await navigator.clipboard.writeText(publicUrlInput.value);
    setStatus("تم نسخ الرابط الديناميكي.", "success");
  });

  async function initialize() {
    const draft = readDraft();
    hydrateState(currentQrCode || draft);
    syncQrTitleUi();

    if (currentQrCode?.logo_url) {
      logoDataUrl = await fetchLogoDataUrl(currentQrCode.logo_url);
      if (logoDataUrl) logoName.textContent = "الصورة المحفوظة";
    } else if (draft?.logoDataUrl) {
      logoDataUrl = draft.logoDataUrl;
      logoName.textContent = "الصورة المحفوظة في المسودة";
    }

    if (currentMode() === "dynamic" && !currentQrCode) {
      showDynamicLockedPreview();
    } else if (hasPreviewContent(currentState())) {
      await renderPreview();
    } else {
      showEmptyPreview();
    }

    if (draft?.pendingSave && config.authenticated && config.verified && !currentQrCode) {
      if (qrCodeLimitReached()) {
        setStatus(qrCodeLimitMessage(), "error");
      } else {
        saveTitleInput.value = draft.title || "رمز QR جديد";
        syncQrTitleUi();
        saveDialog.showModal();
      }
    }
  }

  initialize();
}
