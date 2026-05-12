/**
 * websocket.js — WebSocket client (no polling)
 *
 * ╔════════════════════════════════════════════════════════════╗
 * ║ TO USE: Set WS_URL to your WebSocket server address.     ║
 * ║ The server should emit JSON events:                      ║
 * ║   { type: 'file_added',   data: { ...file } }           ║
 * ║   { type: 'file_removed', data: { id } }                ║
 * ║   { type: 'file_updated', data: { ...file } }           ║
 * ╚════════════════════════════════════════════════════════════╝
 */
import { loadFiles, insertFile, removeFile, updateFileItem } from './fileManager.js';
import { showToast } from './ui.js';

const WS_URL = null;  // e.g. 'ws://localhost:6001/ws/files' — set null to disable

let ws = null;

export function initRealtime() {
    if (WS_URL) {
        connectWS();
    }
    // SMART TRIGGER: Only refresh when tab becomes visible
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            loadFiles();
        }
    });
}

// ── WebSocket connection ──────────────────────────────────────
function connectWS() {
    try {
        ws = new WebSocket(WS_URL);

        ws.addEventListener('open', () => {
            console.log('[WS] Connected');
        });

        ws.addEventListener('message', e => {
            try {
                const msg = JSON.parse(e.data);
                handleEvent(msg);
            } catch { /* ignore malformed */ }
        });

        ws.addEventListener('close', () => {
            console.log('[WS] Disconnected');
            ws = null;
        });

        ws.addEventListener('error', () => {
            ws?.close();
        });
    } catch {
        console.log('[WS] Failed to connect');
    }
}

// ── Event handler ───────────────────────────────────────────
function handleEvent(msg) {
    switch (msg.type) {
        case 'file_added':
            insertFile(msg.data);
            showToast(`"${msg.data.name}" was added`, 'success');
            break;
        case 'file_removed':
            removeFile(msg.data.id);
            break;
        case 'file_updated':
            updateFileItem(msg.data);
            break;
    }
}
