import React from 'react';
import {
    X, Download, Share2, Pencil, Trash2, Star, Clock,
    Folder, FileText, Image, Video, Music, File,
    FileSpreadsheet, Presentation, Archive,
} from 'lucide-react';
import { formatBytes, formatDate, getFileIcon } from '../services/api';

const ICON_MAP = {
    folder: { icon: Folder, bg: '#fef3c7', fg: '#d97706' },
    pdf: { icon: FileText, bg: '#fef2f2', fg: '#dc2626' },
    doc: { icon: FileText, bg: '#eff6ff', fg: '#2563eb' },
    spreadsheet: { icon: FileSpreadsheet, bg: '#f0fdf4', fg: '#16a34a' },
    presentation: { icon: Presentation, bg: '#fef3c7', fg: '#d97706' },
    image: { icon: Image, bg: '#faf5ff', fg: '#7c3aed' },
    video: { icon: Video, bg: '#fef3c7', fg: '#d97706' },
    audio: { icon: Music, bg: '#fdf2f8', fg: '#db2777' },
    archive: { icon: Archive, bg: '#f5f5f5', fg: '#525252' },
    generic: { icon: File, bg: '#f5f5f5', fg: '#525252' },
};

export default function FileDetailsDrawer({ file, onClose, onAction }) {
    if (!file) return null;

    const isFolder = file.type === 'folder';
    const iconType = isFolder ? 'folder' : getFileIcon(file.ext);
    const iconInfo = ICON_MAP[iconType] || ICON_MAP.generic;
    const IconComponent = iconInfo.icon;

    return (
        <>
            {/* Backdrop on mobile */}
            <div
                className="fixed inset-0 z-40 bg-black/10 lg:hidden"
                onClick={onClose}
                aria-hidden="true"
            />

            <aside
                className="animate-slide-in-right fixed top-0 right-0 z-50 h-full w-full sm:w-96 flex flex-col"
                style={{
                    paddingTop: 'var(--navbar-h)',
                    background: 'var(--surface)',
                    borderLeft: '1px solid var(--border)',
                    boxShadow: 'var(--shadow-lg)',
                }}
                role="complementary"
                aria-label="File details"
            >
                {/* Header */}
                <div
                    className="flex items-center justify-between px-5 py-4 shrink-0"
                    style={{ borderBottom: '1px solid var(--border-light)' }}
                >
                    <h2 className="text-base font-semibold" style={{ color: 'var(--text)' }}>
                        Details
                    </h2>
                    <button
                        onClick={onClose}
                        className="focus-ring p-1.5 rounded-lg transition-colors"
                        style={{ color: 'var(--text-tertiary)' }}
                        onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                        onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                        aria-label="Close details panel"
                    >
                        <X size={18} />
                    </button>
                </div>

                {/* Preview */}
                <div className="px-5 py-6 shrink-0">
                    <div
                        className="w-full h-40 rounded-xl flex items-center justify-center"
                        style={{ background: iconInfo.bg }}
                    >
                        <IconComponent size={48} style={{ color: iconInfo.fg }} aria-hidden="true" />
                    </div>
                </div>

                {/* File name */}
                <div className="px-5 pb-4 shrink-0">
                    <h3 className="text-lg font-semibold break-words" style={{ color: 'var(--text)' }}>
                        {file.name}
                    </h3>
                </div>

                {/* Quick actions */}
                <div
                    className="px-5 py-3 flex items-center gap-2 shrink-0"
                    style={{ borderTop: '1px solid var(--border-light)', borderBottom: '1px solid var(--border-light)' }}
                >
                    {!isFolder && (
                        <button
                            onClick={() => onAction?.('download')}
                            className="focus-ring flex-1 inline-flex items-center justify-center gap-2 py-2 text-sm font-medium rounded-lg transition-colors"
                            style={{ background: 'var(--muted)', color: 'var(--text)' }}
                            aria-label="Download file"
                        >
                            <Download size={15} aria-hidden="true" />
                            Download
                        </button>
                    )}
                    <button
                        onClick={() => onAction?.('share')}
                        className="focus-ring flex-1 inline-flex items-center justify-center gap-2 py-2 text-sm font-medium rounded-lg transition-colors"
                        style={{ background: 'var(--muted)', color: 'var(--text)' }}
                        aria-label="Share file"
                    >
                        <Share2 size={15} aria-hidden="true" />
                        Share
                    </button>
                </div>

                {/* Metadata */}
                <div className="flex-1 overflow-y-auto px-5 py-4">
                    <h4 className="text-xs font-semibold uppercase tracking-wider mb-3" style={{ color: 'var(--text-tertiary)' }}>
                        Details
                    </h4>
                    <dl className="space-y-3">
                        <div className="flex justify-between">
                            <dt className="text-sm" style={{ color: 'var(--text-secondary)' }}>Type</dt>
                            <dd className="text-sm font-medium" style={{ color: 'var(--text)' }}>
                                {isFolder ? 'Folder' : (file.ext?.toUpperCase() || 'File')}
                            </dd>
                        </div>
                        {!isFolder && (
                            <div className="flex justify-between">
                                <dt className="text-sm" style={{ color: 'var(--text-secondary)' }}>Size</dt>
                                <dd className="text-sm font-medium" style={{ color: 'var(--text)' }}>
                                    {formatBytes(file.size)}
                                </dd>
                            </div>
                        )}
                        {isFolder && (
                            <div className="flex justify-between">
                                <dt className="text-sm" style={{ color: 'var(--text-secondary)' }}>Items</dt>
                                <dd className="text-sm font-medium" style={{ color: 'var(--text)' }}>
                                    {file.items || 0}
                                </dd>
                            </div>
                        )}
                        <div className="flex justify-between">
                            <dt className="text-sm" style={{ color: 'var(--text-secondary)' }}>Owner</dt>
                            <dd className="text-sm font-medium" style={{ color: 'var(--text)' }}>
                                {file.owner || 'You'}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-sm" style={{ color: 'var(--text-secondary)' }}>Modified</dt>
                            <dd className="text-sm font-medium" style={{ color: 'var(--text)' }}>
                                {formatDate(file.modified)}
                            </dd>
                        </div>
                    </dl>

                    {/* Danger zone */}
                    <div className="mt-6 pt-4" style={{ borderTop: '1px solid var(--border-light)' }}>
                        <div className="flex items-center gap-2">
                            <button
                                onClick={() => onAction?.('rename')}
                                className="focus-ring flex-1 inline-flex items-center justify-center gap-2 py-2 text-sm font-medium rounded-lg transition-colors"
                                style={{ color: 'var(--text-secondary)' }}
                                onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                                onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                            >
                                <Pencil size={14} aria-hidden="true" />
                                Rename
                            </button>
                            <button
                                onClick={() => onAction?.('delete')}
                                className="focus-ring flex-1 inline-flex items-center justify-center gap-2 py-2 text-sm font-medium rounded-lg transition-colors"
                                style={{ color: 'var(--danger)' }}
                                onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--danger-light)')}
                                onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                            >
                                <Trash2 size={14} aria-hidden="true" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </>
    );
}
