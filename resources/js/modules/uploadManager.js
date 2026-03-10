/**
 * uploadManager.js — Drag & drop, multi-file upload, per-file progress, cancel
 */
import { uploadFile } from './api.js';
import { showToast } from './ui.js';
import { insertFile } from './fileManager.js';

let activeUploads = new Map(); // id → { abort, el }

// ── Drag & drop ─────────────────────────────────────────────
let dragCount = 0;

export function initDragDrop() {
    const overlay = document.getElementById('drop-overlay');

    window.addEventListener('dragenter', e => {
        e.preventDefault();
        dragCount++;
        overlay?.classList.remove('opacity-0', 'pointer-events-none');
    });

    window.addEventListener('dragleave', e => {
        e.preventDefault();
        dragCount--;
        if (dragCount <= 0) { dragCount = 0; overlay?.classList.add('opacity-0', 'pointer-events-none'); }
    });

    window.addEventListener('dragover', e => e.preventDefault());

    window.addEventListener('drop', e => {
        e.preventDefault();
        dragCount = 0;
        overlay?.classList.add('opacity-0', 'pointer-events-none');
        if (e.dataTransfer.files.length) processFiles(Array.from(e.dataTransfer.files));
    });
}

// ── File input trigger ──────────────────────────────────────
export function initFileInput() {
    const input = document.getElementById('file-input');
    document.getElementById('upload-btn')?.addEventListener('click', () => input?.click());
    document.getElementById('mobile-fab')?.addEventListener('click', () => input?.click());
    input?.addEventListener('change', function () {
        if (this.files.length) processFiles(Array.from(this.files));
        this.value = '';
    });
}

// ── Process uploads ─────────────────────────────────────────
function processFiles(files) {
    const panel = document.getElementById('upload-progress-panel');
    const list = document.getElementById('upload-progress-list');
    const title = document.getElementById('upload-progress-title');

    panel?.classList.remove('hidden');
    title.textContent = `Uploading ${files.length} file(s)…`;

    files.forEach(file => {
        const uid = 'u-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6);

        // Create progress row
        const row = document.createElement('div');
        row.id = uid;
        row.className = 'px-4 py-3 border-b border-gray-100 flex items-center gap-3';
        row.innerHTML = `
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">${file.name}</p>
                <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden mt-1">
                    <div class="upload-bar h-full bg-blue-600 rounded-full transition-all duration-200" style="width:0%"></div>
                </div>
            </div>
            <span class="upload-pct text-xs text-gray-400 shrink-0 w-8 text-right">0%</span>
            <button class="upload-cancel p-1 text-gray-400 hover:text-red-500 rounded transition-colors" aria-label="Cancel upload">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;
        list?.appendChild(row);

        const bar = row.querySelector('.upload-bar');
        const pct = row.querySelector('.upload-pct');
        const cancelBtn = row.querySelector('.upload-cancel');

        const handle = uploadFile(file, null, {
            onProgress(p) {
                bar.style.width = p + '%';
                pct.textContent = p + '%';
            },
            onDone(newFile) {
                bar.classList.replace('bg-blue-600', 'bg-green-500');
                bar.style.width = '100%';
                pct.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                cancelBtn.classList.add('hidden');
                activeUploads.delete(uid);
                showToast(`"${file.name}" uploaded`, 'success');
                insertFile(newFile);
                // auto-hide row after 3s
                setTimeout(() => { row.remove(); if (!list.children.length) panel.classList.add('hidden'); }, 3000);
            },
            onError(err) {
                bar.classList.replace('bg-blue-600', 'bg-red-500');
                pct.innerHTML = '<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>';
                cancelBtn.classList.add('hidden');
                activeUploads.delete(uid);
                showToast(`Failed: "${file.name}" — ${err}`, 'error');
            },
        });

        activeUploads.set(uid, handle);

        cancelBtn.addEventListener('click', () => {
            handle.abort();
            row.remove();
            activeUploads.delete(uid);
            if (!list.children.length) panel.classList.add('hidden');
        });
    });
}
