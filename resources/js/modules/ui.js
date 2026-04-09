/**
 * ui.js — Toasts, modals, sidebar, context menu, bottom-sheet, view toggle
 */

// ── Toast ───────────────────────────────────────────────────
const toastBox = () => document.getElementById('toast-container');

export function showToast(message, type = 'success', { duration = 4000, action = null, actionLabel = 'Undo' } = {}) {
    const el = document.createElement('div');
    el.className = 'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-md border bg-white border-gray-200 max-w-sm transition-all duration-300';
    const iconSvg = type === 'success'
        ? '<svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        : '<svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    const actionHtml = action
        ? `<button class="toast-action text-xs font-semibold text-blue-600 hover:underline ml-1 shrink-0">${actionLabel}</button>`
        : '';
    el.innerHTML = iconSvg + `<span class="text-sm font-medium text-gray-800">${message}</span>` + actionHtml;

    if (action) el.querySelector('.toast-action').addEventListener('click', () => { action(); el.remove(); });

    toastBox()?.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, duration);
    return el;
}

// ── Confirm modal ───────────────────────────────────────────
let confirmResolver = null;

export function initConfirmModal() {
    document.getElementById('confirm-cancel-btn')?.addEventListener('click', () => {
        document.getElementById('confirm-modal')?.classList.add('hidden');
        confirmResolver?.(false);
    });
    document.getElementById('confirm-modal')?.addEventListener('click', e => {
        if (e.target.id === 'confirm-modal') { e.target.classList.add('hidden'); confirmResolver?.(false); }
    });
}

export function confirmAction({ title = 'Are you sure?', message = 'This cannot be undone.', confirmText = 'Delete', danger = true } = {}) {
    return new Promise(resolve => {
        confirmResolver = resolve;
        const modal = document.getElementById('confirm-modal');
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').textContent = message;
        const btn = document.getElementById('confirm-ok-btn');
        btn.textContent = confirmText;
        btn.className = `px-4 py-2 text-sm font-medium rounded-xl text-white transition-colors ${danger ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-600 hover:bg-blue-700'}`;
        btn.onclick = () => { modal.classList.add('hidden'); resolve(true); };
        modal?.classList.remove('hidden');
    });
}

// ── Sidebar ─────────────────────────────────────────────────
let sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

function applySidebarState() {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main-content');
    if (!sidebar || window.innerWidth < 1024) return;

    const labels = sidebar.querySelectorAll('.sidebar-label');
    const storageBlock = document.getElementById('storage-block');
    const storageIcon = document.getElementById('storage-icon');
    const colLeft = document.getElementById('collapse-icon-left');
    const colRight = document.getElementById('collapse-icon-right');

    if (sidebarCollapsed) {
        sidebar.classList.remove('w-60');
        sidebar.classList.add('w-16');
        main?.classList.remove('lg:ml-60');
        main?.classList.add('lg:ml-16');
        labels.forEach(l => l.classList.add('lg:hidden'));
        storageBlock?.classList.add('hidden');
        storageIcon?.classList.remove('hidden'); storageIcon?.classList.add('flex');
        colLeft?.classList.add('hidden'); colRight?.classList.remove('hidden');
    } else {
        sidebar.classList.remove('w-16');
        sidebar.classList.add('w-60');
        main?.classList.remove('lg:ml-16');
        main?.classList.add('lg:ml-60');
        labels.forEach(l => l.classList.remove('lg:hidden'));
        storageBlock?.classList.remove('hidden');
        storageIcon?.classList.add('hidden'); storageIcon?.classList.remove('flex');
        colLeft?.classList.remove('hidden'); colRight?.classList.add('hidden');
    }
}

export function initSidebar() {
    applySidebarState();
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        overlay?.classList.toggle('hidden');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    });
    document.getElementById('sidebar-collapse-btn')?.addEventListener('click', () => {
        sidebarCollapsed = !sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
        applySidebarState();
    });
}

// ── Mobile search ───────────────────────────────────────────
export function initMobileSearch() {
    const bar = document.getElementById('mobile-search-bar');
    document.getElementById('mobile-search-toggle')?.addEventListener('click', () => {
        bar?.classList.remove('hidden');
        bar?.querySelector('input')?.focus();
    });
    document.getElementById('mobile-search-cancel')?.addEventListener('click', () => {
        bar?.classList.add('hidden');
    });
}

// ── User dropdown ───────────────────────────────────────────
export function initUserDropdown() {
    document.getElementById('user-dropdown-btn')?.addEventListener('click', () => {
        document.getElementById('user-dropdown')?.classList.toggle('hidden');
    });
    document.addEventListener('click', e => {
        const w = document.getElementById('user-dropdown-wrapper');
        if (w && !w.contains(e.target)) document.getElementById('user-dropdown')?.classList.add('hidden');
    });
}

