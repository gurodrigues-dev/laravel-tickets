/**
 * Example Usage of useInfiniteScroll Hook
 *
 * This file demonstrates how to use the useInfiniteScroll hook
 * in different scenarios.
 */

import { useInfiniteScroll, InfiniteScrollLoader } from '@/hooks/useInfiniteScroll';
import axios from 'axios';

// ============================================================
// Example 1: Basic Usage
// ============================================================
export function BasicExample() {
    const fetchData = async (page, perPage) => {
        const response = await axios.get(`/api/v1/items?page=${page}&per_page=${perPage}`);
        return response;
    };

    const {
        data: items,
        loading,
        initialLoading,
        hasMore,
        error,
        retry,
        observerRef
    } = useInfiniteScroll(fetchData, {
        initialPage: 1,
        perPage: 20,
        loadMoreText: 'Loading more items...'
    });

    if (initialLoading) {
        return <div>Loading...</div>;
    }

    return (
        <div>
            <h1>Items ({items.length})</h1>

            {error && (
                <div className="error">
                    {error.message}
                    <button onClick={retry}>Retry</button>
                </div>
            )}

            <div className="items-list">
                {items.map((item) => (
                    <div key={item.id}>{item.name}</div>
                ))}
            </div>

            <div ref={observerRef}>
                <InfiniteScrollLoader
                    loading={loading}
                    hasMore={hasMore}
                    error={error}
                    retry={retry}
                    text="Loading more items..."
                />
            </div>
        </div>
    );
}

// ============================================================
// Example 2: With Search/Filter (Client-Side)
// ============================================================
export function WithFilterExample() {
    const [searchQuery, setSearchQuery] = useState('');
    const [filters, setFilters] = useState({});

    const fetchData = async (page, perPage) => {
        const response = await axios.get(`/api/v1/products?page=${page}&per_page=${perPage}`);
        return response;
    };

    const {
        data: allProducts,
        loading,
        initialLoading,
        hasMore,
        error,
        retry,
        observerRef
    } = useInfiniteScroll(fetchData, {
        initialPage: 1,
        perPage: 15
    });

    // Client-side filtering
    const filteredProducts = useMemo(() => {
        return allProducts.filter(product => {
            const matchesSearch = !searchQuery ||
                product.name.toLowerCase().includes(searchQuery.toLowerCase());

            const matchesCategory = !filters.category ||
                product.category === filters.category;

            return matchesSearch && matchesCategory;
        });
    }, [allProducts, searchQuery, filters]);

    if (initialLoading) {
        return <div>Loading products...</div>;
    }

    return (
        <div>
            {/* Search Input */}
            <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search products..."
            />

            {/* Category Filter */}
            <select
                value={filters.category || ''}
                onChange={(e) => setFilters({ ...filters, category: e.target.value })}
            >
                <option value="">All Categories</option>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
            </select>

            {/* Results Info */}
            <p>Showing {filteredProducts.length} of {allProducts.length} products</p>

            {/* Filtered Products List */}
            <div className="products-grid">
                {filteredProducts.map((product) => (
                    <div key={product.id} className="product-card">
                        <h3>{product.name}</h3>
                        <p>{product.description}</p>
                        <p>${product.price}</p>
                    </div>
                ))}
            </div>

            {/* Empty State */}
            {filteredProducts.length === 0 && (
                <div className="empty-state">
                    No products found matching your criteria.
                </div>
            )}

            {/* Infinite Scroll Loader */}
            <div ref={observerRef}>
                <InfiniteScrollLoader
                    loading={loading}
                    hasMore={hasMore}
                    error={error}
                    retry={retry}
                    text="Loading more products..."
                />
            </div>
        </div>
    );
}

// ============================================================
// Example 3: With Server-Side Filter (Phase 2)
// ============================================================
export function WithServerFilterExample() {
    const [searchQuery, setSearchQuery] = useState('');
    const [category, setCategory] = useState('');

    // Fetch data with server-side filters
    const fetchData = async (page, perPage) => {
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: perPage.toString()
        });

        if (searchQuery) params.append('search', searchQuery);
        if (category) params.append('category', category);

        const response = await axios.get(`/api/v1/products?${params.toString()}`);
        return response;
    };

    const {
        data: products,
        loading,
        initialLoading,
        hasMore,
        error,
        retry,
        reset,  // Use reset to clear data when filters change
        observerRef
    } = useInfiniteScroll(fetchData, {
        initialPage: 1,
        perPage: 15
    });

    // Reset pagination when filters change
    useEffect(() => {
        reset();
    }, [searchQuery, category, reset]);

    if (initialLoading) {
        return <div>Loading products...</div>;
    }

    return (
        <div>
            <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search products..."
            />

            <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
            >
                <option value="">All Categories</option>
                <option value="electronics">Electronics</option>
                <option value="clothing">Clothing</option>
            </select>

            <div className="products-grid">
                {products.map((product) => (
                    <div key={product.id} className="product-card">
                        <h3>{product.name}</h3>
                        <p>{product.description}</p>
                        <p>${product.price}</p>
                    </div>
                ))}
            </div>

            <div ref={observerRef}>
                <InfiniteScrollLoader
                    loading={loading}
                    hasMore={hasMore}
                    error={error}
                    retry={retry}
                    text="Loading more products..."
                />
            </div>
        </div>
    );
}

