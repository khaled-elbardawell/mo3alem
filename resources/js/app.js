function setupAdImpressionTracking() {
  const advertisements = [...document.querySelectorAll("[data-ad-impression-url]")];

  if (!advertisements.length) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const visibilityTimers = new WeakMap();

  const isHalfVisible = (advertisement) => {
    const rectangle = advertisement.getBoundingClientRect();
    const visibleWidth = Math.max(
      0,
      Math.min(rectangle.right, window.innerWidth) - Math.max(rectangle.left, 0)
    );
    const visibleHeight = Math.max(
      0,
      Math.min(rectangle.bottom, window.innerHeight) - Math.max(rectangle.top, 0)
    );
    const advertisementArea = rectangle.width * rectangle.height;

    return advertisementArea > 0
      && (visibleWidth * visibleHeight) / advertisementArea >= 0.5;
  };

  const clearVisibilityTimer = (advertisement) => {
    const timer = visibilityTimers.get(advertisement);

    if (timer) {
      window.clearTimeout(timer);
      visibilityTimers.delete(advertisement);
    }
  };

  const scheduleImpression = (advertisement, observer) => {
    if (
      advertisement.dataset.adImpressionState
      || visibilityTimers.has(advertisement)
      || !isHalfVisible(advertisement)
    ) {
      return;
    }

    const image = advertisement.querySelector("img");

    if (image && (!image.complete || image.naturalWidth === 0)) {
      if (!image.dataset.adImpressionLoadListener) {
        image.dataset.adImpressionLoadListener = "waiting";
        image.addEventListener("load", () => {
          image.dataset.adImpressionLoadListener = "loaded";
          scheduleImpression(advertisement, observer);
        }, { once: true });
      }

      return;
    }

    const timer = window.setTimeout(() => {
      visibilityTimers.delete(advertisement);

      if (document.visibilityState === "visible" && isHalfVisible(advertisement)) {
        recordImpression(advertisement, observer);
      }
    }, 1000);

    visibilityTimers.set(advertisement, timer);
  };

  const recordImpression = async (advertisement, observer = null) => {
    if (advertisement.dataset.adImpressionState) return;

    clearVisibilityTimer(advertisement);
    advertisement.dataset.adImpressionState = "pending";

    try {
      const response = await fetch(advertisement.dataset.adImpressionUrl, {
        method: "POST",
        credentials: "same-origin",
        keepalive: true,
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": csrfToken
        }
      });

      if (!response.ok) throw new Error("Advertising impression was not recorded.");

      advertisement.dataset.adImpressionState = "recorded";
      observer?.unobserve(advertisement);
    } catch (_) {
      delete advertisement.dataset.adImpressionState;
    }
  };

  if (!("IntersectionObserver" in window)) {
    advertisements.forEach((advertisement) => recordImpression(advertisement));
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting || entry.intersectionRatio < 0.5) {
        clearVisibilityTimer(entry.target);
        return;
      }

      scheduleImpression(entry.target, observer);
    });
  }, {
    threshold: [0, 0.5]
  });

  advertisements.forEach((advertisement) => {
    observer.observe(advertisement);
  });
}

setupAdImpressionTracking();

