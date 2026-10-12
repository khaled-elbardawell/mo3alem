import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import { fitImageDimensions, safeBoardBackgroundImage } from "../../resources/js/smart-board/image.js";
import {
  boardKeyboardCommand, calendarMonthGrid, calculatePointerGeometry, commitHistory, createDefaultBoard, createHistory, createWidget, formatDuration,
  migrateState, normalizeBoardAppearance, normalizeWidget, randomDiceValue, redoHistory, reduceBoard, shouldRunSelectionAction,
  shouldWidgetScroll, shiftCalendarMonth, undoHistory
} from "../../resources/js/smart-board/state.js";

const boardInterfaceSource = readFileSync(new URL("../../resources/js/smart-board/index.js", import.meta.url), "utf8");

test("widget creation listeners are limited to explicit add controls", () => {
  assert.match(boardInterfaceSource, /elements\.dock\.querySelectorAll\("\[data-add-widget\]"\)/);
  assert.doesNotMatch(boardInterfaceSource, /querySelectorAll\("\[data-widget-type\]"\)/);
});

test("uploaded background images replace a pending color selection", () => {
  assert.match(boardInterfaceSource, /selectAppearanceBackground\("custom-image"\)/);
  assert.match(boardInterfaceSource, /background: pendingAppearanceBackground/);
  assert.match(boardInterfaceSource, /URL\.createObjectURL\(file\)/);
  assert.match(boardInterfaceSource, /optimizeBackgroundImage\(file\)/);
  assert.match(boardInterfaceSource, /await decodeImageSource\(source\)/);
  assert.match(boardInterfaceSource, /await decodeImageSource\(upload\.url\)/);
  assert.doesNotMatch(boardInterfaceSource, /const background = elements\.appearanceDialog\.querySelector\('\[name="board_background"\]:checked'\)/);
});

test("saved names are imported only after choosing a saved list", () => {
  assert.match(boardInterfaceSource, /data-saved-list-id/);
  assert.match(boardInterfaceSource, /await importSavedNames\(savedListButton\)/);
  assert.match(boardInterfaceSource, /button\.dataset\.savedListId/);
  assert.match(boardInterfaceSource, /encodeURIComponent\(listId\)/);
  assert.doesNotMatch(boardInterfaceSource, /const first = payload\.data\?\.\[0\]/);
});

test("successful board saves use the top status without a duplicate toast", () => {
  assert.match(boardInterfaceSource, /elements\.status\.textContent = "تم الحفظ على حسابك"/);
  assert.doesNotMatch(boardInterfaceSource, /notify\("تم حفظ السبورة\."\)/);
});

test("the more menu contains only tools currently hidden from the dock", () => {
  assert.match(boardInterfaceSource, /if \(open\) syncMoreMenuTools\(\)/);
  assert.match(boardInterfaceSource, /window\.getComputedStyle\(button\)\.display !== "none"/);
  assert.match(boardInterfaceSource, /moreTool\.hidden = visibleDockTypes\.has\(moreTool\.dataset\.widgetType\)/);
  assert.match(boardInterfaceSource, /if \(!elements\.moreMenu\.hidden\) syncMoreMenuTools\(\)/);
});

