import React from 'react';
import { FolderPlus, Upload, ArrowUpDown, LayoutGrid, List, ChevronDown } from 'lucide-react';

export default function ActionToolbar({
    viewMode = 'grid',
    onViewChange,
    sortBy = 'name',
    onSortChange,
    onNewFolder,
    onUpload,
}) {
    return (
        <div className="flex items-center justify-between gap-3 flex-wrap">
            {/* Left: actions */}
            <div className="flex items-center gap-2">
                <button
                    onClick={onNewFolder}
                    className="focus-ring inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-xl transition-colors"
                    style={{
                        background: 'var(--muted)',
                        color: 'var(--text)',
                        border: '1px solid var(--border)',
                    }}
                    onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--border)')}
                    onMouseLeave={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                    aria-label="Create new folder"
                >
                    <FolderPlus size={16} aria-hidden="true" />
                    <span className="hidden sm:inline">New Folder</span>
                </button>
                <button
                    onClick={onUpload}
                    className="focus-ring inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-xl text-white transition-all"
                    style={{ background: 'var(--accent)' }}
                    onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--accent-hover)')}
                    onMouseLeave={(e) => (e.currentTarget.style.background = 'var(--accent)')}
                    aria-label="Upload files"
                >
                    <Upload size={16} aria-hidden="true" />
                    <span className="hidden sm:inline">Upload</span>
                </button>
            </div>

            {/* Right: sort + view toggle */}
            <div className="flex items-center gap-2">
                {/* Sort dropdown */}
                <div className="relative">
                    <select
                        value={sortBy}
                        onChange={(e) => onSortChange(e.target.value)}
                        className="focus-ring appearance-none pl-3 pr-8 py-2 text-sm rounded-xl cursor-pointer"
                        style={{
                            background: 'var(--muted)',
                            border: '1px solid var(--border)',
                            color: 'var(--text-secondary)',
                        }}
                        aria-label="Sort files by"
                    >
                        <option value="name">Name</option>
                        <option value="modified">Date Modified</option>
                        <option value="size">Size</option>
                    </select>
                    <ChevronDown
                        size={14}
                        className="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        style={{ color: 'var(--text-tertiary)' }}
                        aria-hidden="true"
                    />
                </div>

                {/* View toggle */}
                <div
                    className="flex items-center p-1 rounded-xl"
                    style={{ background: 'var(--muted)', border: '1px solid var(--border)' }}
                    role="radiogroup"
                    aria-label="File view mode"
                >
                    <button
                        onClick={() => onViewChange('grid')}
                        className="focus-ring p-1.5 rounded-lg transition-colors"
                        style={{
                            background: viewMode === 'grid' ? 'var(--surface)' : 'transparent',
                            color: viewMode === 'grid' ? 'var(--accent)' : 'var(--text-tertiary)',
                            boxShadow: viewMode === 'grid' ? 'var(--shadow-sm)' : 'none',
                        }}
                        role="radio"
                        aria-checked={viewMode === 'grid'}
                        aria-label="Grid view"
                    >
                        <LayoutGrid size={18} aria-hidden="true" />
                    </button>
                    <button
                        onClick={() => onViewChange('list')}
                        className="focus-ring p-1.5 rounded-lg transition-colors"
                        style={{
                            background: viewMode === 'list' ? 'var(--surface)' : 'transparent',
                            color: viewMode === 'list' ? 'var(--accent)' : 'var(--text-tertiary)',
                            boxShadow: viewMode === 'list' ? 'var(--shadow-sm)' : 'none',
                        }}
                        role="radio"
                        aria-checked={viewMode === 'list'}
                        aria-label="List view"
                    >
                        <List size={18} aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    );
}
