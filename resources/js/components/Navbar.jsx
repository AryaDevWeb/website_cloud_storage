import React, { useState } from 'react';
import { Search, Upload, Bell, ChevronDown, Menu, X, LogOut, User } from 'lucide-react';

export default function Navbar({
    searchQuery,
    onSearchChange,
    onUploadClick,
    onSidebarToggle,
    isMobile,
}) {
    const [mobileSearchOpen, setMobileSearchOpen] = useState(false);
    const [dropdownOpen, setDropdownOpen] = useState(false);

    return (
        <header
            className="fixed top-0 left-0 right-0 z-40 flex items-center"
            style={{
                height: 'var(--navbar-h)',
                background: 'var(--surface)',
                borderBottom: '1px solid var(--border)',
            }}
            role="banner"
        >
            <div className="h-full w-full flex items-center justify-between px-4 lg:px-6">
                {/* ── Left: mobile burger + logo ── */}
                <div className="flex items-center gap-3">
                    {/* Burger (mobile only) */}
                    <button
                        onClick={onSidebarToggle}
                        className="focus-ring lg:hidden p-2 rounded-lg transition-colors"
                        style={{ color: 'var(--text-secondary)' }}
                        onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                        onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                        aria-label="Toggle sidebar menu"
                    >
                        <Menu size={20} aria-hidden="true" />
                    </button>

                    {/* Logo — desktop: always visible; mobile: hidden when search open */}
                    {!(isMobile && mobileSearchOpen) && (
                        <a
                            href="/cloud"
                            className={`flex items-center ${isMobile ? 'absolute left-1/2 -translate-x-1/2' : ''}`}
                            aria-label="Cloud Storage home"
                        >
                            <img
                                src="/images/CLD.png"
                                alt="Cloud Storage Logo"
                                className="w-10 h-10 object-contain"
                            />
                        </a>
                    )}
                </div>

                {/* ── Center: Search (desktop) ── */}
                {!isMobile && (
                    <div className="flex-1 max-w-xl mx-8">
                        <div className="relative">
                            <Search
                                size={16}
                                className="absolute left-3.5 top-1/2 -translate-y-1/2"
                                style={{ color: 'var(--text-tertiary)' }}
                                aria-hidden="true"
                            />
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => onSearchChange(e.target.value)}
                                placeholder="Search files and folders…"
                                className="focus-ring w-full pl-10 pr-4 py-2.5 rounded-xl text-sm transition-all"
                                style={{
                                    background: 'var(--muted)',
                                    border: '1px solid transparent',
                                    color: 'var(--text)',
                                }}
                                onFocus={(e) => {
                                    e.target.style.background = 'var(--surface)';
                                    e.target.style.borderColor = 'var(--accent)';
                                }}
                                onBlur={(e) => {
                                    e.target.style.background = 'var(--muted)';
                                    e.target.style.borderColor = 'transparent';
                                }}
                                aria-label="Search files and folders"
                            />
                        </div>
                    </div>
                )}

                {/* ── Mobile expanded search ── */}
                {isMobile && mobileSearchOpen && (
                    <div className="flex-1 mx-2 animate-fade-in">
                        <div className="relative flex items-center gap-2">
                            <Search
                                size={16}
                                className="absolute left-3.5 top-1/2 -translate-y-1/2"
                                style={{ color: 'var(--text-tertiary)' }}
                                aria-hidden="true"
                            />
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => onSearchChange(e.target.value)}
                                placeholder="Search files and folders…"
                                autoFocus
                                className="focus-ring w-full pl-10 pr-4 py-2.5 rounded-xl text-sm"
                                style={{
                                    background: 'var(--muted)',
                                    border: '1px solid var(--accent)',
                                    color: 'var(--text)',
                                }}
                                aria-label="Search files and folders"
                            />
                            <button
                                onClick={() => {
                                    setMobileSearchOpen(false);
                                    onSearchChange('');
                                }}
                                className="focus-ring p-2 rounded-lg shrink-0"
                                style={{ color: 'var(--text-secondary)' }}
                                aria-label="Cancel search"
                            >
                                <X size={18} />
                            </button>
                        </div>
                    </div>
                )}

                {/* ── Right: Actions ── */}
                <div className="flex items-center gap-1 sm:gap-2">
                    {/* Mobile search icon */}
                    {isMobile && !mobileSearchOpen && (
                        <button
                            onClick={() => setMobileSearchOpen(true)}
                            className="focus-ring p-2 rounded-lg transition-colors"
                            style={{ color: 'var(--text-secondary)' }}
                            aria-label="Open search"
                        >
                            <Search size={20} aria-hidden="true" />
                        </button>
                    )}

                    {/* Upload button (desktop) */}
                    {!isMobile && (
                        <button
                            onClick={onUploadClick}
                            className="focus-ring hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl text-white transition-all"
                            style={{ background: 'var(--accent)' }}
                            onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--accent-hover)')}
                            onMouseLeave={(e) => (e.currentTarget.style.background = 'var(--accent)')}
                            aria-label="Upload files"
                        >
                            <Upload size={16} aria-hidden="true" />
                            Upload
                        </button>
                    )}

                    {/* Notifications */}
                    <button
                        className="focus-ring relative p-2 rounded-lg transition-colors"
                        style={{ color: 'var(--text-secondary)' }}
                        onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                        onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                        aria-label="Notifications"
                    >
                        <Bell size={20} aria-hidden="true" />
                        <span
                            className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full"
                            style={{ background: 'var(--danger)' }}
                            aria-label="You have new notifications"
                        />
                    </button>

                    {/* User dropdown */}
                    <div className="relative">
                        <button
                            onClick={() => setDropdownOpen(!dropdownOpen)}
                            className="focus-ring flex items-center gap-2 p-1.5 rounded-xl transition-colors"
                            onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                            onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                            aria-expanded={dropdownOpen}
                            aria-haspopup="true"
                            aria-label="User menu"
                        >
                            <div
                                className="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-semibold"
                                style={{ background: 'var(--accent)' }}
                            >
                                U
                            </div>
                            {!isMobile && (
                                <>
                                    <span className="hidden lg:inline text-sm font-medium" style={{ color: 'var(--text)' }}>
                                        User
                                    </span>
                                    <ChevronDown size={14} style={{ color: 'var(--text-tertiary)' }} aria-hidden="true" />
                                </>
                            )}
                        </button>

                        {dropdownOpen && (
                            <>
                                <div className="fixed inset-0 z-40" onClick={() => setDropdownOpen(false)} aria-hidden="true" />
                                <div
                                    className="animate-slide-down absolute right-0 top-full mt-2 w-56 py-1.5 rounded-xl z-50"
                                    style={{
                                        background: 'var(--surface)',
                                        border: '1px solid var(--border)',
                                        boxShadow: 'var(--shadow-lg)',
                                    }}
                                    role="menu"
                                >
                                    <div className="px-4 py-3" style={{ borderBottom: '1px solid var(--border-light)' }}>
                                        <p className="text-sm font-medium" style={{ color: 'var(--text)' }}>User</p>
                                        <p className="text-xs mt-0.5" style={{ color: 'var(--text-secondary)' }}>user@example.com</p>
                                    </div>
                                    <a
                                        href="#"
                                        className="focus-ring flex items-center gap-3 px-4 py-2.5 text-sm transition-colors"
                                        style={{ color: 'var(--text-secondary)' }}
                                        onMouseEnter={(e) => {
                                            e.currentTarget.style.background = 'var(--muted)';
                                            e.currentTarget.style.color = 'var(--text)';
                                        }}
                                        onMouseLeave={(e) => {
                                            e.currentTarget.style.background = 'transparent';
                                            e.currentTarget.style.color = 'var(--text-secondary)';
                                        }}
                                        role="menuitem"
                                    >
                                        <User size={16} aria-hidden="true" />
                                        My Profile
                                    </a>
                                    <button
                                        className="focus-ring flex items-center gap-3 w-full px-4 py-2.5 text-sm transition-colors"
                                        style={{ color: 'var(--text-secondary)' }}
                                        onMouseEnter={(e) => {
                                            e.currentTarget.style.background = 'var(--danger-light)';
                                            e.currentTarget.style.color = 'var(--danger)';
                                        }}
                                        onMouseLeave={(e) => {
                                            e.currentTarget.style.background = 'transparent';
                                            e.currentTarget.style.color = 'var(--text-secondary)';
                                        }}
                                        role="menuitem"
                                    >
                                        <LogOut size={16} aria-hidden="true" />
                                        Sign Out
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </header>
    );
}
