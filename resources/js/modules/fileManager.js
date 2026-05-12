/**
 * fileManager.js — File CRUD, selection, sort, pagination, keyboard nav
 * All actions now fully wired: preview, download, rename modal, move modal, share modal, star, trash
 */
import { fetchFiles, deleteFile, updateFile, shareFile, formatBytes, formatDate, getFileIcon } from './api.js';
import { showToast, confirmAction, showBottomSheet, switchView } from './ui.js';
import { openPreview } from './previewManager.js';
import { openRenameModal, openMoveModal, openShareModal } from './modalManager.js';

// ── State ────────────────────────────────────────────────────
let items = [];
let selectedIds = new Set();
let lastClickedIndex = -1;
let currentSort = localStorage.getItem('fileSort') || 'name';
let currentPage = 1;
let totalPages = 1;
let isLoading = false;

// Icon palette
const ICON_COLORS = {
    folder:       { bg: 'bg-amber-50',  text: 'text-amber-500',  fill: true },
    pdf:          { bg: 'bg-red-50',    text: 'text-red-400' },
    doc:          { bg: 'bg-blue-50',   text: 'text-blue-400' },
    spreadsheet:  { bg: 'bg-green-50',  text: 'text-green-400' },
    presentation: { bg: 'bg-orange-50', text: 'text-orange-400' },
    image:        { bg: 'bg-violet-50', text: 'text-violet-400' },
    video:        { bg: 'bg-amber-50',  text: 'text-amber-400' },
    audio:        { bg: 'bg-pink-50',   text: 'text-pink-400' },
    archive:      { bg: 'bg-gray-100',  text: 'text-gray-400' },
    generic:      { bg: 'bg-blue-50',   text: 'text-blue-400' },
};

const ICONS_SVG = {
    folder:       '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>',
    pdf:          '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    doc:          '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
    image:        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    video:        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
    audio:        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>',
    archive:      '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>',
    spreadsheet:  '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
    presentation: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>',
    generic:      '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
};

const ICONS_SVG_LG = Object.fromEntries(
    Object.entries(ICONS_SVG).map(([k,v]) => [k, v.replace(/w-5 h-5/g, 'w-8 h-8')])
);

const isTrash = () => window.__TRASH_MODE__ === true;

// ── Rendering ─────────────────────────────────────────────────
function renderGridItem(item) {
    const iconType = item.type === 'folder' ? 'folder' : getFileIcon(item.ext);
    const c = ICON_COLORS[iconType] || ICON_COLORS.generic;
    const sel = selectedIds.has(item.id);
    const starredBadge = item.starred
        ? `<span class="absolute top-2 left-2 z-10 text-amber-400" title="Starred">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
           </span>` : '';
    const sharedBadge = item.izin === 1
        ? `<span class="absolute top-2 right-8 z-10 w-1.5 h-1.5 bg-green-400 rounded-full" title="Public"></span>` : '';

    let content = `<span class="${c.text}">${ICONS_SVG_LG[iconType] || ICONS_SVG_LG.generic}</span>`;
    if (item.thumbnail_url) {
        content = `<img src="${item.thumbnail_url}" alt="${item.name}" class="w-full h-full object-cover rounded-xl shadow-sm">`;
    } else if (item.conversion_status === 'processing' || item.conversion_status === 'pending') {
        content = `<div class="flex flex-col items-center gap-1.5">
            <svg class="animate-spin h-5 w-5 ${c.text}" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
            <span class="text-[9px] font-bold ${c.text} opacity-80 uppercase tracking-tighter">Processing</span>
        </div>`;
    }

    return `
    <div data-item-id="${item.id}" data-item-type="${item.type}" data-item-name="${item.name}"
         data-item-starred="${item.starred||false}" data-item-izin="${item.izin||0}"
         class="group relative bg-white border ${sel ? 'border-blue-400 ring-2 ring-blue-100' : 'border-gray-200 hover:border-blue-300 hover:shadow-sm'} rounded-xl p-4 cursor-pointer transition-all duration-150"
         tabindex="0" role="option" aria-selected="${sel}" aria-label="${item.type}: ${item.name}">
        ${starredBadge}${sharedBadge}
        <input type="checkbox" ${sel ? 'checked' : ''}
               class="file-checkbox absolute top-3 left-3 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 opacity-0 group-hover:opacity-100 ${sel ? '!opacity-100' : ''} transition-opacity z-10"
               aria-label="Select ${item.name}">
        <button data-kebab class="absolute top-3 right-3 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity z-10" aria-label="Actions">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
        </button>
        <div class="w-full h-20 ${item.thumbnail_url ? '' : c.bg} rounded-xl flex items-center justify-center mb-3 overflow-hidden">
            ${content}
        </div>
        <p class="text-sm font-medium text-gray-800 truncate leading-tight">${item.name}</p>
        <div class="flex items-center justify-between mt-1.5">
            <span class="text-xs text-gray-400">${item.type === 'folder' ? (item.items||0)+' items' : formatBytes(item.size)}</span>
            ${isTrash() ? `<div class="flex gap-1">
                <button data-restore="${item.id}" class="text-xs px-2 py-0.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 font-medium transition-colors">Restore</button>
                <button data-perm-delete="${item.id}" class="text-xs px-2 py-0.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-medium transition-colors">Delete</button>
            </div>` : `<span class="text-xs text-gray-400">${formatDate(item.modified)}</span>`}
        </div>
    </div>`;
}

