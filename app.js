const canvas = document.getElementById("wheelCanvas");
const ctx = canvas.getContext("2d");

const totalCountEl = document.getElementById("totalCount");
const selectAllNames = document.getElementById("selectAllNames");
const selectedCountEl = document.getElementById("selectedCount");
const virtualList = document.getElementById("virtualList");
const virtualSpacer = document.getElementById("virtualSpacer");
const virtualItems = document.getElementById("virtualItems");
const spinBtn = document.getElementById("spinBtn");
const centerSpinBtn = document.getElementById("centerSpinBtn");
const resultCard = document.getElementById("resultCard");
const resultName = document.getElementById("resultName");
const addNameBtn = document.getElementById("addNameBtn");
const nameDialog = document.getElementById("nameDialog");
const nameInput = document.getElementById("nameInput");
const confirmAddName = document.getElementById("confirmAddName");
const importInput = document.getElementById("importInput");
const clearBtn = document.getElementById("clearBtn");
const clearSelectedBtn = document.getElementById("clearSelectedBtn");
const shuffleBtn = document.getElementById("shuffleBtn");
const fullscreenBtn = document.getElementById("fullscreenBtn");
const soundBtn = document.getElementById("soundBtn");
const autoSpin = document.getElementById("autoSpin");
const wheelWrap = document.getElementById("wheelWrap");
const mobileMenuBtn = document.getElementById("mobileMenuBtn");
const mobileDrawer = document.getElementById("mobileDrawer");
const modeTabs = document.querySelectorAll(".mode-tabs button");
const modeHint = document.getElementById("modeHint");
const panelTabs = document.querySelectorAll(".panel-tabs button");
const dataPage = document.getElementById("dataPage");
const resultsPage = document.getElementById("resultsPage");
const winnersList = document.getElementById("winnersList");
const emptyResults = document.getElementById("emptyResults");
const clearResultsBtn = document.getElementById("clearResultsBtn");
const celebration = document.getElementById("celebration");
const celebrationName = document.getElementById("celebrationName");
const celebrationCloseBtn = document.getElementById("celebrationCloseBtn");
const celebrationAudio = new Audio("./assets/voice.m4a");

const colors = [
  "#ef4444", "#f97316", "#f59e0b", "#22c55e", "#14b8a6", "#06b6d4",
  "#3b82f6", "#6366f1", "#8b5cf6", "#a855f7", "#ec4899", "#0ea5e9"
];

const arabicNames = [
  "أحمد محمد", "سارة خالد", "محمد علي", "أريج ناصر", "فاطمة سالم", "علي حسن",
  "نورة عبدالله", "ماجد عبدالله", "كريم محمد", "تركي سالم", "يوسف ماجد", "عبدالله فهد",
  "سعود خالد", "إبراهيم علي", "ياسر ناصر", "ناصر سعود", "صالح وليد", "راشد حسن",
  "خالد إبراهيم", "منى أحمد", "رنا سامي", "هند يوسف", "ريم حسن", "لينا فارس"
];

let names = createInitialNames(10);
let winners = [];
let selectedIds = new Set();
let rotation = 0;
let spinning = false;
let muted = false;
let autoTimer = null;
let celebrationTimer = null;
let confettiTimers = [];
let draggedNameIndex = null;

const rowHeight = 46;
const overscan = 8;
const spinDurationMs = 5100;
const celebrationDurationMs = 5200;

celebrationAudio.preload = "auto";

function createInitialNames(count) {
  return Array.from({ length: count }, (_, i) => arabicNames[i % arabicNames.length]);
}

function formatNumber(number) {
  return new Intl.NumberFormat("en-GB").format(number);
}

