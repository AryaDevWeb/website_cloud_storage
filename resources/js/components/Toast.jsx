import React, { useState, useEffect, useCallback, createContext, useContext } from 'react';
import { X, CheckCircle, AlertCircle, Undo2 } from 'lucide-react';

const ToastContext = createContext(null);

export function useToast() {
    const ctx = useContext(ToastContext);
    if (!ctx) throw new Error('useToast must be used within ToastProvider');
    return ctx;
}

export function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const addToast = useCallback((message, type = 'success', options = {}) => {
        const id = Date.now() + Math.random();
        setToasts((prev) => [...prev, { id, message, type, ...options }]);
        if (!options.persistent) {
            setTimeout(() => removeToast(id), options.duration || 4000);
        }
        return id;
    }, []);

    const removeToast = useCallback((id) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }, []);

    return (
        <ToastContext.Provider value={{ addToast, removeToast }}>
            {children}
            <div
                className="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col items-center gap-2 pointer-events-none"
                role="region"
                aria-label="Notifications"
            >
                {toasts.map((toast) => (
                    <div
                        key={toast.id}
                        className="animate-slide-up pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border max-w-sm"
                        style={{
                            background: 'var(--surface)',
                            borderColor: 'var(--border)',
                            color: 'var(--text)',
                        }}
                        role="alert"
                    >
                        {toast.type === 'success' && <CheckCircle size={18} style={{ color: 'var(--success)' }} aria-hidden="true" />}
                        {toast.type === 'error' && <AlertCircle size={18} style={{ color: 'var(--danger)' }} aria-hidden="true" />}
                        <span className="text-sm font-medium flex-1">{toast.message}</span>
                        {toast.onUndo && (
                            <button
                                onClick={() => {
                                    toast.onUndo();
                                    removeToast(toast.id);
                                }}
                                className="focus-ring text-sm font-semibold px-2 py-1 rounded-lg transition-colors"
                                style={{ color: 'var(--accent)' }}
                                aria-label="Undo action"
                            >
                                <Undo2 size={16} />
                            </button>
                        )}
                        <button
                            onClick={() => removeToast(toast.id)}
                            className="focus-ring p-1 rounded-lg transition-colors"
                            style={{ color: 'var(--text-tertiary)' }}
                            aria-label="Dismiss notification"
                        >
                            <X size={14} />
                        </button>
                    </div>
                ))}
            </div>
        </ToastContext.Provider>
    );
}
