import { cn } from '@/lib/utils';

interface Option {
    label: string;
    value: string | number;
}

interface SelectFieldProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
    label?: string;
    error?: string | string[];
    inputClassName?: string;
    options: Option[];
}

export const SelectField = ({
    className,
    inputClassName,
    name,
    value,
    label,
    onChange,
    onFocus,
    onBlur,
    error,
    options,
    id,
    ...props
}: SelectFieldProps) => {
    const generatedId = id ?? `select-${name}`;
    const hasError = !!error && (Array.isArray(error) ? error.length > 0 : true);

    return (
        <div className={cn('relative', className)}>
            {label && (
                <label htmlFor={generatedId} className="mb-2 block text-sm font-medium text-gray-900">
                    {label}
                </label>
            )}

            <select
                id={generatedId}
                name={name}
                value={value ?? ''}
                onChange={onChange}
                onFocus={onFocus}
                onBlur={onBlur}
                className={cn(
                    'rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900',
                    'focus:border-blue-500 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none',
                    'block w-full p-2.5',
                    {
                        'border-red-500 focus:border-red-500 focus:ring-red-500': hasError,
                    },
                    inputClassName,
                )}
                {...props}
            >
                {options.map((option, index) => (
                    <option key={`${name}-option-${index}`} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            {hasError && (
                <div className="mt-1 pl-1 text-sm text-red-500">
                    {Array.isArray(error) ? (
                        <ul>
                            {error.map((item, index) => (
                                <li key={`${generatedId}-error-${index}`}>{item}</li>
                            ))}
                        </ul>
                    ) : (
                        <span>{error}</span>
                    )}
                </div>
            )}
        </div>
    );
};