function updateCounts() {
  totalCountEl.textContent = `عدد الكل (${formatNumber(names.length)})`;
  selectedCountEl.textContent = `عدد المحدد (${formatNumber(selectedIds.size)})`;
  clearSelectedBtn.disabled = selectedIds.size === 0;
  selectAllNames.disabled = names.length === 0;
  selectAllNames.checked = names.length > 0 && selectedIds.size === names.length;
  selectAllNames.indeterminate = selectedIds.size > 0 && selectedIds.size < names.length;

  const label = names.length > 2000 ? "يدعم أكثر من 2000 اسم" : `عدد الأسماء ${formatNumber(names.length)}`;
}

function updateWheelDensityClass() {
  wheelWrap.classList.toggle("is-few", names.length > 0 && names.length <= 24);
  wheelWrap.classList.toggle("is-many", names.length >= 500);
}

function drawWheel() {
  updateWheelDensityClass();

  const dpr = window.devicePixelRatio || 1;
  const rect = canvas.getBoundingClientRect();
  const cssWidth = Math.max(1, rect.width);
  const cssHeight = Math.max(1, rect.height);

  canvas.width = Math.floor(cssWidth * dpr);
  canvas.height = Math.floor(cssHeight * dpr);
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

  const size = Math.min(cssWidth, cssHeight);
  const center = size / 2;
  const count = Math.max(names.length, 1);

  const zoom = getWheelZoom(count);
  const radius = (center - 10) * zoom.radiusScale;
  const innerRadius = Math.max(32, radius * zoom.innerScale);
  const slice = (Math.PI * 2) / count;
  const fullCircle = Math.PI * 2;

  ctx.clearRect(0, 0, cssWidth, cssHeight);

  if (names.length === 0) {
    drawEmptyWheel(center, radius, innerRadius);
    return;
  }

  const maxDrawnSegments = count > 3500 ? 900 : count;

  if (count > maxDrawnSegments) {
    const groupSlice = fullCircle / maxDrawnSegments;
    for (let i = 0; i < maxDrawnSegments; i++) {
      drawSlice(center, radius, innerRadius, i * groupSlice, (i + 1) * groupSlice, colors[i % colors.length], groupSlice);
    }
  } else {
    for (let i = 0; i < count; i++) {
      drawSlice(center, radius, innerRadius, i * slice, (i + 1) * slice, colors[i % colors.length], slice);
    }
  }

  drawOuterRings(center, radius);

  drawLabels({
    center,
    radius,
    count,
    slice,
    zoom
  });
}

function getWheelZoom(count) {
  // العدد القليل: نكبر القطاعات والخط.
  // العدد الكبير: نصغر الخط والـinner gap ونرسم القطاعات بكثافة أعلى.
  if (count <= 12) return { radiusScale: 1, innerScale: 0.13, maxLabels: count, font: 20 };
  if (count <= 36) return { radiusScale: 1, innerScale: 0.14, maxLabels: count, font: 15 };
  if (count <= 120) return { radiusScale: 0.99, innerScale: 0.15, maxLabels: 80, font: 12 };
  if (count <= 600) return { radiusScale: 0.97, innerScale: 0.13, maxLabels: 70, font: 10 };
  if (count <= 2000) return { radiusScale: 0.94, innerScale: 0.11, maxLabels: 56, font: 9 };
  return { radiusScale: 0.91, innerScale: 0.10, maxLabels: 48, font: 8 };
}

function drawEmptyWheel(center, radius, innerRadius) {
  ctx.beginPath();
  ctx.arc(center, center, radius, 0, Math.PI * 2);
  ctx.arc(center, center, innerRadius, Math.PI * 2, 0, true);
  ctx.fillStyle = "#f3f0ff";
  ctx.fill();

  ctx.save();
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillStyle = "#6d28d9";
  ctx.font = "900 18px Tajawal, sans-serif";
  ctx.fillText("أضف أسماء للبدء", center, center - radius * .38);
  ctx.restore();

  drawOuterRings(center, radius);
}

