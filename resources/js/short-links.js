import { downloadBlob, svgToPngBlob } from "./qr-downloads";

document.querySelectorAll("[data-short-link-tools]").forEach((tools) => {
  const shortUrl = tools.dataset.shortUrl;
  const qrUrl = tools.dataset.qrUrl;
  const filename = tools.dataset.filename || "muallem-short-link";
  const status = tools.querySelector("[data-short-link-status]");

  const setStatus = (message, tone = "success") => {
    if (!status) return;
    status.textContent = message;
    status.className = `min-h-5 text-sm font-bold ${tone === "error" ? "text-red-700" : "text-emerald-700"}`;
  };

  tools.querySelectorAll("[data-copy-short-link]").forEach((button) => {
    button.addEventListener("click", async () => {
      try {
        await navigator.clipboard.writeText(shortUrl);
        setStatus("تم نسخ الرابط المختصر.");
      } catch (_) {
        setStatus("تعذر النسخ التلقائي؛ حدّد الرابط وانسخه يدويًا.", "error");
      }
    });
  });

  tools.querySelectorAll("[data-download-qr]").forEach((button) => {
    button.addEventListener("click", async () => {
      button.disabled = true;

      try {
        const response = await fetch(qrUrl, { credentials: "same-origin" });
        if (!response.ok) throw new Error("تعذر تحميل رمز QR.");
        const svg = await response.text();

        if (button.dataset.downloadQr === "png") {
          const png = await svgToPngBlob(svg);
          if (!png) throw new Error("تعذر إنشاء ملف PNG.");
          downloadBlob(png, `${filename}.png`);
        } else {
          downloadBlob(new Blob([svg], { type: "image/svg+xml" }), `${filename}.svg`);
        }

        setStatus(`تم تنزيل QR بصيغة ${button.dataset.downloadQr.toUpperCase()}.`);
      } catch (error) {
        setStatus(error.message, "error");
      } finally {
        button.disabled = false;
      }
    });
  });
});
