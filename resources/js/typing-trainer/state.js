export function calculateTypingMetrics(correctCharacters, keystrokes, elapsedMilliseconds) {
  if (elapsedMilliseconds <= 0 || keystrokes <= 0) {
    return { wpm: 0, cpm: 0, accuracy: 100, elapsedSeconds: 0 };
  }

  const elapsedMinutes = elapsedMilliseconds / 60000;

  return {
    wpm: Math.round((correctCharacters / 5) / elapsedMinutes),
    cpm: Math.round(correctCharacters / elapsedMinutes),
    accuracy: Math.round((correctCharacters / keystrokes) * 100),
    elapsedSeconds: Math.max(1, Math.round(elapsedMilliseconds / 1000))
  };
}

export function resultStars(accuracy, wpm, goalWpm) {
  if (accuracy >= 97 && wpm >= goalWpm) return 3;
  if (accuracy >= 90 && wpm >= Math.round(goalWpm * 0.7)) return 2;
  return 1;
}

export function emptyProgress() {
  return {
    version: 1,
    results: { ar: {}, en: {} },
    activityDates: []
  };
}

export function normalizeProgress(value) {
  if (!value || typeof value !== "object") return emptyProgress();

  return {
    version: 1,
    results: {
      ar: value.results?.ar && typeof value.results.ar === "object" ? value.results.ar : {},
      en: value.results?.en && typeof value.results.en === "object" ? value.results.en : {}
    },
    activityDates: Array.isArray(value.activityDates)
      ? [...new Set(value.activityDates.filter((date) => /^\d{4}-\d{2}-\d{2}$/.test(date)))].slice(-60)
      : []
  };
}

export function recordResult(progress, language, lessonId, result, activityDate) {
  const normalized = normalizeProgress(progress);
  const previous = normalized.results[language][lessonId];

  normalized.results[language][lessonId] = {
    wpm: Math.max(previous?.wpm || 0, result.wpm),
    cpm: Math.max(previous?.cpm || 0, result.cpm),
    accuracy: Math.max(previous?.accuracy || 0, result.accuracy),
    stars: Math.max(previous?.stars || 0, result.stars),
    attempts: (previous?.attempts || 0) + 1,
    completedAt: activityDate
  };
  normalized.activityDates = [...new Set([...normalized.activityDates, activityDate])].sort().slice(-60);

  return normalized;
}

export function progressSummary(progress) {
  const normalized = normalizeProgress(progress);
  const results = [...Object.values(normalized.results.ar), ...Object.values(normalized.results.en)];

  return {
    completedLessons: results.length,
    totalStars: results.reduce((total, result) => total + (Number(result.stars) || 0), 0),
    bestWpm: results.reduce((best, result) => Math.max(best, Number(result.wpm) || 0), 0),
    streak: activityStreak(normalized.activityDates)
  };
}

export function activityStreak(activityDates, today = new Date()) {
  const dates = new Set(activityDates);
  const cursor = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const format = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, "0"), String(date.getDate()).padStart(2, "0")].join("-");

  if (!dates.has(format(cursor))) {
    cursor.setDate(cursor.getDate() - 1);
  }

  let streak = 0;
  while (dates.has(format(cursor))) {
    streak += 1;
    cursor.setDate(cursor.getDate() - 1);
  }

  return streak;
}
