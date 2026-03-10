/**
 * fileManager.js — File CRUD, selection (shift/ctrl/checkbox), sort, pagination, keyboard nav
 */
import { fetchFiles, deleteFile, updateFile, shareFile, undoDelete, formatBytes, formatDate, getFileIcon } from './api.js';
import { showToast, confirmAction, showBottomSheet, switchView } from './ui.js';

// ── State ───────────────────────────────────────────────────
let items = [];
let selectedIds = new Set();
let lastClickedIndex = -1;
let currentSort = localStorage.getItem('fileSort') || 'name';
let currentPage = 1;
let totalPages = 1;
let isLoading = false;

// Icon colors
const ICON_COLORS = {
    folder: { bg: 'bg-amber-50', text: 'text-amber-500', fill: true },
    pdf: { bg: 'bg-red-50', text: 'text-red-400' },
    doc: { bg: 'bg-blue-50', text: 'text-blue-400' },
    spreadsheet: { bg: 'bg-green-50', text: 'text-green-400' },
    presentation: { bg: 'bg-orange-50', text: 'text-orange-400' },
    image: { bg: 'bg-violet-50', text: 'text-violet-400' },
    video: { bg: 'bg-amber-50', text: 'text-amber-400' },
    audio: { bg: 'bg-pink-50', text: 'text-pink-400' },
    archive: { bg: 'bg-gray-100', text: 'text-gray-400' },
    generic: { bg: 'bg-blue-50', text: 'text-blue-400' },
};

// SVG icons by type (small)
const ICONS_SVG = {
    folder: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>',
    pdf: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    doc: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
    image: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    video: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
    audio: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>',
    archive: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>',
    spreadsheet: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
    presentation: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>',
    generic: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
};

// Big icons for grid cards
const ICONS_SVG_LG = Object.fromEntries(Object.entries(ICONS_SVG).map(([k, v]) => [k, v.replace(/w-5 h-5/g, 'w-8 h-8')]));

// ── Rendering ───────────────────────────────────────────────
function renderGridItem(item) {
    const iconType = item.type === 'folder' ? 'folder' : getFileIcon(item.ext);
    const c = ICON_COLORS[iconType] || ICON_COLORS.generic;
    const sel = selectedIds.has(item.id);
    return `
    <div data-item-id="${item.id}" data-item-type="${item.type}" data-item-name="${item.name}"
         class="group relative bg-white border ${sel ? 'border-blue-400 ring-2 ring-blue-100' : 'border-gray-200 hover:border-gray-300'} rounded-xl p-4 cursor-pointer transition-all"
         tabindex="0" role="option" aria-selected="${sel}" aria-label="${item.type}: ${item.name}">
        <input type="checkbox" ${sel ? 'checked' : ''}
               class="file-checkbox absolute top-3 left-3 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 opacity-0 group-hover:opacity-100 ${sel ? '!opacity-100' : ''} transition-opacity z-10"
               aria-label="Select ${item.name}">
        <button data-kebab class="absolute top-3 right-3 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity z-10" aria-label="Actions">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
        </button>
        <div class="w-full h-24 ${c.bg} rounded-lg flex items-center justify-center mb-3">
            <span class="${c.text}">${ICONS_SVG_LG[iconType] || ICONS_SVG_LG.generic}</span>
        </div>
        <p class="text-sm font-medium text-gray-800 truncate">${item.name}</p>
        <div class="flex items-center justify-between mt-1">
            <span class="text-xs text-gray-400">${item.type === 'folder' ? (item.items || 0) + ' items' : formatBytes(item.size)}</span>
            <span class="text-xs text-gray-400">${formatDate(item.modified)}</span>
        </div>
    </div>`;
}

function renderListRow(item) {
    const iconType = item.type === 'folder' ? 'folder' : getFileIcon(item.ext);
    const c = ICON_COLORS[iconType] || ICON_COLORS.generic;
    const sel = selectedIds.has(item.id);
    return `
    <tr data-item-id="${item.id}" data-item-type="${item.type}" data-item-name="${item.name}"
        class="group ${sel ? 'bg-blue-50' : 'hover:bg-gray-50'} transition-colors cursor-pointer"
        tabindex="0" role="row" aria-selected="${sel}">
        <td class="px-4 py-3 w-10">
            <input type="checkbox" ${sel ? 'checked' : ''} class="file-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select ${item.name}">
        </td>
        <td class="px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 ${c.bg} rounded-lg flex items-center justify-center shrink-0"><span class="${c.text}">${ICONS_SVG[iconType] || ICONS_SVG.generic}</span></div>
                <span class="text-sm font-medium text-gray-800 truncate max-w-[200px]">${item.name}</span>
            </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-500 hidden sm:table-cell">${item.owner || '—'}</td>
        <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">${item.type === 'folder' ? (item.items || 0) + ' items' : formatBytes(item.size)}</td>
        <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">${formatDate(item.modified)}</td>
        <td class="px-4 py-3 w-10">
            <button data-kebab class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity" aria-label="Actions">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
            </button>
        </td>
    </tr>`;
}

