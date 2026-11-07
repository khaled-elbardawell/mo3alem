import { downloadBlob, svgToPngBlob } from "./qr-downloads";

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

function firstError(payload, fallback) {
  return Object.values(payload?.errors || {}).flat()[0] || payload?.message || fallback;
}

function setFeedback(element, message, tone = "success") {
  if (!element) return;

  element.textContent = message;
  element.hidden = false;
  element.classList.remove(
    "hidden",
    "border-red-200",
    "bg-red-50",
    "text-red-700",
    "border-emerald-200",
    "bg-emerald-50",
    "text-emerald-700"
  );
  element.classList.add(
    ...(tone === "error"
      ? ["border-red-200", "bg-red-50", "text-red-700"]
      : ["border-emerald-200", "bg-emerald-50", "text-emerald-700"])
  );
}

async function copyText(value) {
  try {
    await navigator.clipboard.writeText(value);
    return true;
  } catch (_) {
    return false;
  }
}

function colorLuminance(hex) {
  const channels = [1, 3, 5]
    .map((start) => Number.parseInt(hex.slice(start, start + 2), 16) / 255)
    .map((channel) => channel <= 0.03928 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4);

  return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
}

function initializeQrCustomizer(tools) {
  const customizer = tools.querySelector("[data-qr-customizer]");
  if (!customizer) return;

  const preview = customizer.querySelector("[data-short-link-qr]");
  const saveButton = customizer.querySelector("[data-save-qr-design]");
  const status = customizer.querySelector("[data-qr-design-status]");
  const contrastWarning = customizer.querySelector("[data-qr-contrast-warning]");
  const versionInput = tools.querySelector("[data-short-link-version]");
  let renderTimer = null;
  let previewObjectUrl = null;
  let savedDesign = "";

  const currentDesign = () => ({
    style: customizer.querySelector("[data-qr-style]:checked")?.value || "classic",
    foreground_color: customizer.querySelector('[data-qr-color="foreground_color"]')?.value || "#111827",
    eye_color: customizer.querySelector('[data-qr-color="eye_color"]')?.value || "#6d28d9",
    background_color: customizer.querySelector('[data-qr-color="background_color"]')?.value || "#ffffff"
  });

  const updateContrastWarning = () => {
    const design = currentDesign();
    const light = Math.max(colorLuminance(design.foreground_color), colorLuminance(design.background_color));
    const dark = Math.min(colorLuminance(design.foreground_color), colorLuminance(design.background_color));
    contrastWarning?.classList.toggle("hidden", (light + 0.05) / (dark + 0.05) >= 4.5);
  };

  const updateColorLabels = () => {
    customizer.querySelectorAll("[data-qr-color]").forEach((input) => {
      const label = customizer.querySelector(`[data-qr-color-value="${input.dataset.qrColor}"]`);
      if (label) label.textContent = input.value.toUpperCase();
    });
  };

  const applyDesign = (design) => {
    const style = customizer.querySelector(`[data-qr-style][value="${design?.style || "classic"}"]`);
    if (style) style.checked = true;

    customizer.querySelectorAll("[data-qr-color]").forEach((input) => {
      const value = design?.[input.dataset.qrColor];
      if (value) input.value = value;
    });

    updateColorLabels();
    updateContrastWarning();
    savedDesign = JSON.stringify(currentDesign());
    saveButton.textContent = "التصميم محفوظ";
    saveButton.disabled = true;
  };

  const renderPreview = async () => {
    if (!tools.dataset.shortUrl || !tools.dataset.qrPreviewUrl) return;

    try {
      const response = await fetch(tools.dataset.qrPreviewUrl, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
          mode: "static",
          content_type: "url",
          payload: { url: tools.dataset.shortUrl },
          design: {
            ...currentDesign(),
            frame: "none",
            center_type: "none",
            center_text: null
          }
        })
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok || !payload.svg) throw new Error(firstError(payload, "تعذرت معاينة رمز QR."));

      if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
      previewObjectUrl = URL.createObjectURL(new Blob([payload.svg], { type: "image/svg+xml" }));
      preview.src = previewObjectUrl;
    } catch (error) {
      setFeedback(status, error.message, "error");
    }
  };

  const schedulePreview = () => {
    window.clearTimeout(renderTimer);
    renderTimer = window.setTimeout(renderPreview, 180);
  };

  const markDirty = () => {
    updateColorLabels();
    updateContrastWarning();
    status.hidden = true;
    saveButton.disabled = false;
    saveButton.textContent = "حفظ التصميم";
    schedulePreview();
  };

  const saveDesign = async ({ silent = false } = {}) => {
    const design = currentDesign();
    if (JSON.stringify(design) === savedDesign) return true;

    saveButton.disabled = true;
    saveButton.textContent = "جارٍ الحفظ...";

    try {
      const response = await fetch(tools.dataset.updateQrDesignUrl || "", {
        method: "PATCH",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
          design,
          version: Number(versionInput?.value || 1)
        })
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(firstError(payload, "تعذر حفظ تصميم QR."));

      savedDesign = JSON.stringify(payload.data.design);
      if (versionInput) versionInput.value = payload.data.version;
      tools.dataset.qrUrl = payload.data.qr_url;
      saveButton.textContent = "التصميم محفوظ";
      if (!silent) setFeedback(status, payload.message);
      return true;
    } catch (error) {
      saveButton.disabled = false;
      saveButton.textContent = "حفظ التصميم";
      setFeedback(status, error.message, "error");
      return false;
    }
  };

  customizer.querySelectorAll("[data-qr-style], [data-qr-color]").forEach((input) => {
    input.addEventListener("input", markDirty);
    input.addEventListener("change", markDirty);
  });
  saveButton.addEventListener("click", () => saveDesign());

  tools.applyQrDesign = applyDesign;
  tools.saveQrDesign = saveDesign;
  tools.resetQrPreview = () => {
    if (previewObjectUrl) {
      URL.revokeObjectURL(previewObjectUrl);
      previewObjectUrl = null;
    }
    preview.src = `${tools.dataset.qrUrl}?version=${versionInput?.value || Date.now()}`;
  };

  applyDesign(currentDesign());
}