const canvas = document.getElementById("wheelCanvas");
if (canvas) {
const ctx = canvas.getContext("2d");

const dataTabLabel = document.getElementById("dataTabLabel");
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
const emptyAddNameBtn = document.getElementById("emptyAddNameBtn");
const emptyImportNamesBtn = document.getElementById("emptyImportNamesBtn");
const nameDialog = document.getElementById("nameDialog");
const nameInput = document.getElementById("nameInput");
const confirmAddName = document.getElementById("confirmAddName");
const importInput = document.getElementById("importInput");
const importTrigger = document.getElementById("importTrigger");
const importLoader = document.getElementById("importLoader");
const importLoaderTitle = document.getElementById("importLoaderTitle");
const importLoaderProgress = document.getElementById("importLoaderProgress");
const importProgressTrack = document.getElementById("importProgressTrack");
const importProgressBar = document.getElementById("importProgressBar");
const clearBtn = document.getElementById("clearBtn");
const clearSelectedBtn = document.getElementById("clearSelectedBtn");
const shuffleBtn = document.getElementById("shuffleBtn");
const newWheelBtn = document.getElementById("newWheelBtn");
const toolbarFullscreenBtn = document.getElementById("toolbarFullscreenBtn");
const toolbarSoundBtn = document.getElementById("toolbarSoundBtn");
const autoSpin = document.getElementById("autoSpin");
const wheelWrap = document.getElementById("wheelWrap");
const wheelStage = document.querySelector(".wheel-stage");
const mobileMenuBtn = document.getElementById("mobileMenuBtn");
const mobileDrawer = document.getElementById("mobileDrawer");
const modeTabs = document.querySelectorAll(".mode-tabs button");
const modeHint = document.getElementById("modeHint");
const panelTabs = document.querySelectorAll(".panel-tabs button");
const dataPage = document.getElementById("dataPage");
const resultsPage = document.getElementById("resultsPage");
const winnersList = document.getElementById("winnersList");
const winnersTitle = document.getElementById("winnersTitle");
const resultsTabLabel = document.getElementById("resultsTabLabel");
const emptyResults = document.getElementById("emptyResults");
const emptyNames = document.getElementById("emptyNames");
const clearResultsBtn = document.getElementById("clearResultsBtn");
const restoreAllResultsBtn = document.getElementById("restoreAllResultsBtn");
const celebration = document.getElementById("celebration");
const celebrationName = document.getElementById("celebrationName");
const celebrationCloseBtn = document.getElementById("celebrationCloseBtn");
const celebrationAudio = new Audio("./assets/voice.m4a");
const siteHeader = document.querySelector(".site-header");
const navSectionLinks = document.querySelectorAll(
  '.nav-links a[href^="#"], .mobile-drawer a[href^="#"]'
);
const smoothScrollLinks = document.querySelectorAll(
  '.brand[href^="#"], .nav-links a[href^="#"], .mobile-drawer a[href^="#"]'
);
const backToTopBtn = document.getElementById("backToTopBtn");
const spinBtnText = spinBtn.querySelector(".spin-btn__text");
const faqSummaries = document.querySelectorAll(".faq-list summary");
const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const configElement = document.getElementById("wheelAppConfig");
const wheelConfig = JSON.parse(configElement?.dataset.config || "{}");
const cloudSavePanel = document.getElementById("cloudSavePanel");
const guestSaveActions = document.getElementById("guestSaveActions");
const wheelEditor = document.getElementById("wheelEditor");
const savedWheelsBrowser = document.getElementById("savedWheelsBrowser");
const createSavedWheelBtn = document.getElementById("createSavedWheelBtn");
const createSavedWheelDialog = document.getElementById("createSavedWheelDialog");
const createSavedWheelForm = document.getElementById("createSavedWheelForm");
const confirmCreateSavedWheelBtn = document.getElementById("confirmCreateSavedWheelBtn");
const createSavedWheelStatus = document.getElementById("createSavedWheelStatus");
const savedWheelsSearch = document.getElementById("savedWheelsSearch");
const savedWheelsCards = document.getElementById("savedWheelsCards");
const savedWheelsStatus = document.getElementById("savedWheelsStatus");
const savedWheelsEmpty = document.getElementById("savedWheelsEmpty");
const savedWheelsLoader = document.getElementById("savedWheelsLoader");
const backToSavedWheelsBtn = document.getElementById("backToSavedWheelsBtn");
const savedWheelActiveState = document.getElementById("savedWheelActiveState");
const activeSavedWheelTitle = document.getElementById("activeSavedWheelTitle");
const savedWheelTitle = document.getElementById("savedWheelTitle");
const saveStatus = document.getElementById("saveStatus");
const saveConflict = document.getElementById("saveConflict");
const reloadConflictBtn = document.getElementById("reloadConflictBtn");
const copyConflictBtn = document.getElementById("copyConflictBtn");
const localDraftKey = "nard-wheel-draft-v1";

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
let idleSpinFrame = null;
let lastIdleSpinTime = null;
let currentSavedWheel = null;
let serverConflictWheel = null;
let autosaveTimer = null;
let retryTimer = null;
let retryDelay = 2000;
let saveInFlightPromise = null;
let saveQueued = false;
let lastSavedSnapshot = null;
let savedWheelsCursor = null;
let savedWheelsHasMore = true;
let savedWheelsLoading = false;
let savedWheelsSearchTimer = null;
let savedWheelsAbortController = null;
let savedWheelsGeneration = 0;
let isHydrating = true;
let activeMode = wheelConfig.authenticated ? "save" : "guest";
let importingNames = false;

const rowHeight = 46;
const overscan = 8;
const maximumNames = 2000;
const maximumImportFileSize = 20 * 1024 * 1024;
const spinDurationMs = 5100;
const celebrationDurationMs = 5200;
const idleSpinSpeedDegPerSecond = 8;
const wheelSpinTransition = "transform 5s cubic-bezier(0.11, 0.75, 0.13, 1)";

celebrationAudio.preload = "auto";

function createInitialNames(count) {
  return Array.from({ length: count }, (_, i) => arabicNames[i % arabicNames.length]);
}

function readLocalDraft() {
  try {
    const draft = JSON.parse(localStorage.getItem(localDraftKey) || "null");
    return draft && Array.isArray(draft.names) ? draft : null;
  } catch {
    return null;
  }
}

function serializeResults() {
  return winners.map((winner) => ({
    name: winner.name,
    date: winner.time instanceof Date ? winner.time.toISOString() : winner.date || null
  }));
}

function deserializeResults(results) {
  if (!Array.isArray(results)) return [];

  return results
    .filter((result) => result && typeof result.name === "string")
    .map((result, index) => ({
      name: result.name,
      nameNumber: index + 1,
      time: result.date ? new Date(result.date) : new Date()
    }));
}

function persistLocalDraft(pending = false) {
  const draft = {
    names,
    results: serializeResults(),
    title: currentSavedWheel?.title || savedWheelTitle?.value?.trim() || "",
    savedWheelId: currentSavedWheel?.id || null,
    version: currentSavedWheel?.version || null,
    pending,
    updatedAt: new Date().toISOString()
  };

  try {
    localStorage.setItem(localDraftKey, JSON.stringify(draft));
  } catch {
    setSaveStatus("تعذر حفظ المسودة محليًا في هذا المتصفح.", "error");
  }
}

function hydrateState(state) {
  isHydrating = true;
  names = Array.isArray(state?.names) ? state.names.slice(0, maximumNames) : [];
  winners = deserializeResults(state?.results);
  selectedIds.clear();
  rotation = 0;
  setWheelRotation(0);
  resultCard.hidden = true;
  drawWheel();
  renderVirtualList();
  renderWinners();
  isHydrating = false;
  persistLocalDraft(false);
}

function hydrateInitialState() {
  const serverWheel = wheelConfig.savedWheel;
  const localDraft = readLocalDraft();

  if (serverWheel) {
    currentSavedWheel = serverWheel;
    hydrateState({ names: serverWheel.names, results: [] });
    lastSavedSnapshot = getSavedListSnapshot();
    setSaveWorkspace("active");
    return;
  }

  if (!wheelConfig.authenticated && localDraft) {
    hydrateState(localDraft);
    return;
  }

  if (wheelConfig.authenticated) {
    hydrateState({ names: [], results: [] });
    setSaveWorkspace("browser");
    loadSavedWheels({ reset: true });
    return;
  }

  hydrateState({ names, results: [] });
}

function setSaveStatus(message, tone = "neutral") {
  const statusElements = [
    saveStatus,
    createSavedWheelDialog?.open ? createSavedWheelStatus : null
  ].filter(Boolean);

  statusElements.forEach((statusElement) => {
    statusElement.textContent = message;
    statusElement.className = "m-0 min-h-5 text-xs font-bold";
    statusElement.classList.add(
      tone === "success" ? "text-emerald-700" :
        tone === "error" ? "text-red-700" :
          tone === "warning" ? "text-amber-700" : "text-slate-500"
    );
  });
}

function setSaveWorkspace(view = null) {
  const showActiveState = view === "active" && Boolean(currentSavedWheel);

  if (wheelConfig.authenticated) {
    wheelEditor?.classList.toggle("hidden", !showActiveState);
    savedWheelsBrowser?.classList.toggle("hidden", showActiveState);
  }

  if (showActiveState && activeSavedWheelTitle) {
    activeSavedWheelTitle.textContent = currentSavedWheel.title;
  }

  updateControlStates();
}

function updateSaveInterface(mode = "guest") {
  const showGuestSavePanel = !wheelConfig.authenticated && mode === "save";
  cloudSavePanel?.classList.toggle("hidden", !showGuestSavePanel);
  guestSaveActions?.classList.toggle("hidden", !showGuestSavePanel);
  if (!wheelConfig.authenticated) {
    wheelEditor?.classList.toggle("hidden", showGuestSavePanel);
  }

  if (currentSavedWheel) {
    setSaveStatus(`القائمة المفتوحة: ${currentSavedWheel.title}`, "success");
  }
}

function getSavedListSnapshot() {
  if (!currentSavedWheel) return null;

  return JSON.stringify({
    title: currentSavedWheel.title,
    names
  });
}

function markChanged(saveList = true) {
  if (isHydrating) return;
  persistLocalDraft(saveList && Boolean(currentSavedWheel));

  if (!saveList || !wheelConfig.verified || !currentSavedWheel || serverConflictWheel) return;

  clearTimeout(autosaveTimer);
  setSaveStatus("لديك تغييرات قيد الحفظ…");
  autosaveTimer = window.setTimeout(() => saveCurrentWheel(), 2000);
}

function setSaving(disabled) {
  if (confirmCreateSavedWheelBtn) confirmCreateSavedWheelBtn.disabled = disabled;
  if (backToSavedWheelsBtn) backToSavedWheelsBtn.disabled = disabled;
}

async function requestJson(url, options) {
  const response = await fetch(url, {
    ...options,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": wheelConfig.csrfToken,
      ...(options.headers || {})
    }
  });
  const body = await response.json().catch(() => ({}));
  return { response, body };
}

function setSavedWheelsStatus(message, tone = "neutral") {
  if (!savedWheelsStatus) return;

  savedWheelsStatus.textContent = message;
  savedWheelsStatus.className = "m-0 min-h-5 text-xs font-bold";
  savedWheelsStatus.classList.add(
    tone === "error" ? "text-red-700" :
      tone === "success" ? "text-emerald-700" : "text-slate-500"
  );
}

function setSavedWheelsLoading(loading) {
  savedWheelsLoading = loading;
  savedWheelsLoader?.classList.toggle("hidden", !loading);
  savedWheelsLoader?.classList.toggle("flex", loading);
}

function createSavedWheelCard(savedWheel) {
  const card = document.createElement("article");
  const information = document.createElement("div");
  const title = document.createElement("strong");
  const details = document.createElement("span");
  const actions = document.createElement("div");
  const loadButton = document.createElement("button");
  const deleteButton = document.createElement("button");

  card.className = "grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-violet-200 hover:shadow-md";
  card.dataset.savedWheelId = String(savedWheel.id);
  information.className = "grid min-w-0 gap-1";
  title.className = "truncate text-sm font-black text-slate-900";
  details.className = "text-xs font-bold text-slate-500";
  actions.className = "flex shrink-0 gap-2";
  loadButton.className = "inline-flex min-h-9 items-center gap-1.5 rounded-lg bg-violet-700 px-3 text-xs font-black text-white hover:bg-violet-800 disabled:cursor-wait disabled:opacity-60";
  deleteButton.className = "grid h-9 w-9 place-items-center rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50 disabled:cursor-wait disabled:opacity-60";

  title.textContent = savedWheel.title;
  details.textContent = `${formatNumber(savedWheel.names_count)} اسم`;
  loadButton.type = "button";
  loadButton.innerHTML = '<i class="fa-solid fa-folder-open" aria-hidden="true"></i><span>تحميل</span>';
  loadButton.addEventListener("click", () => loadSavedWheel(savedWheel.id, loadButton));
  deleteButton.type = "button";
  deleteButton.title = "حذف القائمة";
  deleteButton.setAttribute("aria-label", `حذف قائمة ${savedWheel.title}`);
  deleteButton.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>';
  deleteButton.addEventListener("click", () => deleteSavedWheel(savedWheel, card, deleteButton));

  information.append(title, details);
  actions.append(loadButton, deleteButton);
  card.append(information, actions);

  return card;
}

