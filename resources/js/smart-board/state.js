export const WIDGET_TYPES = [
  "text", "clock", "timer", "stopwatch", "traffic-light",
  "scoreboard", "dice", "randomizer", "groups", "image",
  "embed", "qr", "poll"
];

const legacyWidgetTypes = [...WIDGET_TYPES, "drawing"];

const defaultDrawing = {
  tool: "pen",
  color: "#6d28d9",
  width: 4,
  strokes: []
};

const boardBackgrounds = [
  "plain", "color", "aurora", "sky", "paper", "night",
  "image-mountains", "image-clouds", "image-shapes", "image-classroom", "custom-image"
];

const defaultAppearance = {
  background: "plain",
  backgroundColor: "#ffffff",
  backgroundImage: "",
  backgroundImageAssetId: null,
  backgroundImageLocal: false,
  backgroundSize: "contain",
  backgroundSizeCustomized: false,
  backgroundOverlay: 18,
  showGrid: false,
  aspectRatio: "auto"
};

const defaults = {
  text: {
    content: "اكتب تعليمات الحصة هنا",
    fontSize: 28,
    fontFamily: "interface",
    fontWeight: "900",
    fontStyle: "normal",
    textDecoration: "none",
    align: "center",
    color: "#312e81",
    backgroundColor: "#ffffff",
    lineHeight: 1.5
  },
  clock: { mode: "analog", hourFormat: "12", showSeconds: true },
  timer: { duration: 300, remaining: 300, running: false, endsAt: null },
  stopwatch: { elapsed: 0, running: false, startedAt: null },
  "traffic-light": { active: "green" },
  scoreboard: { teams: [{ name: "الفريق الأول", score: 0 }, { name: "الفريق الثاني", score: 0 }] },
  dice: { value: 1, count: 1 },
  randomizer: { names: ["أحمد", "سارة", "ليان", "محمد"], selected: null },
  groups: { names: ["أحمد", "سارة", "ليان", "محمد"], groupCount: 2, groups: [] },
  image: { src: "", alt: "صورة السبورة", localAsset: false },
  embed: { url: "", title: "محتوى تعليمي" },
  qr: { content: "https://mo3alem.test", svg: "" },
  poll: {
    question: "هل أنتم مستعدون للبدء؟",
    options: [{ key: "yes", label: "نعم" }, { key: "wait", label: "ليس بعد" }],
    session: null
  }
};

const sizes = {
  text: [34, 22], clock: [16, 35], timer: [25, 25], stopwatch: [25, 24],
  "traffic-light": [18, 34], scoreboard: [34, 30],
  dice: [22, 38], randomizer: [30, 28], groups: [34, 32],
  image: [34, 32], embed: [38, 34], qr: [24, 30], poll: [34, 34]
};