function initializeShortLinkTools(tools) {
  if (tools.dataset.toolsInitialized) return;

  tools.dataset.toolsInitialized = "true";
  const status = tools.querySelector("[data-short-link-status]");
  const slugEditor = tools.querySelector("[data-short-link-slug-form]");
  const qrCustomizer = tools.querySelector("[data-qr-customizer]");
  const slugToggle = tools.querySelector("[data-toggle-slug-editor]");
  const qrToggle = tools.querySelector("[data-toggle-qr-customizer]");
  const resultSection = tools.closest("[data-short-link-result]");
  const createForm = document.querySelector("[data-short-link-create]");
  const panelIsHidden = (panel) => panel.hidden || panel.classList.contains("hidden");
  const syncPageScroll = () => {
    const panelOpen = !panelIsHidden(slugEditor) || !panelIsHidden(qrCustomizer);
    document.documentElement.classList.toggle("overflow-hidden", panelOpen);
  };
  const setPanelOpen = (panel, open) => {
    panel.hidden = !open;
    panel.classList.toggle("hidden", !open);
    syncPageScroll();
  };

  tools.querySelectorAll("[data-copy-short-link]").forEach((button) => {
    button.addEventListener("click", async () => {
      const copied = await copyText(tools.dataset.shortUrl || "");
      setFeedback(
        status,
        copied ? "تم نسخ الرابط المختصر." : "تعذر النسخ التلقائي؛ حدّد الرابط وانسخه يدويًا.",
        copied ? "success" : "error"
      );
    });
  });

  slugToggle?.addEventListener("click", () => {
    const opening = panelIsHidden(slugEditor);
    setPanelOpen(slugEditor, opening);
    slugToggle.setAttribute("aria-expanded", String(opening));
    if (opening) slugEditor.querySelector("[data-short-link-slug]")?.focus();
  });

  const closeSlugEditor = () => {
    setPanelOpen(slugEditor, false);
    slugToggle?.setAttribute("aria-expanded", "false");
    slugToggle?.focus();
  };

  tools.querySelector("[data-close-slug-editor]")?.addEventListener("click", closeSlugEditor);

  const setQrCustomizerOpen = (open) => {
    setPanelOpen(qrCustomizer, open);
    qrToggle?.setAttribute("aria-expanded", String(open));
    if (open) tools.querySelector("[data-close-qr-customizer]")?.focus();
    else qrToggle?.focus();
  };

  qrToggle?.addEventListener("click", () => setQrCustomizerOpen(panelIsHidden(qrCustomizer)));
  tools.querySelector("[data-close-qr-customizer]")?.addEventListener("click", () => setQrCustomizerOpen(false));

  tools.querySelector("[data-create-another-short-link]")?.addEventListener("click", () => {
    closeSlugEditor();
    setQrCustomizerOpen(false);
    resultSection.hidden = true;
    createForm.hidden = false;
    createForm.classList.remove("hidden");
    createForm.reset();
    createForm.querySelector("[data-creation-mode]:checked")?.dispatchEvent(new Event("change", { bubbles: true }));
    createForm.querySelector("[data-expiry-toggle]")?.dispatchEvent(new Event("change", { bubbles: true }));
    createForm.querySelector("[data-short-link-create-status]").hidden = true;
    createForm.querySelector("[data-short-link-destination]")?.focus();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    if (!panelIsHidden(slugEditor)) closeSlugEditor();
    if (!panelIsHidden(qrCustomizer)) setQrCustomizerOpen(false);
  });

  initializeQrCustomizer(tools);
  tools.closeSlugEditor = closeSlugEditor;

  tools.querySelectorAll("[data-download-qr]").forEach((button) => {
    button.addEventListener("click", async () => {
      button.disabled = true;
      const designSaved = await tools.saveQrDesign?.({ silent: true });

      if (designSaved === false) {
        button.disabled = false;
        return;
      }

      try {
        const response = await fetch(tools.dataset.qrUrl || "", { credentials: "same-origin" });
        if (!response.ok) throw new Error("تعذر تحميل رمز QR.");
        const svg = await response.text();
        const filename = tools.dataset.filename || "muallem-short-link";

        if (button.dataset.downloadQr === "png") {
          const png = await svgToPngBlob(svg);
          if (!png) throw new Error("تعذر إنشاء ملف PNG.");
          downloadBlob(png, `${filename}.png`);
        } else {
          downloadBlob(new Blob([svg], { type: "image/svg+xml" }), `${filename}.svg`);
        }

        setFeedback(tools.querySelector("[data-qr-design-status]"), `تم تنزيل QR بصيغة ${button.dataset.downloadQr.toUpperCase()}.`);
      } catch (error) {
        setFeedback(tools.querySelector("[data-qr-design-status]"), error.message, "error");
      } finally {
        button.disabled = false;
      }
    });
  });
}