function beginNewSavedWheel() {
  if (!wheelConfig.verified || !createSavedWheelDialog) return;

  if (createSavedWheelStatus) createSavedWheelStatus.textContent = "";
  if (savedWheelTitle) savedWheelTitle.value = "";

  if (!createSavedWheelDialog.open) createSavedWheelDialog.showModal();
  window.setTimeout(() => savedWheelTitle?.focus(), 50);
}

async function loadSavedWheels({ reset = false } = {}) {
  if (!wheelConfig.routes.index || (!reset && (savedWheelsLoading || !savedWheelsHasMore))) return;

  if (reset) {
    savedWheelsAbortController?.abort();
    savedWheelsLoading = false;
    savedWheelsGeneration += 1;
    savedWheelsCursor = null;
    savedWheelsHasMore = true;
    savedWheelsCards?.replaceChildren();
  }

  const generation = savedWheelsGeneration;
  const controller = new AbortController();
  savedWheelsAbortController = controller;
  setSavedWheelsLoading(true);
  setSavedWheelsStatus("جارٍ تحميل قوائمك…");

  const url = new URL(wheelConfig.routes.index, window.location.origin);
  const search = savedWheelsSearch?.value.trim() || "";
  if (search) url.searchParams.set("search", search);
  if (savedWheelsCursor) url.searchParams.set("cursor", savedWheelsCursor);

  try {
    const { response, body } = await requestJson(url.toString(), {
      method: "GET",
      signal: controller.signal
    });

    if (!response.ok) throw new Error(body.message || "تعذر تحميل القوائم.");
    if (generation !== savedWheelsGeneration) return;

    const savedWheels = Array.isArray(body.data) ? body.data : [];
    const fragment = document.createDocumentFragment();
    savedWheels.forEach((savedWheel) => fragment.append(createSavedWheelCard(savedWheel)));
    savedWheelsCards?.append(fragment);
    savedWheelsCursor = body.next_cursor || null;
    savedWheelsHasMore = Boolean(body.has_more && savedWheelsCursor);

    const hasCards = Boolean(savedWheelsCards?.children.length);
    savedWheelsEmpty?.classList.toggle("hidden", hasCards);
    savedWheelsEmpty?.classList.toggle("grid", !hasCards);
    setSavedWheelsStatus(
      hasCards
        ? `${formatNumber(savedWheelsCards.children.length)} قائمة معروضة`
        : (search ? "لا توجد قوائم مطابقة للبحث." : "لا توجد قوائم محفوظة بعد."),
      hasCards ? "success" : "neutral"
    );
  } catch (error) {
    if (error.name !== "AbortError") {
      setSavedWheelsStatus(error.message || "تعذر تحميل القوائم. حاول مجددًا.", "error");
    }
  } finally {
    if (generation === savedWheelsGeneration) setSavedWheelsLoading(false);
  }
}

async function loadSavedWheel(savedWheelId, trigger = null) {
  if (!wheelConfig.routes.showBase) return;

  if (trigger) trigger.disabled = true;
  setSavedWheelsStatus("جارٍ فتح القائمة…");

  try {
    const { response, body } = await requestJson(
      `${wheelConfig.routes.showBase}/${savedWheelId}`,
      { method: "GET" }
    );

    if (!response.ok || !body.data) {
      throw new Error(body.message || "تعذر فتح القائمة.");
    }

    currentSavedWheel = body.data;
    serverConflictWheel = null;
    saveConflict?.classList.add("hidden");
    hydrateState({ names: currentSavedWheel.names, results: [] });
    lastSavedSnapshot = getSavedListSnapshot();
    setSaveWorkspace("active");
    setSaveStatus(`تم تحميل «${currentSavedWheel.title}». يمكنك إضافة أسماء أو تعديلها.`, "success");
    history.replaceState(null, "", `${location.pathname}?wheel=${currentSavedWheel.id}`);
  } catch (error) {
    setSavedWheelsStatus(error.message || "تعذر فتح القائمة. حاول مجددًا.", "error");
  } finally {
    if (trigger) trigger.disabled = false;
  }
}

async function createSavedWheel(title, initialNames = []) {
  if (!wheelConfig.verified || !wheelConfig.routes.store) return false;
  if (!title) {
    setSaveStatus("اكتب اسمًا للقائمة أولًا.", "error");
    savedWheelTitle?.focus();
    return false;
  }

  setSaving(true);
  setSaveStatus("جارٍ إنشاء القائمة…");

  try {
    const { response, body } = await requestJson(wheelConfig.routes.store, {
      method: "POST",
      body: JSON.stringify({ title, names: initialNames.slice(0, maximumNames) })
    });

    if (!response.ok) {
      const message = response.status === 429
        ? "وصلت إلى حد إنشاء القوائم مؤقتًا. حاول لاحقًا."
        : Object.values(body.errors || {}).flat()[0] || body.message || "تعذر إنشاء القائمة.";
      throw new Error(message);
    }

    currentSavedWheel = body.data;
    serverConflictWheel = null;
    hydrateState({ names: currentSavedWheel.names, results: [] });
    lastSavedSnapshot = getSavedListSnapshot();
    saveConflict?.classList.add("hidden");
    retryDelay = 2000;
    persistLocalDraft(false);
    setSaveWorkspace("active");
    setSaveStatus(`تم إنشاء «${currentSavedWheel.title}». أضف الأسماء للبدء.`, "success");
    history.replaceState(null, "", `${location.pathname}?wheel=${currentSavedWheel.id}`);

    return true;
  } catch (error) {
    setSaveStatus(error.message || "تعذر إنشاء القائمة.", "error");
    return false;
  } finally {
    setSaving(false);
  }
}

async function saveCurrentWheel() {
  clearTimeout(autosaveTimer);
  autosaveTimer = null;

  if (!wheelConfig.verified || !currentSavedWheel || serverConflictWheel) return false;

  if (saveInFlightPromise) {
    saveQueued = true;
    await saveInFlightPromise;
    return getSavedListSnapshot() === lastSavedSnapshot || saveCurrentWheel();
  }

  const snapshot = getSavedListSnapshot();
  if (snapshot === lastSavedSnapshot) {
    persistLocalDraft(false);
    return true;
  }

  clearTimeout(retryTimer);
  const savedWheelId = currentSavedWheel.id;
  const payload = {
    title: currentSavedWheel.title,
    names: names.slice(),
    version: currentSavedWheel.version
  };

  setSaving(true);
  setSaveStatus("جارٍ الحفظ التلقائي…");

  const cyclePromise = (async () => {
    try {
      const { response, body } = await requestJson(
        `${wheelConfig.routes.updateBase}/${savedWheelId}`,
        {
          method: "PATCH",
          body: JSON.stringify(payload)
        }
      );

      if (response.status === 409) {
        serverConflictWheel = body.data;
        saveConflict?.classList.remove("hidden");
        setSaveStatus("توجد نسخة أحدث من هذه القائمة على الخادم.", "warning");
        persistLocalDraft(true);
        return false;
      }

      if (!response.ok) {
        const message = Object.values(body.errors || {}).flat()[0] || body.message || "تعذر الحفظ.";
        throw new Error(message);
      }

      if (currentSavedWheel?.id !== savedWheelId) return false;

      currentSavedWheel = body.data;
      lastSavedSnapshot = snapshot;
      activeSavedWheelTitle.textContent = currentSavedWheel.title;
      serverConflictWheel = null;
      saveConflict?.classList.add("hidden");
      retryDelay = 2000;
      persistLocalDraft(getSavedListSnapshot() !== lastSavedSnapshot);
      setSaveStatus("تم حفظ جميع التغييرات.", "success");

      return true;
    } catch (error) {
      persistLocalDraft(true);
      setSaveStatus(error.message || "تعذر الحفظ. سنحاول مجددًا عند عودة الاتصال.", "error");
      retryTimer = window.setTimeout(() => {
        retryDelay = Math.min(retryDelay * 2, 60000);
        saveCurrentWheel();
      }, retryDelay);

      return false;
    } finally {
      setSaving(false);
    }
  })();

  saveInFlightPromise = cyclePromise;
  const saved = await cyclePromise;
  if (saveInFlightPromise === cyclePromise) saveInFlightPromise = null;

  const needsLatestState = saved
    && currentSavedWheel?.id === savedWheelId
    && (saveQueued || getSavedListSnapshot() !== lastSavedSnapshot);
  saveQueued = false;

  return needsLatestState && getSavedListSnapshot() !== lastSavedSnapshot
    ? saveCurrentWheel()
    : saved;
}

