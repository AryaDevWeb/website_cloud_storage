/**
 * api.js — Fetch wrappers + mock data
 *
 * ╔═══════════════════════════════════════════════════════════╗
 * ║  TO USE REAL BACKEND:                                    ║
 * ║  1. Set USE_MOCKS = false                                ║
 * ║  2. Ensure your Laravel routes match the endpoints below ║
 * ║  3. Return JSON in the same shape as the mock responses  ║
 * ╚═══════════════════════════════════════════════════════════╝
 */

const USE_MOCKS = false;
const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

// ── Mock data ───────────────────────────────────────────────
const MOCK_FOLDERS = [
    { id: 'f1', type: 'folder', name: 'Documents', items: 12, modified: '2026-03-08T10:00:00Z', owner: 'You' },
    { id: 'f2', type: 'folder', name: 'Photos', items: 48, modified: '2026-03-07T14:30:00Z', owner: 'You' },
    { id: 'f3', type: 'folder', name: 'Projects', items: 7, modified: '2026-03-05T09:15:00Z', owner: 'You' },
    { id: 'f4', type: 'folder', name: 'Shared', items: 3, modified: '2026-03-01T16:00:00Z', owner: 'Team' },
];

const MOCK_FILES = [
    { id: '1', type: 'file', name: 'Proposal.pdf', ext: 'pdf', size: 2456789, modified: '2026-03-09T12:00:00Z', owner: 'You' },
    { id: '2', type: 'file', name: 'Budget.xlsx', ext: 'xlsx', size: 1023456, modified: '2026-03-08T09:30:00Z', owner: 'You' },
    { id: '3', type: 'file', name: 'Presentation.pptx', ext: 'pptx', size: 5678901, modified: '2026-03-07T15:00:00Z', owner: 'You' },
    { id: '4', type: 'file', name: 'Photo-001.jpg', ext: 'jpg', size: 3456789, modified: '2026-03-06T11:00:00Z', owner: 'You' },
    { id: '5', type: 'file', name: 'Video-demo.mp4', ext: 'mp4', size: 45678901, modified: '2026-03-05T08:00:00Z', owner: 'You' },
    { id: '6', type: 'file', name: 'Notes.txt', ext: 'txt', size: 4567, modified: '2026-03-04T17:00:00Z', owner: 'You' },
    { id: '7', type: 'file', name: 'Archive.zip', ext: 'zip', size: 12345678, modified: '2026-03-03T10:00:00Z', owner: 'You' },
    { id: '8', type: 'file', name: 'Screenshot.png', ext: 'png', size: 891234, modified: '2026-03-02T14:00:00Z', owner: 'You' },
    { id: '9', type: 'file', name: 'Report.docx', ext: 'docx', size: 2345678, modified: '2026-03-01T16:30:00Z', owner: 'You' },
    { id: '10', type: 'file', name: 'Soundtrack.mp3', ext: 'mp3', size: 8765432, modified: '2026-02-28T12:00:00Z', owner: 'Team' },
    { id: '11', type: 'file', name: 'Diagram.svg', ext: 'svg', size: 34567, modified: '2026-02-27T09:00:00Z', owner: 'You' },
    { id: '12', type: 'file', name: 'Config.json', ext: 'json', size: 2345, modified: '2026-02-26T11:00:00Z', owner: 'You' },
];

let mockStore = { folders: [...MOCK_FOLDERS], files: [...MOCK_FILES] };
let deletedItems = []; // for undo

// ── Helpers ─────────────────────────────────────────────────
function delay(ms = 400) { return new Promise(r => setTimeout(r, ms)); }

export function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024, sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

