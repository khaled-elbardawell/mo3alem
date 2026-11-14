export const lessons = {
  ar: [
    {
      id: "ar-home-start",
      title: "نقطة البداية",
      subtitle: "الحرفان ت و ن",
      level: "مبتدئ",
      goalWpm: 8,
      text: "ت ن ت ن نت تن نت تن ت ت ن ن تن نت"
    },
    {
      id: "ar-home-row",
      title: "الصف الأوسط",
      subtitle: "ا ل ب ي س ك م ط",
      level: "مبتدئ",
      goalWpm: 10,
      text: "تبت لنا بيت سلم كتاب كاتب باب بيت سلام"
    },
    {
      id: "ar-upper-row",
      title: "الصف العلوي",
      subtitle: "ض ص ث ق ف غ ع ه خ ح ج د",
      level: "أساسي",
      goalWpm: 12,
      text: "هدف حق فكر عقل علم صفحة خط جهد دقة"
    },
    {
      id: "ar-lower-row",
      title: "الصف السفلي",
      subtitle: "ئ ء ؤ ر ى ة و ز ظ",
      level: "أساسي",
      goalWpm: 14,
      text: "ورقة زهرة رؤية قراءة مدرسة مهارة سرعة"
    },
    {
      id: "ar-words",
      title: "كلمات شائعة",
      subtitle: "ربط الحروف بإيقاع ثابت",
      level: "متوسط",
      goalWpm: 18,
      text: "التعليم رحلة جميلة تبدأ بخطوة صغيرة وتتطور بالممارسة اليومية"
    },
    {
      id: "ar-sentences",
      title: "جمل متكاملة",
      subtitle: "السرعة مع المحافظة على الدقة",
      level: "متوسط",
      goalWpm: 22,
      text: "يكتب الطالب بتركيز، ويعيد أصابعه دائما إلى الصف الأوسط بعد كل كلمة."
    },
    {
      id: "ar-punctuation",
      title: "الترقيم والمسافات",
      subtitle: "الفاصلة والنقطة وعلامة السؤال",
      level: "متقدم",
      goalWpm: 24,
      text: "هل تتدرب كل يوم؟ نعم، لأن الدقة أولا، ثم تأتي السرعة."
    },
    {
      id: "ar-speed",
      title: "تحدي السرعة",
      subtitle: "نص عربي متنوع",
      level: "تحدي",
      goalWpm: 30,
      text: "التدريب القصير المنتظم يصنع فرقا كبيرا. حافظ على وضعية جلوس مريحة، وانظر إلى الشاشة، ودع أصابعك تتذكر مواقع الحروف."
    }
  ],
  en: [
    {
      id: "en-home-start",
      title: "Home position",
      subtitle: "Keys F and J",
      level: "Starter",
      goalWpm: 8,
      text: "f j f j fj jf fj jf f f j j fj jf"
    },
    {
      id: "en-home-row",
      title: "Home row",
      subtitle: "A S D F J K L",
      level: "Beginner",
      goalWpm: 12,
      text: "ask dad fall salad flask all sad lads"
    },
    {
      id: "en-upper-row",
      title: "Top row",
      subtitle: "Q W E R T Y U I O P",
      level: "Basic",
      goalWpm: 15,
      text: "type write power quiet route your proper"
    },
    {
      id: "en-lower-row",
      title: "Bottom row",
      subtitle: "Z X C V B N M",
      level: "Basic",
      goalWpm: 17,
      text: "move zoom cabin mix brave name cinema"
    },
    {
      id: "en-words",
      title: "Common words",
      subtitle: "Build a steady rhythm",
      level: "Intermediate",
      goalWpm: 22,
      text: "practice every day and keep your hands relaxed while you type"
    },
    {
      id: "en-sentences",
      title: "Full sentences",
      subtitle: "Balance speed and accuracy",
      level: "Intermediate",
      goalWpm: 27,
      text: "Small steps each day will help you type faster and make fewer mistakes."
    },
    {
      id: "en-punctuation",
      title: "Punctuation",
      subtitle: "Commas, periods and questions",
      level: "Advanced",
      goalWpm: 30,
      text: "Are you ready? Focus, breathe, and keep a smooth rhythm."
    },
    {
      id: "en-speed",
      title: "Speed challenge",
      subtitle: "A varied English passage",
      level: "Challenge",
      goalWpm: 38,
      text: "Consistent practice builds confident typing. Keep your eyes on the screen, return to the home row, and let accuracy lead your speed."
    }
  ]
};