async function flushAutosave() {
  clearTimeout(autosaveTimer);
  autosaveTimer = null;

  if (saveInFlightPromise) {
    saveQueued = true;
    await saveInFlightPromise;
  }

  return getSavedListSnapshot() === lastSavedSnapshot || saveCurrentWheel();
}

async function deleteSavedWheel(savedWheel, card, trigger) {
  if (!confirm(`هل تريد حذف قائمة «${savedWheel.title}»؟ لا يمكن التراجع عن هذا الإجراء.`)) return;

  trigger.disabled = true;

  try {
    const { response, body } = await requestJson(
      `${wheelConfig.routes.updateBase}/${savedWheel.id}`,
      { method: "DELETE" }
    );

    if (!response.ok) throw new Error(body.message || "تعذر حذف القائمة.");

    card.remove();
    const hasCards = Boolean(savedWheelsCards?.children.length);
    savedWheelsEmpty?.classList.toggle("hidden", hasCards);
    savedWheelsEmpty?.classList.toggle("grid", !hasCards);
    setSavedWheelsStatus("تم حذف القائمة.", "success");

    if (!hasCards && savedWheelsHasMore) loadSavedWheels();
  } catch (error) {
    trigger.disabled = false;
    setSavedWheelsStatus(error.message || "تعذر حذف القائمة.", "error");
  }
}

async function returnToSavedWheels() {
  if (!currentSavedWheel) return;

  setSaving(true);
  const saved = await flushAutosave();
  setSaving(false);

  if (!saved && getSavedListSnapshot() !== lastSavedSnapshot) {
    setSaveStatus("تعذر حفظ آخر تعديل. ابقَ في القائمة وحاول مجددًا.", "error");
    return;
  }

  currentSavedWheel = null;
  serverConflictWheel = null;
  lastSavedSnapshot = null;
  winners = [];
  hydrateState({ names: [], results: [] });
  setSaveWorkspace("browser");
  history.replaceState(null, "", location.pathname);
  loadSavedWheels({ reset: true });
}

function recordActivity(event) {
  fetch(wheelConfig.routes.metrics, {
    method: "POST",
    keepalive: true,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": wheelConfig.csrfToken
    },
    body: JSON.stringify({ event })
  }).catch(() => {});
}

function formatNumber(number) {
  return new Intl.NumberFormat("en-GB").format(number);
}

function updateCounts() {
  dataTabLabel.textContent = `البيانات (${formatNumber(names.length)})`;
  selectedCountEl.textContent = `عدد المحدد (${formatNumber(selectedIds.size)})`;
  clearSelectedBtn.disabled = spinning || selectedIds.size === 0;
  selectAllNames.disabled = spinning || names.length === 0;
  selectAllNames.checked = names.length > 0 && selectedIds.size === names.length;
  selectAllNames.indeterminate = selectedIds.size > 0 && selectedIds.size < names.length;

  updateControlStates();
}

function setDisabled(elements, disabled) {
  elements.forEach((element) => {
    if (element) element.disabled = disabled;
  });
}

function updateControlStates() {
  const hasEditableWorkspace = wheelConfig.authenticated
    ? Boolean(currentSavedWheel)
    : activeMode === "guest";
  const hasNames = hasEditableWorkspace && names.length > 0;
  const controlsLocked = spinning || importingNames;
  const spinDisabled = controlsLocked || !hasNames;

  if (!hasNames) stopIdleSpin();
  else if (!spinning) startIdleSpin();

  setDisabled([spinBtn, centerSpinBtn], spinDisabled);
  setDisabled(
    [addNameBtn, emptyAddNameBtn, emptyImportNamesBtn, importInput],
    !hasEditableWorkspace || controlsLocked
  );
  setDisabled([clearBtn, shuffleBtn], !hasNames || controlsLocked);
  setDisabled([newWheelBtn], !hasEditableWorkspace || controlsLocked);
  setDisabled([clearResultsBtn], winners.length === 0 || controlsLocked);
  setDisabled([restoreAllResultsBtn], winners.length === 0 || controlsLocked);
  syncNameRowControls();

  spinBtn.classList.toggle("is-loading", spinning);
  centerSpinBtn.classList.toggle("is-loading", spinning);
  wheelStage?.setAttribute("aria-busy", String(spinning));
  importTrigger?.setAttribute("aria-disabled", String(!hasEditableWorkspace || controlsLocked));
  importTrigger?.classList.toggle("pointer-events-none", !hasEditableWorkspace || controlsLocked);
  importTrigger?.classList.toggle("opacity-60", !hasEditableWorkspace || controlsLocked);

  if (spinBtnText) {
    spinBtnText.textContent = spinning ? "جاري اللف..." : "لف العجلة";
  }

  spinBtn.setAttribute(
    "aria-label",
    spinning ? "العجلة تدور الآن" : "لف العجلة واختيار اسم"
  );
  centerSpinBtn.setAttribute(
    "aria-label",
    spinning ? "العجلة تدور الآن" : "لف العجلة"
  );
}

function syncNameRowControls() {
  virtualItems.querySelectorAll("input, button").forEach((control) => {
    control.disabled = spinning;
  });

  virtualItems.querySelectorAll(".name-row").forEach((row) => {
    row.draggable = !spinning;
  });
}

function updateWheelDensityClass() {
  wheelWrap.classList.toggle("is-few", names.length > 0 && names.length <= 24);
  wheelWrap.classList.toggle("is-many", names.length >= 500);
}

function setWheelRotation(degrees, transition = "none") {
  canvas.style.transition = transition;
  canvas.style.transform = `rotate(${degrees}deg)`;
}

function normalizeDegrees(degrees) {
  return ((degrees % 360) + 360) % 360;
}

function startIdleSpin() {
  if (prefersReducedMotion || idleSpinFrame || spinning || names.length === 0) return;

  lastIdleSpinTime = null;

  const tick = (timestamp) => {
    if (spinning || names.length === 0) {
      idleSpinFrame = null;
      lastIdleSpinTime = null;
      return;
    }

    if (lastIdleSpinTime !== null) {
      const elapsedSeconds = (timestamp - lastIdleSpinTime) / 1000;
      rotation += idleSpinSpeedDegPerSecond * elapsedSeconds;
      setWheelRotation(rotation);
    }

    lastIdleSpinTime = timestamp;
    idleSpinFrame = requestAnimationFrame(tick);
  };

  idleSpinFrame = requestAnimationFrame(tick);
}

function stopIdleSpin() {
  if (idleSpinFrame) {
    cancelAnimationFrame(idleSpinFrame);
    idleSpinFrame = null;
  }

  lastIdleSpinTime = null;
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
  const isEmpty = names.length === 0;

  virtualSpacer.style.height = "0px";
  virtualItems.style.transform = "none";
  virtualItems.replaceChildren();
  virtualList.classList.toggle("is-empty", isEmpty);
  emptyNames.hidden = !isEmpty;

  if (isEmpty) {
    return;
  }

  const fragment = document.createDocumentFragment();

  for (let i = 0; i < names.length; i++) {
    const row = document.createElement("div");
    row.className = "name-row";
    row.setAttribute("role", "option");
    row.draggable = !spinning;
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
    checkbox.disabled = spinning;
    checkbox.addEventListener("change", () => {
      if (checkbox.checked) selectedIds.add(i);
      else selectedIds.delete(i);
      updateCounts();
    });

    const text = document.createElement("span");
    text.className = "name-row__name min-w-0 flex-1 overflow-hidden text-ellipsis whitespace-nowrap font-bold text-[#344054]";
    text.textContent = names[i];

    const actions = document.createElement("div");
    actions.className = "name-row__actions inline-flex shrink-0 items-center gap-[5px] max-[620px]:gap-[3px]";

    const deleteBtn = createRowButton("fa-trash", "حذف الاسم", () => deleteName(i));
    deleteBtn.disabled = spinning;

    actions.append(deleteBtn);
    row.append(order, checkbox, text, actions);
    fragment.appendChild(row);
  }

  virtualItems.appendChild(fragment);
}