function drawSlice(cx, radius, innerRadius, start, end, color, sliceSize) {
  ctx.beginPath();
  ctx.arc(cx, cx, radius, start, end);
  ctx.arc(cx, cx, innerRadius, end, start, true);
  ctx.closePath();
  ctx.fillStyle = color;
  ctx.fill();

  if (sliceSize > 0.008) {
    ctx.strokeStyle = "rgba(255,255,255,.44)";
    ctx.lineWidth = sliceSize > 0.04 ? 1.3 : 0.5;
    ctx.stroke();
  }
}

function drawOuterRings(center, radius) {
  ctx.save();

  ctx.beginPath();
  ctx.arc(center, center, radius + 2, 0, Math.PI * 2);
  ctx.lineWidth = 8;
  ctx.strokeStyle = "#ffffff";
  ctx.stroke();

  ctx.beginPath();
  ctx.arc(center, center, radius + 7, 0, Math.PI * 2);
  ctx.lineWidth = 2;
  ctx.strokeStyle = "rgba(109,40,217,.15)";
  ctx.stroke();

  ctx.restore();
}

function drawLabels({ center, radius, count, slice, zoom }) {
  const maxLabels = Math.min(count, zoom.maxLabels);
  const labelEvery = Math.max(1, Math.floor(count / maxLabels));
  const showNames = count <= 140;

  ctx.save();
  ctx.translate(center, center);
  ctx.font = `800 ${zoom.font}px Tajawal, sans-serif`;
  ctx.textAlign = "right";
  ctx.textBaseline = "middle";
  ctx.fillStyle = "#fff";

  for (let i = 0; i < count; i += labelEvery) {
    const angle = i * slice + slice / 2;
    ctx.save();
    ctx.rotate(angle);
    const label = showNames ? names[i].slice(0, 18) : `${i + 1}`;
    ctx.fillText(label, radius - 22, 0);
    ctx.restore();
  }

  ctx.restore();
}

function renderVirtualList() {
  updateCounts();

  virtualSpacer.style.height = "0px";
  virtualItems.style.transform = "none";
  virtualItems.replaceChildren();

  if (names.length === 0) {
    return;
  }

  const fragment = document.createDocumentFragment();

  for (let i = 0; i < names.length; i++) {
    const row = document.createElement("div");
    row.className = "name-row";
    row.setAttribute("role", "option");
    row.draggable = true;
    row.addEventListener("dragstart", (event) => {
      draggedNameIndex = i;
      row.classList.add("is-dragging");
      event.dataTransfer.effectAllowed = "move";
      event.dataTransfer.setData("text/plain", String(i));
    });
    row.addEventListener("dragover", (event) => {
      event.preventDefault();
      row.classList.add("is-drop-target");
    });
    row.addEventListener("dragleave", () => row.classList.remove("is-drop-target"));
    row.addEventListener("drop", (event) => {
      event.preventDefault();
      row.classList.remove("is-drop-target");
      const droppedIndex = event.dataTransfer.getData("text/plain");
      const fromIndex = droppedIndex === "" ? draggedNameIndex : Number(droppedIndex);
      if (fromIndex === null || Number.isNaN(fromIndex)) return;
      moveNameToIndex(fromIndex, i);
      draggedNameIndex = null;
    });
    row.addEventListener("dragend", () => {
      draggedNameIndex = null;
      row.classList.remove("is-dragging", "is-drop-target");
    });

    const order = document.createElement("span");
    order.className = "name-row__order";
    order.textContent = formatNumber(i + 1);

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.checked = selectedIds.has(i);
    checkbox.addEventListener("change", () => {
      if (checkbox.checked) selectedIds.add(i);
      else selectedIds.delete(i);
      updateCounts();
    });

    const text = document.createElement("span");
    text.className = "name-row__name";
    text.textContent = names[i];

    const actions = document.createElement("div");
    actions.className = "name-row__actions";

    const deleteBtn = createRowButton("fa-trash", "حذف الاسم", () => deleteName(i), "name-row__btn--danger");

    actions.append(deleteBtn);
    row.append(order, checkbox, text, actions);
    fragment.appendChild(row);
  }

  virtualItems.appendChild(fragment);
}