const englishLabels = {
  Backquote: "`", Digit1: "1", Digit2: "2", Digit3: "3", Digit4: "4", Digit5: "5",
  Digit6: "6", Digit7: "7", Digit8: "8", Digit9: "9", Digit0: "0", Minus: "-", Equal: "=",
  KeyQ: "Q", KeyW: "W", KeyE: "E", KeyR: "R", KeyT: "T", KeyY: "Y", KeyU: "U", KeyI: "I",
  KeyO: "O", KeyP: "P", BracketLeft: "[", BracketRight: "]", Backslash: "\\",
  KeyA: "A", KeyS: "S", KeyD: "D", KeyF: "F", KeyG: "G", KeyH: "H", KeyJ: "J", KeyK: "K",
  KeyL: "L", Semicolon: ";", Quote: "'", KeyZ: "Z", KeyX: "X", KeyC: "C", KeyV: "V",
  KeyB: "B", KeyN: "N", KeyM: "M", Comma: ",", Period: ".", Slash: "/"
};

const arabicLabels = {
  ...englishLabels,
  KeyQ: "ض", KeyW: "ص", KeyE: "ث", KeyR: "ق", KeyT: "ف", KeyY: "غ", KeyU: "ع", KeyI: "ه",
  KeyO: "خ", KeyP: "ح", BracketLeft: "ج", BracketRight: "د", Backslash: "ذ",
  KeyA: "ش", KeyS: "س", KeyD: "ي", KeyF: "ب", KeyG: "ل", KeyH: "ا", KeyJ: "ت", KeyK: "ن",
  KeyL: "م", Semicolon: "ك", Quote: "ط", KeyZ: "ئ", KeyX: "ء", KeyC: "ؤ", KeyV: "ر",
  KeyB: "لا", KeyN: "ى", KeyM: "ة", Comma: "و", Period: "ز", Slash: "ظ"
};

const keyboardRows = [
  ["Backquote", "Digit1", "Digit2", "Digit3", "Digit4", "Digit5", "Digit6", "Digit7", "Digit8", "Digit9", "Digit0", "Minus", "Equal", "Backspace"],
  ["Tab", "KeyQ", "KeyW", "KeyE", "KeyR", "KeyT", "KeyY", "KeyU", "KeyI", "KeyO", "KeyP", "BracketLeft", "BracketRight", "Backslash"],
  ["CapsLock", "KeyA", "KeyS", "KeyD", "KeyF", "KeyG", "KeyH", "KeyJ", "KeyK", "KeyL", "Semicolon", "Quote", "Enter"],
  ["ShiftLeft", "KeyZ", "KeyX", "KeyC", "KeyV", "KeyB", "KeyN", "KeyM", "Comma", "Period", "Slash", "ShiftRight"],
  ["Space"]
];

const specialLabels = {
  Backspace: "حذف", Tab: "Tab", CapsLock: "Caps", Enter: "Enter",
  ShiftLeft: "Shift", ShiftRight: "Shift", Space: "Space"
};