function createRowButton(icon, label, onClick) {
  const button = document.createElement("button");
  button.type = "button";
  button.className = "name-row__btn grid h-[30px] w-[30px] cursor-pointer place-items-center rounded-[9px] border border-[#e7e2f0] bg-white text-[13px] text-[#ef4444] transition-[background,border-color,color,transform] duration-200 enabled:hover:-translate-y-px enabled:hover:border-[rgba(239,68,68,0.25)] enabled:hover:bg-[#fff1f2] disabled:cursor-not-allowed disabled:opacity-[0.38] max-[620px]:h-7 max-[620px]:w-7 max-[620px]:rounded-lg max-[620px]:text-xs";
  button.setAttribute("aria-label", label);
  button.title = label;
  button.innerHTML = `<i class="fa-solid ${icon}"></i>`;
  button.addEventListener("click", onClick);
  return button;
}

function setData(newNames) {
  names = Array.isArray(newNames)
    ? newNames.map((name) => String(name).trim().slice(0, 120)).filter(Boolean).slice(0, maximumNames)
    : [];
  selectedIds.clear();
  virtualList.scrollTop = 0;
  resultCard.hidden = true;
  drawWheel();
  renderVirtualList();
  markChanged();
}

function spinWheel() {
  if (spinning || names.length === 0) return;

  recordActivity("spin");
  stopIdleSpin();
  spinning = true;
  updateControlStates();
  resultCard.hidden = true;

  const selectedIndex = Math.floor(Math.random() * names.length);
  const selectedName = names[selectedIndex];
  const sliceDeg = 360 / names.length;
  const pointerDeg = 180;
  const selectedMiddleDeg = selectedIndex * sliceDeg + sliceDeg / 2;
  const extraSpins = 6 + Math.floor(Math.random() * 3);
  const targetDeg = normalizeDegrees(pointerDeg - selectedMiddleDeg);
  const currentDeg = normalizeDegrees(rotation);
  const targetRotation = (extraSpins * 360) + normalizeDegrees(targetDeg - currentDeg);

  setWheelRotation(rotation);
  void canvas.offsetWidth;
  rotation += targetRotation;
  setWheelRotation(rotation, wheelSpinTransition);

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
    updateControlStates();
    startIdleSpin();
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
  markChanged();
}

function deleteName(index) {
  if (index < 0 || index >= names.length) return;
  names.splice(index, 1);
  updateSelectionAfterDelete(index);
  resultCard.hidden = true;
  clampVirtualScroll();
  drawWheel();
  renderVirtualList();
  markChanged();
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
  markChanged();
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
  markChanged();
}

function updateSelectionAfterDelete(deletedIndex) {
  selectedIds = new Set([...selectedIds]
    .filter((index) => index !== deletedIndex)
    .map((index) => index > deletedIndex ? index - 1 : index));
}

function updateSelectionAfterInsert(insertIndex) {
  selectedIds = new Set([...selectedIds]
    .map((index) => index >= insertIndex ? index + 1 : index));
}

function clampVirtualScroll() {
  virtualList.scrollTop = Math.min(
    virtualList.scrollTop,
    Math.max(0, names.length * rowHeight - virtualList.clientHeight)
  );
}

function getRestoreIndex(winner) {
  const originalIndex = Number.isFinite(winner?.nameNumber) ? winner.nameNumber - 1 : names.length;
  return Math.min(Math.max(originalIndex, 0), names.length);
}

function restoreWinnerToNames(winner) {
  if (!winner?.name) return;

  const restoreIndex = getRestoreIndex(winner);
  names.splice(restoreIndex, 0, winner.name);
  updateSelectionAfterInsert(restoreIndex);
}

function refreshWheelAndNames() {
  clampVirtualScroll();
  drawWheel();
  renderVirtualList();
}

function restoreWinner(index) {
  if (spinning || index < 0 || index >= winners.length) return;

  const [winner] = winners.splice(index, 1);
  restoreWinnerToNames(winner);
  refreshWheelAndNames();
  renderWinners();
  markChanged();
}

function restoreAllWinners() {
  if (spinning || winners.length === 0) return;

  winners
    .slice()
    .sort((a, b) => {
      const aNumber = Number.isFinite(a.nameNumber) ? a.nameNumber : Number.MAX_SAFE_INTEGER;
      const bNumber = Number.isFinite(b.nameNumber) ? b.nameNumber : Number.MAX_SAFE_INTEGER;
      return aNumber - bNumber || a.time - b.time;
    })
    .forEach(restoreWinnerToNames);

  winners = [];
  refreshWheelAndNames();
  renderWinners();
  markChanged();
}

