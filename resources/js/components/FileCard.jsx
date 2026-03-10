import React from 'react';
import {
    Folder, FileText, Image, Video, Music, Archive, FileSpreadsheet,
    Presentation, MoreVertical, File,
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

export default function FileCard({ item, selected, onSelect, onClick, onContextMenu, onMenuClick }) {
    const isFolder = item.type === 'folder';
    const iconType = isFolder ? 'folder' : getFileIcon(item.ext);
    const iconInfo = ICON_MAP[iconType] || ICON_MAP.generic;
    const IconComponent = iconInfo.icon;

    return (
        <div
            className="focus-ring group rounded-xl p-4 cursor-pointer transition-all"
            style={{
                background: selected ? 'var(--accent-light)' : 'var(--surface)',
                border: `1px solid ${selected ? 'var(--accent)' : 'var(--border)'}`,
            }}
            onClick={(e) => {
                if (e.ctrlKey || e.metaKey) {
                    onSelect?.(item.id, !selected);
                } else {
                    onClick?.(item);
                }
            }}
            onContextMenu={(e) => {
                e.preventDefault();
                onContextMenu?.(e, item);
            }}
            tabIndex={0}
            role="button"
            aria-label={`${isFolder ? 'Folder' : 'File'}: ${item.name}`}
            aria-selected={selected}
            onMouseEnter={(e) => {
                if (!selected) e.currentTarget.style.borderColor = 'var(--text-tertiary)';
            }}
            onMouseLeave={(e) => {
                if (!selected) e.currentTarget.style.borderColor = 'var(--border)';
            }}
        >
            {/* Thumbnail / Icon */}
            <div
                className="w-full h-28 rounded-lg flex items-center justify-center mb-3 transition-colors"
                style={{ background: iconInfo.bg }}
            >
                <IconComponent size={32} style={{ color: iconInfo.fg }} aria-hidden="true" />
            </div>

            {/* File Info */}
            <div className="flex items-start justify-between gap-2">
                <div className="min-w-0 flex-1">
                    <p
                        className="text-sm font-medium truncate transition-colors"
                        style={{ color: 'var(--text)' }}
                    >
                        {item.name}
                    </p>
                    <div className="flex items-center gap-2 mt-1">
                        {!isFolder && (
                            <span className="text-xs" style={{ color: 'var(--text-tertiary)' }}>
                                {formatBytes(item.size)}
                            </span>
                        )}
                        {isFolder && item.items !== undefined && (
                            <span className="text-xs" style={{ color: 'var(--text-tertiary)' }}>
                                {item.items} items
                            </span>
                        )}
                        <span className="text-xs" style={{ color: 'var(--text-tertiary)' }}>
                            {formatDate(item.modified)}
                        </span>
                    </div>
                </div>
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        onMenuClick?.(e, item);
                    }}
                    className="focus-ring p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all"
                    style={{ color: 'var(--text-tertiary)' }}
                    onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                    onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                    aria-label={`Actions for ${item.name}`}
                >
                    <MoreVertical size={16} aria-hidden="true" />
                </button>
            </div>
        </div>
    );
}
