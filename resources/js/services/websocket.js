/**
 * WebSocket Service — Real-time file updates.
 *
 * Connects to: WS /ws/files
 * Events:
 *   - file_added    { file }
 *   - file_removed  { id }
 *   - file_updated  { file }
 *
 * Falls back to polling GET /api/files every 10s if WebSocket unavailable.
 */

class FileWebSocket {
    constructor() {
        this.ws = null;
        this.listeners = new Map();
        this.pollInterval = null;
        this.connected = false;
    }

    /**
     * Connect to the WebSocket server.
     * @param {string} url — WebSocket URL, e.g. ws://localhost:8080/ws/files
     */
    connect(url = 'ws://localhost:8080/ws/files') {
        try {
            this.ws = new WebSocket(url);

            this.ws.onopen = () => {
                this.connected = true;
                console.log('[WS] Connected to', url);
                this._stopPolling();
            };

            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this._emit(data.event, data.payload);
                } catch (err) {
                    console.warn('[WS] Failed to parse message:', err);
                }
            };

            this.ws.onclose = () => {
                this.connected = false;
                console.log('[WS] Disconnected — falling back to polling');
                this._startPolling();
            };

            this.ws.onerror = (err) => {
                console.warn('[WS] Connection error — falling back to polling', err);
                this.connected = false;
                this._startPolling();
            };
        } catch {
            console.warn('[WS] WebSocket not available — using polling');
            this._startPolling();
        }
    }

    /**
     * Register a listener for a specific event.
     * @param {'file_added'|'file_removed'|'file_updated'} event
     * @param {Function} callback
     */
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
        return () => {
            const cbs = this.listeners.get(event);
            if (cbs) {
                const idx = cbs.indexOf(callback);
                if (idx > -1) cbs.splice(idx, 1);
            }
        };
    }

    _emit(event, payload) {
        const cbs = this.listeners.get(event);
        if (cbs) cbs.forEach((cb) => cb(payload));
    }

    _startPolling() {
        if (this.pollInterval) return;
        this.pollInterval = setInterval(() => {
            // STUB: In production, fetch fresh file list and diff against local state.
            // import { fetchFiles } from './api';
            // const data = await fetchFiles();
            // ... compare and emit events
            console.log('[WS] Polling for updates...');
        }, 10000);
    }

    _stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    disconnect() {
        this._stopPolling();
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
        this.connected = false;
    }
}

// Singleton export
const fileWS = new FileWebSocket();
export default fileWS;
