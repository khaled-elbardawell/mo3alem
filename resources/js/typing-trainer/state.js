export function normalizeTypingText(value) {
  return String(value ?? "").normalize("NFC").trim();
}

export function tokenizePassage(content) {
  const normalized = normalizeTypingText(content);
  return normalized ? normalized.split(/\s+/u) : [];
}

export function expectedSecondsForContent(content) {
  const standardWords = Array.from(normalizeTypingText(content)).length / 5;
  return Math.max(10, Math.min(600, Math.ceil((standardWords / 30) * 60)));
}

export function createTypingSession(passage) {
  const words = tokenizePassage(passage.content);

  return {
    passage,
    words,
    statuses: words.map(() => "pending"),
    currentIndex: 0,
    correctCount: 0,
    incorrectCount: 0,
    correctCharacters: 0,
    startedAt: null,
    finishedAt: null,
    finishReason: null
  };
}

export function restartTypingSession(session) {
  return createTypingSession(session.passage);
}

export function startTypingSession(session, now = Date.now()) {
  if (session.startedAt !== null || session.finishedAt !== null) return session;

  return { ...session, startedAt: now };
}

export function commitTypingWord(session, input, now = Date.now()) {
  if (session.finishedAt !== null || session.currentIndex >= session.words.length) return session;

  const answer = normalizeTypingText(input);
  if (!answer) return session;

  const expected = normalizeTypingText(session.words[session.currentIndex]);
  const isCorrect = answer === expected;
  const statuses = [...session.statuses];
  statuses[session.currentIndex] = isCorrect ? "correct" : "incorrect";
  const nextIndex = session.currentIndex + 1;
  const next = {
    ...session,
    statuses,
    currentIndex: nextIndex,
    correctCount: session.correctCount + (isCorrect ? 1 : 0),
    incorrectCount: session.incorrectCount + (isCorrect ? 0 : 1),
    correctCharacters: session.correctCharacters + (
      isCorrect ? Array.from(expected).length + (session.currentIndex < session.words.length - 1 ? 1 : 0) : 0
    )
  };

  return nextIndex >= session.words.length ? finishTypingSession(next, now, "completed") : next;
}

export function finishTypingSession(session, now = Date.now(), reason = "timeout") {
  if (session.finishedAt !== null) return session;

  return {
    ...session,
    startedAt: session.startedAt ?? now,
    finishedAt: now,
    finishReason: reason
  };
}

export function remainingTypingSeconds(session, now = Date.now()) {
  if (session.startedAt === null) return session.passage.expected_seconds;

  const end = session.finishedAt ?? now;
  const elapsed = Math.floor((end - session.startedAt) / 1000);
  return Math.max(0, session.passage.expected_seconds - elapsed);
}

export function typingResult(session) {
  const elapsedMilliseconds = session.startedAt === null
    ? 0
    : Math.max(1000, (session.finishedAt ?? session.startedAt) - session.startedAt);
  const elapsedSeconds = Math.ceil(elapsedMilliseconds / 1000);
  const elapsedMinutes = elapsedMilliseconds / 60000;
  const attempted = session.correctCount + session.incorrectCount;

  return {
    wordsPerMinute: elapsedMinutes > 0 ? Math.round((session.correctCharacters / 5) / elapsedMinutes) : 0,
    accuracy: attempted > 0 ? Math.round((session.correctCount / attempted) * 100) : 0,
    completion: session.words.length > 0 ? Math.round((attempted / session.words.length) * 100) : 0,
    correctCount: session.correctCount,
    incorrectCount: session.incorrectCount,
    elapsedSeconds,
    expectedSeconds: session.passage.expected_seconds,
    reason: session.finishReason
  };
}

export function formatTypingDuration(seconds) {
  const safeSeconds = Math.max(0, Math.floor(Number(seconds) || 0));
  const minutes = Math.floor(safeSeconds / 60).toString().padStart(2, "0");
  const remainder = (safeSeconds % 60).toString().padStart(2, "0");
  return `${minutes}:${remainder}`;
}
