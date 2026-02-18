import { useState, useEffect, useRef, useCallback } from 'react';

/**
 * Custom hook for implementing infinite scroll pagination
 * @param {Function} fetchFunction - Async function to fetch data, accepts (page, perPage) params
 * @param {Object} options - Configuration options
 * @param {number} options.initialPage - Initial page number (default: 1)
 * @param {number} options.perPage - Items per page (default: 10)
 * @param {string} options.loadMoreText - Text shown when loading more (default: 'Loading more...')
 * @param {boolean} options.enabled - Enable/disable the hook (default: true)
 * @returns {Object} - Hook state and methods
 */
export function useInfiniteScroll(fetchFunction, options = {}) {
    const {
        initialPage = 1,
        perPage = 10,
        loadMoreText = 'Loading more...',
        enabled = true
    } = options;

    // State management
    const [data, setData] = useState([]);
    const [page, setPage] = useState(initialPage);
    const [loading, setLoading] = useState(false);
    const [initialLoading, setInitialLoading] = useState(true);
    const [hasMore, setHasMore] = useState(true);
    const [error, setError] = useState(null);
    const [total, setTotal] = useState(0);

    // Refs
    const observerRef = useRef(null);
    const loadingRef = useRef(false);
    const fetchFunctionRef = useRef(fetchFunction);

    // Update fetch function ref
    useEffect(() => {
        fetchFunctionRef.current = fetchFunction;
    }, [fetchFunction]);

    // Reset pagination state
    const reset = useCallback(() => {
        setData([]);
        setPage(initialPage);
        setLoading(false);
        setInitialLoading(true);
        setHasMore(true);
        setError(null);
        setTotal(0);
        loadingRef.current = false;
    }, [initialPage]);

    // Fetch data for a specific page
    const fetchPage = useCallback(async (pageNum) => {
        if (!enabled || loadingRef.current) {
            return;
        }

        loadingRef.current = true;
        setLoading(true);

        try {
            const response = await fetchFunctionRef.current(pageNum, perPage);

            // Handle different response formats
            let newData = [];
            let paginationMeta = null;

            if (response?.data && Array.isArray(response.data)) {
                // Direct array response
                newData = response.data;
                paginationMeta = {
                    current_page: pageNum,
                    last_page: response.meta?.last_page || Math.ceil((response.meta?.total || response.total || response.data.length) / perPage),
                    per_page: perPage,
                    total: response.meta?.total || response.total || response.data.length
                };
            } else if (response?.data?.data && Array.isArray(response.data.data)) {
                // Laravel-style paginated response
                newData = response.data.data;
                paginationMeta = {
                    current_page: response.data.meta?.current_page || pageNum,
                    last_page: response.data.meta?.last_page || pageNum,
                    per_page: response.data.meta?.per_page || perPage,
                    total: response.data.meta?.total || response.data.data.length
                };
            } else if (Array.isArray(response)) {
                // Direct array
                newData = response;
                paginationMeta = {
                    current_page: pageNum,
                    last_page: Math.ceil(response.length / perPage),
                    per_page: perPage,
                    total: response.length
                };
            }

            // Update state
            if (pageNum === initialPage) {
                setData(newData);
            } else {
                setData(prev => [...prev, ...newData]);
            }

            setTotal(paginationMeta.total);

            // Determine if there are more pages
            const newHasMore = paginationMeta.current_page < paginationMeta.last_page && newData.length === perPage;
            setHasMore(newHasMore);
            setPage(paginationMeta.current_page);

            setError(null);
        } catch (err) {
            console.error('Error fetching paginated data:', err);
            setError(err);
        } finally {
            setLoading(false);
            setInitialLoading(false);
            loadingRef.current = false;
        }
    }, [enabled, perPage, initialPage]);

    // Load more items
    const loadMore = useCallback(() => {
        if (!loading && hasMore && !loadingRef.current && enabled) {
            fetchPage(page + 1);
        }
    }, [loading, hasMore, page, fetchPage, enabled]);

    // Initialize data fetch
    useEffect(() => {
        if (enabled) {
            reset();
            fetchPage(initialPage);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [enabled, initialPage]);

    // Intersection Observer for infinite scroll
    useEffect(() => {
        if (!enabled || !hasMore) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const [entry] = entries;
                if (entry.isIntersecting && hasMore && !loadingRef.current) {
                    loadMore();
                }
            },
            {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            }
        );

        if (observerRef.current) {
            observer.observe(observerRef.current);
        }

        return () => {
            if (observerRef.current) {
                observer.unobserve(observerRef.current);
            }
            observer.disconnect();
        };
    }, [hasMore, loadMore, enabled]);

    // Retry loading current page
    const retry = useCallback(() => {
        setError(null);
        fetchPage(page);
    }, [page, fetchPage]);

    return {
        // State
        data,
        loading,
        initialLoading,
        hasMore,
        error,
        total,
        currentPage: page,
        perPage,
        loadMoreText,

        // Methods
        loadMore,
        reset,
        retry,
        fetchPage,

        // Refs
        observerRef,
        loadingRef
    };
}

/**
 * Component for displaying loading indicator at the bottom of list
 */
export function InfiniteScrollLoader({ loading, hasMore, error, retry, text = 'Loading more...' }) {
    if (error) {
        return (
            <div className="flex flex-col items-center justify-center py-8">
                <div className="flex items-center gap-2 text-red-600 dark:text-red-400 mb-2">
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span className="text-sm">Failed to load more items</span>
                </div>
                <button
                    onClick={retry}
                    className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors min-h-[44px]"
                    aria-label="Retry loading more items"
                >
                    Retry
                </button>
            </div>
        );
    }

    if (!hasMore && !loading) {
        return null;
    }

    if (loading) {
        return (
            <div className="flex items-center justify-center py-8" role="status" aria-live="polite">
                <div className="flex items-center gap-3">
                    <div className="h-5 w-5 animate-spin rounded-full border-2 border-solid border-indigo-600 border-t-transparent" aria-hidden="true" />
                    <span className="text-sm text-gray-600 dark:text-gray-400">{text}</span>
                </div>
            </div>
        );
    }

    return null;
}
