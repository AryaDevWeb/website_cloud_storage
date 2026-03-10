import React from 'react';
import { FolderPlus, Upload, SearchX } from 'lucide-react';

export default function EmptyState({ type = 'no-files', onUpload, onNewFolder }) {
    if (type === 'no-results') {
        return (
            <div className="flex flex-col items-center justify-center py-20 text-center">
                <div
                    className="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
                    style={{ background: 'var(--muted)' }}
                >
                    <SearchX size={28} style={{ color: 'var(--text-tertiary)' }} aria-hidden="true" />
                </div>
                <h3 className="text-lg font-semibold" style={{ color: 'var(--text)' }}>
                    No results found
                </h3>
                <p className="text-sm mt-1 max-w-xs" style={{ color: 'var(--text-secondary)' }}>
                    Try adjusting your search terms or check the spelling.
                </p>
            </div>
        );
    }

    return (
        <div
            className="flex flex-col items-center justify-center py-20 rounded-2xl text-center"
            style={{ background: 'var(--surface)', border: '1px solid var(--border)' }}
        >
            <div
                className="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
                style={{ background: 'var(--muted)' }}
            >
                <FolderPlus size={28} style={{ color: 'var(--text-tertiary)' }} aria-hidden="true" />
            </div>
            <h3 className="text-lg font-semibold" style={{ color: 'var(--text)' }}>
                No files yet
            </h3>
            <p className="text-sm mt-1 max-w-xs" style={{ color: 'var(--text-secondary)' }}>
                Upload your first file or create a new folder to get started.
            </p>
            <div className="flex items-center gap-3 mt-6">
                <button
                    onClick={onUpload}
                    className="focus-ring inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-xl text-white transition-all"
                    style={{ background: 'var(--accent)' }}
                    aria-label="Upload a file"
                >
                    <Upload size={16} aria-hidden="true" />
                    Upload File
                </button>
                <button
                    onClick={onNewFolder}
                    className="focus-ring inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-xl transition-colors"
                    style={{
                        background: 'var(--muted)',
                        color: 'var(--text)',
                        border: '1px solid var(--border)',
                    }}
                    aria-label="Create new folder"
                >
                    <FolderPlus size={16} aria-hidden="true" />
                    New Folder
                </button>
            </div>
        </div>
    );
}