function renderWinners() {
  winnersList.replaceChildren();
  const isEmpty = winners.length === 0;
  const winnersCount = formatNumber(winners.length);

  winnersTitle.textContent = `الفائزون بالترتيب (${winnersCount})`;
  resultsTabLabel.textContent = `النتائج (${winnersCount})`;
  emptyResults.hidden = !isEmpty;
  winnersList.hidden = isEmpty;
  winnersList.classList.toggle("is-empty", isEmpty);
  updateControlStates();

  winners.forEach((winner, index) => {
    const li = document.createElement("li");
    const nameNumber = Number.isFinite(winner.nameNumber) ? winner.nameNumber : index + 1;

    const entry = document.createElement("div");
    entry.className = "winner-entry";
    entry.innerHTML = `
      <strong>${formatNumber(nameNumber)}. ${escapeHtml(winner.name)}</strong>
      <span class="winner-time">${formatTime(winner.time)}</span>
    `;

    const restoreBtn = document.createElement("button");
    restoreBtn.type = "button";
    restoreBtn.className = "winner-restore-btn";
    restoreBtn.disabled = spinning;
    restoreBtn.title = "إرجاع للقائمة";
    restoreBtn.setAttribute("aria-label", `إرجاع ${winner.name} للقائمة`);
    restoreBtn.innerHTML = '<i class="fa-solid fa-rotate-left"></i>';
    restoreBtn.addEventListener("click", () => restoreWinner(index));

    li.append(entry, restoreBtn);
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
    setTimeout(launchConfetti, 360),
    setTimeout(launchConfetti, 760),
    setTimeout(launchConfetti, 1240),
    setTimeout(launchConfetti, 1760)
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
  const bursts = Array.from({ length: 7 }, () => ({
    left: 12 + Math.random() * 76,
    top: 16 + Math.random() * 58
  }));

  bursts.forEach((burst, burstIndex) => {
    const piecesCount = 42 + Math.floor(Math.random() * 20);

    for (let i = 0; i < piecesCount; i++) {
      const piece = document.createElement("span");
      const angle = (Math.PI * 2 * i) / piecesCount + (Math.random() - 0.5) * 0.55;
      const distance = 90 + Math.random() * 260;
      const xShift = Math.cos(angle) * distance;
      const yShift = Math.sin(angle) * distance;

      piece.className = "confetti pointer-events-none fixed z-[109] h-[18px] w-2.5 rounded-[3px] opacity-0 animate-confetti-burst";
      piece.style.left = `${burst.left}vw`;
      piece.style.top = `${burst.top}vh`;
      piece.style.background = confettiColors[(i + burstIndex) % confettiColors.length];
      piece.style.setProperty("--duration", `${1.15 + Math.random() * 0.9}s`);
      piece.style.setProperty("--x-shift", `${xShift}px`);
      piece.style.setProperty("--y-shift", `${yShift}px`);
      piece.style.setProperty("--spin", `${540 + Math.random() * 1260}deg`);
      piece.style.animationDelay = `${Math.random() * 0.12}s`;
      document.body.appendChild(piece);
      piece.addEventListener("animationend", () => piece.remove(), { once: true });
    }
  });
}

function addName(name) {
  if (
    (wheelConfig.authenticated && !currentSavedWheel)
    || (!wheelConfig.authenticated && activeMode !== "guest")
  ) {
    return;
  }

  const clean = String(name || "").trim().slice(0, 120);
  if (!clean) return;
  if (names.length >= maximumNames) {
    alert(`الحد الأقصى للقائمة هو ${maximumNames} اسم.`);
    return;
  }
  setData([clean, ...names]);
}

function waitForNextPaint() {
  return new Promise((resolve) => window.requestAnimationFrame(resolve));
}

function showImportLoader(fileName) {
  importingNames = true;
  importLoader?.classList.remove("hidden");
  importLoader?.classList.add("flex");
  importLoader?.setAttribute("aria-hidden", "false");
  importTrigger?.setAttribute("aria-disabled", "true");
  importTrigger?.classList.add("pointer-events-none", "opacity-60");
  document.body.setAttribute("aria-busy", "true");
  if (importLoaderTitle) importLoaderTitle.textContent = "جارٍ قراءة الملف…";
  if (importLoaderProgress) importLoaderProgress.textContent = fileName;
  updateImportProgress(0);
}

function updateImportProgress(progress, message = null) {
  const normalizedProgress = Math.max(0, Math.min(100, Math.round(progress)));
  if (importProgressBar) importProgressBar.style.width = `${normalizedProgress}%`;
  importProgressTrack?.setAttribute("aria-valuenow", String(normalizedProgress));
  if (message && importLoaderProgress) importLoaderProgress.textContent = message;
}

function hideImportLoader() {
  importingNames = false;
  importLoader?.classList.add("hidden");
  importLoader?.classList.remove("flex");
  importLoader?.setAttribute("aria-hidden", "true");
  importTrigger?.setAttribute("aria-disabled", "false");
  importTrigger?.classList.remove("pointer-events-none", "opacity-60");
  document.body.removeAttribute("aria-busy");
  updateControlStates();
}

function parseNamesChunk(text, state) {
  for (let index = 0; index < text.length && state.names.length < state.limit; index++) {
    const character = text[index];

    if (character === "," || character === "\r" || character === "\n") {
      const importedName = state.currentName.trim().slice(0, 120);
      if (importedName) state.names.push(importedName);
      state.currentName = "";
      continue;
    }

    if (state.currentName.length < 240) {
      state.currentName += character;
    }
  }
}

function finishNamesParsing(state) {
  if (state.names.length >= state.limit) return;

  const importedName = state.currentName.trim().slice(0, 120);
  if (importedName) state.names.push(importedName);
  state.currentName = "";
}

async function readNamesFromFile(file, limit) {
  const state = { names: [], currentName: "", limit };
  const decoder = new TextDecoder();
  let bytesRead = 0;

  if (typeof file.stream !== "function") {
    const text = await file.text();

    for (let offset = 0; offset < text.length && state.names.length < limit; offset += 65536) {
      parseNamesChunk(text.slice(offset, offset + 65536), state);
      updateImportProgress(
        (offset / Math.max(text.length, 1)) * 100,
        `تم تجهيز ${formatNumber(state.names.length)} من ${formatNumber(limit)} اسم`
      );
      await waitForNextPaint();
    }

    finishNamesParsing(state);
    updateImportProgress(100, `تمت قراءة ${formatNumber(state.names.length)} اسم`);
    return state.names;
  }

  const reader = file.stream().getReader();

  while (state.names.length < limit) {
    const { done, value } = await reader.read();
    if (done) break;

    bytesRead += value.byteLength;
    parseNamesChunk(decoder.decode(value, { stream: true }), state);

    const progress = file.size ? (bytesRead / file.size) * 100 : 0;
    updateImportProgress(
      progress,
      `تم تجهيز ${formatNumber(state.names.length)} من ${formatNumber(limit)} اسم`
    );
    await waitForNextPaint();
  }

  if (state.names.length >= limit) {
    await reader.cancel();
  } else {
    parseNamesChunk(decoder.decode(), state);
    finishNamesParsing(state);
  }

  updateImportProgress(100, `تمت قراءة ${formatNumber(state.names.length)} اسم`);
  return state.names;
}

async function importNamesFromFile(file) {
  const availableSlots = maximumNames - names.length;
  const extension = file.name.split(".").pop()?.toLowerCase();

  if (!["txt", "csv"].includes(extension)) {
    alert("اختر ملفًا بصيغة TXT أو CSV.");
    return;
  }

  if (file.size > maximumImportFileSize) {
    alert("حجم الملف أكبر من 20 ميجابايت. قسّم الملف ثم حاول مجددًا.");
    return;
  }

  if (availableSlots <= 0) {
    alert(`القائمة تحتوي بالفعل على الحد الأقصى وهو ${maximumNames} اسم.`);
    return;
  }

  showImportLoader(file.name);
  await waitForNextPaint();

  try {
    const importedNames = await readNamesFromFile(file, availableSlots);

    if (!importedNames.length) {
      throw new Error("لم يتم العثور على أسماء صالحة داخل الملف.");
    }

    setData(importedNames.concat(names));
    recordActivity("import");
    if (importLoaderTitle) importLoaderTitle.textContent = "اكتمل الاستيراد";
    updateImportProgress(100, `تمت إضافة ${formatNumber(importedNames.length)} اسم`);
    await new Promise((resolve) => window.setTimeout(resolve, 450));
  } catch (error) {
    alert(error.message || "تعذر قراءة الملف. تأكد من صيغته وحاول مجددًا.");
  } finally {
    hideImportLoader();
  }
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

function startNewWheel() {
  if (spinning) return;

  if (wheelConfig.authenticated) {
    returnToSavedWheels();
    return;
  }

  const hasCurrentData = names.length > 0 || winners.length > 0;
  if (
    hasCurrentData &&
    !confirm("هل تريد بدء عجلة جديدة؟ سيتم مسح الأسماء والنتائج الحالية.")
  ) {
    return;
  }

  stopCelebration();
  selectedIds.clear();
  winners = [];
  currentSavedWheel = null;
  serverConflictWheel = null;
  if (savedWheelTitle) savedWheelTitle.value = "";
  saveConflict?.classList.add("hidden");
  setSaveWorkspace(null);
  rotation = 0;
  setWheelRotation(0);
  resultCard.hidden = true;
  autoSpin.checked = false;
  setAutoSpin(false);
  setData([]);
  renderWinners();
  switchTab("data");
}

function toggleFullscreen() {
  const target = document.querySelector(".wheel-stage");
  if (!document.fullscreenElement) target?.requestFullscreen?.();
  else document.exitFullscreen?.();
}

function updateSoundControls() {
  const icon = muted ? "fa-volume-xmark" : "fa-volume-high";

  if (toolbarSoundBtn) {
    toolbarSoundBtn.querySelector(".wheel-tool__icon").innerHTML =
      `<i class="fa-solid ${icon}"></i>`;
    toolbarSoundBtn.querySelector("strong").textContent =
      muted ? "تشغيل الصوت" : "كتم الصوت";
    toolbarSoundBtn.setAttribute("aria-pressed", String(muted));
    toolbarSoundBtn.classList.toggle("is-active", muted);
  }
}

function toggleSound() {
  muted = !muted;
  if (muted) stopCelebration();
  updateSoundControls();
}

function updateFullscreenControls() {
  const isFullscreen = Boolean(document.fullscreenElement);
  const icon = isFullscreen ? "fa-compress" : "fa-expand";

  if (toolbarFullscreenBtn) {
    toolbarFullscreenBtn.querySelector(".wheel-tool__icon").innerHTML =
      `<i class="fa-solid ${icon}"></i>`;
    toolbarFullscreenBtn.querySelector("strong").textContent =
      isFullscreen ? "تصغير" : "تكبير";
    toolbarFullscreenBtn.classList.toggle("is-active", isFullscreen);
  }
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
  panelTabs.forEach((button) => {
    const isActive = button.dataset.tab === tab;
    button.classList.toggle("active", isActive);
    button.setAttribute("aria-selected", String(isActive));
    button.tabIndex = isActive ? 0 : -1;
  });

  const showData = tab === "data";
  dataPage.classList.toggle("active", showData);
  dataPage.hidden = !showData;
  resultsPage.classList.toggle("active", !showData);
  resultsPage.hidden = showData;
}

function switchMode(mode) {
  const selectedMode = wheelConfig.authenticated ? "save" : mode;
  activeMode = selectedMode;

  modeTabs.forEach((button) => {
    const isActive = button.dataset.mode === selectedMode;
    button.classList.toggle("active", isActive);
    button.setAttribute("aria-pressed", String(isActive));
  });
  modeHint.textContent = selectedMode === "save"
    ? (wheelConfig.authenticated
      ? (wheelConfig.verified
        ? "وضع الحفظ: أنشئ قائمة جديدة أو حمّل إحدى قوائمك، ثم أضف الأسماء يدويًا أو من ملف."
        : "وضع الحفظ: يمكنك تحميل قوائمك، وفعّل بريدك لحفظ التعديلات.")
      : "وضع الحفظ: سجّل الدخول لحفظ مسودتك وفتح قوائمك من أي جهاز.")
    : "وضع الضيف: استخدم العجلة مباشرة بدون حفظ دائم.";
  updateSaveInterface(selectedMode);
  updateControlStates();
}

function getKeyboardStep(event) {
  const rtlStep = document.documentElement.dir === "rtl" ? -1 : 1;

  if (event.key === "ArrowRight") return rtlStep;
  if (event.key === "ArrowLeft") return -rtlStep;
  if (event.key === "ArrowDown") return 1;
  if (event.key === "ArrowUp") return -1;
  return 0;
}

function focusIndexedElement(elements, index) {
  const nextIndex = (index + elements.length) % elements.length;
  elements[nextIndex]?.focus();
  return nextIndex;
}

function setupButtonGroupKeyboard(buttons, activateButton) {
  const buttonList = Array.from(buttons);

  buttonList.forEach((button, index) => {
    button.addEventListener("keydown", (event) => {
      if (event.key === "Home" || event.key === "End") {
        event.preventDefault();
        const targetIndex = event.key === "Home" ? 0 : buttonList.length - 1;
        buttonList[targetIndex].focus();
        activateButton(buttonList[targetIndex]);
        return;
      }

      const step = getKeyboardStep(event);
      if (!step) return;

      event.preventDefault();
      const targetIndex = focusIndexedElement(buttonList, index + step);
      activateButton(buttonList[targetIndex]);
    });
  });
}

function setupFaqKeyboard() {
  const summaries = Array.from(faqSummaries);

  summaries.forEach((summary, index) => {
    summary.addEventListener("keydown", (event) => {
      if (!["ArrowUp", "ArrowDown", "Home", "End"].includes(event.key)) return;

      event.preventDefault();

      if (event.key === "Home") {
        summaries[0]?.focus();
        return;
      }

      if (event.key === "End") {
        summaries[summaries.length - 1]?.focus();
        return;
      }

      const step = event.key === "ArrowDown" ? 1 : -1;
      focusIndexedElement(summaries, index + step);
    });
  });
}

function setupFaqToggleAnimations() {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  faqSummaries.forEach((summary) => {
    const details = summary.parentElement;
    const content = details?.querySelector("p");

    if (!details || !content) return;

    summary.addEventListener("click", (event) => {
      if (reduceMotion) return;

      event.preventDefault();
      details.classList.remove("is-closing");

      if (details.open) {
        details.classList.add("is-closing");
        content.animate([
          { height: `${content.scrollHeight}px`, opacity: 1, transform: "translateY(0)" },
          { height: "0px", opacity: 0, transform: "translateY(-6px)" }
        ], {
          duration: 240,
          easing: "ease"
        }).finished.finally(() => {
          details.open = false;
          details.classList.remove("is-closing");
          content.style.height = "";
        });
        return;
      }

      details.open = true;
      content.animate([
        { height: "0px", opacity: 0, transform: "translateY(-6px)" },
        { height: `${content.scrollHeight}px`, opacity: 1, transform: "translateY(0)" }
      ], {
        duration: 280,
        easing: "ease"
      }).finished.finally(() => {
        content.style.height = "";
      });
    });
  });
}

function isInteractiveElement(element) {
  return Boolean(element?.closest?.(
    "a, button, input, select, textarea, summary, [role='tab'], [contenteditable='true']"
  ));
}

function setupScrollAnimations() {
  const animatedElements = document.querySelectorAll([
    ".ad-link",
    ".wheel-toolbar",
    ".wheel-stage",
    ".names-panel",
    ".stats-section h2",
    ".stat-card",
    ".section-heading",
    ".step-card",
    ".save-mode",
    ".use-card",
    ".faq-list details"
  ].join(", "));

  if (!animatedElements.length) return;

  document.documentElement.classList.add("animate-ready");

  animatedElements.forEach((element, index) => {
    element.classList.add("reveal-on-scroll");
    element.style.setProperty("--reveal-delay", `${Math.min(index % 6, 5) * 70}ms`);
  });

  if (!("IntersectionObserver" in window)) {
    animatedElements.forEach((element) => element.classList.add("is-visible"));
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add("is-visible");
      observer.unobserve(entry.target);
    });
  }, {
    threshold: 0.16,
    rootMargin: "0px 0px -8% 0px"
  });

  animatedElements.forEach((element) => observer.observe(element));
}