export const fingers = {
  leftPinky: { label: "الخنصر الأيسر", short: "خ أ", color: "rose" },
  leftRing: { label: "البنصر الأيسر", short: "ب أ", color: "amber" },
  leftMiddle: { label: "الوسطى اليسرى", short: "و أ", color: "lime" },
  leftIndex: { label: "السبابة اليسرى", short: "س أ", color: "cyan" },
  rightIndex: { label: "السبابة اليمنى", short: "س ي", color: "blue" },
  rightMiddle: { label: "الوسطى اليمنى", short: "و ي", color: "violet" },
  rightRing: { label: "البنصر اليمنى", short: "ب ي", color: "fuchsia" },
  rightPinky: { label: "الخنصر اليمنى", short: "خ ي", color: "pink" },
  thumbs: { label: "الإبهام", short: "إ", color: "slate" }
};

const fingerCodes = {
  leftPinky: ["Backquote", "Digit1", "KeyQ", "KeyA", "KeyZ", "Tab", "CapsLock", "ShiftLeft"],
  leftRing: ["Digit2", "KeyW", "KeyS", "KeyX"],
  leftMiddle: ["Digit3", "KeyE", "KeyD", "KeyC"],
  leftIndex: ["Digit4", "Digit5", "KeyR", "KeyT", "KeyF", "KeyG", "KeyV", "KeyB"],
  rightIndex: ["Digit6", "Digit7", "KeyY", "KeyU", "KeyH", "KeyJ", "KeyN", "KeyM"],
  rightMiddle: ["Digit8", "KeyI", "KeyK", "Comma"],
  rightRing: ["Digit9", "KeyO", "KeyL", "Period"],
  rightPinky: ["Digit0", "Minus", "Equal", "KeyP", "BracketLeft", "BracketRight", "Backslash", "Semicolon", "Quote", "Slash", "Backspace", "Enter", "ShiftRight"],
  thumbs: ["Space"]
};

const characterCodes = {
  ar: {
    ض: "KeyQ", ص: "KeyW", ث: "KeyE", ق: "KeyR", ف: "KeyT", غ: "KeyY", ع: "KeyU", ه: "KeyI",
    خ: "KeyO", ح: "KeyP", ج: "BracketLeft", د: "BracketRight", ذ: "Backslash", ش: "KeyA", س: "KeyS",
    ي: "KeyD", ب: "KeyF", ل: "KeyG", ا: "KeyH", أ: "KeyH", إ: "KeyH", آ: "KeyH", ت: "KeyJ",
    ن: "KeyK", م: "KeyL", ك: "Semicolon", ط: "Quote", ئ: "KeyZ", ء: "KeyX", ؤ: "KeyC", ر: "KeyV",
    ى: "KeyN", ة: "KeyM", و: "Comma", ز: "Period", ظ: "Slash", "،": "KeyK", ".": "Period",
    "؟": "Slash", " ": "Space"
  },
  en: {
    " ": "Space", ",": "Comma", ".": "Period", "?": "Slash", ";": "Semicolon", ":": "Semicolon",
    "'": "Quote", "\"": "Quote", "-": "Minus"
  }
};

export function keyboardFor(language) {
  const labels = language === "ar" ? arabicLabels : englishLabels;

  return keyboardRows.map((row) => row.map((code) => ({
    code,
    label: specialLabels[code] || labels[code],
    finger: fingerForCode(code),
    size: ["Backspace", "Tab", "CapsLock", "Enter", "ShiftLeft", "ShiftRight"].includes(code) ? "wide" : code === "Space" ? "space" : "normal"
  })));
}

export function fingerForCode(code) {
  return Object.entries(fingerCodes).find(([, codes]) => codes.includes(code))?.[0] || "thumbs";
}

export function codeForCharacter(character, language) {
  if (language === "en" && /^[a-z]$/i.test(character)) {
    return `Key${character.toUpperCase()}`;
  }

  if (/^[0-9]$/.test(character)) {
    return `Digit${character}`;
  }

  return characterCodes[language]?.[character] || null;
}

export function matchesExpectedCharacter(expected, actual, code, language) {
  if (actual === expected) return true;
  if (!code || !/^\p{L}$/u.test(expected)) return false;

  return codeForCharacter(expected, language) === code;
}
