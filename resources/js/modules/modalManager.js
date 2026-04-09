/**
 * modalManager.js — Rename, Move, and Share modals
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

// ── RENAME MODAL ───────────────────────────────────────────────
let _renameCallback = null;

export function openRenameModal(item, onConfirm) {
    const modal = document.getElementById('rename-modal');
    const input = document.getElementById('rename-input');
    if (!modal || !input) return;

    input.value = item.name;
    _renameCallback = onConfirm;
    modal.classList.remove('hidden');
    input.focus();
    input.select();
}

export function initRenameModal() {
    const modal  = document.getElementById('rename-modal');
    const input  = document.getElementById('rename-input');
    const okBtn  = document.getElementById('rename-ok-btn');
    const cancel = document.getElementById('rename-cancel-btn');

    const close = () => { modal?.classList.add('hidden'); _renameCallback = null; };
    const confirm = () => {
        const name = input?.value.trim();
        if (name && _renameCallback) {
            _renameCallback(name);
            close();
        }
    };

    okBtn?.addEventListener('click', confirm);
    cancel?.addEventListener('click', close);
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });
    input?.addEventListener('keydown', e => { if (e.key === 'Enter') confirm(); if (e.key === 'Escape') close(); });
}

// ── MOVE MODAL ─────────────────────────────────────────────────
let _moveCallback = null;
let _selectedFolderId = '';

export async function openMoveModal(item, onConfirm) {
    const modal = document.getElementById('move-modal');
    const list  = document.getElementById('move-folder-list');
    if (!modal || !list) return;

    _moveCallback = onConfirm;
    _selectedFolderId = '';

    // Reset list
    list.innerHTML = `
        <button data-folder-id=""
                class="move-folder-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 bg-blue-50 text-blue-700 font-medium transition-colors">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            Root (My Files)
        </button>`;

    try {
        const res = await fetch('/api/folders/tree');
        const folders = await res.json();
        folders.forEach(f => {
            const btn = document.createElement('button');
            btn.dataset.folderId = f.id;
            btn.className = 'move-folder-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors';
            btn.innerHTML = `
                <svg class="w-4 h-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                </svg>
                <span class="truncate">${f.name}</span>`;
            list.appendChild(btn);
        });
    } catch(e) {
        // ignore, keep root only
    }

    // Bind selection
    list.querySelectorAll('.move-folder-item').forEach(btn => {
        btn.addEventListener('click', () => {
            _selectedFolderId = btn.dataset.folderId || '';
            list.querySelectorAll('.move-folder-item').forEach(b => {
                b.classList.remove('bg-blue-50','text-blue-700','font-medium');
            });
            btn.classList.add('bg-blue-50','text-blue-700','font-medium');
        });
    });

    modal.classList.remove('hidden');
}

export function initMoveModal() {
    const modal  = document.getElementById('move-modal');
    const okBtn  = document.getElementById('move-ok-btn');
    const cancel = document.getElementById('move-cancel-btn');

    const close   = () => { modal?.classList.add('hidden'); _moveCallback = null; };
    const confirm = () => {
        if (_moveCallback) { _moveCallback(_selectedFolderId || null); }
        close();
    };

    okBtn?.addEventListener('click', confirm);
    cancel?.addEventListener('click', close);
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });
}

// ── SHARE MODAL ────────────────────────────────────────────────
let _shareItem = null;

export function openShareModal(item) {
    const modal    = document.getElementById('share-modal');
    const filename = document.getElementById('share-filename');
    const linkRow  = document.getElementById('share-link-row');
    const linkInput= document.getElementById('share-link-input');
    if (!modal) return;

    _shareItem = item;
    filename.textContent = item.name;

    // Show current permission state
    const currentIzin = parseInt(item.izin ?? '0');
    updateSharePermUI(currentIzin);

    // If public, show link immediately
    if (currentIzin === 1) {
        const url = `${window.location.origin}/open_file/${item.id}`;
        linkInput.value = url;
        linkRow.classList.remove('hidden');
    } else {
        linkRow.classList.add('hidden');
    }

    modal.classList.remove('hidden');
}

function updateSharePermUI(izin) {
    const privBtn = document.getElementById('share-private-btn');
    const pubBtn  = document.getElementById('share-public-btn');

    // Private
    privBtn?.classList.toggle('border-gray-300', izin !== 0);
    privBtn?.classList.toggle('bg-white', izin !== 0);
    privBtn?.classList.toggle('text-gray-500', izin !== 0);
    privBtn?.classList.toggle('border-blue-500', izin === 0);
    privBtn?.classList.toggle('bg-blue-50', izin === 0);
    privBtn?.classList.toggle('text-blue-700', izin === 0);

    // Public
    pubBtn?.classList.toggle('border-gray-300', izin !== 1);
    pubBtn?.classList.toggle('bg-white', izin !== 1);
    pubBtn?.classList.toggle('text-gray-500', izin !== 1);
    pubBtn?.classList.toggle('border-green-500', izin === 1);
    pubBtn?.classList.toggle('bg-green-50', izin === 1);
    pubBtn?.classList.toggle('text-green-700', izin === 1);
}

export function initShareModal() {
    const modal    = document.getElementById('share-modal');
    const closeBtn = document.getElementById('share-close-btn');
    const linkRow  = document.getElementById('share-link-row');
    const linkInput= document.getElementById('share-link-input');
    const copyBtn  = document.getElementById('share-copy-btn');
    const privBtn  = document.getElementById('share-private-btn');
    const pubBtn   = document.getElementById('share-public-btn');

    const close = () => { modal?.classList.add('hidden'); _shareItem = null; };

    closeBtn?.addEventListener('click', close);
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });

    // Copy link
    copyBtn?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(linkInput.value);
            const orig = copyBtn.textContent;
            copyBtn.textContent = 'Copied!';
            setTimeout(() => { copyBtn.textContent = orig; }, 2000);
        } catch {
            linkInput.select();
            document.execCommand('copy');
        }
    });

    // Permission toggle
    async function setPermission(izin) {
        if (!_shareItem) return;
        updateSharePermUI(izin);

        try {
            const res = await fetch(`/api/file/${_shareItem.id}/permission`, {
                method: 'PATCH',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ izin }),
            });
            const data = await res.json();

            if (izin === 1) {
                linkInput.value = data.url || `${window.location.origin}/open_file/${_shareItem.id}`;
                linkRow.classList.remove('hidden');
            } else {
                linkRow.classList.add('hidden');
            }

            // Update item state
            if (_shareItem) _shareItem.izin = izin;
        } catch(e) {
            console.error('Permission update failed', e);
        }
    }

    privBtn?.addEventListener('click', () => setPermission(0));
    pubBtn?.addEventListener('click',  () => setPermission(1));
}