function getHeaderOffset() {
  return siteHeader?.getBoundingClientRect().height || 0;
}

function getAnchorHash(link) {
  const href = link.getAttribute("href");
  return href && href.startsWith("#") && href.length > 1 ? href : null;
}

function getHashTarget(hash) {
  try {
    return document.getElementById(decodeURIComponent(hash.slice(1)));
  } catch (_) {
    return null;
  }
}

function scrollToTarget(target, behavior = "smooth") {
  const top = target.getBoundingClientRect().top + window.scrollY - getHeaderOffset();
  window.scrollTo({
    top: Math.max(0, top),
    behavior
  });
}

function closeMobileDrawer() {
  mobileDrawer.classList.remove("is-open");
  mobileMenuBtn.setAttribute("aria-expanded", "false");
  mobileMenuBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
}

function setActiveNav(hash) {
  navSectionLinks.forEach((link) => {
    const isActive = getAnchorHash(link) === hash;
    link.classList.toggle("active", isActive);

    if (isActive) link.setAttribute("aria-current", "page");
    else link.removeAttribute("aria-current");
  });
}

const scrollSpySections = Array.from(new Set(
  Array.from(navSectionLinks)
    .map(getAnchorHash)
    .filter(Boolean)
)).map((hash) => ({
  hash,
  target: getHashTarget(hash)
})).filter((section) => section.target);

function updateScrollState() {
  backToTopBtn?.classList.toggle("is-visible", window.scrollY > 360);

  if (clickedScrollHash) {
    setActiveNav(clickedScrollHash);
    scheduleClickedScrollRelease();
    return;
  }

  const activationLine = getHeaderOffset() + 8;
  let currentSection = scrollSpySections[0];

  scrollSpySections.forEach((section) => {
    if (section.target.getBoundingClientRect().top <= activationLine) {
      currentSection = section;
    }
  });

  if (currentSection) setActiveNav(currentSection.hash);
}

let scrollStateQueued = false;
let clickedScrollHash = null;
let clickedScrollTimer = null;

function scheduleClickedScrollRelease() {
  if (clickedScrollTimer) clearTimeout(clickedScrollTimer);

  clickedScrollTimer = setTimeout(() => {
    clickedScrollHash = null;
    updateScrollState();
  }, 180);
}

function holdClickedActiveLink(hash) {
  clickedScrollHash = hash;
  setActiveNav(hash);
  scheduleClickedScrollRelease();
}

function requestScrollStateUpdate() {
  if (scrollStateQueued) return;
  scrollStateQueued = true;
  requestAnimationFrame(() => {
    updateScrollState();
    scrollStateQueued = false;
  });
}

window.addEventListener("scrollend", () => {
  clickedScrollHash = null;
  updateScrollState();
});

smoothScrollLinks.forEach((link) => {
  link.addEventListener("click", (event) => {
    const hash = getAnchorHash(link);
    const target = hash ? getHashTarget(hash) : null;

    if (!hash || !target) return;

    event.preventDefault();
    holdClickedActiveLink(hash);
    closeMobileDrawer();
    scrollToTarget(target);
    history.pushState(null, "", hash);
  });
});

backToTopBtn?.addEventListener("click", () => {
  holdClickedActiveLink("#home");
  window.scrollTo({ top: 0, behavior: "smooth" });
  history.pushState(null, "", "#home");
});

