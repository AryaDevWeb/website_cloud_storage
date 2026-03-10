import React, { useState, useRef, useCallback, useEffect } from 'react';
import { Upload, X, CheckCircle, AlertCircle, Plus } from 'lucide-react';
import { uploadFile, formatBytes } from '../services/api';
import { useToast } from './Toast';

export default function UploadManager({ onFileUploaded, isMobile }) {
    const [isDragging, setIsDragging] = useState(false);
    const [uploads, setUploads] = useState([]);
    const fileInputRef = useRef(null);
    const dragCounter = useRef(0);
    const { addToast } = useToast();

    // ── Drag & Drop handlers ──────────────────────────
    const handleDragEnter = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter.current++;
        if (e.dataTransfer.items && e.dataTransfer.items.length > 0) {
            setIsDragging(true);
        }
    }, []);

    const handleDragLeave = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter.current--;
        if (dragCounter.current === 0) {
            setIsDragging(false);
        }
    }, []);

    const handleDragOver = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
    }, []);

    const handleDrop = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
        dragCounter.current = 0;

        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            processFiles(Array.from(e.dataTransfer.files));
        }
    }, []);

    useEffect(() => {
        window.addEventListener('dragenter', handleDragEnter);
        window.addEventListener('dragleave', handleDragLeave);
        window.addEventListener('dragover', handleDragOver);
        window.addEventListener('drop', handleDrop);
        return () => {
            window.removeEventListener('dragenter', handleDragEnter);
            window.removeEventListener('dragleave', handleDragLeave);
            window.removeEventListener('dragover', handleDragOver);
            window.removeEventListener('drop', handleDrop);
        };
    }, [handleDragEnter, handleDragLeave, handleDragOver, handleDrop]);

    // ── File processing ────────────────────────────────
    const processFiles = useCallback((files) => {
        files.forEach((file) => {
            const uploadId = Date.now() + Math.random();
            setUploads((prev) => [
                ...prev,
                { id: uploadId, name: file.name, size: file.size, progress: 0, status: 'uploading' },
            ]);

            uploadFile(file, null, (progress) => {
                setUploads((prev) =>
                    prev.map((u) => (u.id === uploadId ? { ...u, progress } : u))
                );
            })
                .then((newFile) => {
                    setUploads((prev) =>
                        prev.map((u) => (u.id === uploadId ? { ...u, progress: 100, status: 'done' } : u))
                    );
                    addToast(`"${file.name}" uploaded successfully`, 'success');
                    onFileUploaded?.(newFile);

                    // Remove from list after a delay
                    setTimeout(() => {
                        setUploads((prev) => prev.filter((u) => u.id !== uploadId));
                    }, 3000);
                })
                .catch(() => {
                    setUploads((prev) =>
                        prev.map((u) => (u.id === uploadId ? { ...u, status: 'error' } : u))
                    );
                    addToast(`Failed to upload "${file.name}"`, 'error');
                });
        });
    }, [addToast, onFileUploaded]);

    const cancelUpload = useCallback((uploadId) => {
        setUploads((prev) => prev.filter((u) => u.id !== uploadId));
    }, []);

    const triggerFileInput = useCallback(() => {
        fileInputRef.current?.click();
    }, []);

    const handleFileInputChange = useCallback((e) => {
        if (e.target.files && e.target.files.length > 0) {
            processFiles(Array.from(e.target.files));
            e.target.value = '';
        }
    }, [processFiles]);

    return (
        <>
            {/* Hidden file input */}
            <input
                ref={fileInputRef}
                type="file"
                multiple
                onChange={handleFileInputChange}
                className="hidden"
                aria-label="Choose files to upload"
            />

            {/* Drag & Drop Overlay */}
            {isDragging && (
                <div
                    className="fixed inset-0 z-[60] flex items-center justify-center animate-fade-in"
                    style={{
                        background: 'rgba(82, 82, 82, 0.08)',
                        border: '3px dashed var(--accent)',
                    }}
                >
                    <div
                        className="px-8 py-6 rounded-2xl text-center"
                        style={{
                            background: 'var(--surface)',
                            border: '1px solid var(--border)',
                            boxShadow: 'var(--shadow-lg)',
                        }}
                    >
                        <div
                            className="w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4"
                            style={{ background: 'var(--muted)' }}
                        >
                            <Upload size={28} style={{ color: 'var(--accent)' }} aria-hidden="true" />
                        </div>
                        <h2 className="text-lg font-semibold mb-1" style={{ color: 'var(--text)' }}>
                            Drop to upload
                        </h2>
                        <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                            Your files will be saved immediately
                        </p>
                    </div>
                </div>
            )}

            {/* Upload progress panel */}
            {uploads.length > 0 && (
                <div
                    className="fixed bottom-20 right-6 z-[70] w-80 rounded-xl overflow-hidden animate-slide-up"
                    style={{
                        background: 'var(--surface)',
                        border: '1px solid var(--border)',
                        boxShadow: 'var(--shadow-lg)',
                    }}
                >
                    <div
                        className="px-4 py-3 flex items-center justify-between"
                        style={{ borderBottom: '1px solid var(--border-light)' }}
                    >
                        <span className="text-sm font-semibold" style={{ color: 'var(--text)' }}>
                            Uploading {uploads.filter((u) => u.status === 'uploading').length} file(s)
                        </span>
                    </div>
                    <div className="max-h-48 overflow-y-auto">
                        {uploads.map((upload) => (
                            <div
                                key={upload.id}
                                className="px-4 py-3 flex items-center gap-3"
                                style={{ borderBottom: '1px solid var(--border-light)' }}
                            >
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium truncate" style={{ color: 'var(--text)' }}>
                                        {upload.name}
                                    </p>
                                    <div className="flex items-center gap-2 mt-1">
                                        <div
                                            className="flex-1 h-1.5 rounded-full overflow-hidden"
                                            style={{ background: 'var(--border)' }}
                                        >
                                            <div
                                                className="h-full rounded-full transition-all duration-300"
                                                style={{
                                                    width: `${upload.progress}%`,
                                                    background: upload.status === 'error' ? 'var(--danger)' : 'var(--accent)',
                                                }}
                                            />
                                        </div>
                                        <span className="text-xs shrink-0" style={{ color: 'var(--text-tertiary)' }}>
                                            {upload.progress}%
                                        </span>
                                    </div>
                                </div>
                                {upload.status === 'done' && (
                                    <CheckCircle size={16} style={{ color: 'var(--success)' }} aria-label="Upload complete" />
                                )}
                                {upload.status === 'error' && (
                                    <AlertCircle size={16} style={{ color: 'var(--danger)' }} aria-label="Upload failed" />
                                )}
                                {upload.status === 'uploading' && (
                                    <button
                                        onClick={() => cancelUpload(upload.id)}
                                        className="focus-ring p-1 rounded-lg"
                                        style={{ color: 'var(--text-tertiary)' }}
                                        aria-label={`Cancel upload of ${upload.name}`}
                                    >
                                        <X size={14} />
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Mobile FAB */}
            {isMobile && (
                <button
                    onClick={triggerFileInput}
                    className="focus-ring fixed bottom-6 right-6 z-40 w-14 h-14 rounded-2xl text-white flex items-center justify-center transition-all active:scale-95"
                    style={{
                        background: 'var(--accent)',
                        boxShadow: '0 4px 16px rgba(82, 82, 82, 0.3)',
                    }}
                    aria-label="Upload files"
                >
                    <Plus size={24} aria-hidden="true" />
                </button>
            )}
        </>
    );
}