export function formatDate(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

export function getFileIcon(ext) {
    const map = {
        pdf: 'pdf', doc: 'doc', docx: 'doc', txt: 'doc', rtf: 'doc',
        xls: 'spreadsheet', xlsx: 'spreadsheet', csv: 'spreadsheet',
        ppt: 'presentation', pptx: 'presentation',
        jpg: 'image', jpeg: 'image', png: 'image', gif: 'image', svg: 'image', webp: 'image',
        mp4: 'video', webm: 'video', mov: 'video', avi: 'video',
        mp3: 'audio', wav: 'audio', ogg: 'audio', flac: 'audio',
        zip: 'archive', rar: 'archive', '7z': 'archive', tar: 'archive', gz: 'archive',
    };
    return map[(ext || '').toLowerCase()] || 'generic';
}

// ── API functions ───────────────────────────────────────────

/**
 * GET /api/files?folder_id=&q=&sort=&page=&per_page=
 */
export async function fetchFiles({ folderId = null, q = '', sort = 'name', page = 1, perPage = 12 } = {}) {
    if (USE_MOCKS) {
        await delay(300);
        let items = [...mockStore.folders, ...mockStore.files];
        if (q) items = items.filter(i => i.name.toLowerCase().includes(q.toLowerCase()));

        // sort
        items.sort((a, b) => {
            if (sort === 'name') return a.name.localeCompare(b.name);
            if (sort === 'date') return new Date(b.modified) - new Date(a.modified);
            if (sort === 'size') return (b.size || 0) - (a.size || 0);
            return 0;
        });

        const total = items.length;
        const start = (page - 1) * perPage;
        const paged = items.slice(start, start + perPage);
        return { data: paged, total, page, perPage, lastPage: Math.ceil(total / perPage) };
    }

    /* REAL: swap to your Laravel endpoint */
    const params = new URLSearchParams({ folder_id: folderId || '', q, sort, page, per_page: perPage });
    const res = await fetch(`/api/files?${params}`);
    return res.json();
}

/**
 * GET /api/files/{id}
 */
export async function fetchFile(id) {
    if (USE_MOCKS) {
        await delay(200);
        return mockStore.files.find(f => f.id === id) || mockStore.folders.find(f => f.id === id) || null;
    }
    const res = await fetch(`/api/files/${id}`);
    return res.json();
}

/**
 * POST /api/upload   (multipart/form-data)
 * Returns XHR so caller can track progress + cancel
 */
export function uploadFile(file, folderId, { onProgress, onDone, onError }) {
    const formData = new FormData();
    formData.append('upload', file);
    formData.append('folder_id', folderId || '');

    if (USE_MOCKS) {
        // simulate progress
        let pct = 0;
        const iv = setInterval(() => {
            pct += Math.random() * 25 + 5;
            if (pct >= 100) { pct = 100; clearInterval(iv); }
            onProgress?.(Math.round(pct));
            if (pct >= 100) {
                const newFile = {
                    id: 'new-' + Date.now(),
                    type: 'file',
                    name: file.name,
                    ext: file.name.split('.').pop(),
                    size: file.size,
                    modified: new Date().toISOString(),
                    owner: 'You',
                };
                mockStore.files.unshift(newFile);
                onDone?.(newFile);
            }
        }, 200);
        return { abort: () => { clearInterval(iv); onError?.('Upload cancelled'); } };
    }

    /* REAL: XHR for progress tracking */
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/upload');
    xhr.setRequestHeader('X-CSRF-TOKEN', CSRF());
    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) onProgress?.(Math.round((e.loaded / e.total) * 100));
    });
    xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 300) {
            onDone?.(JSON.parse(xhr.responseText));
        } else {
            onError?.(xhr.statusText);
        }
    });
    xhr.addEventListener('error', () => onError?.('Network error'));
    xhr.send(formData);
    return { abort: () => xhr.abort() };
}

/**
 * PATCH /api/file/{id}    { name?, folder_id? }
 */
export async function updateFile(id, data) {
    if (USE_MOCKS) {
        await delay(300);
        const item = mockStore.files.find(f => f.id === id) || mockStore.folders.find(f => f.id === id);
        if (item && data.name) item.name = data.name;
        return item;
    }
    const res = await fetch(`/api/file/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
        body: JSON.stringify(data),
    });
    return res.json();
}

/**
 * DELETE /api/file/{id}
 */
export async function deleteFile(id) {
    if (USE_MOCKS) {
        await delay(200);
        let removed = null;
        mockStore.files = mockStore.files.filter(f => { if (f.id === id) { removed = f; return false; } return true; });
        mockStore.folders = mockStore.folders.filter(f => { if (f.id === id) { removed = f; return false; } return true; });
        if (removed) deletedItems.push(removed);
        return { success: true };
    }
    const res = await fetch(`/api/file/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF() },
    });
    return res.json();
}

/**
 * POST /api/file/{id}/share   → { url }
 */
export async function shareFile(id) {
    if (USE_MOCKS) {
        await delay(300);
        return { url: `${window.location.origin}/shared/${id}?token=${Math.random().toString(36).slice(2, 10)}` };
    }
    const res = await fetch(`/api/file/${id}/share`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF() },
    });
    return res.json();
}

/**
 * Undo last delete (mock only — in production, call your undo endpoint)
 */
export async function undoDelete() {
    if (USE_MOCKS) {
        await delay(200);
        const item = deletedItems.pop();
        if (!item) return null;
        if (item.type === 'folder') mockStore.folders.unshift(item);
        else mockStore.files.unshift(item);
        return item;
    }
    // REAL: POST /api/undo-delete
    return null;
}

/**
 * POST /api/folder   { name, parent_id }
 */
export async function createFolder(name, parentId = null) {
    if (USE_MOCKS) {
        await delay(300);
        const folder = {
            id: 'f-' + Date.now(),
            type: 'folder',
            name,
            items: 0,
            modified: new Date().toISOString(),
            owner: 'You',
        };
        mockStore.folders.unshift(folder);
        return folder;
    }
    const res = await fetch('/api/folder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() },
        body: JSON.stringify({ name, parent_id: parentId }),
    });
    return res.json();
}

/**
 * GET /api/notifications
 */
export async function fetchNotifications() {
    if (USE_MOCKS) {
        await delay(200);
        return [
            { id: 1, text: 'Budget.xlsx was shared with you', time: '2 hours ago', read: false },
            { id: 2, text: '3 files uploaded successfully', time: '1 day ago', read: true },
        ];
    }
    const res = await fetch('/api/notifications');
    return res.json();
}