function renderPagination() {
    const el = document.getElementById('pagination');
    if (!el || totalPages <= 1) { el && (el.innerHTML = ''); return; }
    let html = '<div class="flex items-center gap-1">';
    for (let p = 1; p <= totalPages; p++) {
        html += `<button data-page="${p}" class="px-3 py-1.5 text-sm rounded-lg transition-colors ${p === currentPage ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'}">${p}</button>`;
    }
    html += '</div>';
    el.innerHTML = html;
    el.querySelectorAll('[data-page]').forEach(btn => {
        btn.addEventListener('click', () => { currentPage = parseInt(btn.dataset.page); loadFiles(); });
    });
}

function renderSelectionBar() {
    const bar = document.getElementById('selection-bar');
    if (!bar) return;
    if (selectedIds.size === 0) { bar.classList.add('hidden'); return; }
    bar.classList.remove('hidden');
    bar.querySelector('#sel-count').textContent = `${selectedIds.size} selected`;
}

// ── Load + render ───────────────────────────────────────────
export async function loadFiles(searchQuery = '') {
    if (isLoading) return;
    isLoading = true;

    const gridEl = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    const emptyEl = document.getElementById('empty-state');
    const loadingEl = document.getElementById('loading-spinner');

    loadingEl?.classList.remove('hidden');
    emptyEl?.classList.add('hidden');

    try {
        const data = await fetchFiles({ q: searchQuery, sort: currentSort, page: currentPage });
        items = data.data;
        totalPages = data.lastPage;

        if (items.length === 0) {
            gridEl && (gridEl.innerHTML = '');
            listBody && (listBody.innerHTML = '');
            emptyEl?.classList.remove('hidden');
        } else {
            gridEl && (gridEl.innerHTML = items.map(renderGridItem).join(''));
            listBody && (listBody.innerHTML = items.map(renderListRow).join(''));
            emptyEl?.classList.add('hidden');
        }

        renderPagination();
        renderSelectionBar();
        bindItemEvents();
    } finally {
        isLoading = false;
        loadingEl?.classList.add('hidden');
    }
}

// ── Item events ─────────────────────────────────────────────
function bindItemEvents() {
    document.querySelectorAll('[data-item-id]').forEach((el, idx) => {
        // click — select or open
        el.addEventListener('click', e => {
            if (e.target.closest('[data-kebab]') || e.target.classList.contains('file-checkbox')) return;

            if (e.target.classList.contains('file-checkbox')) return; // handled separately
            const id = el.dataset.itemId;

            if (e.ctrlKey || e.metaKey) {
                toggleSelect(id);
            } else if (e.shiftKey && lastClickedIndex >= 0) {
                const currIdx = idx;
                const [start, end] = [Math.min(lastClickedIndex, currIdx), Math.max(lastClickedIndex, currIdx)];
                const allEls = document.querySelectorAll('[data-item-id]');
                for (let i = start; i <= end; i++) addSelect(allEls[i].dataset.itemId);
            } else {
                selectedIds.clear();
                addSelect(id);
            }

            lastClickedIndex = idx;
            refreshSelectionUI();
        });

        // double-click — open
        el.addEventListener('dblclick', () => handleOpen(el.dataset.itemId, el.dataset.itemType));

        // checkbox
        el.querySelector('.file-checkbox')?.addEventListener('change', e => {
            e.stopPropagation();
            if (e.target.checked) addSelect(el.dataset.itemId);
            else removeSelect(el.dataset.itemId);
            refreshSelectionUI();
        });
    });
}

// ── Selection helpers ───────────────────────────────────────
function addSelect(id) { selectedIds.add(id); }
function removeSelect(id) { selectedIds.delete(id); }
function toggleSelect(id) { selectedIds.has(id) ? selectedIds.delete(id) : selectedIds.add(id); }

function refreshSelectionUI() {
    document.querySelectorAll('[data-item-id]').forEach(el => {
        const sel = selectedIds.has(el.dataset.itemId);
        el.classList.toggle('border-blue-400', sel);
        el.classList.toggle('ring-2', sel);
        el.classList.toggle('ring-blue-100', sel);
        el.classList.toggle('bg-blue-50', sel);
        el.setAttribute('aria-selected', sel);
        const cb = el.querySelector('.file-checkbox');
        if (cb) cb.checked = sel;
    });
    renderSelectionBar();
}

export function clearSelection() { selectedIds.clear(); refreshSelectionUI(); }

