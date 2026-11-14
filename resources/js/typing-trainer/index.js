import { codeForCharacter, fingers, keyboardFor, lessons, matchesExpectedCharacter } from "./content.js";
import {
  calculateTypingMetrics,
  emptyProgress,
  normalizeProgress,
  progressSummary,
  recordResult,
  resultStars
} from "./state.js";

const app = document.querySelector("[data-typing-trainer]");

if (app) {
  const storageKey = "mo3alem.typing-trainer.progress.v1";
  const soundStorageKey = "mo3alem.typing-trainer.sound.v1";
  let audioContext = null;
  const elements = {
    languageButtons: [...app.querySelectorAll("[data-typing-language]")],
    lessonList: app.querySelector("[data-typing-lessons]"),
    lessonNumber: app.querySelector("[data-typing-lesson-number]"),
    lessonTitle: app.querySelector("[data-typing-lesson-title]"),
    lessonSubtitle: app.querySelector("[data-typing-lesson-subtitle]"),
    lessonGoal: app.querySelector("[data-typing-goal]"),
    text: app.querySelector("[data-typing-text]"),
    progressBar: app.querySelector("[data-typing-progress-bar]"),
    progressLabel: app.querySelector("[data-typing-progress-label]"),
    wpm: app.querySelector("[data-typing-wpm]"),
    cpm: app.querySelector("[data-typing-cpm]"),
    accuracy: app.querySelector("[data-typing-accuracy]"),
    errors: app.querySelector("[data-typing-errors]"),
    time: app.querySelector("[data-typing-time]"),
    status: app.querySelector("[data-typing-status]"),
    soundToggle: app.querySelector("[data-typing-sound-toggle]"),
    soundIcon: app.querySelector("[data-typing-sound-icon]"),
    soundLabel: app.querySelector("[data-typing-sound-label]"),
    keyboard: app.querySelector("[data-typing-keyboard]"),
    keyboardPanel: app.querySelector("[data-typing-keyboard-panel]"),
    keyboardToggle: app.querySelector("[data-typing-keyboard-toggle]"),
    keyboardChevron: app.querySelector("[data-typing-keyboard-chevron]"),
    fingerName: app.querySelector("[data-typing-finger-name]"),
    fingerBadge: app.querySelector("[data-typing-finger-badge]"),
    intro: app.querySelector("[data-typing-intro]"),
    practice: app.querySelector("[data-typing-practice]"),
    guideTitle: app.querySelector("[data-typing-guide-title]"),
    guideDescription: app.querySelector("[data-typing-guide-description]"),
    guideFocus: app.querySelector("[data-typing-guide-focus]"),
    capture: app.querySelector("[data-typing-capture]"),
    start: app.querySelector("[data-typing-start]"),
    reset: app.querySelector("[data-typing-reset]"),
    next: app.querySelector("[data-typing-next]"),
    focus: app.querySelector("[data-typing-focus]"),
    completedLessons: app.querySelector("[data-typing-completed]"),
    totalStars: app.querySelector("[data-typing-stars]"),
    bestWpm: app.querySelector("[data-typing-best]"),
    streak: app.querySelector("[data-typing-streak]"),
    resultDialog: app.querySelector("[data-typing-result-dialog]"),
    resultStars: app.querySelector("[data-result-stars]"),
    resultWpm: app.querySelector("[data-result-wpm]"),
    resultAccuracy: app.querySelector("[data-result-accuracy]"),
    resultTime: app.querySelector("[data-result-time]"),
    resultMessage: app.querySelector("[data-result-message]"),
    resultRetry: app.querySelector("[data-result-retry]"),
    resultNext: app.querySelector("[data-result-next]")
  };

  const state = {
    language: "ar",
    lessonIndex: 0,
    position: 0,
    correctCharacters: 0,
    keystrokes: 0,
    errors: 0,
    startedAt: null,
    timer: null,
    completed: false,
    errorAtPosition: false,
    lessonStarted: false,
    soundMuted: readSoundPreference(),
    progress: readProgress()
  };

  function readSoundPreference() {
    try {
      return localStorage.getItem(soundStorageKey) === "muted";
    } catch {
      return false;
    }
  }

  function saveSoundPreference() {
    try {
      localStorage.setItem(soundStorageKey, state.soundMuted ? "muted" : "enabled");
    } catch {
      // Sound feedback can continue without persisting this preference.
    }
  }

  function ensureAudioContext() {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return null;

    audioContext ??= new AudioContext();
    if (audioContext.state === "suspended") void audioContext.resume();

    return audioContext;
  }

  function playTypingSound(isCorrect) {
    if (state.soundMuted) return;

    const context = ensureAudioContext();
    if (!context) return;

    const oscillator = context.createOscillator();
    const gain = context.createGain();
    const startedAt = context.currentTime;
    const duration = isCorrect ? 0.045 : 0.11;

    oscillator.type = isCorrect ? "sine" : "square";
    oscillator.frequency.setValueAtTime(isCorrect ? 720 : 180, startedAt);
    gain.gain.setValueAtTime(isCorrect ? 0.035 : 0.045, startedAt);
    gain.gain.exponentialRampToValueAtTime(0.0001, startedAt + duration);
    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start(startedAt);
    oscillator.stop(startedAt + duration);
  }

  function renderSoundToggle() {
    elements.soundToggle.setAttribute("aria-pressed", String(state.soundMuted));
    elements.soundToggle.setAttribute("aria-label", state.soundMuted ? "تشغيل صوت الكتابة" : "كتم صوت الكتابة");
    elements.soundIcon.className = state.soundMuted ? "fa-solid fa-volume-xmark" : "fa-solid fa-volume-high";
    elements.soundLabel.textContent = state.soundMuted ? "تشغيل الصوت" : "كتم الصوت";
  }

  function toggleSound() {
    state.soundMuted = !state.soundMuted;
    saveSoundPreference();
    renderSoundToggle();

    if (!state.soundMuted) playTypingSound(true);
    if (state.lessonStarted) focusCapture();
  }

  function readProgress() {
    try {
      return normalizeProgress(JSON.parse(localStorage.getItem(storageKey)));
    } catch {
      return emptyProgress();
    }
  }

  function saveProgress() {
    try {
      localStorage.setItem(storageKey, JSON.stringify(state.progress));
    } catch {
      elements.status.textContent = "تعذر حفظ التقدم على هذا الجهاز، لكن يمكنك متابعة التدريب.";
    }
  }

  function currentLesson() {
    return lessons[state.language][state.lessonIndex];
  }

  function currentCharacters() {
    return Array.from(currentLesson().text);
  }

  function localDate() {
    const date = new Date();
    return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, "0"), String(date.getDate()).padStart(2, "0")].join("-");
  }

  function elapsedMilliseconds() {
    return state.startedAt ? Date.now() - state.startedAt : 0;
  }

  function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    return `${String(minutes).padStart(2, "0")}:${String(seconds % 60).padStart(2, "0")}`;
  }

  function metrics() {
    return calculateTypingMetrics(state.correctCharacters, state.keystrokes, elapsedMilliseconds());
  }

  function resetLesson() {
    window.clearInterval(state.timer);
    Object.assign(state, {
      position: 0,
      correctCharacters: 0,
      keystrokes: 0,
      errors: 0,
      startedAt: null,
      timer: null,
      completed: false,
      errorAtPosition: false,
      lessonStarted: false
    });
    elements.status.textContent = "ضع أصابعك على الصف الأوسط، ثم ابدأ عندما تكون جاهزًا.";
    renderLesson();
    renderMetrics();
  }

  function selectLesson(index) {
    state.lessonIndex = Math.max(0, Math.min(index, lessons[state.language].length - 1));
    resetLesson();
    renderLessonList();
  }

  function beginLesson() {
    state.lessonStarted = true;
    if (!state.soundMuted) ensureAudioContext();
    renderLessonStage();
    focusCapture();
  }

  function renderLessonStage() {
    elements.intro.hidden = state.lessonStarted;
    elements.practice.hidden = !state.lessonStarted;
  }

  function setLanguage(language) {
    if (!lessons[language] || state.language === language) return;
    state.language = language;
    state.lessonIndex = 0;
    resetLesson();
    renderLanguageButtons();
    renderLessonList();
    renderKeyboard();
  }

  function startTimer() {
    if (state.startedAt) return;
    state.startedAt = Date.now();
    elements.status.textContent = state.language === "ar"
      ? "رائع، حافظ على الدقة ولا تنظر إلى لوحة المفاتيح."
      : "Great start. Keep your eyes on the screen and type smoothly.";
    state.timer = window.setInterval(renderMetrics, 250);
  }

  function processCharacter(character, code = null) {
    if (!state.lessonStarted || state.completed || character.length !== 1) return;

    const expected = currentCharacters()[state.position];
    if (expected === undefined) return;

    startTimer();
    state.keystrokes += 1;

    if (matchesExpectedCharacter(expected, character, code, state.language)) {
      playTypingSound(true);
      state.position += 1;
      state.correctCharacters += 1;
      state.errorAtPosition = false;
      elements.status.textContent = state.language === "ar" ? "استمر، إيقاعك جيد." : "Keep going. Your rhythm looks good.";
    } else {
      playTypingSound(false);
      state.errors += 1;
      state.errorAtPosition = true;
      const languageMismatch = state.language === "ar" ? /^[a-z]$/i.test(character) : /[\u0600-\u06ff]/.test(character);
      elements.status.textContent = languageMismatch
        ? `حوّل لغة لوحة المفاتيح إلى ${state.language === "ar" ? "العربية" : "الإنجليزية"}.`
        : `الحرف المتوقع هو «${expected === " " ? "مسافة" : expected}». حاول مرة أخرى.`;
      flashWrongKey(code);
    }

    renderText();
    renderMetrics();
    updateKeyboardTarget();

    if (state.position >= currentCharacters().length) finishLesson();
  }

  function finishLesson() {
    state.completed = true;
    window.clearInterval(state.timer);
    const result = metrics();
    const stars = resultStars(result.accuracy, result.wpm, currentLesson().goalWpm);
    state.progress = recordResult(state.progress, state.language, currentLesson().id, { ...result, stars }, localDate());
    saveProgress();
    renderLessonList();
    renderSummary();
    showResult(result, stars);
  }

  function showResult(result, stars) {
    elements.resultStars.innerHTML = [1, 2, 3].map((number) => `<i class="fa-solid fa-star ${number <= stars ? "is-earned" : ""}" aria-hidden="true"></i>`).join("");
    elements.resultWpm.textContent = String(result.wpm);
    elements.resultAccuracy.textContent = `${result.accuracy}%`;
    elements.resultTime.textContent = formatTime(result.elapsedSeconds);
    elements.resultMessage.textContent = stars === 3
      ? "ممتاز! حققت هدف السرعة بدقة عالية."
      : stars === 2
        ? "أداء جميل. أعد المحاولة لرفع السرعة والدقة."
        : "بداية موفقة. ركّز على الدقة وستأتي السرعة تدريجيًا.";
    elements.resultNext.hidden = state.lessonIndex >= lessons[state.language].length - 1;

    if (typeof elements.resultDialog.showModal === "function") {
      elements.resultDialog.showModal();
    } else {
      elements.resultDialog.setAttribute("open", "");
    }
  }

  function closeResult() {
    if (typeof elements.resultDialog.close === "function") elements.resultDialog.close();
    else elements.resultDialog.removeAttribute("open");
  }

  function renderLanguageButtons() {
    elements.languageButtons.forEach((button) => {
      const selected = button.dataset.typingLanguage === state.language;
      button.classList.toggle("is-active", selected);
      button.setAttribute("aria-pressed", String(selected));
    });
  }

  function renderLessonList() {
    elements.lessonList.replaceChildren();

    lessons[state.language].forEach((lesson, index) => {
      const result = state.progress.results[state.language][lesson.id];
      const button = document.createElement("button");
      const number = document.createElement("span");
      const copy = document.createElement("span");
      const title = document.createElement("strong");
      const subtitle = document.createElement("small");
      const score = document.createElement("span");

      button.type = "button";
      button.className = "typing-lesson-button";
      button.classList.toggle("is-active", index === state.lessonIndex);
      button.setAttribute("aria-current", index === state.lessonIndex ? "step" : "false");
      number.className = "typing-lesson-number";
      number.textContent = result ? "✓" : String(index + 1);
      copy.className = "min-w-0 flex-1 text-start";
      title.className = "block truncate text-sm font-black text-slate-800";
      title.textContent = lesson.title;
      subtitle.className = "mt-0.5 block truncate text-xs font-bold text-slate-400";
      subtitle.textContent = lesson.subtitle;
      score.className = "typing-lesson-score";
      score.textContent = result ? `${result.wpm} WPM` : lesson.level;
      copy.append(title, subtitle);
      button.append(number, copy, score);
      button.addEventListener("click", () => selectLesson(index));
      elements.lessonList.append(button);
    });
  }

  function renderLesson() {
    const lesson = currentLesson();
    elements.lessonNumber.textContent = `الدرس ${state.lessonIndex + 1} من ${lessons[state.language].length}`;
    elements.lessonTitle.textContent = lesson.title;
    elements.lessonSubtitle.textContent = lesson.subtitle;
    elements.lessonGoal.textContent = `${lesson.goalWpm} WPM`;
    elements.guideTitle.textContent = lesson.title;
    elements.guideDescription.textContent = `في هذا الدرس ستتدرب على: ${lesson.subtitle}. ثبّت وضعية يديك أولًا ثم ابدأ.`;
    elements.guideFocus.textContent = lesson.subtitle;
    elements.text.dir = state.language === "ar" ? "rtl" : "ltr";
    elements.text.lang = state.language;
    elements.next.disabled = state.lessonIndex >= lessons[state.language].length - 1;
    renderLessonStage();
    renderText();
    updateKeyboardTarget();
  }

  function renderText() {
    const characters = currentCharacters();
    const fragment = document.createDocumentFragment();

    characters.forEach((character, index) => {
      const span = document.createElement("span");
      span.className = "typing-character";
      if (index < state.position) span.classList.add("is-complete");
      if (index === state.position) span.classList.add("is-current");
      if (index === state.position && state.errorAtPosition) span.classList.add("is-error");
      span.textContent = character === " " ? "\u00a0" : character;
      fragment.append(span);
    });

    elements.text.replaceChildren(fragment);
    const percentage = Math.round((state.position / characters.length) * 100);
    elements.progressBar.style.width = `${percentage}%`;
    elements.progressLabel.textContent = `${percentage}%`;
    elements.text.querySelector(".is-current")?.scrollIntoView({ block: "nearest", inline: "center" });
  }

  function renderMetrics() {
    const result = metrics();
    elements.wpm.textContent = String(result.wpm);
    elements.cpm.textContent = String(result.cpm);
    elements.accuracy.textContent = `${result.accuracy}%`;
    elements.errors.textContent = String(state.errors);
    elements.time.textContent = formatTime(result.elapsedSeconds);
  }

  function renderKeyboard() {
    elements.keyboard.replaceChildren();

    keyboardFor(state.language).forEach((row) => {
      const rowElement = document.createElement("div");
      rowElement.className = "typing-keyboard-row";

      row.forEach((key) => {
        const keyElement = document.createElement("span");
        keyElement.className = `typing-key typing-key--${key.size}`;
        keyElement.dataset.keyCode = key.code;
        keyElement.dataset.finger = key.finger;
        keyElement.textContent = key.label;
        rowElement.append(keyElement);
      });

      elements.keyboard.append(rowElement);
    });

    updateKeyboardTarget();
  }

  function updateKeyboardTarget() {
    elements.keyboard.querySelectorAll(".is-next").forEach((key) => key.classList.remove("is-next"));
    const character = currentCharacters()[state.position];
    const code = codeForCharacter(character, state.language);
    const key = code ? elements.keyboard.querySelector(`[data-key-code="${code}"]`) : null;
    key?.classList.add("is-next");

    const finger = key?.dataset.finger ? fingers[key.dataset.finger] : null;
    elements.fingerName.textContent = finger?.label || "—";
    elements.fingerBadge.textContent = finger?.short || "—";
    elements.fingerBadge.dataset.finger = key?.dataset.finger || "thumbs";
  }

  function flashWrongKey(code) {
    const key = code ? elements.keyboard.querySelector(`[data-key-code="${code}"]`) : null;
    if (!key) return;
    key.classList.add("is-wrong");
    window.setTimeout(() => key.classList.remove("is-wrong"), 180);
  }

  function renderSummary() {
    const summary = progressSummary(state.progress);
    elements.completedLessons.textContent = String(summary.completedLessons);
    elements.totalStars.textContent = String(summary.totalStars);
    elements.bestWpm.textContent = String(summary.bestWpm);
    elements.streak.textContent = String(summary.streak);
  }

  function focusCapture() {
    elements.capture.focus({ preventScroll: true });
  }

  function isTypingTarget(target) {
    return target === elements.capture || !target.closest("button, a, textarea, select, [contenteditable='true']");
  }

  document.addEventListener("keydown", (event) => {
    if (!isTypingTarget(event.target) || event.ctrlKey || event.metaKey || event.altKey || event.isComposing) return;
    if (event.key.length !== 1) return;
    event.preventDefault();
    processCharacter(event.key, event.code);
  });

  elements.capture.addEventListener("input", (event) => {
    Array.from(event.target.value).forEach((character) => processCharacter(character));
    event.target.value = "";
  });

  elements.languageButtons.forEach((button) => button.addEventListener("click", () => setLanguage(button.dataset.typingLanguage)));
  elements.soundToggle.addEventListener("click", toggleSound);
  elements.start.addEventListener("click", beginLesson);
  elements.text.addEventListener("click", focusCapture);
  elements.reset.addEventListener("click", resetLesson);
  elements.next.addEventListener("click", () => selectLesson(state.lessonIndex + 1));
  elements.resultRetry.addEventListener("click", () => {
    closeResult();
    resetLesson();
  });
  elements.resultNext.addEventListener("click", () => {
    closeResult();
    selectLesson(state.lessonIndex + 1);
  });
  elements.focus.addEventListener("click", async () => {
    if (document.fullscreenElement) await document.exitFullscreen();
    else await app.requestFullscreen?.();
  });
  elements.keyboardToggle.addEventListener("click", () => {
    const willOpen = elements.keyboardPanel.hidden;
    elements.keyboardPanel.hidden = !willOpen;
    elements.keyboardToggle.setAttribute("aria-expanded", String(willOpen));
    elements.keyboardToggle.querySelector("span").textContent = willOpen ? "إخفاء لوحة المفاتيح" : "إظهار لوحة المفاتيح";
    elements.keyboardChevron.classList.toggle("rotate-180", willOpen);
  });
  document.addEventListener("fullscreenchange", () => {
    elements.focus.querySelector("span").textContent = document.fullscreenElement ? "إنهاء التركيز" : "وضع التركيز";
    elements.focus.querySelector("i").className = document.fullscreenElement ? "fa-solid fa-compress" : "fa-solid fa-expand";
  });

  renderLanguageButtons();
  renderLessonList();
  renderKeyboard();
  renderSummary();
  renderSoundToggle();
  resetLesson();
}
