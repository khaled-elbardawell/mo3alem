import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import {
  boardKeyboardCommand, calculatePointerGeometry, commitHistory, createDefaultBoard, createHistory, createWidget, formatDuration,
  migrateState, normalizeBoardAppearance, normalizeWidget, randomDiceValue, redoHistory, reduceBoard, shouldRunSelectionAction,
  shouldWidgetScroll, undoHistory
} from "../../resources/js/smart-board/state.js";

const boardInterfaceSource = readFileSync(new URL("../../resources/js/smart-board/index.js", import.meta.url), "utf8");

test("widget creation listeners are limited to explicit add controls", () => {
  assert.match(boardInterfaceSource, /elements\.dock\.querySelectorAll\("\[data-add-widget\]"\)/);
  assert.doesNotMatch(boardInterfaceSource, /querySelectorAll\("\[data-widget-type\]"\)/);
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
  assert.equal(appearance.showGrid, true);
  assert.equal(appearance.aspectRatio, "16/9");
});

test("legacy image backgrounds migrate to a no-crop fit", () => {
  const appearance = normalizeBoardAppearance({
    background: "image-mountains",
    backgroundSize: "cover"
  });

  assert.equal(appearance.backgroundSize, "contain");
  assert.equal(appearance.backgroundSizeCustomized, false);
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
