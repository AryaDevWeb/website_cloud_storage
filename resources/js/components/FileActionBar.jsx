import React from 'react';
import { Download, Share2, Move, Pencil, Trash2, MoreHorizontal, X } from 'lucide-react';

export default function FileActionBar({ selectedCount, onDownload, onShare, onMove, onRename, onDelete, onClear }) {
    if (selectedCount === 0) return null;

    return (
        <div
            className="animate-slide-up fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-4 py-3 rounded-2xl"
            style={{
                background: 'var(--text)',
                color: 'var(--surface)',
                boxShadow: 'var(--shadow-lg)',
            }}
            role="toolbar"
            aria-label="File actions toolbar"
        >
            <span className="text-sm font-medium mr-2">
                {selectedCount} selected
            </span>

            <div className="w-px h-5 bg-white/20" aria-hidden="true" />

            <button
                onClick={onDownload}
                className="focus-ring p-2 rounded-lg transition-colors hover:bg-white/10"
                aria-label="Download selected files"
            >
                <Download size={18} aria-hidden="true" />
            </button>
            <button
                onClick={onShare}
                className="focus-ring p-2 rounded-lg transition-colors hover:bg-white/10"
                aria-label="Share selected files"
            >
                <Share2 size={18} aria-hidden="true" />
            </button>
            <button
                onClick={onMove}
                className="focus-ring p-2 rounded-lg transition-colors hover:bg-white/10"
                aria-label="Move selected files"
            >
                <Move size={18} aria-hidden="true" />
            </button>
            {selectedCount === 1 && (
                <button
                    onClick={onRename}
                    className="focus-ring p-2 rounded-lg transition-colors hover:bg-white/10"
                    aria-label="Rename selected file"
                >
                    <Pencil size={18} aria-hidden="true" />
                </button>
            )}
            <button
                onClick={onDelete}
                className="focus-ring p-2 rounded-lg transition-colors hover:bg-white/10"
                aria-label="Delete selected files"
            >
                <Trash2 size={18} aria-hidden="true" />
            </button>

            <div className="w-px h-5 bg-white/20" aria-hidden="true" />

            <button
                onClick={onClear}
                className="focus-ring p-2 rounded-lg transition-colors hover:bg-white/10"
                aria-label="Clear selection"
            >
                <X size={18} aria-hidden="true" />
            </button>
        </div>
    );
}
