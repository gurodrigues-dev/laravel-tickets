import { createContext, useContext, useState, useEffect, useCallback } from 'react';

const ThemeContext = createContext();

const defaultTheme = 'light';

export function ThemeProvider({ children }) {
    const [theme, setTheme] = useState(() => {
        // Initialize from localStorage or default to 'light'
        if (typeof window !== 'undefined') {
            const storedTheme = localStorage.getItem('theme');
            return storedTheme || defaultTheme;
        }
        return defaultTheme;
    });

    const [isInitialized, setIsInitialized] = useState(false);

    useEffect(() => {
        const storedTheme = localStorage.getItem('theme') || defaultTheme;
        const root = window.document.documentElement;

        root.classList.remove('light', 'dark');

        if (storedTheme === 'dark') {
            root.classList.add('dark');
        } else {
            root.classList.add('light');
        }

        setTheme(storedTheme);
        setIsInitialized(true);
    }, []);

    const setThemeValue = useCallback((newTheme) => {
        if (newTheme === theme) return;

        const root = window.document.documentElement;

        root.classList.remove('light', 'dark');

        if (newTheme === 'dark') {
            root.classList.add('dark');
        } else {
            root.classList.add('light');
        }

        localStorage.setItem('theme', newTheme);
        setTheme(newTheme);
    }, [theme]);

    const toggleTheme = useCallback(() => {
        const newTheme = theme === 'light' ? 'dark' : 'light';
        setThemeValue(newTheme);
    }, [theme, setThemeValue]);

    const value = {
        theme,
        setTheme: setThemeValue,
        toggleTheme,
        isInitialized,
        isDark: theme === 'dark',
        isLight: theme === 'light',
    };

    if (!isInitialized) {
        return null;
    }

    return (
        <ThemeContext.Provider value={value}>
            {children}
        </ThemeContext.Provider>
    );
}

export function useTheme() {
    const context = useContext(ThemeContext);
    if (!context) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }
    return context;
}