spinBtn.addEventListener("click", spinWheel);
centerSpinBtn.addEventListener("click", spinWheel);
virtualList.addEventListener("scroll", renderVirtualList);
window.addEventListener("resize", () => {
  requestAnimationFrame(drawWheel);
  requestScrollStateUpdate();
});
window.addEventListener("scroll", requestScrollStateUpdate, { passive: true });

addNameBtn.addEventListener("click", () => {
  if (typeof nameDialog.showModal === "function") {
    nameInput.value = "";
    nameDialog.showModal();
    setTimeout(() => nameInput.focus(), 50);
  } else {
    addName(prompt("اكتب الاسم"));
  }
});
emptyAddNameBtn?.addEventListener("click", () => addNameBtn.click());
emptyImportNamesBtn?.addEventListener("click", () => importInput.click());

confirmAddName.addEventListener("click", (event) => {
  event.preventDefault();
  addName(nameInput.value);
  nameDialog.close();
});
nameInput.addEventListener("keydown", (event) => {
  if (event.key !== "Enter" || event.isComposing || event.keyCode === 229) return;

  event.preventDefault();
  if (!confirmAddName.disabled) confirmAddName.click();
});

importInput.addEventListener("change", async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  try {
    await importNamesFromFile(file);
  } finally {
    event.target.value = "";
  }
});

clearBtn.addEventListener("click", clearNames);
clearSelectedBtn.addEventListener("click", deleteSelectedNames);
shuffleBtn.addEventListener("click", shuffleNames);
newWheelBtn?.addEventListener("click", startNewWheel);

selectAllNames.addEventListener("change", () => {
  selectedIds = selectAllNames.checked
    ? new Set(names.map((_, index) => index))
    : new Set();
  renderVirtualList();
});

clearResultsBtn.addEventListener("click", () => {
  winners = [];
  renderWinners();
  markChanged(false);
});

restoreAllResultsBtn.addEventListener("click", restoreAllWinners);

toolbarFullscreenBtn?.addEventListener("click", toggleFullscreen);
document.addEventListener("fullscreenchange", updateFullscreenControls);

toolbarSoundBtn?.addEventListener("click", toggleSound);

document.querySelector('label.wheel-tool[for="importInput"]')?.addEventListener(
  "keydown",
  (event) => {
    if (event.key !== "Enter" && event.key !== " ") return;
    event.preventDefault();
    importInput.click();
  }
);

celebrationCloseBtn.addEventListener("click", stopCelebration);

autoSpin.addEventListener("change", (event) => setAutoSpin(event.target.checked));

panelTabs.forEach((button) => {
  button.addEventListener("click", () => switchTab(button.dataset.tab));
});

modeTabs.forEach((button) => {
  button.addEventListener("click", () => switchMode(button.dataset.mode));
});

createSavedWheelBtn?.addEventListener("click", beginNewSavedWheel);
createSavedWheelForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  const saved = await createSavedWheel(savedWheelTitle?.value.trim() || "", []);
  if (saved) createSavedWheelDialog?.close();
});
savedWheelTitle?.addEventListener("keydown", (event) => {
  if (event.key !== "Enter" || event.isComposing || event.keyCode === 229) return;

  event.preventDefault();
  if (!confirmCreateSavedWheelBtn?.disabled) confirmCreateSavedWheelBtn?.click();
});
createSavedWheelDialog?.querySelectorAll("[data-close-dialog]").forEach((button) => {
  button.addEventListener("click", () => createSavedWheelDialog.close());
});
backToSavedWheelsBtn?.addEventListener("click", returnToSavedWheels);
savedWheelsSearch?.addEventListener("input", () => {
  clearTimeout(savedWheelsSearchTimer);
  savedWheelsSearchTimer = window.setTimeout(() => loadSavedWheels({ reset: true }), 300);
});
savedWheelsCards?.addEventListener("scroll", () => {
  const isNearEnd = savedWheelsCards.scrollTop + savedWheelsCards.clientHeight
    >= savedWheelsCards.scrollHeight - 120;
  if (isNearEnd) loadSavedWheels();
});

reloadConflictBtn?.addEventListener("click", () => {
  if (!serverConflictWheel) return;
  currentSavedWheel = serverConflictWheel;
  serverConflictWheel = null;
  saveConflict.classList.add("hidden");
  hydrateState({ names: currentSavedWheel.names, results: [] });
  lastSavedSnapshot = getSavedListSnapshot();
  setSaveWorkspace("active");
  setSaveStatus("تم تحميل أحدث نسخة من الخادم.", "success");
});

copyConflictBtn?.addEventListener("click", async () => {
  if (!currentSavedWheel) return;

  const copyTitle = `${currentSavedWheel.title} - نسخة`;
  saveConflict.classList.add("hidden");
  await createSavedWheel(copyTitle, names);
});

window.addEventListener("online", () => {
  const draft = readLocalDraft();
  if (draft?.pending && currentSavedWheel) saveCurrentWheel();
});

setupButtonGroupKeyboard(panelTabs, (button) => switchTab(button.dataset.tab));
setupButtonGroupKeyboard(modeTabs, (button) => switchMode(button.dataset.mode));
setupFaqKeyboard();
setupFaqToggleAnimations();

mobileMenuBtn.addEventListener("click", () => {
  const isOpen = mobileDrawer.classList.toggle("is-open");
  mobileMenuBtn.setAttribute("aria-expanded", String(isOpen));
  mobileMenuBtn.innerHTML = isOpen
    ? '<i class="fa-solid fa-xmark"></i>'
    : '<i class="fa-solid fa-bars"></i>';
});

mobileDrawer.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => {
    closeMobileDrawer();
  });
});

document.addEventListener("keydown", (event) => {
  if (event.code === "Space" && !isInteractiveElement(document.activeElement)) {
    event.preventDefault();
    spinWheel();
  }
});

hydrateInitialState();
switchMode(wheelConfig.authenticated || wheelConfig.savedWheel ? "save" : "guest");
setupScrollAnimations();
if (location.hash) {
  const target = getHashTarget(location.hash);
  if (target) requestAnimationFrame(() => scrollToTarget(target, "auto"));
}
updateScrollState();
}

if (!canvas) {
  const mobileMenuBtn = document.getElementById("mobileMenuBtn");
  const mobileDrawer = document.getElementById("mobileDrawer");

  if (mobileMenuBtn && mobileDrawer) {
    const closeMobileDrawer = () => {
      mobileDrawer.classList.remove("is-open");
      mobileMenuBtn.setAttribute("aria-expanded", "false");
      mobileMenuBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
    };

    mobileMenuBtn.addEventListener("click", () => {
      const isOpen = mobileDrawer.classList.toggle("is-open");
      mobileMenuBtn.setAttribute("aria-expanded", String(isOpen));
      mobileMenuBtn.innerHTML = isOpen
        ? '<i class="fa-solid fa-xmark"></i>'
        : '<i class="fa-solid fa-bars"></i>';
    });

    mobileDrawer.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", closeMobileDrawer);
    });
  }
}

document.querySelectorAll('input[type="file"][accept*=".webp"]').forEach((input) => {
  input.addEventListener("change", () => {
    const file = input.files?.[0];
    const preview = document.getElementById(input.dataset.previewTarget || "");
    if (!file || !preview) return;
    preview.src = URL.createObjectURL(file);
    preview.classList.remove("hidden");
  });
});

document.querySelectorAll("[data-confirm]").forEach((form) => {
  form.addEventListener("submit", (event) => {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
  });
});

document.querySelectorAll("[data-rename-wheel]").forEach((button) => {
  button.addEventListener("click", async () => {
    const title = prompt("الاسم الجديد للقائمة", button.dataset.currentTitle);
    if (!title?.trim() || title.trim() === button.dataset.currentTitle) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const response = await fetch(button.dataset.renameWheel, {
      method: "PATCH",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken
      },
      body: JSON.stringify({
        title: title.trim(),
        version: Number(button.dataset.version)
      })
    });
    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
      alert(Object.values(body.errors || {}).flat()[0] || body.message || "تعذر تغيير الاسم.");
      return;
    }

    location.reload();
  });
});
