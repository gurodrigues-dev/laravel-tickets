export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-500 bg-white dark:bg-gray-700 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-400 ' +
                className
            }
        />
    );
}
