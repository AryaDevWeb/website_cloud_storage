import { useEffect, useCallback } from 'react';

/**
 * Keyboard navigation hook for file grid/list.
 * - Enter = open selected item
 * - Space = toggle selection
 * - Arrow keys = navigate grid
 * - Delete / Backspace = trigger delete on selected
 * - Escape = clear selection
 * - Ctrl+A = select all
 */
export function useKeyboard({ items, selectedIds, onSelect, onOpen, onDelete, onSelectAll, onClearSelection, columns = 4 }) {
    const handleKeyDown = useCallback(
        (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;

            const currentIndex = items.findIndex((item) => selectedIds.has(item.id));

            switch (e.key) {
                case 'Enter': {
                    e.preventDefault();
                    if (selectedIds.size === 1) {
                        const selected = items.find((f) => selectedIds.has(f.id));
                        if (selected) onOpen?.(selected);
                    }
                    break;
                }
                case ' ': {
                    e.preventDefault();
                    if (currentIndex >= 0) {
                        onSelect?.(items[currentIndex].id, !selectedIds.has(items[currentIndex].id));
                    }
                    break;
                }
                case 'ArrowRight': {
                    e.preventDefault();
                    const next = Math.min(currentIndex + 1, items.length - 1);
                    onClearSelection?.();
                    onSelect?.(items[next >= 0 ? next : 0].id, true);
                    break;
                }
                case 'ArrowLeft': {
                    e.preventDefault();
                    const prev = Math.max(currentIndex - 1, 0);
                    onClearSelection?.();
                    onSelect?.(items[prev].id, true);
                    break;
                }
                case 'ArrowDown': {
                    e.preventDefault();
                    const down = Math.min(currentIndex + columns, items.length - 1);
                    onClearSelection?.();
                    onSelect?.(items[down >= 0 ? down : 0].id, true);
                    break;
                }
                case 'ArrowUp': {
                    e.preventDefault();
                    const up = Math.max(currentIndex - columns, 0);
                    onClearSelection?.();
                    onSelect?.(items[up].id, true);
                    break;
                }
                case 'Delete':
                case 'Backspace': {
                    if (selectedIds.size > 0) {
                        e.preventDefault();
                        onDelete?.();
                    }
                    break;
                }
                case 'Escape': {
                    onClearSelection?.();
                    break;
                }
                case 'a': {
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        onSelectAll?.();
                    }
                    break;
                }
                default:
                    break;
            }
        },
        [items, selectedIds, onSelect, onOpen, onDelete, onSelectAll, onClearSelection, columns]
    );

    useEffect(() => {
        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [handleKeyDown]);
}
