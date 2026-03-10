import React from 'react';
import { AlertTriangle, X } from 'lucide-react';

export default function ConfirmModal({ open, title, message, confirmLabel = 'Delete', onConfirm, onCancel }) {
    if (!open) return null;

    return (
        <div
            className="fixed inset-0 z-[90] flex items-center justify-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-title"
        >
            {/* Backdrop */}
            <div className="absolute inset-0 bg-black/20" onClick={onCancel} aria-hidden="true" />

            {/* Modal */}
            <div
                className="animate-scale-in relative w-full max-w-md mx-4 rounded-2xl p-6"
                style={{
                    background: 'var(--surface)',
                    border: '1px solid var(--border)',
                    boxShadow: 'var(--shadow-lg)',
                }}
            >
                <button
                    onClick={onCancel}
                    className="focus-ring absolute top-4 right-4 p-1.5 rounded-lg transition-colors"
                    style={{ color: 'var(--text-tertiary)' }}
                    aria-label="Close dialog"
                >
                    <X size={18} />
                </button>

                <div className="flex items-start gap-4">
                    <div
                        className="flex items-center justify-center w-10 h-10 rounded-xl shrink-0"
                        style={{ background: 'var(--danger-light)' }}
                    >
                        <AlertTriangle size={20} style={{ color: 'var(--danger)' }} aria-hidden="true" />
                    </div>
                    <div className="flex-1 min-w-0">
                        <h3 id="confirm-title" className="text-base font-semibold" style={{ color: 'var(--text)' }}>
                            {title}
                        </h3>
                        <p className="mt-1 text-sm" style={{ color: 'var(--text-secondary)' }}>
                            {message}
                        </p>
                    </div>
                </div>

                <div className="flex justify-end gap-3 mt-6">
                    <button
                        onClick={onCancel}
                        className="focus-ring px-4 py-2 text-sm font-medium rounded-xl transition-colors"
                        style={{
                            color: 'var(--text-secondary)',
                            background: 'var(--muted)',
                        }}
                    >
                        Cancel
                    </button>
                    <button
                        onClick={onConfirm}
                        className="focus-ring px-4 py-2 text-sm font-medium rounded-xl transition-colors text-white"
                        style={{ background: 'var(--danger)' }}
                    >
                        {confirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
