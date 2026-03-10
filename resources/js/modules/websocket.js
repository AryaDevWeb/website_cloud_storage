/**
 * websocket.js — WebSocket client + polling fallback
 *
 * ╔════════════════════════════════════════════════════════════╗
 * ║ TO USE: Set WS_URL to your WebSocket server address.     ║
 * ║ The server should emit JSON events:                      ║
 * ║   { type: 'file_added',   data: { ...file } }           ║
 * ║   { type: 'file_removed', data: { id } }                ║
 * ║   { type: 'file_updated', data: { ...file } }           ║
 * ╚════════════════════════════════════════════════════════════╝
 */
import { loadFiles, insertFile } from './fileManager.js';
import { showToast } from './ui.js';

const WS_URL = null;  // e.g. 'ws://localhost:6001/ws/files' — set null to use polling
const POLL_INTERVAL = 30000; // 30s fallback polling

let ws = null;
let pollTimer = null;

export function initRealtime() {
    if (WS_URL) {
        connectWS();
    } else {
        startPolling();
    }
}

// ── WebSocket ───────────────────────────────────────────────
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
            console.log('[WS] Disconnected, falling back to polling');
            ws = null;
            startPolling();
        });

        ws.addEventListener('error', () => {
            ws?.close();
        });
    } catch {
        startPolling();
    }
}

// ── Polling fallback ────────────────────────────────────────
function startPolling() {
    if (pollTimer) return;
    console.log('[Poll] Starting poll every', POLL_INTERVAL / 1000, 's');
    pollTimer = setInterval(() => {
        loadFiles(); // re-fetch from API
    }, POLL_INTERVAL);
}

export function stopRealtime() {
    ws?.close();
    clearInterval(pollTimer);
    pollTimer = null;
}

// ── Event handler ───────────────────────────────────────────
function handleEvent(msg) {
    switch (msg.type) {
        case 'file_added':
            insertFile(msg.data);
            showToast(`"${msg.data.name}" was added`, 'success');
            break;
        case 'file_removed':
            loadFiles(); // simple reload
            break;
        case 'file_updated':
            loadFiles(); // simple reload
            break;
    }
}
