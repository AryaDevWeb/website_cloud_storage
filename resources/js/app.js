/**
 * app.js — Main entry point
 * Imports and initializes all cloud-storage UI modules.
 */
import './bootstrap';
import { initSidebar, initMobileSearch, initUserDropdown, initViewToggle, initConfirmModal, initContextMenu, initBottomSheet, initKebabMenus } from './modules/ui.js';
import { loadFiles, handleAction, bulkDelete, bulkDownload, bulkMove, clearSelection, setSort, initKeyboard } from './modules/fileManager.js';
import { initDragDrop, initFileInput } from './modules/uploadManager.js';
import { initSearch } from './modules/searchManager.js';
import { initRealtime } from './modules/websocket.js';

document.addEventListener('DOMContentLoaded', () => {
    // ── UI chrome ────────────────────────────────────────
    initSidebar();
    initMobileSearch();
    initUserDropdown();
    initConfirmModal();
    initBottomSheet();
    initFileInput();

    // ── File views (only on pages that have the file explorer) ──
    const hasExplorer = document.getElementById('grid-view') || document.getElementById('list-view');

        initViewToggle();
        initContextMenu(handleAction);
        initKebabMenus(handleAction);
        initKeyboard();
        initSearch();
        initDragDrop();
        initRealtime();

        // Sort dropdown
        document.getElementById('sort-select')?.addEventListener('change', e => setSort(e.target.value));
        // Restore sort
        const saved = localStorage.getItem('fileSort');
        if (saved) {
            const sel = document.getElementById('sort-select');
            if (sel) sel.value = saved;
        }

        // Selection bar buttons
        document.getElementById('sel-delete')?.addEventListener('click', bulkDelete);
        document.getElementById('sel-download')?.addEventListener('click', bulkDownload);
        document.getElementById('sel-move')?.addEventListener('click', bulkMove);
        document.getElementById('sel-clear')?.addEventListener('click', clearSelection);

        // Load initial files
        loadFiles();
});