// ============================================================
// Example 4: Manual Load More (Button)
// ============================================================
export function ManualLoadMoreExample() {
    const fetchData = async (page, perPage) => {
        const response = await axios.get(`/api/v1/articles?page=${page}&per_page=${perPage}`);
        return response;
    };

    const {
        data: articles,
        loading,
        hasMore,
        error,
        retry,
        loadMore  // Manual load more function
    } = useInfiniteScroll(fetchData, {
        initialPage: 1,
        perPage: 10
    });

    return (
        <div>
            <h1>Articles</h1>

            {error && (
                <div className="error">
                    {error.message}
                    <button onClick={retry}>Retry</button>
                </div>
            )}

            <div className="articles-list">
                {articles.map((article) => (
                    <article key={article.id}>
                        <h2>{article.title}</h2>
                        <p>{article.excerpt}</p>
                    </article>
                ))}
            </div>

            {/* Manual Load More Button */}
            {hasMore && (
                <button
                    onClick={loadMore}
                    disabled={loading}
                    className="load-more-btn"
                >
                    {loading ? 'Loading...' : 'Load More Articles'}
                </button>
            )}

            {!hasMore && articles.length > 0 && (
                <p className="end-message">No more articles to load.</p>
            )}
        </div>
    );
}

// ============================================================
// Example 5: With Data Refresh
// ============================================================
export function WithRefreshExample() {
    const [lastUpdate, setLastUpdate] = useState(null);

    const fetchData = async (page, perPage) => {
        const response = await axios.get(`/api/v1/notifications?page=${page}&per_page=${perPage}`);
        return response;
    };

    const {
        data: notifications,
        loading,
        initialLoading,
        hasMore,
        retry,
        observerRef
    } = useInfiniteScroll(fetchData, {
        initialPage: 1,
        perPage: 10
    });

    const handleRefresh = () => {
        setLastUpdate(new Date());
        retry();  // Reload from first page
    };

    if (initialLoading) {
        return <div>Loading notifications...</div>;
    }

    return (
        <div>
            <header>
                <h1>Notifications</h1>
                <button onClick={handleRefresh}>Refresh</button>
                {lastUpdate && (
                    <span className="last-update">
                        Last updated: {lastUpdate.toLocaleTimeString()}
                    </span>
                )}
            </header>

            <div className="notifications-list">
                {notifications.map((notification) => (
                    <div key={notification.id} className="notification">
                        <h3>{notification.title}</h3>
                        <p>{notification.message}</p>
                        <span className="date">{notification.created_at}</span>
                    </div>
                ))}
            </div>

            <div ref={observerRef}>
                <InfiniteScrollLoader
                    loading={loading}
                    hasMore={hasMore}
                    text="Loading more notifications..."
                />
            </div>
        </div>
    );
}

// ============================================================
// Example 6: Error Handling with Custom Error UI
// ============================================================
export function CustomErrorExample() {
    const fetchData = async (page, perPage) => {
        const response = await axios.get(`/api/v1/comments?page=${page}&per_page=${perPage}`);
        return response;
    };

    const {
        data: comments,
        loading,
        initialLoading,
        hasMore,
        error,
        retry,
        observerRef
    } = useInfiniteScroll(fetchData, {
        initialPage: 1,
        perPage: 10
    });

    if (initialLoading) {
        return <div className="loading-state">Loading comments...</div>;
    }

    // Custom error handling
    if (error && comments.length === 0) {
        return (
            <div className="error-state">
                <svg className="error-icon" viewBox="0 0 24 24">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2>Oops! Something went wrong</h2>
                <p>{error.message}</p>
                <button onClick={retry} className="retry-button">
                    Try Again
                </button>
            </div>
        );
    }

    return (
        <div>
            <h1>Comments ({comments.length})</h1>

            <div className="comments-list">
                {comments.map((comment) => (
                    <div key={comment.id} className="comment">
                        <div className="comment-header">
                            <span className="author">{comment.author}</span>
                            <span className="date">{comment.created_at}</span>
                        </div>
                        <p className="comment-text">{comment.text}</p>
                    </div>
                ))}
            </div>

            <div ref={observerRef}>
                <InfiniteScrollLoader
                    loading={loading}
                    hasMore={hasMore}
                    error={error}
                    retry={retry}
                    text="Loading more comments..."
                />
            </div>
        </div>
    );
}
