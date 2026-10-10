const DATABASE_NAME = "mo3alem-smart-board";
const STORE_NAME = "drafts";
const DRAFT_KEY = "guest-draft-v1";

function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DATABASE_NAME, 1);
    request.onupgradeneeded = () => {
      if (!request.result.objectStoreNames.contains(STORE_NAME)) {
        request.result.createObjectStore(STORE_NAME);
      }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

export async function loadDraft() {
  if (!("indexedDB" in window)) return null;
  const database = await openDatabase();
  return new Promise((resolve, reject) => {
    const request = database.transaction(STORE_NAME).objectStore(STORE_NAME).get(DRAFT_KEY);
    request.onsuccess = () => resolve(request.result || null);
    request.onerror = () => reject(request.error);
  }).finally(() => database.close());
}

export async function saveDraft(state) {
  if (!("indexedDB" in window)) return;
  const database = await openDatabase();
  return new Promise((resolve, reject) => {
    const transaction = database.transaction(STORE_NAME, "readwrite");
    transaction.objectStore(STORE_NAME).put(state, DRAFT_KEY);
    transaction.oncomplete = resolve;
    transaction.onerror = () => reject(transaction.error);
  }).finally(() => database.close());
}