function createRowButton(icon, label, onClick, extraClass = "") {
  const button = document.createElement("button");
  button.type = "button";
  button.className = `name-row__btn ${extraClass}`.trim();
  button.setAttribute("aria-label", label);
  button.title = label;
  button.innerHTML = `<i class="fa-solid ${icon}"></i>`;
  button.addEventListener("click", onClick);
  return button;
}

function setData(newNames) {
  names = Array.isArray(newNames) ? newNames : [];
  selectedIds.clear();
  virtualList.scrollTop = 0;
  resultCard.hidden = true;
  drawWheel();
  renderVirtualList();
}

function spinWheel() {
  if (spinning || names.length === 0) return;

  spinning = true;
  resultCard.hidden = true;

  const selectedIndex = Math.floor(Math.random() * names.length);
  const selectedName = names[selectedIndex];
  const sliceDeg = 360 / names.length;
  const pointerDeg = 180;
  const selectedMiddleDeg = selectedIndex * sliceDeg + sliceDeg / 2;
  const extraSpins = 6 + Math.floor(Math.random() * 3);
  const targetRotation = (extraSpins * 360) + pointerDeg - selectedMiddleDeg;

  rotation += targetRotation;
  canvas.style.transform = `rotate(${rotation}deg)`;

  if (!muted) spinTickSound();

  window.setTimeout(() => {
    const winner = selectedName;
    spinning = false;
    resultName.textContent = winner;
    resultCard.hidden = false;

    selectedIds.clear();
    addWinner(winner, selectedIndex + 1);
    removeWinnerFromNames(selectedIndex, winner);
    showCelebration(winner);
  }, spinDurationMs);
}

function addWinner(name, nameNumber) {
  winners.unshift({
    name,
    nameNumber,
    time: new Date()
  });

  renderWinners();
}

function removeWinnerFromNames(index, winnerName) {
  const winnerIndex = names[index] === winnerName ? index : names.indexOf(winnerName);

  if (winnerIndex !== -1) {
    names.splice(winnerIndex, 1);
    updateSelectionAfterDelete(winnerIndex);
  }

  clampVirtualScroll();
  drawWheel();
  renderVirtualList();
}

function deleteName(index) {
  if (index < 0 || index >= names.length) return;
  names.splice(index, 1);
  updateSelectionAfterDelete(index);
  resultCard.hidden = true;
  clampVirtualScroll();
  drawWheel();
  renderVirtualList();
}

function deleteSelectedNames() {
  if (!selectedIds.size) return;
  if (!confirm("هل تريد حذف كل الأسماء المحددة؟")) return;

  names = names.filter((_, index) => !selectedIds.has(index));
  selectedIds.clear();
  resultCard.hidden = true;
  clampVirtualScroll();
  drawWheel();
  renderVirtualList();
}

function moveNameToIndex(index, targetIndex) {
  if (index === targetIndex || index < 0 || targetIndex < 0 || index >= names.length || targetIndex >= names.length) return;

  const [movedName] = names.splice(index, 1);
  names.splice(targetIndex, 0, movedName);
  selectedIds = new Set([...selectedIds].map((selectedIndex) => {
    if (selectedIndex === index) return targetIndex;
    if (index < targetIndex && selectedIndex > index && selectedIndex <= targetIndex) return selectedIndex - 1;
    if (index > targetIndex && selectedIndex >= targetIndex && selectedIndex < index) return selectedIndex + 1;
    return selectedIndex;
  }));

  virtualList.scrollTop = Math.max(0, targetIndex * rowHeight - virtualList.clientHeight / 2);
  resultCard.hidden = true;
  drawWheel();
  renderVirtualList();
}

function updateSelectionAfterDelete(deletedIndex) {
  selectedIds = new Set([...selectedIds]
    .filter((index) => index !== deletedIndex)
    .map((index) => index > deletedIndex ? index - 1 : index));
}

function clampVirtualScroll() {
  virtualList.scrollTop = Math.min(
    virtualList.scrollTop,
    Math.max(0, names.length * rowHeight - virtualList.clientHeight)
  );
}