export function makeId(prefix = "widget") {
  if (globalThis.crypto?.randomUUID) return `${prefix}-${globalThis.crypto.randomUUID()}`;
  return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2)}`;
}

export function randomDiceValue(random = Math.random) {
  return Math.floor(Math.min(Math.max(Number(random()) || 0, 0), 0.999999) * 6) + 1;
}

export function shouldRunSelectionAction(armedAction, requestedAction) {
  return Boolean(requestedAction) && armedAction === requestedAction;
}

export function boardKeyboardCommand(event, hasSelection = false) {
  const key = String(event?.key || "").toLowerCase();
  const hasCommandModifier = Boolean(event?.ctrlKey || event?.metaKey);

  if (hasCommandModifier && key === "z") return event?.shiftKey ? "redo" : "undo";
  if (hasCommandModifier && key === "y") return "redo";
  if (hasCommandModifier && key === "d" && hasSelection) return "duplicate";
  if (!hasCommandModifier && key === "delete" && hasSelection) return "delete";

  return null;
}

export function createWidget(type, index = 0) {
  if (!WIDGET_TYPES.includes(type)) throw new Error(`Unknown widget type: ${type}`);
  const [width, height] = sizes[type];
  const offset = (index % 6) * 2.2;

  return {
    id: makeId(type),
    type,
    x: clamp(50 - width / 2 + offset, 0, 100 - width),
    y: clamp(42 - height / 2 + offset, 0, 100 - height),
    width,
    height,
    zIndex: Math.min(index + 1, 1000),
    locked: false,
    config: structuredClone(defaults[type])
  };
}

export function createDefaultBoard() {
  const welcome = createWidget("text", 0);
  welcome.x = 22;
  welcome.y = 10;
  welcome.width = 56;
  welcome.height = 19;
  welcome.config.content = "أهلًا بكم 👋\nلنبدأ حصة رائعة";
  welcome.config.fontSize = 34;

  const clock = createWidget("clock", 1);
  clock.x = 6;
  clock.y = 9;

  const timer = createWidget("timer", 2);
  timer.x = 66;
  timer.y = 57;

  return {
    schemaVersion: 1,
    board: { ...structuredClone(defaultAppearance), title: "سبورتي الذكية", drawing: structuredClone(defaultDrawing) },
    widgets: [welcome, clock, timer]
  };
}

export function createBoardTemplate(name) {
  const board = createDefaultBoard();
  if (name === "activity") {
    board.board.title = "نشاط صفّي";
    board.widgets = [createWidget("text", 0), createWidget("timer", 1), createWidget("groups", 2), createWidget("scoreboard", 3)];
    board.widgets[0].config.content = "نشاط اليوم\nتعاونوا، فكّروا، ثم شاركوا";
  }
  if (name === "quiz") {
    board.board.title = "اختبار سريع";
    board.widgets = [createWidget("text", 0), createWidget("timer", 1), createWidget("traffic-light", 2), createWidget("poll", 3)];
    board.widgets[0].config.content = "اختبار سريع\nاقرأ السؤال واختر الإجابة";
  }
  return board;
}

export function normalizeWidget(widget) {
  const width = clamp(Number(widget.width) || 20, 5, 100);
  const height = clamp(Number(widget.height) || 20, 5, 100);

  return {
    ...widget,
    x: clamp(Number(widget.x) || 0, 0, 100 - width),
    y: clamp(Number(widget.y) || 0, 0, 100 - height),
    width,
    height,
    zIndex: clamp(Math.trunc(Number(widget.zIndex) || 1), 1, 1000),
    locked: Boolean(widget.locked),
    config: widget.config && typeof widget.config === "object" ? widget.config : {}
  };
}

export function migrateState(value) {
  if (!value || typeof value !== "object") return createDefaultBoard();
  const normalizedWidgets = Array.isArray(value.widgets)
    ? value.widgets.filter((widget) => legacyWidgetTypes.includes(widget?.type)).slice(0, 40).map(normalizeWidget).map(migrateLegacyClock).map(migrateLegacyDice)
    : [];
  const legacyDrawings = normalizedWidgets.filter((widget) => widget.type === "drawing");
  const widgets = normalizedWidgets.filter((widget) => widget.type !== "drawing");
  const appearance = normalizeBoardAppearance(value.board);

  return {
    schemaVersion: 1,
    board: {
      ...appearance,
      title: value.board?.title || "سبورتي الذكية",
      drawing: normalizeBoardDrawing(value.board?.drawing, legacyDrawings)
    },
    widgets
  };
}

export function normalizeBoardAppearance(board = {}) {
  const backgroundColor = typeof board?.backgroundColor === "string" && /^#[0-9a-f]{6}$/i.test(board.backgroundColor)
    ? board.backgroundColor
    : defaultAppearance.backgroundColor;
  const backgroundImage = typeof board?.backgroundImage === "string" && board.backgroundImage.length <= 7_000_000
    ? board.backgroundImage
    : "";
  const backgroundImageAssetId = Number.isInteger(Number(board?.backgroundImageAssetId)) && Number(board.backgroundImageAssetId) > 0
    ? Number(board.backgroundImageAssetId)
    : null;

  return {
    background: boardBackgrounds.includes(board?.background) ? board.background : defaultAppearance.background,
    backgroundColor,
    backgroundImage,
    backgroundImageAssetId,
    backgroundImageLocal: Boolean(board?.backgroundImageLocal && backgroundImage.startsWith("data:image/")),
    backgroundSize: board?.backgroundSizeCustomized === true && ["cover", "contain"].includes(board?.backgroundSize)
      ? board.backgroundSize
      : defaultAppearance.backgroundSize,
    backgroundSizeCustomized: board?.backgroundSizeCustomized === true,
    backgroundOverlay: clamp(Number(board?.backgroundOverlay ?? defaultAppearance.backgroundOverlay), 0, 70),
    showGrid: board?.showGrid === true,
    aspectRatio: ["auto", "16/9", "4/3"].includes(board?.aspectRatio) ? board.aspectRatio : defaultAppearance.aspectRatio
  };
}

function migrateLegacyClock(widget) {
  if (widget.type !== "clock" || widget.width !== 24 || widget.height !== 24) return widget;

  const width = 16;
  const height = 35;
  const centerX = widget.x + widget.width / 2;
  const centerY = widget.y + widget.height / 2;

  return {
    ...widget,
    x: clamp(centerX - width / 2, 0, 100 - width),
    y: clamp(centerY - height / 2, 0, 100 - height),
    width,
    height,
    config: {
      ...widget.config,
      hourFormat: widget.config.hourFormat || "12",
      showSeconds: true
    }
  };
}

function migrateLegacyDice(widget) {
  if (widget.type !== "dice" || widget.width !== 20 || widget.height !== 24) return widget;

  const width = 22;
  const height = 38;
  const centerX = widget.x + widget.width / 2;
  const centerY = widget.y + widget.height / 2;

  return {
    ...widget,
    x: clamp(centerX - width / 2, 0, 100 - width),
    y: clamp(centerY - height / 2, 0, 100 - height),
    width,
    height
  };
}

export function normalizeBoardDrawing(drawing, legacyDrawings = []) {
  const current = drawing && typeof drawing === "object" ? drawing : {};
  const strokes = Array.isArray(current.strokes) ? current.strokes.slice(-200) : [];

  legacyDrawings.forEach((widget) => {
    (widget.config.strokes || []).forEach((stroke) => {
      strokes.push({
        ...stroke,
        points: (stroke.points || []).map(([x, y]) => [
          clamp((widget.x + (Number(x) || 0) * widget.width) / 100, 0, 1),
          clamp((widget.y + (Number(y) || 0) * widget.height) / 100, 0, 1)
        ])
      });
    });
  });

  return {
    tool: ["pen", "highlight", "eraser"].includes(current.tool) ? current.tool : defaultDrawing.tool,
    color: typeof current.color === "string" ? current.color : defaultDrawing.color,
    width: clamp(Number(current.width) || defaultDrawing.width, 1, 24),
    strokes: strokes.slice(-200)
  };
}

export function reduceBoard(state, action) {
  switch (action.type) {
    case "add":
      return { ...state, widgets: [...state.widgets, normalizeWidget(action.widget)] };
    case "update":
      return {
        ...state,
        widgets: state.widgets.map((widget) => widget.id === action.id
          ? normalizeWidget({ ...widget, ...action.changes, config: action.changes.config ?? widget.config })
          : widget)
      };
    case "remove":
      return { ...state, widgets: state.widgets.filter((widget) => widget.id !== action.id) };
    case "duplicate": {
      const source = state.widgets.find((widget) => widget.id === action.id);
      if (!source) return state;
      const copy = normalizeWidget({
        ...structuredClone(source),
        id: makeId(source.type),
        x: source.x + 3,
        y: source.y + 3,
        zIndex: Math.min(Math.max(...state.widgets.map((widget) => widget.zIndex), 0) + 1, 1000)
      });
      return { ...state, widgets: [...state.widgets, copy] };
    }
    case "front": {
      const target = state.widgets.find((widget) => widget.id === action.id);
      if (!target) return state;

      const orderedWidgets = state.widgets
        .map((widget, index) => ({ widget, index }))
        .sort((first, second) => first.widget.zIndex - second.widget.zIndex || first.index - second.index)
        .map(({ widget }) => widget)
        .filter((widget) => widget.id !== action.id);
      orderedWidgets.push(target);
      const layers = new Map(orderedWidgets.map((widget, index) => [widget.id, index + 1]));

      return {
        ...state,
        widgets: state.widgets.map((widget) => ({ ...widget, zIndex: layers.get(widget.id) }))
      };
    }
    case "clear":
      return {
        ...state,
        board: {
          ...state.board,
          drawing: normalizeBoardDrawing({ ...state.board.drawing, strokes: [] })
        },
        widgets: []
      };
    case "appearance":
      return { ...state, board: { ...state.board, ...action.changes } };
    case "drawing":
      return { ...state, board: { ...state.board, drawing: normalizeBoardDrawing({ ...state.board.drawing, ...action.changes }) } };
    case "title":
      return { ...state, board: { ...state.board, title: action.title } };
    case "replace":
      return migrateState(action.state);
    default:
      return state;
  }
}

export function createHistory(state) {
  return { past: [], present: structuredClone(state), future: [] };
}

export function commitHistory(history, nextState, limit = 50) {
  if (JSON.stringify(history.present) === JSON.stringify(nextState)) return history;
  return {
    past: [...history.past, structuredClone(history.present)].slice(-limit),
    present: structuredClone(nextState),
    future: []
  };
}

export function undoHistory(history) {
  if (!history.past.length) return history;
  const previous = history.past.at(-1);
  return {
    past: history.past.slice(0, -1),
    present: structuredClone(previous),
    future: [structuredClone(history.present), ...history.future].slice(0, 50)
  };
}

export function redoHistory(history) {
  if (!history.future.length) return history;
  const next = history.future[0];
  return {
    past: [...history.past, structuredClone(history.present)].slice(-50),
    present: structuredClone(next),
    future: history.future.slice(1)
  };
}

export function formatDuration(seconds) {
  const safeSeconds = Math.max(0, Math.floor(Number(seconds) || 0));
  const minutes = Math.floor(safeSeconds / 60);
  return `${String(minutes).padStart(2, "0")}:${String(safeSeconds % 60).padStart(2, "0")}`;
}

export function shouldWidgetScroll(widget) {
  if (widget.type === "text") {
    const content = String(widget.config.content || "");

    return content.length > 1200 || content.split("\n").length > 24;
  }

  if (widget.type === "groups") {
    return (widget.config.groups || []).flat().length > 50;
  }

  if (widget.type === "scoreboard") {
    return (widget.config.teams || []).length > 10;
  }

  if (widget.type === "poll") {
    return (widget.config.options || []).length > 12;
  }

  return false;
}

export function calculatePointerGeometry(widget, pointerMovement, resizing = false) {
  const stageWidth = Math.max(1, Number(pointerMovement.stageWidth) || 1);
  const stageHeight = Math.max(1, Number(pointerMovement.stageHeight) || 1);
  const deltaX = ((Number(pointerMovement.deltaX) || 0) / stageWidth) * 100;
  const deltaY = ((Number(pointerMovement.deltaY) || 0) / stageHeight) * 100;

  if (resizing) {
    const rightEdge = widget.x + widget.width;
    const minimumWidth = Math.min(8, rightEdge);
    const minimumHeight = Math.min(8, 100 - widget.y);
    const x = clamp(widget.x + deltaX, 0, rightEdge - minimumWidth);

    return {
      x,
      width: rightEdge - x,
      height: clamp(widget.height + deltaY, minimumHeight, 100 - widget.y)
    };
  }

  return {
    x: clamp(widget.x + deltaX, 0, 100 - widget.width),
    y: clamp(widget.y + deltaY, 0, 100 - widget.height)
  };
}

export function clamp(value, minimum, maximum) {
  return Math.min(Math.max(value, minimum), maximum);
}
