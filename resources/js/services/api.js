/**
 * API Service — Stub implementations for cloud storage endpoints.
 * Replace these stubs with actual fetch/axios calls to your Laravel backend.
 *
 * Endpoints:
 *   GET    /api/files?folder_id=&q=&view=&page=   — List files and folders
 *   POST   /api/upload                             — Upload file(s)
 *   DELETE /api/files/:id                          — Delete a file
 *   PATCH  /api/files/:id                          — Rename / move a file
 *   POST   /api/files/:id/share                    — Share a file
 *   GET    /api/storage                            — Get storage usage
 */

// ── Stub data ──────────────────────────────────────────────────────
const STUB_FOLDERS = [
    { id: 'f1', name: 'Documents', type: 'folder', owner: 'You', modified: '2026-03-08T10:30:00Z', items: 12 },
    { id: 'f2', name: 'Photos', type: 'folder', owner: 'You', modified: '2026-03-07T14:00:00Z', items: 48 },
    { id: 'f3', name: 'Projects', type: 'folder', owner: 'You', modified: '2026-03-05T09:15:00Z', items: 7 },
    { id: 'f4', name: 'Music', type: 'folder', owner: 'You', modified: '2026-02-28T16:45:00Z', items: 23 },
];

const STUB_FILES = [
    { id: '1', name: 'presentation.pptx', type: 'file', ext: 'pptx', size: 4520000, owner: 'You', modified: '2026-03-09T08:00:00Z', starred: false },
    { id: '2', name: 'budget-2026.xlsx', type: 'file', ext: 'xlsx', size: 125000, owner: 'You', modified: '2026-03-08T14:30:00Z', starred: true },
    { id: '3', name: 'team-photo.jpg', type: 'file', ext: 'jpg', size: 3200000, owner: 'You', modified: '2026-03-07T11:20:00Z', starred: false },
    { id: '4', name: 'meeting-notes.pdf', type: 'file', ext: 'pdf', size: 890000, owner: 'You', modified: '2026-03-06T16:45:00Z', starred: false },
    { id: '5', name: 'design-mockup.fig', type: 'file', ext: 'fig', size: 15600000, owner: 'You', modified: '2026-03-05T09:30:00Z', starred: true },
    { id: '6', name: 'report-final.docx', type: 'file', ext: 'docx', size: 2340000, owner: 'You', modified: '2026-03-04T13:15:00Z', starred: false },
    { id: '7', name: 'demo-video.mp4', type: 'file', ext: 'mp4', size: 89000000, owner: 'You', modified: '2026-03-03T10:00:00Z', starred: false },
    { id: '8', name: 'analytics-data.csv', type: 'file', ext: 'csv', size: 456000, owner: 'You', modified: '2026-03-02T15:40:00Z', starred: false },
];

// ── Helpers ──────────────────────────────────────────────────────
function delay(ms = 300) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

export function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

export function formatDate(isoString) {
    const d = new Date(isoString);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

export function getFileIcon(ext) {
    const map = {
        pdf: 'pdf', doc: 'doc', docx: 'doc', txt: 'doc',
        xls: 'spreadsheet', xlsx: 'spreadsheet', csv: 'spreadsheet',
        ppt: 'presentation', pptx: 'presentation',
        jpg: 'image', jpeg: 'image', png: 'image', gif: 'image', svg: 'image', webp: 'image',
        mp4: 'video', webm: 'video', mov: 'video', avi: 'video',
        mp3: 'audio', wav: 'audio', ogg: 'audio',
        zip: 'archive', rar: 'archive', '7z': 'archive', tar: 'archive', gz: 'archive',
    };
    return map[ext?.toLowerCase()] || 'generic';
}

// ── API functions (stubs) ───────────────────────────────────────
/**
 * GET /api/files?folder_id=&q=&view=&page=
 * Fetches files and folders for a given folder.
 */
export async function fetchFiles(folderId = null, query = '', page = 1) {
    await delay(400);
    let folders = [...STUB_FOLDERS];
    let files = [...STUB_FILES];

    if (query) {
        const q = query.toLowerCase();
        folders = folders.filter((f) => f.name.toLowerCase().includes(q));
        files = files.filter((f) => f.name.toLowerCase().includes(q));
    }

    return { folders, files, page, totalPages: 1 };
}

/**
 * POST /api/upload
 * Uploads file(s). In production, use FormData with the actual file.
 */
export async function uploadFile(file, folderId = null, onProgress) {
    // Simulate upload progress
    return new Promise((resolve) => {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 25;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                onProgress?.(100);
                resolve({
                    id: 'new-' + Date.now(),
                    name: file.name,
                    type: 'file',
                    ext: file.name.split('.').pop(),
                    size: file.size,
                    owner: 'You',
                    modified: new Date().toISOString(),
                    starred: false,
                });
            } else {
                onProgress?.(Math.round(progress));
            }
        }, 200);
    });
}

/**
 * DELETE /api/files/:id
 */
export async function deleteFile(fileId) {
    await delay(300);
    return { success: true, id: fileId };
}

/**
 * PATCH /api/files/:id  — body: { name }
 */
export async function renameFile(fileId, newName) {
    await delay(300);
    return { success: true, id: fileId, name: newName };
}

/**
 * PATCH /api/files/:id  — body: { folder_id }
 */
export async function moveFile(fileId, targetFolderId) {
    await delay(300);
    return { success: true, id: fileId, folder_id: targetFolderId };
}

/**
 * POST /api/files/:id/share
 */
export async function shareFile(fileId, email, permission = 'view') {
    await delay(300);
    return { success: true, id: fileId, shared_with: email, permission };
}

/**
 * POST /api/folders  — body: { name, parent_id }
 */
export async function createFolder(name, parentId = null) {
    await delay(300);
    return {
        id: 'folder-' + Date.now(),
        name,
        type: 'folder',
        owner: 'You',
        modified: new Date().toISOString(),
        items: 0,
    };
}

/**
 * GET /api/storage
 */
export async function fetchStorageUsage() {
    await delay(200);
    return {
        used: 2147483648,   // 2 GB
        total: 16106127360,  // 15 GB
    };
}