// ── View toggle (grid/list) — persisted ─────────────────────
export function initViewToggle(onSwitch) {
    const saved = localStorage.getItem('fileView') || 'grid';
    switchView(saved);

    document.getElementById('grid-btn')?.addEventListener('click', () => { switchView('grid'); onSwitch?.('grid'); });
    document.getElementById('list-btn')?.addEventListener('click', () => { switchView('list'); onSwitch?.('list'); });
}

export function switchView(view) {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridBtn = document.getElementById('grid-btn');
    const listBtn = document.getElementById('list-btn');
    if (!gridView || !listView) return;

    const isGrid = view === 'grid';
    gridView.classList.toggle('hidden', !isGrid);
    listView.classList.toggle('hidden', isGrid);
    gridBtn?.classList.toggle('bg-white', isGrid); gridBtn?.classList.toggle('shadow-sm', isGrid);
    gridBtn?.classList.toggle('text-blue-600', isGrid); gridBtn?.classList.toggle('text-gray-400', !isGrid);
    listBtn?.classList.toggle('bg-white', !isGrid); listBtn?.classList.toggle('shadow-sm', !isGrid);
    listBtn?.classList.toggle('text-blue-600', !isGrid); listBtn?.classList.toggle('text-gray-400', isGrid);
    localStorage.setItem('fileView', view);
}

// ── Context menu (desktop right-click) ──────────────────────
let ctxTargetItem = null;
export function getCtxTarget() { return ctxTargetItem; }

export function initContextMenu(actionHandler) {
    const menu = document.getElementById('context-menu');
    document.addEventListener('contextmenu', e => {
        const card = e.target.closest('[data-item-id]');
        if (!card || !menu) return;
        e.preventDefault();
        ctxTargetItem = {
            id: card.dataset.itemId,
            type: card.dataset.itemType,
            name: card.dataset.itemName,
        };
        // hide download for folders
        menu.querySelector('[data-ctx="download"]')?.classList.toggle('hidden', ctxTargetItem.type === 'folder');
        menu.style.left = Math.min(e.clientX, window.innerWidth - 200) + 'px';
        menu.style.top = Math.min(e.clientY, window.innerHeight - 280) + 'px';
        menu.classList.remove('hidden');
    });

    menu?.querySelectorAll('[data-ctx]').forEach(btn => {
        btn.addEventListener('click', () => {
            menu.classList.add('hidden');
            actionHandler?.(btn.dataset.ctx, ctxTargetItem);
        });
    });

    document.addEventListener('click', e => { if (!menu?.contains(e.target)) menu?.classList.add('hidden'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') menu?.classList.add('hidden'); });
}

// ── Bottom sheet (mobile actions) ───────────────────────────
let bsTarget = null;
export function getBsTarget() { return bsTarget; }

export function showBottomSheet(item, actionHandler) {
    bsTarget = item;
    const sheet = document.getElementById('bottom-sheet');
    const title = document.getElementById('bs-title');
    if (!sheet) return;
    title.textContent = item.name;
    // hide download for folders
    sheet.querySelector('[data-bs="download"]')?.classList.toggle('hidden', item.type === 'folder');

    sheet.querySelectorAll('[data-bs]').forEach(btn => {
        btn.onclick = () => { hideBottomSheet(); actionHandler?.(btn.dataset.bs, bsTarget); };
    });
    sheet.classList.remove('hidden');
}

export function hideBottomSheet() {
    document.getElementById('bottom-sheet')?.classList.add('hidden');
}

export function initBottomSheet() {
    document.getElementById('bs-overlay')?.addEventListener('click', hideBottomSheet);
    document.getElementById('bs-close')?.addEventListener('click', hideBottomSheet);
}

// ── Kebab menu (three-dot on cards/rows) ────────────────────
export function initKebabMenus(actionHandler) {
    document.addEventListener('click', e => {
        const kebab = e.target.closest('[data-kebab]');
        if (!kebab) return;
        e.stopPropagation();
        const card = kebab.closest('[data-item-id]');
        if (!card) return;
        const item = { id: card.dataset.itemId, type: card.dataset.itemType, name: card.dataset.itemName };

        if (window.innerWidth < 640) {
            showBottomSheet(item, actionHandler);
        } else {
            const menu = document.getElementById('context-menu');
            ctxTargetItem = item;
            menu.querySelector('[data-ctx="download"]')?.classList.toggle('hidden', item.type === 'folder');
            const rect = kebab.getBoundingClientRect();
            menu.style.left = Math.min(rect.left, window.innerWidth - 200) + 'px';
            menu.style.top = (rect.bottom + 4) + 'px';
            menu.classList.remove('hidden');
        }
    });
}
