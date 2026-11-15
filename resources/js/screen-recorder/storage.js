const DATABASE_NAME = "muallem-screen-recorder";
const DATABASE_VERSION = 1;
const PROJECT_STORE = "projects";
const ROOT_DIRECTORY = "screen-recorder";

let databasePromise;

export function supportsLocalProjectStorage() {
  return "indexedDB" in globalThis && typeof navigator?.storage?.getDirectory === "function";
}

export async function persistLocalStorage() {
  if (typeof navigator.storage?.persist !== "function") return false;

  return navigator.storage.persist();
}

export async function storageEstimate() {
  const estimate = await navigator.storage?.estimate?.();

  return {
    quota: Number(estimate?.quota) || 0,
    usage: Number(estimate?.usage) || 0,
    available: Math.max(0, (Number(estimate?.quota) || 0) - (Number(estimate?.usage) || 0))
  };
}

export async function saveProject(manifest) {
  const database = await openDatabase();
  const transaction = database.transaction(PROJECT_STORE, "readwrite");

  transaction.objectStore(PROJECT_STORE).put(structuredClone(manifest));
  await transactionComplete(transaction);

  return manifest;
}

export async function getProject(id) {
  const database = await openDatabase();
  const transaction = database.transaction(PROJECT_STORE, "readonly");
  const project = await requestResult(transaction.objectStore(PROJECT_STORE).get(id));

  await transactionComplete(transaction);

  return project || null;
}

export async function listProjects() {
  const database = await openDatabase();
  const transaction = database.transaction(PROJECT_STORE, "readonly");
  const projects = await requestResult(transaction.objectStore(PROJECT_STORE).getAll());

  await transactionComplete(transaction);

  return projects.sort((first, second) => String(second.updatedAt).localeCompare(String(first.updatedAt)));
}

export async function deleteProject(id) {
  const database = await openDatabase();
  const transaction = database.transaction(PROJECT_STORE, "readwrite");

  transaction.objectStore(PROJECT_STORE).delete(id);
  await transactionComplete(transaction);

  const root = await getRootDirectory();

  try {
    await root.removeEntry(id, { recursive: true });
  } catch (error) {
    if (error?.name !== "NotFoundError") throw error;
  }
}

export async function createProjectFileWriter(projectId, fileName) {
  const projectDirectory = await getProjectDirectory(projectId, true);
  const fileHandle = await projectDirectory.getFileHandle(fileName, { create: true });
  const writable = await fileHandle.createWritable();
  let position = 0;
  let closed = false;

  return {
    async write(blob) {
      if (closed || !blob?.size) return;

      await writable.write({ type: "write", position, data: blob });
      position += blob.size;
    },
    async close() {
      if (closed) return;

      closed = true;
      await writable.close();
    },
    async abort() {
      if (closed) return;

      closed = true;
      await writable.abort();
    },
    get size() {
      return position;
    }
  };
}

export async function getProjectFile(projectId, fileName) {
  const projectDirectory = await getProjectDirectory(projectId);
  const fileHandle = await projectDirectory.getFileHandle(fileName);

  return fileHandle.getFile();
}

export async function getProjectFileUrl(projectId, fileName) {
  const file = await getProjectFile(projectId, fileName);

  return URL.createObjectURL(file);
}

export async function projectFileExists(projectId, fileName) {
  try {
    await getProjectFile(projectId, fileName);
    return true;
  } catch (error) {
    if (error?.name === "NotFoundError") return false;
    throw error;
  }
}

export async function removeProjectFile(projectId, fileName) {
  try {
    const projectDirectory = await getProjectDirectory(projectId);
    await projectDirectory.removeEntry(fileName);
  } catch (error) {
    if (error?.name !== "NotFoundError") throw error;
  }
}

async function getRootDirectory() {
  const opfsRoot = await navigator.storage.getDirectory();

  return opfsRoot.getDirectoryHandle(ROOT_DIRECTORY, { create: true });
}

async function getProjectDirectory(projectId, create = false) {
  const root = await getRootDirectory();

  return root.getDirectoryHandle(projectId, { create });
}

function openDatabase() {
  if (databasePromise) return databasePromise;

  databasePromise = new Promise((resolve, reject) => {
    const request = indexedDB.open(DATABASE_NAME, DATABASE_VERSION);

    request.onupgradeneeded = () => {
      const database = request.result;

      if (!database.objectStoreNames.contains(PROJECT_STORE)) {
        const store = database.createObjectStore(PROJECT_STORE, { keyPath: "id" });
        store.createIndex("updatedAt", "updatedAt");
      }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });

  return databasePromise;
}

function requestResult(request) {
  return new Promise((resolve, reject) => {
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function transactionComplete(transaction) {
  return new Promise((resolve, reject) => {
    transaction.oncomplete = resolve;
    transaction.onerror = () => reject(transaction.error);
    transaction.onabort = () => reject(transaction.error || new Error("تعذر إكمال عملية التخزين المحلية."));
  });
}
