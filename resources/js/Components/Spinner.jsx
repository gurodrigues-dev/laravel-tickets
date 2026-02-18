import React from 'react';

/**
 * Loading Spinner Component
 * Provides a consistent, accessible loading indicator
 */
export default function Spinner({
    size = 'md',
    color = 'indigo',
    text = null,
    className = '',
    overlay = false
}) {
    const sizeClasses = {
        sm: 'h-4 w-4',
        md: 'h-8 w-8',
        lg: 'h-12 w-12',
        xl: 'h-16 w-16'
    };

    const colorClasses = {
        indigo: 'border-indigo-600 border-t-transparent',
        gray: 'border-gray-400 border-t-transparent',
        white: 'border-white border-t-transparent',
        green: 'border-green-600 border-t-transparent',
        red: 'border-red-600 border-t-transparent'
    };

    const textSizeClasses = {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-base',
        xl: 'text-lg'
    };

    const spinner = (
        <>
            <div
                className={`animate-spin rounded-full border-4 border-solid ${sizeClasses[size]} ${colorClasses[color]} ${className}`}
                role="status"
                aria-label={text || 'Loading'}
            />
            {text && (
                <span className={`ml-2 ${textSizeClasses[size]} text-gray-600 dark:text-gray-400`}>{text}</span>
            )}
        </>
    );

    if (overlay) {
        return (
            <div className="flex items-center justify-center inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 absolute">
                {spinner}
            </div>
        );
    }

    return (
        <div className="flex items-center justify-center">
            {spinner}
        </div>
    );
}

export function FullPageSpinner({ text = 'Loading...' }) {
    return (
        <div className="fixed inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-50">
            <div className="text-center">
                <Spinner size="xl" text={text} />
            </div>
        </div>
    );
}

export function ButtonSpinner({ color = 'white' }) {
    return (
        <div className="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-t-transparent" />
    );
}
