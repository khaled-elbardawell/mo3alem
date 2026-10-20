import {
  commitTypingWord,
  createTypingSession,
  expectedSecondsForContent,
  finishTypingSession,
  formatTypingDuration,
  remainingTypingSeconds,
  restartTypingSession,
  startTypingSession,
  typingResult
} from "./state.js";

function setupTypingTrainer(root) {
  const config = JSON.parse(root.querySelector("[data-typing-trainer-config]")?.textContent || "{}");
  const elements = {
    selection: root.querySelector("[data-typing-language-selection]"),
    loading: root.querySelector("[data-typing-loading]"),
    test: root.querySelector("[data-typing-test]"),
    result: root.querySelector("[data-typing-result]"),
    error: root.querySelector("[data-typing-error]"),
    passage: root.querySelector("[data-typing-passage]"),
    input: root.querySelector("[data-typing-input]"),
    timer: root.querySelector("[data-typing-timer]"),
    progress: root.querySelector("[data-typing-progress]"),
    correct: root.querySelector("[data-typing-correct]"),
    resultTitle: root.querySelector("[data-typing-result-title]"),
    resultSummary: root.querySelector("[data-typing-result-summary]"),
    resultWpm: root.querySelector("[data-result-wpm]"),
    resultAccuracy: root.querySelector("[data-result-accuracy]"),
    resultCorrect: root.querySelector("[data-result-correct]"),
    resultIncorrect: root.querySelector("[data-result-incorrect]"),
    resultCompletion: root.querySelector("[data-result-completion]"),
    resultTime: root.querySelector("[data-result-time]")
  };
  let session = null;
  let selectedLanguage = null;
  let timerId = null;

  const display = (active) => {
    for (const section of [elements.selection, elements.loading, elements.test, elements.result]) {
      section?.classList.toggle("hidden", section !== active);
      section?.classList.toggle("grid", section === active && section === elements.loading);
    }
  };

  const stopTimer = () => {
    if (timerId !== null) window.clearInterval(timerId);
    timerId = null;
  };

  const renderWords = () => {
    elements.passage.replaceChildren();
    session.words.forEach((word, index) => {
      const span = document.createElement("span");
      const status = session.statuses[index];
      span.textContent = word;
      span.dataset.wordIndex = String(index);
      span.className = "rounded-lg px-1.5 transition-colors";

      if (status === "correct") span.classList.add("bg-emerald-50", "text-emerald-700");
      else if (status === "incorrect") span.classList.add("bg-red-50", "text-red-600", "line-through", "decoration-2", "decoration-red-500");
      else if (index === session.currentIndex) span.classList.add("bg-amber-200", "text-slate-950", "ring-2", "ring-amber-300");
      else span.classList.add("text-slate-400");

      elements.passage.append(span);
    });
  };

  const renderStatus = () => {
    elements.timer.textContent = formatTypingDuration(remainingTypingSeconds(session));
    elements.progress.textContent = `${session.currentIndex} / ${session.words.length}`;
    elements.correct.textContent = String(session.correctCount);
    renderWords();
  };

  const showResult = () => {
    stopTimer();
    elements.input.disabled = true;
    const result = typingResult(session);
    elements.resultTitle.textContent = result.reason === "completed" ? "أكملت الفقرة" : "انتهى الوقت";
    elements.resultSummary.textContent = result.reason === "completed"
      ? "رائع، راجع نتيجتك وابدأ فقرة جديدة عندما تكون جاهزا."
      : "راجع تقدمك وحاول مرة أخرى لتحسين السرعة والدقة.";
    elements.resultWpm.textContent = String(result.wordsPerMinute);
    elements.resultAccuracy.textContent = `${result.accuracy}%`;
    elements.resultCorrect.textContent = String(result.correctCount);
    elements.resultIncorrect.textContent = String(result.incorrectCount);
    elements.resultCompletion.textContent = `${result.completion}%`;
    elements.resultTime.textContent = `${formatTypingDuration(result.elapsedSeconds)} / ${formatTypingDuration(result.expectedSeconds)}`;
    display(elements.result);
  };

  const finishForTimeout = () => {
    if (elements.input.value.trim()) {
      session = commitTypingWord(session, elements.input.value, Date.now());
      elements.input.value = "";
    }
    if (session.finishedAt === null) session = finishTypingSession(session, Date.now(), "timeout");
    showResult();
  };

  const tick = () => {
    const remaining = remainingTypingSeconds(session);
    elements.timer.textContent = formatTypingDuration(remaining);
    if (remaining <= 0) finishForTimeout();
  };

  const beginTimer = () => {
    if (session.startedAt !== null) return;
    session = startTypingSession(session, Date.now());
    timerId = window.setInterval(tick, 200);
  };

  const loadPassage = async () => {
    stopTimer();
    elements.error.classList.add("hidden");
    display(elements.loading);
    const url = new URL(config.randomPassageUrl, window.location.origin);
    url.searchParams.set("language", selectedLanguage);
    if (session?.passage?.id) url.searchParams.set("exclude", String(session.passage.id));

    try {
      const response = await fetch(url, { headers: { Accept: "application/json" } });
      if (!response.ok) throw new Error(`Passage request failed with ${response.status}`);
      const payload = await response.json();
      session = createTypingSession(payload.data);
      elements.passage.dir = payload.data.direction;
      elements.input.dir = payload.data.direction;
      elements.input.lang = payload.data.language;
      elements.input.disabled = false;
      elements.input.value = "";
      renderStatus();
      display(elements.test);
      elements.input.focus();
    } catch (error) {
      display(elements.selection);
      elements.error.classList.remove("hidden");
      console.error(error);
    }
  };

  root.querySelectorAll("[data-typing-language]").forEach((button) => {
    button.addEventListener("click", () => {
      selectedLanguage = button.dataset.typingLanguage;
      loadPassage();
    });
  });

  elements.input.addEventListener("input", () => {
    if (elements.input.value.length > 0) beginTimer();
  });

  elements.input.addEventListener("paste", (event) => {
    event.preventDefault();
  });

  elements.input.addEventListener("beforeinput", (event) => {
    if (event.inputType === "insertFromPaste") event.preventDefault();
  });

  elements.input.addEventListener("keydown", (event) => {
    if (event.isComposing) return;
    const isLastWord = session.currentIndex === session.words.length - 1;
    const shouldCommit = event.key === " " || (event.key === "Enter" && isLastWord);
    if (!shouldCommit) return;
    event.preventDefault();
    if (!elements.input.value.trim()) return;

    beginTimer();
    session = commitTypingWord(session, elements.input.value, Date.now());
    elements.input.value = "";
    renderStatus();
    if (session.finishedAt !== null) showResult();
  });

  root.querySelector("[data-typing-restart]")?.addEventListener("click", () => {
    stopTimer();
    session = restartTypingSession(session);
    elements.input.disabled = false;
    elements.input.value = "";
    renderStatus();
    display(elements.test);
    elements.input.focus();
  });
  root.querySelector("[data-typing-retry]")?.addEventListener("click", loadPassage);
  root.querySelector("[data-typing-change-language]")?.addEventListener("click", () => {
    stopTimer();
    session = null;
    selectedLanguage = null;
    elements.error.classList.add("hidden");
    display(elements.selection);
  });
}

function setupTypingPassageForm(form) {
  const content = form.querySelector("[data-typing-passage-content]");
  const automatic = form.querySelector("[data-auto-calculate-time]");
  const seconds = form.querySelector("[data-expected-seconds]");

  const sync = () => {
    seconds.disabled = automatic.checked;
    if (automatic.checked) seconds.value = String(expectedSecondsForContent(content.value));
  };

  content.addEventListener("input", sync);
  automatic.addEventListener("change", sync);
  sync();
}

document.querySelectorAll("[data-typing-trainer]").forEach(setupTypingTrainer);
document.querySelectorAll("[data-typing-passage-form]").forEach(setupTypingPassageForm);