function renderListRow(item) {
    const iconType = item.type === 'folder' ? 'folder' : getFileIcon(item.ext);
    const c = ICON_COLORS[iconType] || ICON_COLORS.generic;
    const sel = selectedIds.has(item.id);
    return `
    <tr data-item-id="${item.id}" data-item-type="${item.type}" data-item-name="${item.name}"
        data-item-starred="${item.starred||false}" data-item-izin="${item.izin||0}"
        class="group ${sel ? 'bg-blue-50' : 'hover:bg-gray-50/80'} transition-colors cursor-pointer"
        tabindex="0" role="row" aria-selected="${sel}">
        <td class="px-4 py-3 w-10">
            <input type="checkbox" ${sel ? 'checked' : ''} class="file-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select ${item.name}">
        </td>
        <td class="px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 ${item.thumbnail_url ? '' : c.bg} rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                    ${item.thumbnail_url 
                        ? `<img src="${item.thumbnail_url}" alt="" class="w-full h-full object-cover">` 
                        : `<span class="${c.text}">${ICONS_SVG[iconType]||ICONS_SVG.generic}</span>`}
                </div>
                <div class="min-w-0">
                    <span class="text-sm font-medium text-gray-800 truncate max-w-[180px] block">${item.name}</span>
                    ${item.izin===1 ? '<span class="text-xs text-green-600 font-medium">Public</span>' : ''}
                    ${item.starred ? '<span class="text-xs text-amber-500">⭐</span>' : ''}
                </div>
            </div>
        </td>
        <td class="px-4 py-3 text-sm text-gray-400 hidden sm:table-cell">${item.owner||'—'}</td>
        <td class="px-4 py-3 text-sm text-gray-400 hidden md:table-cell">${item.type==='folder'?(item.items||0)+' items':formatBytes(item.size)}</td>
        <td class="px-4 py-3 text-sm text-gray-400 hidden lg:table-cell">${formatDate(item.modified)}</td>
        <td class="px-4 py-3 w-16">
            ${isTrash() ? `<div class="flex gap-1">
                <button data-restore="${item.id}" class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 font-medium">Restore</button>
                <button data-perm-delete="${item.id}" class="text-xs px-2 py-1 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-medium">Delete</button>
            </div>` : `<button data-kebab class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity" aria-label="Actions"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg></button>`}
        </td>
    </tr>`;
}

function renderPagination() {
    const el = document.getElementById('pagination');
    if (!el || totalPages <= 1) { el && (el.innerHTML=''); return; }
    let html = '<div class="flex items-center gap-1">';
    for (let p=1; p<=totalPages; p++) {
        html += `<button data-page="${p}" class="px-3 py-1.5 text-sm rounded-lg transition-colors ${p===currentPage?'bg-blue-600 text-white':'text-gray-500 hover:bg-gray-100'}">${p}</button>`;
    }
    html += '</div>';
    el.innerHTML = html;
    el.querySelectorAll('[data-page]').forEach(btn =>
        btn.addEventListener('click', () => { currentPage=parseInt(btn.dataset.page); loadFiles(); })
    );
}

function renderSelectionBar() {
    const bar = document.getElementById('selection-bar');
    if (!bar) return;
    if (selectedIds.size===0) { bar.classList.add('hidden'); return; }
    bar.classList.remove('hidden');
    bar.querySelector('#sel-count').textContent = `${selectedIds.size} selected`;
}

// ── SMART UPDATE: Partial DOM update based on data diff ────────
function smartUpdate(newItems) {
    const gridEl   = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    const emptyEl  = document.getElementById('empty-state');
    
    const newIds = new Set(newItems.map(i => i.id));
    const oldIds = new Set(items.map(i => i.id));
    
    // 1. Remove items no longer in server response
    items.forEach(oldItem => {
        if (!newIds.has(oldItem.id)) {
            gridEl?.querySelector(`[data-item-id="${oldItem.id}"]`)?.remove();
            listBody?.querySelector(`[data-item-id="${oldItem.id}"]`)?.remove();
        }
    });
    
    // 2. Update existing items (partial update)
    newItems.forEach(newItem => {
        const existingEl = gridEl?.querySelector(`[data-item-id="${newItem.id}"]`) 
                        || listBody?.querySelector(`[data-item-id="${newItem.id}"]`);
        
        if (existingEl) {
            // Partial update: only change what's different
            const nameEl = existingEl.querySelector('.text-sm.font-medium');
            if (nameEl && nameEl.textContent !== newItem.name) {
                nameEl.textContent = newItem.name;
            }
            
            // Update data attributes
            existingEl.dataset.itemName = newItem.name;
            existingEl.dataset.itemStarred = newItem.starred || false;
            existingEl.dataset.itemIzin = newItem.izin || 0;
        } else {
            // 3. Add new items
            if (gridEl) gridEl.insertAdjacentHTML('beforeend', renderGridItem(newItem));
            if (listBody) listBody.insertAdjacentHTML('beforeend', renderListRow(newItem));
        }
    });
    
    // 4. Handle empty state
    if (newItems.length === 0) {
        gridEl && (gridEl.innerHTML = '');
        listBody && (listBody.innerHTML = '');
        emptyEl?.classList.remove('hidden');
    } else {
        emptyEl?.classList.add('hidden');
    }
    
    // Update items array
    items = newItems;
    
    // Rebind events for new elements
    bindItemEvents();
}

// ── Load + render (with smart update) ──────────────────────────
export async function loadFiles(searchQuery='') {
    if (isLoading) return;
    isLoading = true;

    const gridEl   = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    const emptyEl  = document.getElementById('empty-state');
    const loadingEl= document.getElementById('loading-spinner');

    loadingEl?.classList.remove('hidden');
    emptyEl?.classList.add('hidden');

    try {
        const section = window.__FILE_SECTION__ || 'files';
        let endpoint = '/api/files';
        if (section === 'recent')  endpoint = '/api/files/recent';
        if (section === 'starred') endpoint = '/api/files/starred';
        if (section === 'shared')  endpoint = '/api/files/shared';
        if (section === 'trash')   endpoint = '/api/files/trash';

        let data;
        if (section === 'files') {
            const { fetchFiles: ff } = await import('./api.js');
            data = await ff({ q: searchQuery, sort: currentSort, page: currentPage });
        } else {
            const res = await fetch(endpoint);
            data = await res.json();
        }

        const newItems = data.data;
        totalPages = data.lastPage || 1;

        // SMART UPDATE: Use diff-based update instead of full re-render
        if (items.length === 0) {
            // First load - full render
            if (newItems.length === 0) {
                gridEl && (gridEl.innerHTML='');
                listBody && (listBody.innerHTML='');
                emptyEl?.classList.remove('hidden');
            } else {
                gridEl && (gridEl.innerHTML = newItems.map(renderGridItem).join(''));
                listBody && (listBody.innerHTML = newItems.map(renderListRow).join(''));
                emptyEl?.classList.add('hidden');
            }
        } else {
            // Subsequent loads - smart update
            smartUpdate(newItems);
        }

        renderPagination();
        renderSelectionBar();
    } catch(e) {
        showToast('Failed to load files: ' + e.message, 'error');
    } finally {
        isLoading = false;
        loadingEl?.classList.add('hidden');
    }
}

// ── Item events ────────────────────────────────────────────────
function bindItemEvents() {
    // Trash: restore / permanent delete buttons
    document.querySelectorAll('[data-restore]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.stopPropagation();
            const id = btn.dataset.restore;
            await fetch(`/api/file/${id}/restore`, { method:'POST', headers:{'X-CSRF-TOKEN': csrfToken()} });
            showToast('Item restored', 'success');
            loadFiles();
        });
    });
    document.querySelectorAll('[data-perm-delete]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.stopPropagation();
            const id = btn.dataset.permDelete;
            const ok = await confirmAction({ title:'Permanently delete?', message:'This cannot be undone.', confirmText:'Delete Forever' });
            if (!ok) return;
            await fetch(`/api/file/${id}/permanent`, { method:'DELETE', headers:{'X-CSRF-TOKEN': csrfToken()} });
            showToast('Permanently deleted', 'success');
            loadFiles();
        });
    });

    document.querySelectorAll('[data-item-id]').forEach((el, idx) => {
        el.addEventListener('click', e => {
            if (e.target.closest('[data-kebab]') || e.target.classList.contains('file-checkbox') ||
                e.target.closest('[data-restore]') || e.target.closest('[data-perm-delete]')) return;
            const id = el.dataset.itemId;
            if (e.ctrlKey || e.metaKey) {
                toggleSelect(id);
            } else if (e.shiftKey && lastClickedIndex >= 0) {
                const [start, end] = [Math.min(lastClickedIndex,idx), Math.max(lastClickedIndex,idx)];
                Array.from(document.querySelectorAll('[data-item-id]')).slice(start, end+1).forEach(el2 => addSelect(el2.dataset.itemId));
            } else {
                selectedIds.clear();
                addSelect(id);
            }
            lastClickedIndex = idx;
            refreshSelectionUI();
        });

        el.addEventListener('dblclick', () => handleOpen(el.dataset.itemId, el.dataset.itemType, el.dataset.itemName));

        el.querySelector('.file-checkbox')?.addEventListener('change', e => {
            e.stopPropagation();
            e.target.checked ? addSelect(el.dataset.itemId) : removeSelect(el.dataset.itemId);
            refreshSelectionUI();
        });
    });
}

// ── Helpers ────────────────────────────────────────────────────
function csrfToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
function addSelect(id)    { selectedIds.add(id); }
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

// ── Open / Preview ─────────────────────────────────────────────
function handleOpen(id, type, name) {
    if (type === 'folder') {
        const realId = id.replace(/^f/, '');
        window.location.href = `/folder_open/${realId}`;
    } else {
        openPreview(id, name);
    }
}

// ── Actions ────────────────────────────────────────────────────
export async function handleAction(action, item) {
    switch (action) {
        case 'open':
            handleOpen(item.id, item.type, item.name);
            break;

        case 'download':
            if (item.type === 'file') {
                window.location.href = `/download/${item.id}`;
            }
            break;

        case 'star': {
            const res = await fetch(`/api/file/${item.id}/star`, {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() }
            });
            const data = await res.json();
            showToast(data.starred ? '⭐ Added to Starred' : 'Removed from Starred', 'success');
            loadFiles();
            break;
        }

        case 'rename':
            openRenameModal(item, async (newName) => {
                await updateFile(item.id, { name: newName });
                showToast(`Renamed to "${newName}"`, 'success');
                loadFiles();
            });
            break;

        case 'move':
            openMoveModal(item, async (folderId) => {
                await fetch(`/api/file/${item.id}/move`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ folder_id: folderId }),
                });
                showToast(`"${item.name}" moved`, 'success');
                loadFiles();
            });
            break;

        case 'share':
            openShareModal(item);
            break;

        case 'delete': {
            const ok = await confirmAction({ title: `Delete "${item.name}"?`, message: 'File will be moved to Trash.' });
            if (!ok) return;
            await deleteFile(item.id);
            selectedIds.delete(item.id);
            showToast(`"${item.name}" moved to Trash`, 'success', {
                action: () => { window.location.href='/trash'; },
                actionLabel: 'View Trash',
            });
            loadFiles();
            break;
        }

        case 'restore': {
            await fetch(`/api/file/${item.id}/restore`, { method:'POST', headers:{'X-CSRF-TOKEN': csrfToken()} });
            showToast('Item restored', 'success');
            loadFiles();
            break;
        }
    }
}

// ── Bulk actions ───────────────────────────────────────────────
export async function bulkDelete() {
    const count = selectedIds.size;
    const ok = await confirmAction({ title: `Delete ${count} item(s)?`, message: 'They will be moved to Trash.' });
    if (!ok) return;

    // Parallelize deletions
    await Promise.all([...selectedIds].map(id => deleteFile(id)));

    selectedIds.clear();
    showToast(`${count} item(s) moved to Trash`, 'success');
    loadFiles();
}

export async function bulkDownload() {
    for (const id of selectedIds) {
        if (!id.startsWith('f')) {
            const a = document.createElement('a');
            a.href = `/download/${id}`;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            // Brief delay to prevent browser download congestion
            await new Promise(r => setTimeout(r, 200));
        }
    }
}

export async function bulkMove() {
    const fakeManyItem = { id: [...selectedIds][0], name: `${selectedIds.size} items` };
    openMoveModal(fakeManyItem, async (folderId) => {
        // Parallelize move operations
        await Promise.all([...selectedIds].map(id => {
            return fetch(`/api/file/${id}/move`, {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ folder_id: folderId }),
            });
        }));
        
        showToast(`${selectedIds.size} items moved`, 'success');
        selectedIds.clear();
        loadFiles();
    });
}

// ── Sort ───────────────────────────────────────────────────────
export function setSort(sort) {
    currentSort = sort;
    localStorage.setItem('fileSort', sort);
    currentPage = 1;
    loadFiles();
}

// ── Keyboard navigation ────────────────────────────────────────
export function initKeyboard() {
    document.addEventListener('keydown', e => {
        const active = document.activeElement;
        if (active?.tagName === 'INPUT' || active?.tagName === 'TEXTAREA') return;

        const allItems = Array.from(document.querySelectorAll('[data-item-id]'));
        const focusedIdx = allItems.indexOf(document.activeElement);

        switch (e.key) {
            case 'ArrowRight': case 'ArrowDown': {
                e.preventDefault();
                const next = focusedIdx < allItems.length-1 ? focusedIdx+1 : 0;
                allItems[next]?.focus(); break;
            }
            case 'ArrowLeft': case 'ArrowUp': {
                e.preventDefault();
                const prev = focusedIdx > 0 ? focusedIdx-1 : allItems.length-1;
                allItems[prev]?.focus(); break;
            }
            case 'Enter':
                if (focusedIdx >= 0) {
                    const el = allItems[focusedIdx];
                    handleOpen(el.dataset.itemId, el.dataset.itemType, el.dataset.itemName);
                } break;
            case ' ':
                e.preventDefault();
                if (focusedIdx >= 0) {
                    toggleSelect(allItems[focusedIdx].dataset.itemId);
                    refreshSelectionUI();
                } break;
            case 'Delete':
                if (selectedIds.size > 0) bulkDelete(); break;
            case 'a':
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    allItems.forEach(el => addSelect(el.dataset.itemId));
                    refreshSelectionUI();
                } break;
            case 'Escape':
                clearSelection(); break;
        }
    });
}

// ── Insert file without reload ─────────────────────────────────
export function insertFile(file) {
    const gridEl   = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    if (!gridEl && !listBody) return; // not on an explorer page
    items.unshift(file);
    if (gridEl) gridEl.insertAdjacentHTML('afterbegin', renderGridItem(file));
    if (listBody) listBody.insertAdjacentHTML('afterbegin', renderListRow(file));
    document.getElementById('empty-state')?.classList.add('hidden');
    bindItemEvents();
}

// ── Remove file without full reload ────────────────────────────
export function removeFile(id) {
    const gridEl   = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    
    // Remove from items array
    items = items.filter(item => item.id !== id);
    
    // Remove from DOM
    const gridItem = gridEl?.querySelector(`[data-item-id="${id}"]`);
    const listItem = listBody?.querySelector(`[data-item-id="${id}"]`);
    
    gridItem?.remove();
    listItem?.remove();
    
    // Show empty state if no items
    if (items.length === 0) {
        gridEl && (gridEl.innerHTML = '');
        listBody && (listBody.innerHTML = '');
        document.getElementById('empty-state')?.classList.remove('hidden');
    }
}

// ── Update file item without full reload ───────────────────────
export function updateFileItem(updatedFile) {
    const gridEl   = document.getElementById('grid-view');
    const listBody = document.getElementById('list-body');
    const id = updatedFile.id;
    
    // Update items array
    const idx = items.findIndex(item => item.id === id);
    if (idx >= 0) {
        items[idx] = { ...items[idx], ...updatedFile };
    }
    
    // Update DOM - partial update only
    const gridItem = gridEl?.querySelector(`[data-item-id="${id}"]`);
    const listItem = listBody?.querySelector(`[data-item-id="${id}"]`);
    
    if (gridItem) {
        // Update name
        const nameEl = gridItem.querySelector('.text-sm.font-medium');
        if (nameEl && updatedFile.name) nameEl.textContent = updatedFile.name;
        
        // Update starred badge
        if (updatedFile.starred !== undefined) {
            const existingBadge = gridItem.querySelector('.text-amber-400');
            if (updatedFile.starred && !existingBadge) {
                const badge = document.createElement('span');
                badge.className = 'absolute top-2 left-2 z-10 text-amber-400';
                badge.title = 'Starred';
                badge.innerHTML = '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
                gridItem.insertBefore(badge, gridItem.firstChild);
            } else if (!updatedFile.starred && existingBadge) {
                existingBadge.remove();
            }
        }
        
        // Update izin badge
        if (updatedFile.izin !== undefined) {
            const existingBadge = gridItem.querySelector('.bg-green-400');
            if (updatedFile.izin === 1 && !existingBadge) {
                const badge = document.createElement('span');
                badge.className = 'absolute top-2 right-8 z-10 w-1.5 h-1.5 bg-green-400 rounded-full';
                badge.title = 'Public';
                gridItem.insertBefore(badge, gridItem.firstChild);
            } else if (updatedFile.izin !== 1 && existingBadge) {
                existingBadge.remove();
            }
        }
    }
    
    if (listItem) {
        // Update name
        const nameEl = listItem.querySelector('.text-sm.font-medium');
        if (nameEl && updatedFile.name) nameEl.textContent = updatedFile.name;
        
        // Update badges
        if (updatedFile.izin !== undefined) {
            const existingPublic = listItem.querySelector('.text-green-600');
            if (updatedFile.izin === 1 && !existingPublic) {
                const span = document.createElement('span');
                span.className = 'text-xs text-green-600 font-medium';
                span.textContent = 'Public';
                nameEl?.parentNode.appendChild(span);
            } else if (updatedFile.izin !== 1 && existingPublic) {
                existingPublic.remove();
            }
        }
        
        if (updatedFile.starred !== undefined) {
            const existingStar = listItem.querySelector('.text-amber-500');
            if (updatedFile.starred && !existingStar) {
                const span = document.createElement('span');
                span.className = 'text-xs text-amber-500';
                span.textContent = '⭐';
                nameEl?.parentNode.appendChild(span);
            } else if (!updatedFile.starred && existingStar) {
                existingStar.remove();
            }
        }
    }
}
