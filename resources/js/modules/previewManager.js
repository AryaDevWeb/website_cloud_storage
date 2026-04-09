/**
 * previewManager.js — Full-screen file preview modal
 * Supports: images, video, audio, PDF (iframe), text, office, generic
 */
import { fetchFile, getFileIcon, formatBytes } from './api.js';

const ICON_SVG = {
    image: `<svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`,
    video: `<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`,
    audio: `<svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>`,
    pdf:   `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
    doc:   `<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`,
};

function getIconHtml(ext) {
    const type = getFileIcon(ext);
    return ICON_SVG[type] || ICON_SVG.doc;
}

// ── Components ────────────────────────────────────────────────────────────

function LoadingPreview(message = 'Generating preview...') {
    return `
        <div class="flex flex-col items-center justify-center gap-3 text-gray-400 h-full w-full py-20 transition-opacity duration-300">
            <svg class="animate-spin w-8 h-8" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
            <span class="text-sm font-medium animate-pulse">${message}</span>
        </div>`;
}

function ImagePreview(url, name) {
    // Uses vanilla toggle class for zoom in/out with standard tailwind transitions
    return `<img src="${url}" class="max-w-full max-h-[70vh] rounded-xl object-contain shadow-sm opacity-0 transition-all duration-500 bg-transparent ease-out" onload="this.classList.remove('opacity-0')" alt="${name}" style="cursor: zoom-in;" onclick="this.classList.toggle('max-h-[70vh]'); this.classList.toggle('max-h-none'); this.classList.toggle('cursor-zoom-in'); this.classList.toggle('cursor-zoom-out');">`;
}

function VideoPreview(url, ext) {
    return `
        <div class="flex items-center justify-center w-full shadow-sm rounded-xl overflow-hidden bg-black opacity-0 transition-opacity duration-500" onload="this.classList.remove('opacity-0')" style="animation: fadeIn 0.5s forwards;">
            <video controls class="max-w-full max-h-[70vh]" preload="metadata">
                <source src="${url}" type="video/${ext === 'mov' ? 'mp4' : ext}">
                <p class="text-gray-400 text-sm p-4">Your browser does not support this video format.</p>
            </video>
        </div>
        <style>@keyframes fadeIn { to { opacity: 1; } }</style>
    `;
}

function AudioPreview(url, ext, name) {
    return `
        <div class="flex flex-col items-center gap-6 p-8 shadow-sm rounded-xl bg-white w-full max-w-lg mx-auto transition-opacity duration-500 opacity-0" style="animation: fadeIn 0.5s forwards;">
            <div class="w-24 h-24 bg-pink-50 rounded-3xl flex items-center justify-center">
                <svg class="w-12 h-12 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">${name}</p>
            <audio controls class="w-full max-w-sm">
                <source src="${url}" type="audio/${ext}">
            </audio>
        </div>
        <style>@keyframes fadeIn { to { opacity: 1; } }</style>
    `;
}

function PdfPreview(url, name) {
    return `<iframe src="${url}" class="w-full h-[75vh] border-0 rounded-xl shadow-lg bg-gray-50 opacity-0 transition-opacity duration-300" onload="this.classList.remove('opacity-0')" title="${name}"></iframe>`;
}

function TextPreview() {
    return `
        <div class="w-full h-[70vh] bg-[#0d1117] rounded-xl overflow-auto border border-gray-200 shadow-lg relative opacity-0 transition-opacity duration-300 ease-in-out" style="animation: fadeIn 0.5s forwards;">
            <pre class="p-5 text-sm font-mono text-gray-200"><code id="code-content">Loading content...</code></pre>
        </div>
        <style>@keyframes fadeIn { to { opacity: 1; } }</style>
    `;
}

function FallbackPreview(name, ext, fileId, message = 'Preview not available') {
    return `
        <div class="flex flex-col items-center gap-4 p-12 text-center bg-white rounded-xl shadow-sm w-full max-w-lg mx-auto opacity-0 transition-opacity duration-500" style="animation: fadeIn 0.5s forwards;">
            <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center">
                ${getIconHtml(ext)}
            </div>
            <h4 class="text-base font-semibold text-gray-800">${name}</h4>
            <p class="text-xs text-gray-500">${message} for .${ext} files</p>
            <a href="/download/${fileId}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-xl transition-colors mt-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download File
            </a>
        </div>
        <style>@keyframes fadeIn { to { opacity: 1; } }</style>
    `;
}

// ── Main Controller ───────────────────────────────────────────────────────

export async function openPreview(fileId, name = '') {
    const modal    = document.getElementById('preview-modal');
    const body     = document.getElementById('preview-body');
    const filename = document.getElementById('preview-filename');
    const meta     = document.getElementById('preview-meta');
    const iconEl   = document.getElementById('preview-icon');
    const dlBtn    = document.getElementById('preview-download-btn');

    if (!modal) return;

    // 1. Setup UI Skeleton/Loading State Immediately
    body.innerHTML = LoadingPreview();
    filename.textContent = name || 'Loading…';
    meta.textContent = '';
    iconEl.innerHTML = '';
    dlBtn.href = `/download/${fileId}`;

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // 2. Fetch remote metadata dynamically
    let fileObj = null;
    try {
        fileObj = await fetchFile(fileId);
    } catch (e) {
        console.warn('Could not fetch file specifics', e);
    }

    if (!fileObj) {
        fileObj = { id: fileId, name: name };
    }

    // 3. Fallback extraction & type mapping
    const actualName = fileObj.name || name || 'unknown';
    const ext = (actualName.split('.').pop() || '').toLowerCase();
    
    // Header UI population
    filename.textContent = actualName;
    iconEl.innerHTML = getIconHtml(ext);
    if (fileObj.size) {
        meta.textContent = `${formatBytes(fileObj.size)} • ${ext.toUpperCase()}`;
    } else {
        meta.textContent = ext.toUpperCase();
    }

    // Determine type safely
    let pType = fileObj.preview_type;
    if (!pType) {
        if (['jpg','jpeg','png','gif','webp','svg','bmp'].includes(ext)) {
            pType = 'image';
        } else if (['mp4','webm','mov','avi','ogg'].includes(ext)) {
            pType = 'video';
        } else if (['mp3','wav','ogg','flac','aac'].includes(ext)) {
            pType = 'audio';
        } else if (['pdf'].includes(ext)) {
            pType = 'pdf';
        } else if (['txt','md','json','csv','xml','html','css','js','ts','php','py','sh'].includes(ext)) {
            pType = 'text/code';
        } else if (['docx','xlsx','pptx'].includes(ext)) {
            pType = 'office';
        } else {
            pType = 'unknown';
        }
    }

    // Determine Status safely
    let status = fileObj.status || 'ready';

    if (status === 'processing') {
        body.innerHTML = LoadingPreview('File is being processed...');
        return;
    }

    if (status === 'failed') {
        body.innerHTML = FallbackPreview(actualName, ext, fileId, 'Preview generation failed');
        return;
    }

    // Determine path (using backend provided preview_path or graceful fallback routes)
    let url = fileObj.preview_path;
    if (!url) {
        if (pType === 'image') url = `/open_file_img/${fileId}`;
        else if (pType === 'video' || pType === 'audio') url = `/open_file_stream/${fileId}`;
        else url = `/open_file/${fileId}`;
    }

    // 4. Render Layouts based on resolved type
    if (pType === 'image') {
        body.innerHTML = ImagePreview(url, actualName);
    } else if (pType === 'video') {
        body.innerHTML = VideoPreview(url, ext);
    } else if (pType === 'audio') {
        body.innerHTML = AudioPreview(url, ext, actualName);
    } else if (pType === 'pdf' || pType === 'office') {
        // Office documents are rendered as PDF through iframe using the backend provided preview_path
        body.innerHTML = PdfPreview(url, actualName);
    } else if (pType === 'text/code') {
        body.innerHTML = TextPreview();
        fetchAndHighlightText(url, ext, actualName, fileId);
    } else {
        body.innerHTML = FallbackPreview(actualName, ext, fileId);
    }
}

async function fetchAndHighlightText(url, ext, actualName, fileId) {
    const body = document.getElementById('preview-body');
    const cnode = document.getElementById('code-content');
    
    if (!cnode) return;

    try {
        const res = await fetch(url);
        if (res.ok) {
            const rawText = await res.text();
            
            // XSS Prevention for plain text
            const escapeEl = document.createElement('div');
            escapeEl.innerText = rawText;
            cnode.innerHTML = escapeEl.innerHTML;
            
            // Inject Highlight.js dynamically for code highlighting
            if (!window.hljs) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css';
                document.head.appendChild(link);
                
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js';
                script.onload = () => {
                    cnode.classList.add(`language-${ext}`);
                    window.hljs.highlightElement(cnode);
                };
                document.head.appendChild(script);
            } else {
                cnode.classList.add(`language-${ext}`);
                window.hljs.highlightElement(cnode);
            }

        } else {
            body.innerHTML = FallbackPreview(actualName, ext, fileId, 'Could not load text context');
        }
    } catch (e) {
        body.innerHTML = FallbackPreview(actualName, ext, fileId, 'Error getting text content');
    }
}

export function closePreview() {
    document.getElementById('preview-modal')?.classList.add('hidden');
    document.body.style.overflow = '';
}

export function initPreview() {
    document.getElementById('preview-close')?.addEventListener('click', closePreview);
    document.getElementById('preview-modal')?.addEventListener('click', e => {
        if (e.target.id === 'preview-modal') closePreview();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closePreview();
    });
}
