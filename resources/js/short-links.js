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

function initializeShortLinkTools(tools) {
  if (tools.dataset.toolsInitialized) return;

  tools.dataset.toolsInitialized = "true";
  const status = tools.querySelector("[data-short-link-status]");

  tools.querySelectorAll("[data-copy-short-link]").forEach((button) => {
    button.addEventListener("click", async () => {
      try {
        await navigator.clipboard.writeText(tools.dataset.shortUrl || "");
        setFeedback(status, "تم نسخ الرابط المختصر.");
      } catch (_) {
        setFeedback(status, "تعذر النسخ التلقائي؛ حدّد الرابط وانسخه يدويًا.", "error");
      }
    });
  });

  tools.querySelectorAll("[data-download-qr]").forEach((button) => {
    button.addEventListener("click", async () => {
      button.disabled = true;

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

        setFeedback(status, `تم تنزيل QR بصيغة ${button.dataset.downloadQr.toUpperCase()}.`);
      } catch (error) {
        setFeedback(status, error.message, "error");
      } finally {
        button.disabled = false;
      }
    });
  });
}

function applyShortLinkResult(result, message) {
  const resultSection = document.querySelector("[data-short-link-result]");
  if (!resultSection) return;

  const tools = resultSection.querySelector("[data-short-link-tools]");
  const slugForm = resultSection.querySelector("[data-short-link-slug-form]");
  const qrImage = resultSection.querySelector("[data-short-link-qr]");
  const managementLink = resultSection.querySelector("[data-short-link-management]");

  tools.dataset.shortUrl = result.short_url;
  tools.dataset.qrUrl = result.qr_url;
  tools.dataset.filename = result.filename;
  resultSection.querySelector("[data-short-url-output]").value = result.short_url;
  resultSection.querySelector("[data-short-link-slug]").value = result.slug;
  resultSection.querySelector("[data-short-link-version]").value = result.version;
  slugForm.action = result.update_slug_url;
  qrImage.src = `${result.qr_url}?version=${result.version}`;
  managementLink.href = result.management_url;
  managementLink.hidden = false;
  resultSection.hidden = false;

  initializeShortLinkTools(tools);
  setFeedback(resultSection.querySelector("[data-short-link-status]"), message);
}

document.querySelectorAll("[data-short-link-tools]").forEach(initializeShortLinkTools);

document.querySelectorAll("[data-short-link-create]").forEach((form) => {
  const expiryToggle = form.querySelector("[data-expiry-toggle]");
  const expiryFields = form.querySelector("[data-expiry-fields]");
  const expiryInput = form.querySelector("[data-expiry-input]");
  const submitButton = form.querySelector("[data-create-short-link-button]");
  const buttonLabel = form.querySelector("[data-button-label]");
  const status = form.querySelector("[data-short-link-create-status]");

  const syncExpiry = () => {
    const enabled = expiryToggle.checked;
    expiryFields.hidden = !enabled;
    expiryInput.disabled = !enabled;
    if (!enabled) expiryInput.value = "";
  };

  expiryToggle.addEventListener("change", syncExpiry);
  syncExpiry();

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    submitButton.disabled = true;
    buttonLabel.textContent = "جارٍ الإنشاء...";
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

      applyShortLinkResult(payload.data, payload.message);
      form.reset();
      syncExpiry();

      const resultSection = document.querySelector("[data-short-link-result]");
      resultSection?.scrollIntoView({
        behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth",
        block: "start"
      });
    } catch (error) {
      setFeedback(status, error.message, "error");
    } finally {
      submitButton.disabled = false;
      buttonLabel.textContent = "إنشاء الرابط";
    }
  });
});

document.querySelectorAll("[data-short-link-slug-form]").forEach((form) => {
  const saveButton = form.querySelector("[data-save-short-link-slug]");
  const resultSection = form.closest("[data-short-link-result]");
  const status = resultSection?.querySelector("[data-short-link-status]");

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    saveButton.disabled = true;

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

      if (!response.ok) {
        throw new Error(firstError(payload, "تعذر حفظ المقطع الجديد."));
      }

      applyShortLinkResult(payload.data, payload.message);
    } catch (error) {
      setFeedback(status, error.message, "error");
    } finally {
      saveButton.disabled = false;
    }
  });
});
