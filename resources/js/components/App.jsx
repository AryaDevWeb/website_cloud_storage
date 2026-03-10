import React, { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import Navbar from './Navbar';
import Sidebar from './Sidebar';
import Breadcrumbs from './Breadcrumbs';
import ActionToolbar from './ActionToolbar';
import FileGrid from './FileGrid';
import FileList from './FileList';
import FileCard from './FileCard';
import FileActionBar from './FileActionBar';
import FileDetailsDrawer from './FileDetailsDrawer';
import ContextMenu from './ContextMenu';
import UploadManager from './UploadManager';
import EmptyState from './EmptyState';
import ConfirmModal from './ConfirmModal';
import { ToastProvider, useToast } from './Toast';
import { fetchFiles, deleteFile, renameFile, createFolder } from '../services/api';
import { useSearch } from '../hooks/useSearch';
import { useKeyboard } from '../hooks/useKeyboard';

function AppInner() {
    // ── State ─────────────────────────────────────────
    const [files, setFiles] = useState([]);
    const [folders, setFolders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
    const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);
    const [activeNav, setActiveNav] = useState('my-files');
    const [viewMode, setViewMode] = useState(() => localStorage.getItem('cloudViewMode') || 'grid');
    const [sortBy, setSortBy] = useState(() => localStorage.getItem('cloudSortBy') || 'name');
    const [selectedIds, setSelectedIds] = useState(new Set());
    const [detailFile, setDetailFile] = useState(null);
    const [contextMenu, setContextMenu] = useState(null);
    const [confirmModal, setConfirmModal] = useState(null);
    const [breadcrumbPath, setBreadcrumbPath] = useState([]);
    const [newFolderModal, setNewFolderModal] = useState(false);
    const [newFolderName, setNewFolderName] = useState('');
    const [isMobile, setIsMobile] = useState(window.innerWidth < 1024);
    const uploadRef = useRef(null);
    const { addToast } = useToast();
    const { query: searchQuery, setQuery: setSearchQuery, debouncedQuery } = useSearch(300);

    // ── Responsive ─────────────────────────────────────
    useEffect(() => {
        const handleResize = () => setIsMobile(window.innerWidth < 1024);
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    // ── Persist preferences ────────────────────────────
    useEffect(() => { localStorage.setItem('cloudViewMode', viewMode); }, [viewMode]);
    useEffect(() => { localStorage.setItem('cloudSortBy', sortBy); }, [sortBy]);

    // ── Load files ─────────────────────────────────────
    useEffect(() => {
        setLoading(true);
        fetchFiles(null, debouncedQuery).then((data) => {
            setFolders(data.folders);
            setFiles(data.files);
            setLoading(false);
        });
    }, [debouncedQuery]);

    // ── Sort items ─────────────────────────────────────
    const allItems = useMemo(() => {
        const combined = [...folders, ...files];
        return combined.sort((a, b) => {
            if (sortBy === 'name') return a.name.localeCompare(b.name);
            if (sortBy === 'modified') return new Date(b.modified) - new Date(a.modified);
            if (sortBy === 'size') return (b.size || 0) - (a.size || 0);
            return 0;
        });
    }, [folders, files, sortBy]);

    // ── Highlight search matches ───────────────────────
    const filteredItems = useMemo(() => {
        if (!debouncedQuery) return allItems;
        return allItems;
    }, [allItems, debouncedQuery]);

    // ── Selection handlers ─────────────────────────────
    const handleSelect = useCallback((id, selected) => {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (selected) next.add(id);
            else next.delete(id);
            return next;
        });
    }, []);

    const handleClearSelection = useCallback(() => setSelectedIds(new Set()), []);

    const handleSelectAll = useCallback(() => {
        setSelectedIds(new Set(allItems.map((i) => i.id)));
    }, [allItems]);

    // ── File actions ───────────────────────────────────
    const handleItemClick = useCallback((item) => {
        setSelectedIds(new Set([item.id]));
        setDetailFile(item);
    }, []);

    const handleOpenItem = useCallback((item) => {
        if (item.type === 'folder') {
            setBreadcrumbPath((prev) => [...prev, { id: item.id, name: item.name }]);
        }
    }, []);

    const handleContextMenu = useCallback((e, item) => {
        handleSelect(item.id, true);
        setContextMenu({ x: e.clientX, y: e.clientY, item });
    }, [handleSelect]);

    const handleMenuClick = useCallback((e, item) => {
        const rect = e.currentTarget.getBoundingClientRect();
        setContextMenu({ x: rect.left, y: rect.bottom + 4, item });
    }, []);

    const handleContextAction = useCallback((action) => {
        const item = contextMenu?.item;
        if (!item) return;

        switch (action) {
            case 'open':
                handleOpenItem(item);
                break;
            case 'download':
                addToast(`Downloading "${item.name}"…`, 'success');
                break;
            case 'share':
                addToast(`Share dialog for "${item.name}" — stub`, 'success');
                break;
            case 'rename': {
                const newName = prompt('Enter new name:', item.name);
                if (newName && newName !== item.name) {
                    renameFile(item.id, newName).then(() => {
                        setFiles((prev) => prev.map((f) => (f.id === item.id ? { ...f, name: newName } : f)));
                        setFolders((prev) => prev.map((f) => (f.id === item.id ? { ...f, name: newName } : f)));
                        addToast(`Renamed to "${newName}"`, 'success');
                    });
                }
                break;
            }
            case 'move':
                addToast(`Move dialog — stub`, 'success');
                break;
            case 'delete':
                setConfirmModal({
                    title: `Delete "${item.name}"?`,
                    message: 'This action cannot be undone. The item will be permanently deleted.',
                    onConfirm: () => {
                        deleteFile(item.id).then(() => {
                            setFiles((prev) => prev.filter((f) => f.id !== item.id));
                            setFolders((prev) => prev.filter((f) => f.id !== item.id));
                            setSelectedIds((prev) => {
                                const next = new Set(prev);
                                next.delete(item.id);
                                return next;
                            });
                            if (detailFile?.id === item.id) setDetailFile(null);
                            addToast(`"${item.name}" deleted`, 'success', {
                                onUndo: () => {
                                    // STUB: In production, call an undo-delete endpoint
                                    addToast(`Undo not implemented in stub`, 'error');
                                },
                            });
                        });
                        setConfirmModal(null);
                    },
                });
                break;
            default:
                break;
        }
        setContextMenu(null);
    }, [contextMenu, addToast, handleOpenItem, detailFile]);

    const handleBulkDelete = useCallback(() => {
        const count = selectedIds.size;
        setConfirmModal({
            title: `Delete ${count} item(s)?`,
            message: 'This action cannot be undone. The selected items will be permanently deleted.',
            onConfirm: () => {
                selectedIds.forEach((id) => deleteFile(id));
                setFiles((prev) => prev.filter((f) => !selectedIds.has(f.id)));
                setFolders((prev) => prev.filter((f) => !selectedIds.has(f.id)));
                setSelectedIds(new Set());
                setDetailFile(null);
                addToast(`${count} item(s) deleted`, 'success');
                setConfirmModal(null);
            },
        });
    }, [selectedIds, addToast]);

    // ── Detail panel action ────────────────────────────
    const handleDetailAction = useCallback((action) => {
        if (detailFile) {
            setContextMenu(null);
            handleContextAction(action);
        }
    }, [detailFile, handleContextAction]);

    // ── Upload callback ────────────────────────────────
    const handleFileUploaded = useCallback((newFile) => {
        setFiles((prev) => [newFile, ...prev]);
    }, []);

    // ── New folder ─────────────────────────────────────
    const handleCreateFolder = useCallback(() => {
        if (!newFolderName.trim()) return;
        createFolder(newFolderName.trim()).then((folder) => {
            setFolders((prev) => [folder, ...prev]);
            addToast(`Folder "${folder.name}" created`, 'success');
            setNewFolderModal(false);
            setNewFolderName('');
        });
    }, [newFolderName, addToast]);

    // ── Trigger upload ─────────────────────────────────
    const triggerUpload = useCallback(() => {
        document.querySelector('input[type="file"][aria-label="Choose files to upload"]')?.click();
    }, []);

    // ── Keyboard navigation ────────────────────────────
    useKeyboard({
        items: filteredItems,
        selectedIds,
        onSelect: handleSelect,
        onOpen: handleOpenItem,
        onDelete: handleBulkDelete,
        onSelectAll: handleSelectAll,
        onClearSelection: handleClearSelection,
        columns: isMobile ? 1 : 4,
    });

    // ── Compute sidebar offset ─────────────────────────
    const mainMarginLeft = isMobile ? 0 : sidebarCollapsed ? 64 : 240;

    return (
        <div className="min-h-screen" style={{ background: 'var(--bg)' }}>
            <Navbar
                searchQuery={searchQuery}
                onSearchChange={setSearchQuery}
                onUploadClick={triggerUpload}
                onSidebarToggle={() => setMobileSidebarOpen(!mobileSidebarOpen)}
                isMobile={isMobile}
            />

            <Sidebar
                activeItem={activeNav}
                onItemClick={setActiveNav}
                collapsed={sidebarCollapsed}
                onToggleCollapse={() => setSidebarCollapsed(!sidebarCollapsed)}
                isMobile={isMobile}
                mobileOpen={mobileSidebarOpen}
                onMobileClose={() => setMobileSidebarOpen(false)}
            />

            {/* Main Content */}
            <main
                className="transition-all duration-300"
                style={{
                    marginLeft: mainMarginLeft,
                    paddingTop: 'var(--navbar-h)',
                    minHeight: '100vh',
                }}
            >
                <div className="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
                    {/* Breadcrumbs */}
                    <div className="mb-4">
                        <Breadcrumbs
                            path={breadcrumbPath}
                            onNavigate={(id) => {
                                if (id === null) {
                                    setBreadcrumbPath([]);
                                } else {
                                    const idx = breadcrumbPath.findIndex((s) => s.id === id);
                                    if (idx >= 0) setBreadcrumbPath(breadcrumbPath.slice(0, idx + 1));
                                }
                            }}
                        />
                    </div>

                    {/* Action Toolbar */}
                    <div className="mb-6">
                        <ActionToolbar
                            viewMode={viewMode}
                            onViewChange={setViewMode}
                            sortBy={sortBy}
                            onSortChange={setSortBy}
                            onNewFolder={() => setNewFolderModal(true)}
                            onUpload={triggerUpload}
                        />
                    </div>

                    {/* Loading */}
                    {loading && (
                        <div className="flex items-center justify-center py-20">
                            <div
                                className="w-8 h-8 rounded-full border-2 border-t-transparent animate-spin"
                                style={{ borderColor: 'var(--border)', borderTopColor: 'transparent' }}
                            />
                        </div>
                    )}

                    {/* File views */}
                    {!loading && filteredItems.length === 0 && (
                        <EmptyState
                            type={debouncedQuery ? 'no-results' : 'no-files'}
                            onUpload={triggerUpload}
                            onNewFolder={() => setNewFolderModal(true)}
                        />
                    )}

                    {!loading && filteredItems.length > 0 && viewMode === 'grid' && (
                        <div className="animate-fade-in">
                            <FileGrid
                                items={filteredItems}
                                selectedIds={selectedIds}
                                onSelect={handleSelect}
                                onClick={handleItemClick}
                                onContextMenu={handleContextMenu}
                                onMenuClick={handleMenuClick}
                            />
                        </div>
                    )}

                    {!loading && filteredItems.length > 0 && viewMode === 'list' && (
                        <div className="animate-fade-in">
                            <FileList
                                items={filteredItems}
                                selectedIds={selectedIds}
                                onSelect={handleSelect}
                                onClick={handleItemClick}
                                onContextMenu={handleContextMenu}
                                onMenuClick={handleMenuClick}
                                sortBy={sortBy}
                                onSortChange={setSortBy}
                            />
                        </div>
                    )}
                </div>
            </main>

            {/* Contextual action bar */}
            <FileActionBar
                selectedCount={selectedIds.size}
                onDownload={() => addToast('Downloading selected files — stub', 'success')}
                onShare={() => addToast('Sharing selected files — stub', 'success')}
                onMove={() => addToast('Moving selected files — stub', 'success')}
                onRename={() => {
                    const id = Array.from(selectedIds)[0];
                    const item = allItems.find((i) => i.id === id);
                    if (item) {
                        const newName = prompt('Enter new name:', item.name);
                        if (newName && newName !== item.name) {
                            renameFile(item.id, newName).then(() => {
                                setFiles((prev) => prev.map((f) => (f.id === item.id ? { ...f, name: newName } : f)));
                                setFolders((prev) => prev.map((f) => (f.id === item.id ? { ...f, name: newName } : f)));
                                addToast(`Renamed to "${newName}"`, 'success');
                            });
                        }
                    }
                }}
                onDelete={handleBulkDelete}
                onClear={handleClearSelection}
            />

            {/* File details drawer */}
            {detailFile && (
                <FileDetailsDrawer
                    file={detailFile}
                    onClose={() => setDetailFile(null)}
                    onAction={(action) => {
                        setContextMenu({ item: detailFile });
                        setTimeout(() => handleContextAction(action), 0);
                    }}
                />
            )}

            {/* Context menu */}
            {contextMenu && contextMenu.x !== undefined && (
                <ContextMenu
                    x={contextMenu.x}
                    y={contextMenu.y}
                    onAction={handleContextAction}
                    onClose={() => setContextMenu(null)}
                />
            )}

            {/* Upload manager */}
            <UploadManager
                onFileUploaded={handleFileUploaded}
                isMobile={isMobile}
            />

            {/* Confirm modal */}
            <ConfirmModal
                open={!!confirmModal}
                title={confirmModal?.title}
                message={confirmModal?.message}
                onConfirm={confirmModal?.onConfirm}
                onCancel={() => setConfirmModal(null)}
            />

            {/* New folder modal */}
            {newFolderModal && (
                <div
                    className="fixed inset-0 z-[90] flex items-center justify-center"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Create new folder"
                >
                    <div className="absolute inset-0 bg-black/20" onClick={() => setNewFolderModal(false)} aria-hidden="true" />
                    <div
                        className="animate-scale-in relative w-full max-w-md mx-4 rounded-2xl p-6"
                        style={{
                            background: 'var(--surface)',
                            border: '1px solid var(--border)',
                            boxShadow: 'var(--shadow-lg)',
                        }}
                    >
                        <h3 className="text-base font-semibold mb-4" style={{ color: 'var(--text)' }}>
                            Create New Folder
                        </h3>
                        <input
                            type="text"
                            value={newFolderName}
                            onChange={(e) => setNewFolderName(e.target.value)}
                            placeholder="Folder name…"
                            autoFocus
                            onKeyDown={(e) => e.key === 'Enter' && handleCreateFolder()}
                            className="focus-ring w-full px-4 py-3 rounded-xl text-sm mb-4"
                            style={{
                                background: 'var(--muted)',
                                border: '1px solid var(--border)',
                                color: 'var(--text)',
                            }}
                            aria-label="Folder name"
                        />
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => { setNewFolderModal(false); setNewFolderName(''); }}
                                className="focus-ring px-4 py-2 text-sm font-medium rounded-xl"
                                style={{ color: 'var(--text-secondary)' }}
                            >
                                Cancel
                            </button>
                            <button
                                onClick={handleCreateFolder}
                                className="focus-ring px-5 py-2 text-sm font-medium rounded-xl text-white"
                                style={{ background: 'var(--accent)' }}
                            >
                                Create
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

export default function App() {
    return (
        <ToastProvider>
            <AppInner />
        </ToastProvider>
    );
}
