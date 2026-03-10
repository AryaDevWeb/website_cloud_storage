import { useState, useEffect, useRef, useCallback } from 'react';

/**
 * Debounced search hook.
 * Returns { query, setQuery, debouncedQuery, isSearching }.
 * @param {number} delayMs — debounce delay in ms (default 300)
 */
export function useSearch(delayMs = 300) {
    const [query, setQuery] = useState('');
    const [debouncedQuery, setDebouncedQuery] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const timerRef = useRef(null);

    useEffect(() => {
        if (query) {
            setIsSearching(true);
        }

        if (timerRef.current) {
            clearTimeout(timerRef.current);
        }

        timerRef.current = setTimeout(() => {
            setDebouncedQuery(query);
            setIsSearching(false);
        }, delayMs);

        return () => {
            if (timerRef.current) clearTimeout(timerRef.current);
        };
    }, [query, delayMs]);

    const clearSearch = useCallback(() => {
        setQuery('');
        setDebouncedQuery('');
        setIsSearching(false);
    }, []);

    return { query, setQuery, debouncedQuery, isSearching, clearSearch };
}
