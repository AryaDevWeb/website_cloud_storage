# Cloud Storage UI — Integration Guide

## File Structure

```
resources/
├── css/app.css                    ← Tailwind entry + custom styles
├── js/
│   ├── app.js                     ← Main entry (imports all modules)
│   └── modules/
│       ├── api.js                 ← Fetch wrappers + mock data
│       ├── ui.js                  ← Toasts, modals, sidebar, context menu, bottom-sheet
│       ├── fileManager.js         ← File CRUD, selection, sort, pagination, keyboard nav
│       ├── uploadManager.js       ← Drag & drop, multi-file upload, progress, cancel
│       ├── searchManager.js       ← 300ms debounced search
│       └── websocket.js           ← WebSocket client + polling fallback
└── views/
    ├── layouts/app.blade.php      ← Global layout (navbar, sidebar, overlays, modals)
    ├── beranda.blade.php          ← File explorer (JS-rendered grid/list)
    └── dashboard.blade.php        ← Stats + recent files
```

## Setup

### 1. Place the logo
Put `CLD.png` at `public/images/CLD.png`.  
The logo appears at `{{ asset('images/CLD.png') }}` — **never add text beside it**.

### 2. Install & build
```bash
npm install
npm run dev     # development
npm run build   # production
```

### 3. Switch from mocks to real API

Open `resources/js/modules/api.js` and set:
```js
const USE_MOCKS = false;
```

Then ensure your Laravel backend provides these routes returning JSON:

| Method   | Endpoint                | Body / Params              | Response shape                                          |
|----------|-------------------------|----------------------------|---------------------------------------------------------|
| `GET`    | `/api/files`            | `?folder_id=&q=&sort=&page=&per_page=` | `{ data: [...], total, page, perPage, lastPage }` |
| `GET`    | `/api/files/{id}`       | —                          | `{ id, type, name, ext, size, modified, owner }`         |
| `POST`   | `/api/upload`           | `FormData: file, folder_id`| `{ id, type, name, ext, size, modified }`                |
| `PATCH`  | `/api/file/{id}`        | `{ name?, folder_id? }`    | `{ ...updated file }`                                   |
| `DELETE` | `/api/file/{id}`        | —                          | `{ success: true }`                                     |
| `POST`   | `/api/file/{id}/share`  | —                          | `{ url: "..." }`                                        |
| `POST`   | `/api/folder`           | `{ name, parent_id }`      | `{ id, type, name, ... }`                                |
| `GET`    | `/api/notifications`    | —                          | `[{ id, text, time, read }]`                             |

### 4. WebSocket (optional)

Open `resources/js/modules/websocket.js` and set:
```js
const WS_URL = 'ws://your-server/ws/files';
```

Emit JSON events from your server:
```json
{ "type": "file_added",   "data": { ...file } }
{ "type": "file_removed", "data": { "id": "..." } }
{ "type": "file_updated", "data": { ...file } }
```

If `WS_URL` is `null`, the UI falls back to polling every 30s.

## Features

- **Selection**: Click (single), Ctrl+Click (toggle), Shift+Click (range), checkbox
- **Keyboard**: ↑↓←→ navigate, Enter open, Space select, Delete delete, Ctrl+A all, Esc clear
- **Upload**: Drag & drop + file picker, per-file progress, cancel, auto-insert into DOM
- **Actions**: Open, Download, Rename, Move, Share (copy link), Delete (confirm + undo toast)
- **View**: Grid/List toggle persisted in localStorage, sort by name/date/size
- **Mobile**: Burger drawer, centered logo, search expands full-width, FAB upload, bottom-sheet actions
- **Context menu**: Right-click on desktop, three-dot kebab on cards → same actions
