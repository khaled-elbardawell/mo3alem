import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import {
  codeForCharacter,
  fingerForCode,
  keyboardFor,
  lessons,
  matchesExpectedCharacter
} from "../../resources/js/typing-trainer/content.js";
import {
  activityStreak,
  calculateTypingMetrics,
  emptyProgress,
  progressSummary,
  recordResult,
  resultStars
} from "../../resources/js/typing-trainer/state.js";

const typingInterfaceSource = readFileSync(new URL("../../resources/js/typing-trainer/index.js", import.meta.url), "utf8");

test("typing metrics calculate speed, accuracy and elapsed time", () => {
  assert.deepEqual(calculateTypingMetrics(50, 55, 60000), {
    wpm: 10,
    cpm: 50,
    accuracy: 91,
    elapsedSeconds: 60
  });
  assert.deepEqual(calculateTypingMetrics(0, 0, 0), {
    wpm: 0,
    cpm: 0,
    accuracy: 100,
    elapsedSeconds: 0
  });
});

test("result stars reward accuracy and lesson speed goals", () => {
  assert.equal(resultStars(98, 20, 20), 3);
  assert.equal(resultStars(93, 14, 20), 2);
  assert.equal(resultStars(89, 30, 20), 1);
});

test("progress keeps personal bests and counts attempts", () => {
  let progress = recordResult(emptyProgress(), "ar", "first", {
    wpm: 18, cpm: 90, accuracy: 96, stars: 2
  }, "2026-11-13");
  progress = recordResult(progress, "ar", "first", {
    wpm: 16, cpm: 80, accuracy: 99, stars: 3
  }, "2026-11-14");

  assert.deepEqual(progress.results.ar.first, {
    wpm: 18,
    cpm: 90,
    accuracy: 99,
    stars: 3,
    attempts: 2,
    completedAt: "2026-11-14"
  });
  assert.deepEqual(progressSummary(progress), {
    completedLessons: 1,
    totalStars: 3,
    bestWpm: 18,
    streak: 2
  });
});

test("activity streak accepts today or yesterday as the most recent practice", () => {
  const today = new Date(2026, 10, 14);
  assert.equal(activityStreak(["2026-11-12", "2026-11-13", "2026-11-14"], today), 3);
  assert.equal(activityStreak(["2026-11-11", "2026-11-12", "2026-11-13"], today), 3);
  assert.equal(activityStreak(["2026-11-10"], today), 0);
});

test("Arabic and English lessons map their next character to a physical key and finger", () => {
  assert.equal(codeForCharacter("ت", "ar"), "KeyJ");
  assert.equal(codeForCharacter("f", "en"), "KeyF");
  assert.equal(codeForCharacter(" ", "ar"), "Space");
  assert.equal(fingerForCode("KeyJ"), "rightIndex");
  assert.equal(keyboardFor("ar").length, 5);
  assert.equal(lessons.ar.length, 8);
  assert.equal(lessons.en.length, 8);
});

test("letter keys are accepted by physical position when the operating system uses the other language", () => {
  assert.equal(matchesExpectedCharacter("ت", "j", "KeyJ", "ar"), true);
  assert.equal(matchesExpectedCharacter("f", "ب", "KeyF", "en"), true);
  assert.equal(matchesExpectedCharacter("ت", "ن", "KeyK", "ar"), false);
  assert.equal(matchesExpectedCharacter("؟", "/", "Slash", "ar"), false);
});

test("selecting another lesson refreshes the active lesson in the sidebar", () => {
  assert.match(
    typingInterfaceSource,
    /function selectLesson\([\s\S]*?resetLesson\(\);\s*renderLessonList\(\);/
  );
});

test("lesson guidance stays visible until the learner starts the lesson", () => {
  assert.match(typingInterfaceSource, /lessonStarted: false/);
  assert.match(
    typingInterfaceSource,
    /function beginLesson\(\) \{\s*state\.lessonStarted = true;[\s\S]*?renderLessonStage\(\);\s*focusCapture\(\);/
  );
  assert.match(typingInterfaceSource, /elements\.intro\.hidden = state\.lessonStarted;/);
  assert.match(typingInterfaceSource, /elements\.practice\.hidden = !state\.lessonStarted;/);
  assert.match(typingInterfaceSource, /if \(!state\.lessonStarted \|\| state\.completed/);
});

test("typing feedback uses different sounds for correct and incorrect characters", () => {
  assert.match(typingInterfaceSource, /function playTypingSound\(isCorrect\)/);
  assert.match(typingInterfaceSource, /isCorrect \? 720 : 180/);
  assert.match(typingInterfaceSource, /if \(matchesExpectedCharacter[\s\S]*?playTypingSound\(true\);[\s\S]*?else \{\s*playTypingSound\(false\);/);
});

test("sound feedback can be muted and keeps the learner preference", () => {
  assert.match(typingInterfaceSource, /soundMuted: readSoundPreference\(\)/);
  assert.match(typingInterfaceSource, /localStorage\.setItem\(soundStorageKey, state\.soundMuted \? "muted" : "enabled"\)/);
  assert.match(typingInterfaceSource, /elements\.soundToggle\.addEventListener\("click", toggleSound\)/);
});
