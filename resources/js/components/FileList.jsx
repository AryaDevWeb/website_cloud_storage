import React from 'react';
import {
    Folder, FileText, Image, Video, Music, Archive, FileSpreadsheet,
    Presentation, File, MoreVertical,
} from 'lucide-react';
import { formatBytes, formatDate, getFileIcon } from '../services/api';

const ICON_MAP = {
    folder: { icon: Folder, fg: '#d97706' },
    pdf: { icon: FileText, fg: '#dc2626' },
    doc: { icon: FileText, fg: '#2563eb' },
    spreadsheet: { icon: FileSpreadsheet, fg: '#16a34a' },
    presentation: { icon: Presentation, fg: '#d97706' },
    image: { icon: Image, fg: '#7c3aed' },
    video: { icon: Video, fg: '#d97706' },
    audio: { icon: Music, fg: '#db2777' },
    archive: { icon: Archive, fg: '#525252' },
    generic: { icon: File, fg: '#525252' },
};

export default function FileList({ items, selectedIds, onSelect, onClick, onContextMenu, onMenuClick, sortBy, onSortChange }) {
    return (
        <div
            className="rounded-xl overflow-hidden"
            style={{ background: 'var(--surface)', border: '1px solid var(--border)' }}
        >
            <table className="w-full text-left text-sm" role="table">
                <thead>
                    <tr style={{ background: 'var(--muted)' }}>
                        <th className="w-10 px-4 py-3">
                            <input
                                type="checkbox"
                                onChange={(e) => {
                                    items.forEach((item) => onSelect?.(item.id, e.target.checked));
                                }}
                                checked={items.length > 0 && items.every((i) => selectedIds.has(i.id))}
                                className="focus-ring rounded"
                                aria-label="Select all files"
                            />
                        </th>
                        <th
                            className="px-4 py-3 font-medium text-xs uppercase tracking-wider cursor-pointer select-none"
                            style={{ color: 'var(--text-secondary)' }}
                            onClick={() => onSortChange?.('name')}
                        >
                            Name
                        </th>
                        <th
                            className="px-4 py-3 font-medium text-xs uppercase tracking-wider hidden sm:table-cell cursor-pointer select-none"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            Owner
                        </th>
                        <th
                            className="px-4 py-3 font-medium text-xs uppercase tracking-wider hidden md:table-cell cursor-pointer select-none"
                            style={{ color: 'var(--text-secondary)' }}
                            onClick={() => onSortChange?.('size')}
                        >
                            Size
                        </th>
                        <th
                            className="px-4 py-3 font-medium text-xs uppercase tracking-wider hidden lg:table-cell cursor-pointer select-none"
                            style={{ color: 'var(--text-secondary)' }}
                            onClick={() => onSortChange?.('modified')}
                        >
                            Modified
                        </th>
                        <th className="w-12 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    {items.map((item) => {
                        const isFolder = item.type === 'folder';
                        const iconType = isFolder ? 'folder' : getFileIcon(item.ext);
                        const iconInfo = ICON_MAP[iconType] || ICON_MAP.generic;
                        const IconComponent = iconInfo.icon;
                        const isSelected = selectedIds.has(item.id);

                        return (
                            <tr
                                key={item.id}
                                className="group cursor-pointer transition-colors"
                                style={{
                                    background: isSelected ? 'var(--accent-light)' : 'transparent',
                                    borderBottom: '1px solid var(--border-light)',
                                }}
                                onClick={(e) => {
                                    if (e.ctrlKey || e.metaKey) {
                                        onSelect?.(item.id, !isSelected);
                                    } else {
                                        onClick?.(item);
                                    }
                                }}
                                onContextMenu={(e) => {
                                    e.preventDefault();
                                    onContextMenu?.(e, item);
                                }}
                                onMouseEnter={(e) => {
                                    if (!isSelected) e.currentTarget.style.background = 'var(--muted)';
                                }}
                                onMouseLeave={(e) => {
                                    e.currentTarget.style.background = isSelected ? 'var(--accent-light)' : 'transparent';
                                }}
                            >
                                <td className="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        checked={isSelected}
                                        onChange={(e) => {
                                            e.stopPropagation();
                                            onSelect?.(item.id, e.target.checked);
                                        }}
                                        onClick={(e) => e.stopPropagation()}
                                        className="focus-ring rounded"
                                        aria-label={`Select ${item.name}`}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center gap-3">
                                        <div
                                            className="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                            style={{ background: 'var(--muted)' }}
                                        >
                                            <IconComponent size={16} style={{ color: iconInfo.fg }} aria-hidden="true" />
                                        </div>
                                        <span
                                            className="font-medium truncate max-w-[200px]"
                                            style={{ color: 'var(--text)' }}
                                        >
                                            {item.name}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-4 py-3 hidden sm:table-cell" style={{ color: 'var(--text-secondary)' }}>
                                    {item.owner || '—'}
                                </td>
                                <td className="px-4 py-3 hidden md:table-cell" style={{ color: 'var(--text-secondary)' }}>
                                    {isFolder ? `${item.items || 0} items` : formatBytes(item.size)}
                                </td>
                                <td className="px-4 py-3 hidden lg:table-cell" style={{ color: 'var(--text-secondary)' }}>
                                    {formatDate(item.modified)}
                                </td>
                                <td className="px-4 py-3">
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            onMenuClick?.(e, item);
                                        }}
                                        className="focus-ring p-1 rounded-lg opacity-0 group-hover:opacity-100 transition-all"
                                        style={{ color: 'var(--text-tertiary)' }}
                                        aria-label={`Actions for ${item.name}`}
                                    >
                                        <MoreVertical size={16} aria-hidden="true" />
                                    </button>
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}
