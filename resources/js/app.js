/**
 * app.js — Main entry point
 */
import './bootstrap';
import {
    initSidebar, initMobileSearch, initUserDropdown,
    initViewToggle, initConfirmModal, initContextMenu,
    initBottomSheet, initKebabMenus
} from './modules/ui.js';
import {
    loadFiles, handleAction, bulkDelete, bulkDownload,
    bulkMove, clearSelection, setSort, initKeyboard
} from './modules/fileManager.js';
import { initDragDrop, initFileInput } from './modules/uploadManager.js';
import { initSearch } from './modules/searchManager.js';
import { initRealtime } from './modules/websocket.js';
import { initPreview } from './modules/previewManager.js';
import { initRenameModal, initMoveModal, initShareModal } from './modules/modalManager.js';

document.addEventListener('DOMContentLoaded', () => {
    // ── UI chrome ─────────────────────────────────────────
    initSidebar();
    initMobileSearch();
    initUserDropdown();
    initConfirmModal();
    initBottomSheet();

    // ── New modals + preview (available on all pages) ─────
    initPreview();
    initRenameModal();
    initMoveModal();
    initShareModal();

    // ── Upload (always bind so dashboard upload works too) ─
    initDragDrop();
    initFileInput();

    // ── File explorer pages ───────────────────────────────
    const hasExplorer = document.getElementById('grid-view') || document.getElementById('list-view');
    if (hasExplorer) {
        initViewToggle();
        initContextMenu(handleAction);
        initKebabMenus(handleAction);
        initKeyboard();
        initSearch();
        initRealtime();

        // Sort
        document.getElementById('sort-select')?.addEventListener('change', e => setSort(e.target.value));
        const savedSort = localStorage.getItem('fileSort');
        if (savedSort) {
            const sel = document.getElementById('sort-select');
            if (sel) sel.value = savedSort;
        }

        // Selection bar
        document.getElementById('sel-delete')?.addEventListener('click', bulkDelete);
        document.getElementById('sel-download')?.addEventListener('click', bulkDownload);
        document.getElementById('sel-move')?.addEventListener('click', bulkMove);
        document.getElementById('sel-clear')?.addEventListener('click', clearSelection);

        // Empty Trash
        document.getElementById('empty-trash-btn')?.addEventListener('click', async () => {
            const { confirmAction } = await import('./modules/ui.js');
            const { showToast } = await import('./modules/ui.js');
            const ok = await confirmAction({
                title: 'Empty Trash?',
                message: 'All items in Trash will be permanently deleted.',
                confirmText: 'Empty Trash',
            });
            if (!ok) return;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            // Delete all trashed files one by one
            const res = await fetch('/api/files/trash');
            const data = await res.json();
            for (const item of data.data) {
                await fetch(`/api/file/${item.id}/permanent`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf }
                });
            }
            showToast('Trash emptied', 'success');
            loadFiles();
        });

        // Load initial files
        loadFiles();
    }
});