async function applyShortLinkResult(result, message, { copy = false } = {}) {
  const resultSection = document.querySelector("[data-short-link-result]");
  if (!resultSection) return;

  const tools = resultSection.querySelector("[data-short-link-tools]");
  const slugForm = resultSection.querySelector("[data-short-link-slug-form]");
  const managementLink = resultSection.querySelector("[data-short-link-management]");
  const shortUrlOutput = resultSection.querySelector("[data-short-url-output]");

  tools.dataset.shortUrl = result.short_url;
  tools.dataset.qrUrl = result.qr_url;
  tools.dataset.updateQrDesignUrl = result.update_qr_design_url;
  tools.dataset.filename = result.filename;
  shortUrlOutput.value = result.short_url;
  resultSection.querySelector("[data-short-link-slug]").value = result.slug;
  resultSection.querySelector("[data-short-link-version]").value = result.version;
  slugForm.action = result.update_slug_url;
  managementLink.href = result.management_url;
  managementLink.hidden = false;
  resultSection.hidden = false;
  const createForm = document.querySelector("[data-short-link-create]");
  if (createForm) {
    createForm.hidden = true;
    createForm.classList.add("hidden");
  }

  initializeShortLinkTools(tools);
  tools.applyQrDesign?.(result.qr_design);
  tools.resetQrPreview?.();

  let feedback = message;
  if (copy) {
    const copied = await copyText(result.short_url);
    feedback = copied ? `${message} وتم نسخه.` : `${message} انسخه من الحقل الظاهر.`;
  }
  setFeedback(resultSection.querySelector("[data-short-link-status]"), feedback);
}

function saveFormDraft(form) {
  const key = form.dataset.draftKey;
  if (!key) return;

  try {
    sessionStorage.setItem(key, JSON.stringify({
      destination_url: form.querySelector("[data-short-link-destination]")?.value || "",
      creation_mode: form.querySelector("[data-creation-mode]:checked")?.value || "automatic",
      custom_alias: form.querySelector("[data-custom-alias-input]")?.value || ""
    }));
  } catch (_) {
    // The form still works when storage is unavailable.
  }
}

document.querySelectorAll("[data-short-link-tools]").forEach(initializeShortLinkTools);

