import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import {
  commitTypingWord,
  createTypingSession,
  expectedSecondsForContent,
  finishTypingSession,
  formatTypingDuration,
  normalizeTypingText,
  remainingTypingSeconds,
  restartTypingSession,
  startTypingSession,
  tokenizePassage,
  typingResult
} from "../../resources/js/typing-trainer/state.js";

const trainerInterfaceSource = readFileSync(
  new URL("../../resources/js/typing-trainer/index.js", import.meta.url),
  "utf8"
);

const passage = {
  id: 1,
  language: "en",
  direction: "ltr",
  content: "Clear words matter.",
  word_count: 3,
  expected_seconds: 30
};

test("passages are tokenized without losing punctuation", () => {
  assert.deepEqual(tokenizePassage("  تعلم بوضوح، واكتب بدقة.  "), ["تعلم", "بوضوح،", "واكتب", "بدقة."]);
});

test("answers use NFC normalization but keep case and punctuation exact", () => {
  assert.equal(normalizeTypingText(" cafe\u0301 "), "café");
  let session = startTypingSession(createTypingSession({ ...passage, content: "café Word." }), 1000);
  session = commitTypingWord(session, "cafe\u0301", 2000);
  session = commitTypingWord(session, "word", 3000);

  assert.deepEqual(session.statuses, ["correct", "incorrect"]);
  assert.equal(session.finishReason, "completed");
});

test("the strict timer starts on demand and reaches zero without drift", () => {
  let session = createTypingSession(passage);
  assert.equal(remainingTypingSeconds(session, 5000), 30);
  session = startTypingSession(session, 5000);
  assert.equal(remainingTypingSeconds(session, 14999), 21);
  assert.equal(remainingTypingSeconds(session, 35000), 0);
});

test("retrying resets progress and keeps the same passage", () => {
  let session = startTypingSession(createTypingSession(passage), 1000);
  session = commitTypingWord(session, "wrong", 2000);
  const restarted = restartTypingSession(session);

  assert.equal(restarted.passage, passage);
  assert.equal(restarted.currentIndex, 0);
  assert.equal(restarted.startedAt, null);
  assert.deepEqual(restarted.statuses, ["pending", "pending", "pending"]);
});

test("the typing input blocks paste events", () => {
  assert.match(trainerInterfaceSource, /addEventListener\("paste"/u);
  assert.match(trainerInterfaceSource, /inputType === "insertFromPaste"/u);
  assert.match(trainerInterfaceSource, /event\.preventDefault\(\)/u);
});

test("results report speed accuracy completion and elapsed time", () => {
  let session = startTypingSession(createTypingSession(passage), 0);
  session = commitTypingWord(session, "Clear", 10000);
  session = commitTypingWord(session, "wrong", 20000);
  session = finishTypingSession(session, 30000, "timeout");
  const result = typingResult(session);

  assert.equal(result.wordsPerMinute, 2);
  assert.equal(result.accuracy, 50);
  assert.equal(result.completion, 67);
  assert.equal(result.correctCount, 1);
  assert.equal(result.incorrectCount, 1);
  assert.equal(result.elapsedSeconds, 30);
  assert.equal(result.reason, "timeout");
});

test("expected time and duration formatting remain within supported bounds", () => {
  assert.equal(expectedSecondsForContent("short"), 10);
  assert.equal(expectedSecondsForContent("a".repeat(10000)), 600);
  assert.equal(formatTypingDuration(65), "01:05");
});