// ── Actions ─────────────────────────────────────────────────
function handleOpen(id, type) {
    if (type === 'folder') {
        // WIRE: navigate to folder
        showToast(`Opening folder — wire to /folder/${id}`, 'success');
    } else {
        // WIRE: open file preview
        showToast(`Preview file — wire to /open_file/${id}`, 'success');
    }
}

export async function handleAction(action, item) {
    switch (action) {
        case 'open':
            handleOpen(item.id, item.type);
            break;

        case 'download':
            // WIRE: window.location.href = `/download/${item.id}`;
            showToast(`Downloading "${item.name}"…`, 'success');
            break;

        case 'rename': {
            const newName = prompt('Enter new name:', item.name);
            if (newName && newName !== item.name) {
                await updateFile(item.id, { name: newName });
                showToast(`Renamed to "${newName}"`, 'success');
                loadFiles();
            }
            break;
        }

        case 'move':
            // WIRE: open folder picker modal
            showToast(`Move "${item.name}" — wire folder picker`, 'success');
            break;

        case 'share': {
            const result = await shareFile(item.id);
            if (result.url) {
                await navigator.clipboard.writeText(result.url);
                showToast('Link copied to clipboard!', 'success');
            }
            break;
        }

        case 'delete': {
            const ok = await confirmAction({ title: `Delete "${item.name}"?`, message: 'This cannot be undone.' });
            if (!ok) return;
            await deleteFile(item.id);
            selectedIds.delete(item.id);
            showToast(`"${item.name}" deleted`, 'success', {
                action: async () => {
                    const restored = await undoDelete();
                    if (restored) { showToast(`"${restored.name}" restored`, 'success'); loadFiles(); }
                },
                actionLabel: 'Undo',
            });
            loadFiles();
            break;
        }
    }
}

// ── Bulk actions ────────────────────────────────────────────
export async function bulkDelete() {
    const count = selectedIds.size;
    const ok = await confirmAction({ title: `Delete ${count} item(s)?`, message: 'This cannot be undone.' });
    if (!ok) return;
    for (const id of selectedIds) await deleteFile(id);
    selectedIds.clear();
    showToast(`${count} item(s) deleted`, 'success');
    loadFiles();
}

export async function bulkDownload() {
    showToast(`Downloading ${selectedIds.size} file(s)… — wire multi-download endpoint`, 'success');
}

export async function bulkMove() {
    showToast(`Moving ${selectedIds.size} item(s)… — wire folder picker`, 'success');
}

// ── Sort ────────────────────────────────────────────────────
export function setSort(sort) {
    currentSort = sort;
    localStorage.setItem('fileSort', sort);
    currentPage = 1;
    loadFiles();
}

// ── Keyboard navigation ─────────────────────────────────────
export function initKeyboard() {
    document.addEventListener('keydown', e => {
        // only when focus is inside file area or nothing specific
        const active = document.activeElement;
        if (active?.tagName === 'INPUT' || active?.tagName === 'TEXTAREA') return;

        const allItems = Array.from(document.querySelectorAll('[data-item-id]'));
        const focusedIdx = allItems.indexOf(document.activeElement);

        switch (e.key) {
            case 'ArrowRight':
            case 'ArrowDown': {
                e.preventDefault();
                const next = focusedIdx < allItems.length - 1 ? focusedIdx + 1 : 0;
                allItems[next]?.focus();
                break;
            }
            case 'ArrowLeft':
            case 'ArrowUp': {
                e.preventDefault();
                const prev = focusedIdx > 0 ? focusedIdx - 1 : allItems.length - 1;
                allItems[prev]?.focus();
                break;
            }
            case 'Enter': {
                if (focusedIdx >= 0) {
                    const el = allItems[focusedIdx];
                    handleOpen(el.dataset.itemId, el.dataset.itemType);
                }
                break;
            }
            case ' ': {
                e.preventDefault();
                if (focusedIdx >= 0) {
                    const el = allItems[focusedIdx];
                    toggleSelect(el.dataset.itemId);
                    refreshSelectionUI();
                }
                break;
            }
            case 'Delete': {
                if (selectedIds.size > 0) bulkDelete();
                break;
            }
            case 'a': {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    allItems.forEach(el => addSelect(el.dataset.itemId));
                    refreshSelectionUI();
                }
                break;
            }
            case 'Escape':
                clearSelection();
                break;
        }
    });
}

// ── Insert file without reload (called by upload) ───────────
export function insertFile(file) {
    items.unshift(file);
    const gridEl = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    if (gridEl) gridEl.insertAdjacentHTML('afterbegin', renderGridItem(file));
    if (listBody) listBody.insertAdjacentHTML('afterbegin', renderListRow(file));
    document.getElementById('empty-state')?.classList.add('hidden');
    bindItemEvents();
}