function renderWinners() {
  winnersList.replaceChildren();
  emptyResults.hidden = winners.length > 0;

  winners.forEach((winner, index) => {
    const li = document.createElement("li");
    const nameNumber = Number.isFinite(winner.nameNumber) ? winner.nameNumber : index + 1;
    li.innerHTML = `
      <strong>${formatNumber(nameNumber)}. ${escapeHtml(winner.name)}</strong>
      <span class="winner-time">${formatTime(winner.time)}</span>
    `;
    winnersList.appendChild(li);
  });
}

function formatTime(date) {
  return new Intl.DateTimeFormat("ar", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit"
  }).format(date);
}

function escapeHtml(text) {
  return String(text).replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  }[char]));
}

function spinTickSound() {
  try {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    oscillator.type = "triangle";
    oscillator.frequency.value = 520;
    gain.gain.value = 0.035;
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    oscillator.start();
    oscillator.frequency.exponentialRampToValueAtTime(880, audioContext.currentTime + .16);
    gain.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + .18);
    oscillator.stop(audioContext.currentTime + .2);
  } catch (_) {}
}

function celebrationSound() {
  if (muted) return;

  try {
    celebrationAudio.pause();
    celebrationAudio.currentTime = 0;
    celebrationAudio.play().catch(() => {});
  } catch (_) {}
}

function showCelebration(name) {
  stopCelebration();
  celebrationName.textContent = name;
  celebration.classList.add("is-show");
  celebration.setAttribute("aria-hidden", "false");
  celebrationSound();
  launchConfetti();
  confettiTimers = [
    setTimeout(launchConfetti, 900),
    setTimeout(launchConfetti, 1800)
  ];

  celebrationTimer = setTimeout(stopCelebration, celebrationDurationMs);
}

function stopCelebration() {
  celebration.classList.remove("is-show");
  celebration.setAttribute("aria-hidden", "true");

  if (celebrationTimer) {
    clearTimeout(celebrationTimer);
    celebrationTimer = null;
  }

  confettiTimers.forEach((timer) => clearTimeout(timer));
  confettiTimers = [];

  try {
    celebrationAudio.pause();
    celebrationAudio.currentTime = 0;
  } catch (_) {}

  document.querySelectorAll(".confetti").forEach((piece) => piece.remove());
}

function launchConfetti() {
  const confettiColors = ["#7c3aed", "#f59e0b", "#22c55e", "#06b6d4", "#ef4444", "#ec4899"];

  for (let i = 0; i < 120; i++) {
    const piece = document.createElement("span");
    piece.className = "confetti";
    piece.style.left = `${12 + Math.random() * 76}vw`;
    piece.style.top = `${16 + Math.random() * 68}vh`;
    piece.style.background = confettiColors[i % confettiColors.length];
    piece.style.setProperty("--duration", `${1.3 + Math.random() * 1.4}s`);
    piece.style.setProperty("--x-shift", `${(Math.random() - 0.5) * 520}px`);
    piece.style.setProperty("--y-shift", `${(Math.random() - 0.5) * 420}px`);
    piece.style.setProperty("--spin", `${360 + Math.random() * 1080}deg`);
    piece.style.animationDelay = `${Math.random() * 0.2}s`;
    document.body.appendChild(piece);
    piece.addEventListener("animationend", () => piece.remove(), { once: true });
  }
}

function addName(name) {
  const clean = String(name || "").trim();
  if (!clean) return;
  setData([clean, ...names]);
}

function importNames(text) {
  const imported = text
    .split(/\r?\n|,/)
    .map((name) => name.trim())
    .filter(Boolean);

  if (!imported.length) return;
  setData(imported.concat(names));
}

function shuffleNames() {
  const copy = names.slice();
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  setData(copy);
}

function clearNames() {
  if (!names.length) return;
  if (!confirm("هل تريد مسح جميع الأسماء؟")) return;
  setData([]);
}

