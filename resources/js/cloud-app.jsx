import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './components/App';

const rootEl = document.getElementById('cloud-root');
if (rootEl) {
    createRoot(rootEl).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}
