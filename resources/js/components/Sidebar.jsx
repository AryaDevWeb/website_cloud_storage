import React, { useState } from 'react';
import {
    Home, FolderClosed, Clock, Star, Users, Share2, Trash2,
    ChevronLeft, ChevronRight, Database, X,
} from 'lucide-react';
import { formatBytes } from '../services/api';

const NAV_ITEMS = [
    { id: 'home', label: 'Home', icon: Home },
    { id: 'my-files', label: 'My Files', icon: FolderClosed },
    { id: 'recent', label: 'Recent', icon: Clock },
    { id: 'starred', label: 'Starred', icon: Star },
    { id: 'shared-with-me', label: 'Shared With Me', icon: Users },
    { id: 'shared-by-me', label: 'Shared By Me', icon: Share2 },
    { id: 'trash', label: 'Trash', icon: Trash2 },
];

export default function Sidebar({
    activeItem = 'my-files',
    onItemClick,
    collapsed,
    onToggleCollapse,
    storageUsed = 2147483648,
    storageTotal = 16106127360,
    isMobile,
    mobileOpen,
    onMobileClose,
}) {
    const [hoveredItem, setHoveredItem] = useState(null);
    const percentage = Math.round((storageUsed / storageTotal) * 100);

    const sidebarContent = (
        <div className="flex flex-col h-full">
            {/* Mobile close button */}
            {isMobile && (
                <div className="flex items-center justify-between px-4 py-3" style={{ borderBottom: '1px solid var(--border-light)' }}>
                    <img src="/images/CLD.png" alt="Cloud Storage Logo" className="w-8 h-8 object-contain" />
                    <button
                        onClick={onMobileClose}
                        className="focus-ring p-2 rounded-lg"
                        style={{ color: 'var(--text-secondary)' }}
                        aria-label="Close sidebar"
                    >
                        <X size={20} />
                    </button>
                </div>
            )}

            {/* Collapse toggle (desktop only) */}
            {!isMobile && (
                <div className="flex items-center justify-end px-3 py-2">
                    <button
                        onClick={onToggleCollapse}
                        className="focus-ring p-1.5 rounded-lg transition-colors"
                        style={{ color: 'var(--text-tertiary)' }}
                        onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                        onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                        aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                    >
                        {collapsed ? <ChevronRight size={16} /> : <ChevronLeft size={16} />}
                    </button>
                </div>
            )}

            {/* Navigation */}
            <nav className="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto" aria-label="Main navigation">
                {NAV_ITEMS.map((item) => {
                    const isActive = activeItem === item.id;
                    return (
                        <div key={item.id} className="relative">
                            <button
                                onClick={() => onItemClick?.(item.id)}
                                onMouseEnter={() => setHoveredItem(item.id)}
                                onMouseLeave={() => setHoveredItem(null)}
                                className={`focus-ring w-full flex items-center gap-3 rounded-xl text-sm font-medium transition-all ${collapsed && !isMobile ? 'justify-center px-3 py-2.5' : 'px-3 py-2.5'
                                    }`}
                                style={{
                                    background: isActive ? 'var(--accent)' : 'transparent',
                                    color: isActive ? '#ffffff' : 'var(--text-secondary)',
                                }}
                                onMouseEnterCapture={(e) => {
                                    if (!isActive) {
                                        e.currentTarget.style.background = 'var(--muted)';
                                        e.currentTarget.style.color = 'var(--text)';
                                    }
                                }}
                                onMouseLeaveCapture={(e) => {
                                    if (!isActive) {
                                        e.currentTarget.style.background = 'transparent';
                                        e.currentTarget.style.color = 'var(--text-secondary)';
                                    }
                                }}
                                aria-label={item.label}
                                aria-current={isActive ? 'page' : undefined}
                            >
                                <item.icon size={20} aria-hidden="true" className="shrink-0" />
                                {(!collapsed || isMobile) && <span>{item.label}</span>}
                            </button>

                            {/* Tooltip for collapsed mode */}
                            {collapsed && !isMobile && hoveredItem === item.id && (
                                <div
                                    className="animate-fade-in absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap z-50"
                                    style={{
                                        background: 'var(--text)',
                                        color: 'var(--surface)',
                                        boxShadow: 'var(--shadow-md)',
                                    }}
                                    role="tooltip"
                                >
                                    {item.label}
                                </div>
                            )}
                        </div>
                    );
                })}
            </nav>

            {/* Storage usage */}
            <div className="px-3 py-4" style={{ borderTop: '1px solid var(--border-light)' }}>
                {(!collapsed || isMobile) ? (
                    <div
                        className="p-3 rounded-xl"
                        style={{ background: 'var(--muted)', border: '1px solid var(--border-light)' }}
                    >
                        <div className="flex items-center gap-2 mb-3">
                            <div
                                className="w-8 h-8 rounded-lg flex items-center justify-center"
                                style={{ background: 'var(--surface)' }}
                            >
                                <Database size={16} style={{ color: 'var(--accent)' }} aria-hidden="true" />
                            </div>
                            <span className="text-xs font-semibold" style={{ color: 'var(--text)' }}>Storage</span>
                        </div>
                        <div
                            className="h-2 w-full rounded-full overflow-hidden mb-2"
                            style={{ background: 'var(--border)' }}
                            role="progressbar"
                            aria-valuenow={percentage}
                            aria-valuemin={0}
                            aria-valuemax={100}
                            aria-label={`Storage: ${percentage}% used`}
                        >
                            <div
                                className="h-full rounded-full transition-all duration-500"
                                style={{
                                    width: `${percentage}%`,
                                    background: percentage > 90 ? 'var(--danger)' : percentage > 70 ? 'var(--warning)' : 'var(--accent)',
                                }}
                            />
                        </div>
                        <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                            <span className="font-medium" style={{ color: 'var(--text)' }}>{formatBytes(storageUsed)}</span>
                            {' '}of {formatBytes(storageTotal)}
                        </p>
                    </div>
                ) : (
                    <div className="flex justify-center" title={`${formatBytes(storageUsed)} of ${formatBytes(storageTotal)}`}>
                        <Database size={20} style={{ color: 'var(--text-tertiary)' }} aria-hidden="true" />
                    </div>
                )}
            </div>
        </div>
    );

    // Mobile: slide-in drawer
    if (isMobile) {
        return (
            <>
                {mobileOpen && (
                    <div
                        className="fixed inset-0 z-30 bg-black/20"
                        onClick={onMobileClose}
                        aria-hidden="true"
                    />
                )}
                <aside
                    className={`fixed top-0 bottom-0 left-0 z-40 transition-transform duration-300 ${mobileOpen ? 'translate-x-0' : '-translate-x-full'
                        }`}
                    style={{
                        width: 'var(--sidebar-w)',
                        background: 'var(--surface)',
                        borderRight: '1px solid var(--border)',
                    }}
                    role="navigation"
                    aria-label="Sidebar navigation"
                >
                    {sidebarContent}
                </aside>
            </>
        );
    }

    // Desktop: fixed sidebar
    return (
        <aside
            className="fixed bottom-0 left-0 z-30 transition-all duration-300 hidden lg:flex flex-col"
            style={{
                top: 'var(--navbar-h)',
                width: collapsed ? 'var(--sidebar-collapsed-w)' : 'var(--sidebar-w)',
                background: 'var(--surface)',
                borderRight: '1px solid var(--border)',
            }}
            role="navigation"
            aria-label="Sidebar navigation"
        >
            {sidebarContent}
        </aside>
    );
}