test("poll results refresh only when the teacher presses the refresh button", () => {
  assert.match(boardInterfaceSource, /data-widget-control="poll-refresh"/);
  assert.match(boardInterfaceSource, /if \(action === "poll-refresh"\) refreshPollResults\(widget\.id, control\)/);
  assert.match(boardInterfaceSource, /async function refreshPollResults\(widgetId, control\)/);
  assert.doesNotMatch(boardInterfaceSource, /startSessionPolling/);
  assert.doesNotMatch(boardInterfaceSource, /sessionPollTimer/);
  assert.doesNotMatch(boardInterfaceSource, /window\.setInterval\(async/);
});

test("large background images are fitted to full HD before persistence", () => {
  assert.deepEqual(fitImageDimensions(4000, 3000), { width: 1920, height: 1440 });
  assert.deepEqual(fitImageDimensions(1200, 800), { width: 1200, height: 800 });
});

test("an empty or unsafe background image never creates a broken preview", () => {
  const origin = "https://mo3alem.test";

  assert.equal(safeBoardBackgroundImage("", origin), "");
  assert.equal(safeBoardBackgroundImage("https://outside.test/image.png", origin), "");
  assert.equal(safeBoardBackgroundImage("/storage/background.png", origin), `${origin}/storage/background.png`);
  assert.match(boardInterfaceSource, /appearanceImagePreview\.removeAttribute\("src"\)/);
  assert.match(boardInterfaceSource, /appearanceImagePreview\.addEventListener\("error"/);
});

test("board keyboard shortcuts map to editing commands", () => {
  assert.equal(boardKeyboardCommand({ key: "z", ctrlKey: true }), "undo");
  assert.equal(boardKeyboardCommand({ key: "z", metaKey: true, shiftKey: true }), "redo");
  assert.equal(boardKeyboardCommand({ key: "y", ctrlKey: true }), "redo");
  assert.equal(boardKeyboardCommand({ key: "d", ctrlKey: true }, true), "duplicate");
  assert.equal(boardKeyboardCommand({ key: "Delete" }, true), "delete");
  assert.equal(boardKeyboardCommand({ key: "d", ctrlKey: true }, false), null);
  assert.equal(boardKeyboardCommand({ key: "Delete" }, false), null);
});

test("all widgets use normalized percentage geometry", () => {
  const widget = normalizeWidget({ ...createWidget("text"), x: 120, y: -5, width: 30, height: 20 });
  assert.equal(widget.x, 70);
  assert.equal(widget.y, 0);
});

test("new boards use a clean white background by default", () => {
  const board = createDefaultBoard().board;

  assert.equal(board.background, "plain");
  assert.equal(board.backgroundColor, "#ffffff");
  assert.equal(board.showGrid, false);
  assert.equal(board.backgroundSize, "contain");
});

test("new boards place a welcome message between a left calendar and right clock", () => {
  const widgets = createDefaultBoard().widgets;
  const welcome = widgets.find((widget) => widget.type === "text");
  const calendar = widgets.find((widget) => widget.type === "calendar");
  const clock = widgets.find((widget) => widget.type === "clock");

  assert.deepEqual(widgets.map((widget) => widget.type), ["text", "calendar", "clock"]);
  assert.equal(welcome.config.content, "أهلًا بكم 👋\nلنبدأ حصة رائعة");
  assert.equal(welcome.x + welcome.width / 2, 50);
  assert.equal(calendar.x, 2);
  assert.equal(calendar.x + calendar.width, 36);
  assert.equal(welcome.x - (calendar.x + calendar.width), 2);
  assert.equal(clock.x, 80);
  assert.equal(clock.x + clock.width, 96);
  assert.equal(clock.x - (welcome.x + welcome.width), 18);
});

test("board appearance settings are normalized safely", () => {
  const appearance = normalizeBoardAppearance({
    background: "custom-image",
    backgroundColor: "#123abc",
    backgroundImage: "data:image/png;base64,AA==",
    backgroundImageLocal: true,
    backgroundSize: "contain",
    backgroundSizeCustomized: true,
    backgroundOverlay: 90,
    showGrid: true,
    aspectRatio: "16/9"
  });

  assert.equal(appearance.background, "custom-image");
  assert.equal(appearance.backgroundColor, "#123abc");
  assert.equal(appearance.backgroundImageLocal, true);
  assert.equal(appearance.backgroundSize, "contain");
  assert.equal(appearance.backgroundSizeCustomized, true);
  assert.equal(appearance.backgroundOverlay, 70);
  assert.equal(appearance.showGrid, false);
  assert.equal(appearance.aspectRatio, "16/9");
});

test("legacy image backgrounds migrate to a no-crop fit", () => {
  const appearance = normalizeBoardAppearance({
    background: "image-mountains",
    backgroundSize: "cover"
  });

  assert.equal(appearance.background, "image-school-supplies");
  assert.equal(appearance.backgroundSize, "contain");
  assert.equal(appearance.backgroundSizeCustomized, false);
});

test("new ready-made image backgrounds are accepted", () => {
  for (const background of [
    "image-school-supplies",
    "image-learning-journey",
    "image-creativity",
    "image-science"
  ]) {
    assert.equal(normalizeBoardAppearance({ background }).background, background);
  }
});

test("history supports undo and redo", () => {
  const initial = createDefaultBoard();
  let history = createHistory(initial);
  history = commitHistory(history, reduceBoard(initial, { type: "add", widget: createWidget("dice", 3) }));
  assert.equal(history.present.widgets.length, 4);
  history = undoHistory(history);
  assert.equal(history.present.widgets.length, 3);
  history = redoHistory(history);
  assert.equal(history.present.widgets.length, 4);
});

test("bringing a widget forward makes it the unique top layer", () => {
  const board = createDefaultBoard();
  board.widgets[0].zIndex = 2;
  board.widgets[1].zIndex = 1000;
  board.widgets[2].zIndex = 1000;

  const raised = reduceBoard(board, { type: "front", id: board.widgets[0].id });
  const selected = raised.widgets.find((widget) => widget.id === board.widgets[0].id);
  const otherLayers = raised.widgets.filter((widget) => widget.id !== selected.id).map((widget) => widget.zIndex);

  assert.equal(selected.zIndex, 3);
  assert.deepEqual(otherLayers.sort((first, second) => first - second), [1, 2]);
});

test("clearing a board removes widgets and drawing while keeping its appearance", () => {
  const board = createDefaultBoard();
  board.board.background = "night";
  board.board.drawing.strokes = [{ color: "#000000", width: 3, mode: "pen", points: [[0, 0], [1, 1]] }];

  const cleared = reduceBoard(board, { type: "clear" });

  assert.deepEqual(cleared.widgets, []);
  assert.deepEqual(cleared.board.drawing.strokes, []);
  assert.equal(cleared.board.background, "night");
});

test("removed work mode widgets are dropped from legacy boards", () => {
  const board = createDefaultBoard();
  board.widgets.push({
    id: "work-mode-legacy",
    type: "work-mode",
    x: 10,
    y: 10,
    width: 28,
    height: 24,
    zIndex: 4,
    locked: false,
    config: { mode: "quiet" }
  });

  assert.equal(migrateState(board).widgets.some((widget) => widget.type === "work-mode"), false);
});

test("duration formatting is stable", () => {
  assert.equal(formatDuration(65), "01:05");
  assert.equal(formatDuration(-2), "00:00");
});

test("dice rolls always resolve to a valid face", () => {
  assert.equal(randomDiceValue(() => 0), 1);
  assert.equal(randomDiceValue(() => 0.5), 4);
  assert.equal(randomDiceValue(() => 0.999999), 6);
  assert.equal(randomDiceValue(() => 3), 6);
  assert.equal(randomDiceValue(() => -1), 1);
});

test("selection toolbar actions require a matching pointer press", () => {
  assert.equal(shouldRunSelectionAction(null, "duplicate"), false);
  assert.equal(shouldRunSelectionAction("delete", "duplicate"), false);
  assert.equal(shouldRunSelectionAction("duplicate", "duplicate"), true);
});

test("new dice widgets have enough room for the die and controls", () => {
  const dice = createWidget("dice");

  assert.equal(dice.width, 22);
  assert.equal(dice.height, 38);
  assert.equal(dice.config.value, 1);
});

test("legacy small dice widgets migrate without changing their center", () => {
  const board = createDefaultBoard();
  const dice = createWidget("dice");
  dice.x = 40;
  dice.y = 30;
  dice.width = 20;
  dice.height = 24;
  board.widgets.push(dice);

  const migratedDice = migrateState(board).widgets.find((widget) => widget.type === "dice");

  assert.equal(migratedDice.width, 22);
  assert.equal(migratedDice.height, 38);
  assert.equal(migratedDice.x + migratedDice.width / 2, 50);
  assert.equal(migratedDice.y + migratedDice.height / 2, 42);
});

test("new clocks default to a fitted analog face with seconds", () => {
  const clock = createWidget("clock");

  assert.equal(clock.config.mode, "analog");
  assert.equal(clock.config.hourFormat, "12");
  assert.equal(clock.config.showSeconds, true);
  assert.equal(clock.width, 16);
  assert.equal(clock.height, 35);
});

test("calendar builds a Monday-first six-week month grid", () => {
  const days = calendarMonthGrid(2026, 9, new Date(2026, 9, 13));

  assert.equal(days.length, 42);
  assert.deepEqual(days[0], {
    year: 2026,
    month: 8,
    day: 28,
    isCurrentMonth: false,
    isToday: false
  });
  assert.equal(days[3].day, 1);
  assert.equal(days[15].day, 13);
  assert.equal(days[15].isToday, true);
});

test("calendar navigation crosses year boundaries and calendar controls are rendered", () => {
  assert.deepEqual(shiftCalendarMonth(2026, 0, -1), { year: 2025, month: 11 });
  assert.deepEqual(shiftCalendarMonth(2026, 11, 1), { year: 2027, month: 0 });

  const calendar = createWidget("calendar");

  assert.equal(calendar.width, 40);
  assert.equal(calendar.height, 46);
  assert.equal(Number.isInteger(calendar.config.year), true);
  assert.equal(Number.isInteger(calendar.config.month), true);
  assert.match(boardInterfaceSource, /data-widget-control="calendar-previous"/);
  assert.match(boardInterfaceSource, /data-widget-control="calendar-next"/);
});

test("text widgets use safe structured rich text defaults", () => {
  const textWidget = createWidget("text");

  assert.equal(textWidget.config.fontFamily, "interface");
  assert.equal(textWidget.config.fontWeight, "900");
  assert.equal(textWidget.config.fontStyle, "normal");
  assert.equal(textWidget.config.textDecoration, "none");
  assert.equal(textWidget.config.backgroundColor, "#ffffff");
  assert.equal(textWidget.config.lineHeight, 1.5);
});

test("legacy default clocks migrate to the fitted face", () => {
  const board = createDefaultBoard();
  const clock = board.widgets.find((widget) => widget.type === "clock");
  clock.width = 24;
  clock.height = 24;
  clock.config.showSeconds = false;

  const migratedClock = migrateState(board).widgets.find((widget) => widget.type === "clock");

  assert.equal(migratedClock.width, 16);
  assert.equal(migratedClock.height, 35);
  assert.equal(migratedClock.config.showSeconds, true);
});

test("widgets only scroll when their content is exceptionally large", () => {
  const regularText = createWidget("text");
  regularText.config.content = "تعليمات الحصة".repeat(20);
  assert.equal(shouldWidgetScroll(regularText), false);

  const veryLongText = createWidget("text");
  veryLongText.config.content = "سطر\n".repeat(25);
  assert.equal(shouldWidgetScroll(veryLongText), true);

  const regularGroups = createWidget("groups");
  regularGroups.config.groups = Array.from({ length: 5 }, (_, group) =>
    Array.from({ length: 10 }, (_, student) => `${group}-${student}`));
  assert.equal(shouldWidgetScroll(regularGroups), false);

  regularGroups.config.groups[0].push("طالب إضافي");
  assert.equal(shouldWidgetScroll(regularGroups), true);
});

test("pointer movement maps to accurate board geometry", () => {
  const widget = { x: 20, y: 10, width: 30, height: 20 };
  const movement = { deltaX: 100, deltaY: 50, stageWidth: 1000, stageHeight: 500 };

  assert.deepEqual(calculatePointerGeometry(widget, movement), { x: 30, y: 20 });
  assert.deepEqual(calculatePointerGeometry(widget, movement, true), { x: 30, width: 20, height: 30 });
});

test("pointer movement keeps widgets inside the board", () => {
  const widget = { x: 70, y: 75, width: 30, height: 25 };
  const movement = { deltaX: 500, deltaY: 500, stageWidth: 1000, stageHeight: 500 };

  assert.deepEqual(calculatePointerGeometry(widget, movement), { x: 70, y: 75 });
});

test("legacy drawing widgets migrate to the full board drawing layer", () => {
  const state = createDefaultBoard();
  state.widgets.push({
    id: "drawing-1",
    type: "drawing",
    x: 20,
    y: 10,
    width: 40,
    height: 50,
    zIndex: 4,
    locked: false,
    config: {
      strokes: [{ color: "#000000", width: 5, mode: "pen", points: [[0, 0], [1, 1]] }]
    }
  });

  const migrated = migrateState(state);

  assert.equal(migrated.widgets.some((widget) => widget.type === "drawing"), false);
  assert.deepEqual(migrated.board.drawing.strokes[0].points, [[0.2, 0.1], [0.6, 0.6]]);
});

test("drawing settings and strokes participate in board history", () => {
  const initial = createDefaultBoard();
  const stroke = { color: "#6d28d9", width: 4, mode: "pen", points: [[0.1, 0.1], [0.2, 0.2]] };
  const next = reduceBoard(initial, { type: "drawing", changes: { tool: "eraser", strokes: [stroke] } });

  assert.equal(next.board.drawing.tool, "eraser");
  assert.deepEqual(next.board.drawing.strokes, [stroke]);
  assert.deepEqual(initial.board.drawing.strokes, []);
});
