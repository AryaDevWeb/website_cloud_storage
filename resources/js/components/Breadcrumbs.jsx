import React from 'react';
import { ChevronRight, Home } from 'lucide-react';

export default function Breadcrumbs({ path = [], onNavigate }) {
    return (
        <nav aria-label="Breadcrumb" className="flex items-center gap-1 text-sm overflow-x-auto">
            <button
                onClick={() => onNavigate?.(null)}
                className="focus-ring flex items-center gap-1.5 px-2 py-1 rounded-lg transition-colors shrink-0"
                style={{ color: path.length ? 'var(--text-secondary)' : 'var(--text)' }}
                onMouseEnter={(e) => (e.currentTarget.style.background = 'var(--muted)')}
                onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                aria-label="Home"
            >
                <Home size={15} aria-hidden="true" />
                <span className="font-medium">Home</span>
            </button>

            {path.map((segment, i) => {
                const isLast = i === path.length - 1;
                return (
                    <React.Fragment key={segment.id}>
                        <ChevronRight size={14} style={{ color: 'var(--text-tertiary)' }} aria-hidden="true" className="shrink-0" />
                        <button
                            onClick={() => !isLast && onNavigate?.(segment.id)}
                            className="focus-ring px-2 py-1 rounded-lg transition-colors shrink-0 font-medium"
                            style={{
                                color: isLast ? 'var(--text)' : 'var(--text-secondary)',
                                cursor: isLast ? 'default' : 'pointer',
                            }}
                            onMouseEnter={(e) => !isLast && (e.currentTarget.style.background = 'var(--muted)')}
                            onMouseLeave={(e) => (e.currentTarget.style.background = 'transparent')}
                            aria-current={isLast ? 'page' : undefined}
                            disabled={isLast}
                        >
                            {segment.name}
                        </button>
                    </React.Fragment>
                );
            })}
        </nav>
    );
}
