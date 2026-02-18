/**
 * Get user-friendly error message from API error response
 * @param {Object} error - The error object from axios
 * @returns {Object} - { message: string, icon: string, type: string }
 */
export function getErrorDetails(error) {
    if (!error) {
        return {
            message: 'An unexpected error occurred.',
            icon: 'alert-circle',
            type: 'error'
        };
    }

    if (error.response) {
        const status = error.response.status;
        const data = error.response.data;

        switch (status) {
            case 422:
                return {
                    message: data?.message || data?.error || 'Invalid input. Please check your data and try again.',
                    icon: 'alert-triangle',
                    type: 'warning'
                };

            case 409:
                return {
                    message: data?.message || data?.error || 'A conflict occurred. Please refresh and try again.',
                    icon: 'x-circle',
                    type: 'error'
                };

            case 404:
                return {
                    message: 'The requested resource was not found. Please refresh and try again.',
                    icon: 'search-x',
                    type: 'error'
                };

            case 401:
                return {
                    message: 'You are not authenticated. Please log in again.',
                    icon: 'lock-closed',
                    type: 'error'
                };

            case 403:
                return {
                    message: 'You do not have permission to access this resource.',
                    icon: 'shield-exclamation',
                    type: 'error'
                };

            case 500:
            case 502:
            case 503:
                return {
                    message: 'An unexpected error occurred. Please try again later.',
                    icon: 'server',
                    type: 'error'
                };

            default:
                return {
                    message: data?.message || data?.error || `Error ${status}: Something went wrong.`,
                    icon: 'alert-circle',
                    type: 'error'
                };
        }
    }

    if (error.request) {
        return {
            message: 'Unable to connect to the server. Please check your internet connection.',
            icon: 'wifi-off',
            type: 'error'
        };
    }

    return {
        message: error.message || 'An unexpected error occurred.',
        icon: 'alert-circle',
        type: 'error'
    };
}

export function getErrorMessage(error) {
    return getErrorDetails(error).message;
}
