import axios from 'axios';

/**
 * Authentication utility functions for API-based authentication
 * Uses Sanctum's cookie-based sessions (stateful mode)
 */

/**
 * Authenticate user with email and password
 * @param {string} email - User's email
 * @param {string} password - User's password
 * @param {boolean} remember - Remember user (optional)
 * @returns {Promise} Response object
 */
export async function login(email, password, remember = false) {
    try {
        const response = await axios.post('/api/auth/login', {
            email,
            password,
            remember
        }, {
            withCredentials: true
        });

        return {
            success: true,
            data: response.data
        };
    } catch (error) {
        return handleAuthError(error);
    }
}

/**
 * Register a new user
 * @param {Object} userData - User registration data (name, email, password, password_confirmation)
 * @returns {Promise} Response object
 */
export async function register(userData) {
    try {
        const response = await axios.post('/api/register', userData, {
            withCredentials: true
        });

        return {
            success: true,
            data: response.data
        };
    } catch (error) {
        return handleAuthError(error);
    }
}

/**
 * Logout the current user
 * @returns {Promise} Response object
 */
export async function logout() {
    try {
        const response = await axios.post('/api/auth/logout', {}, {
            withCredentials: true
        });

        return {
            success: true,
            data: response.data
        };
    } catch (error) {
        return handleAuthError(error);
    }
}

/**
 * Get current authenticated user
 * @returns {Promise} Response object
 */
export async function getCurrentUser() {
    try {
        const response = await axios.get('/api/auth/user', {
            withCredentials: true
        });

        return {
            success: true,
            data: response.data
        };
    } catch (error) {
        return handleAuthError(error);
    }
}

/**
 * Handle authentication errors
 * @param {Error} error - Axios error object
 * @returns {Object} Error response object
 */
function handleAuthError(error) {
    if (error.response) {
        // Server responded with error status
        const { status, data } = error.response;

        // Rate limiting error (429)
        if (status === 429) {
            return {
                success: false,
                error: 'Too many login attempts. Please try again later.',
                retryAfter: data.retry_after || 60,
                status
            };
        }

        // Validation errors (422)
        if (status === 422) {
            return {
                success: false,
                error: 'Validation failed',
                validationErrors: data.errors || {},
                status
            };
        }

        // Unauthorized (401)
        if (status === 401) {
            return {
                success: false,
                error: data.message || 'Invalid credentials',
                status
            };
        }

        // Server error (500)
        if (status >= 500) {
            return {
                success: false,
                error: 'Server error. Please try again later.',
                status
            };
        }

        // Other errors
        return {
            success: false,
            error: data.message || 'An error occurred',
            status
        };
    } else if (error.request) {
        // Request made but no response
        return {
            success: false,
            error: 'Network error. Please check your connection.',
            status: null
        };
    } else {
        // Error setting up request
        return {
            success: false,
            error: 'Request setup failed',
            status: null
        };
    }
}

/**
 * Format rate limiting message with remaining time
 * @param {number} seconds - Seconds to wait
 * @returns {string} Formatted message
 */
export function formatRateLimitMessage(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;

    if (minutes > 0) {
        return `Too many attempts. Please try again in ${minutes} minute${minutes > 1 ? 's' : ''} and ${remainingSeconds} second${remainingSeconds !== 1 ? 's' : ''}.`;
    }

    return `Too many attempts. Please try again in ${seconds} second${seconds !== 1 ? 's' : ''}.`;
}

/**
 * Get validation error message for a specific field
 * @param {Object} validationErrors - Validation errors object
 * @param {string} field - Field name
 * @returns {string|undefined} Error message or undefined
 */
export function getFieldValidationError(validationErrors, field) {
    if (validationErrors && validationErrors[field]) {
        return Array.isArray(validationErrors[field])
            ? validationErrors[field][0]
            : validationErrors[field];
    }
    return undefined;
}