function setAutoSpin(enabled) {
  if (autoTimer) {
    clearInterval(autoTimer);
    autoTimer = null;
  }

  if (enabled) {
    autoTimer = setInterval(() => {
      if (!spinning && !celebration.classList.contains("is-show")) spinWheel();
    }, 5000);
  }
}

function switchTab(tab) {
  panelTabs.forEach((button) => button.classList.toggle("active", button.dataset.tab === tab));
  dataPage.classList.toggle("active", tab === "data");
  resultsPage.classList.toggle("active", tab === "results");
}

function switchMode(mode) {
  modeTabs.forEach((button) => button.classList.toggle("active", button.dataset.mode === mode));
  modeHint.textContent = mode === "save"
    ? "وضع الحفظ: سجّل الدخول لحفظ القوائم والنتائج والرجوع لها لاحقًا."
    : "وضع الضيف: استخدم العجلة مباشرة بدون حفظ دائم.";
}

spinBtn.addEventListener("click", spinWheel);
centerSpinBtn.addEventListener("click", spinWheel);
virtualList.addEventListener("scroll", renderVirtualList);
window.addEventListener("resize", () => requestAnimationFrame(drawWheel));

addNameBtn.addEventListener("click", () => {
  if (typeof nameDialog.showModal === "function") {
    nameInput.value = "";
    nameDialog.showModal();
    setTimeout(() => nameInput.focus(), 50);
  } else {
    addName(prompt("اكتب الاسم"));
  }
});

confirmAddName.addEventListener("click", (event) => {
  event.preventDefault();
  addName(nameInput.value);
  nameDialog.close();
});

importInput.addEventListener("change", async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;
  const text = await file.text();
  importNames(text);
  event.target.value = "";
});

clearBtn.addEventListener("click", clearNames);
clearSelectedBtn.addEventListener("click", deleteSelectedNames);
shuffleBtn.addEventListener("click", shuffleNames);

selectAllNames.addEventListener("change", () => {
  selectedIds = selectAllNames.checked
    ? new Set(names.map((_, index) => index))
    : new Set();
  renderVirtualList();
});

clearResultsBtn.addEventListener("click", () => {
  winners = [];
  renderWinners();
});

fullscreenBtn.addEventListener("click", () => {
  const target = document.querySelector(".wheel-stage");
  if (!document.fullscreenElement) target?.requestFullscreen?.();
  else document.exitFullscreen?.();
});

soundBtn.addEventListener("click", () => {
  muted = !muted;
  if (muted) stopCelebration();
  soundBtn.innerHTML = muted
    ? '<i class="fa-solid fa-volume-xmark"></i>'
    : '<i class="fa-solid fa-volume-high"></i>';
});

celebrationCloseBtn.addEventListener("click", stopCelebration);

autoSpin.addEventListener("change", (event) => setAutoSpin(event.target.checked));

panelTabs.forEach((button) => {
  button.addEventListener("click", () => switchTab(button.dataset.tab));
});

modeTabs.forEach((button) => {
  button.addEventListener("click", () => switchMode(button.dataset.mode));
});

mobileMenuBtn.addEventListener("click", () => {
  const isOpen = mobileDrawer.classList.toggle("is-open");
  mobileMenuBtn.setAttribute("aria-expanded", String(isOpen));
  mobileMenuBtn.innerHTML = isOpen
    ? '<i class="fa-solid fa-xmark"></i>'
    : '<i class="fa-solid fa-bars"></i>';
});

mobileDrawer.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => {
    mobileDrawer.classList.remove("is-open");
    mobileMenuBtn.setAttribute("aria-expanded", "false");
    mobileMenuBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
  });
});

document.addEventListener("keydown", (event) => {
  if (event.code === "Space" && !["INPUT", "TEXTAREA"].includes(document.activeElement?.tagName)) {
    event.preventDefault();
    spinWheel();
  }
});

drawWheel();
renderVirtualList();
renderWinners();