document.querySelectorAll("[data-short-link-create]").forEach((form) => {
  const modeInputs = [...form.querySelectorAll("[data-creation-mode]")];
  const customAliasFields = form.querySelector("[data-custom-alias-fields]");
  const customAliasInput = form.querySelector("[data-custom-alias-input]");
  const expiryToggle = form.querySelector("[data-expiry-toggle]");
  const expiryFields = form.querySelector("[data-expiry-fields]");
  const expiryInput = form.querySelector("[data-expiry-input]");
  const submitButton = form.querySelector("[data-create-short-link-button]");
  const buttonLabel = form.querySelector("[data-button-label]");
  const status = form.querySelector("[data-short-link-create-status]");
  const canCreate = form.dataset.canCreate === "true";
  const defaultButtonLabel = canCreate ? "اختصر وانسخ" : "تسجيل الدخول والمتابعة";

  const syncMode = () => {
    const isCustom = form.querySelector("[data-creation-mode]:checked")?.value === "custom";
    customAliasFields.hidden = !isCustom;
    customAliasInput.disabled = !isCustom;
    customAliasInput.required = isCustom;
  };

  const syncExpiry = () => {
    const enabled = expiryToggle.checked;
    expiryFields.hidden = !enabled;
    expiryInput.disabled = !enabled;
    if (!enabled) expiryInput.value = "";
  };

  try {
    const draft = JSON.parse(sessionStorage.getItem(form.dataset.draftKey) || "null");
    if (draft?.destination_url && !form.querySelector("[data-short-link-destination]").value) {
      form.querySelector("[data-short-link-destination]").value = draft.destination_url;
      const mode = modeInputs.find((input) => input.value === draft.creation_mode);
      if (mode) mode.checked = true;
      customAliasInput.value = draft.custom_alias || "";
      if (canCreate) setFeedback(status, "استعدنا الرابط الذي أدخلته قبل تسجيل الدخول.");
    }
  } catch (_) {
    // Ignore malformed or unavailable browser storage.
  }

  modeInputs.forEach((input) => input.addEventListener("change", syncMode));
  expiryToggle.addEventListener("change", syncExpiry);
  syncMode();
  syncExpiry();

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (!canCreate) {
      saveFormDraft(form);
      window.location.assign(form.dataset.loginUrl);
      return;
    }

    submitButton.disabled = true;
    buttonLabel.textContent = "جارٍ الاختصار...";
    status.hidden = true;

    try {
      const response = await fetch(form.action, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": csrfToken
        },
        body: new FormData(form)
      });
      const payload = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(firstError(payload, "تعذر إنشاء الرابط. تحقق من البيانات وحاول مجددًا."));
      }

      await applyShortLinkResult(payload.data, payload.message, { copy: true });
      try {
        sessionStorage.removeItem(form.dataset.draftKey);
      } catch (_) {
        // The result is already created, so storage cleanup is optional.
      }
      form.reset();
      syncMode();
      syncExpiry();
    } catch (error) {
      setFeedback(status, error.message, "error");
    } finally {
      submitButton.disabled = false;
      buttonLabel.textContent = defaultButtonLabel;
    }
  });

  form.querySelectorAll("[data-short-link-auth-link]").forEach((link) => {
    link.addEventListener("click", () => saveFormDraft(form));
  });
});

document.querySelectorAll("[data-short-link-slug-form]").forEach((form) => {
  const saveButton = form.querySelector("[data-save-short-link-slug]");
  const resultSection = form.closest("[data-short-link-result]");
  const status = resultSection?.querySelector("[data-short-link-status]");

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    saveButton.disabled = true;
    saveButton.textContent = "جارٍ الحفظ...";

    try {
      const response = await fetch(form.action, {
        method: "PATCH",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken
        },
        body: JSON.stringify({
          slug: form.querySelector("[data-short-link-slug]").value,
          version: Number(form.querySelector("[data-short-link-version]").value)
        })
      });
      const payload = await response.json().catch(() => ({}));

      if (!response.ok) throw new Error(firstError(payload, "تعذر حفظ المقطع الجديد."));

      await applyShortLinkResult(payload.data, payload.message, { copy: true });
      resultSection.querySelector("[data-short-link-tools]")?.closeSlugEditor?.();
    } catch (error) {
      setFeedback(status, error.message, "error");
    } finally {
      saveButton.disabled = false;
      saveButton.textContent = "حفظ ونسخ";
    }
  });
});
