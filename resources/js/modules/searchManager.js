/**
 * searchManager.js — 300ms debounced search
 */
import { loadFiles } from './fileManager.js';

let timer = null;
const DELAY = 300;

export function initSearch() {
    const desktop = document.getElementById('desktop-search');
    const mobile = document.querySelector('#mobile-search-bar input[name="cari"]');

    function handleInput(e) {
        clearTimeout(timer);
        timer = setTimeout(() => {
            loadFiles(e.target.value.trim());
        }, DELAY);
    }

    // prevent form submission — use JS search instead
    function handleSubmit(e) {
        e.preventDefault();
        clearTimeout(timer);
        const input = e.target.querySelector('input');
        loadFiles(input?.value.trim() || '');
    }

    desktop?.addEventListener('input', handleInput);
    mobile?.addEventListener('input', handleInput);

    desktop?.closest('form')?.addEventListener('submit', handleSubmit);
    mobile?.closest('form')?.addEventListener('submit', handleSubmit);
}
