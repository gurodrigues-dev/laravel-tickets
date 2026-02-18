import ThemeToggle from '@/Components/ThemeToggle';
import { useTheme } from '@/Context/ThemeContext';

export default function ThemeSettingsForm({ className = '' }) {
    const { isDark } = useTheme();

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Theme Settings</h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Choose your preferred color scheme for the application. Your preference will be saved and applied automatically on your next visit.
                </p>
            </header>

            <div className="mt-6">
                <div className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                    <div className="flex items-center space-x-3">
                        <div className={`p-2 rounded-lg ${isDark ? 'bg-gray-600 text-yellow-400' : 'bg-gray-200 text-gray-600'}`}>
                            {isDark ? (
                                <svg
                                    className="w-6 h-6"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            ) : (
                                <svg
                                    className="w-6 h-6"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                </svg>
                            )}
                        </div>
                        <div>
                            <div className="text-sm font-medium text-gray-700 dark:text-gray-200">
                                {isDark ? 'Dark Mode' : 'Light Mode'}
                            </div>
                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                Currently active
                            </div>
                        </div>
                    </div>

                    <ThemeToggle />
                </div>

                <p className="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Tip: Toggle the button above to switch between light and dark modes. The theme will automatically adapt based on your system preferences if you haven't made a selection.
                </p>
            </div>
        </section>
    );
}
