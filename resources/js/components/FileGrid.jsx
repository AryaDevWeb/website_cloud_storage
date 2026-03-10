import React from 'react';
import FileCard from './FileCard';

export default function FileGrid({ items, selectedIds, onSelect, onClick, onContextMenu, onMenuClick }) {
    return (
        <div
            className="grid gap-3"
            style={{
                gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
            }}
            role="grid"
            aria-label="Files and folders grid"
        >
            {items.map((item) => (
                <FileCard
                    key={item.id}
                    item={item}
                    selected={selectedIds.has(item.id)}
                    onSelect={onSelect}
                    onClick={onClick}
                    onContextMenu={onContextMenu}
                    onMenuClick={onMenuClick}
                />
            ))}
        </div>
    );
}
