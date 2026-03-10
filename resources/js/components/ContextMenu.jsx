import React, { useEffect, useRef } from 'react';
import { FolderOpen, Download, Share2, Pencil, Move, Trash2 } from 'lucide-react';

const MENU_ITEMS = [
    { id: 'open', label: 'Open', icon: FolderOpen },
    { id: 'download', label: 'Download', icon: Download },
    { id: 'share', label: 'Share', icon: Share2 },
    { id: 'rename', label: 'Rename', icon: Pencil },
    { id: 'move', label: 'Move to…', icon: Move },
    { id: 'delete', label: 'Delete', icon: Trash2, danger: true },
];

export default function ContextMenu({ x, y, onAction, onClose }) {
    const menuRef = useRef(null);

    useEffect(() => {
        const handleClick = (e) => {
            if (menuRef.current && !menuRef.current.contains(e.target)) {
                onClose();
            }
        };
        const handleEsc = (e) => {
            if (e.key === 'Escape') onClose();
        };
        document.addEventListener('mousedown', handleClick);
        document.addEventListener('keydown', handleEsc);
        return () => {
            document.removeEventListener('mousedown', handleClick);
            document.removeEventListener('keydown', handleEsc);
        };
    }, [onClose]);

    // Adjust position to stay within viewport
    useEffect(() => {
        if (!menuRef.current) return;
        const rect = menuRef.current.getBoundingClientRect();
        const el = menuRef.current;
        if (rect.right > window.innerWidth) {
            el.style.left = `${x - rect.width}px`;
        }
        if (rect.bottom > window.innerHeight) {
            el.style.top = `${y - rect.height}px`;
        }
    }, [x, y]);

    return (
        <div
            ref={menuRef}
            className="animate-scale-in fixed z-[80] min-w-[180px] py-1.5 rounded-xl"
            style={{
                left: x,
                top: y,
                background: 'var(--surface)',
                border: '1px solid var(--border)',
                boxShadow: 'var(--shadow-lg)',
            }}
            role="menu"
            aria-label="File actions"
        >
            {MENU_ITEMS.map((item, i) => (
                <React.Fragment key={item.id}>
                    {item.danger && (
                        <div className="my-1" style={{ borderTop: '1px solid var(--border-light)' }} />
                    )}
                    <button
                        onClick={() => { onAction(item.id); onClose(); }}
                        className="focus-ring w-full flex items-center gap-3 px-3 py-2 text-sm transition-colors"
                        style={{
                            color: item.danger ? 'var(--danger)' : 'var(--text-secondary)',
                        }}
                        onMouseEnter={(e) => {
                            e.currentTarget.style.background = item.danger ? 'var(--danger-light)' : 'var(--muted)';
                            e.currentTarget.style.color = item.danger ? 'var(--danger)' : 'var(--text)';
                        }}
                        onMouseLeave={(e) => {
                            e.currentTarget.style.background = 'transparent';
                            e.currentTarget.style.color = item.danger ? 'var(--danger)' : 'var(--text-secondary)';
                        }}
                        role="menuitem"
                    >
                        <item.icon size={16} aria-hidden="true" />
                        {item.label}
                    </button>
                </React.Fragment>
            ))}
        </div>
    );
}
