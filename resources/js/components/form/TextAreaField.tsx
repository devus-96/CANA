import { cn } from '@/lib/utils';
import { ChangeEvent, FC, FocusEvent } from 'react';

interface TextAreaFieldProps {
    id?: string;
    name?: string;
    label: string;
    error?: string | string[];
    labelClass?: string;
    inputClass?: string;
    onChange?: (e: ChangeEvent<HTMLTextAreaElement>) => void;
    onFocus?: (e: FocusEvent<HTMLTextAreaElement>) => void;
    onBlur?: (e: FocusEvent<HTMLTextAreaElement>) => void;
    disabled?: boolean;
    value: string;
    placeholder?: string;
    className?: string;
    rows?: number;
}

export const TextAreaField: FC<TextAreaFieldProps> = ({
    label,
    id,
    name,
    error,
    labelClass,
    inputClass,
    onChange,
    onFocus,
    onBlur,
    disabled = false,
    value,
    placeholder,
    className,
    rows = 4,
}) => {
    const generatedId = id ?? `textarea-${name}`;
    const hasError = !!error && (Array.isArray(error) ? error.length > 0 : true);

    return (
        <div className={cn('relative', className)}>
            <label htmlFor={generatedId} className={cn('mb-1.5 block text-sm font-medium text-gray-900', labelClass)}>
                {label}
            </label>

            <textarea
                id={generatedId}
                name={name}
                rows={rows}
                disabled={disabled}
                value={value ?? ''}
                placeholder={placeholder}
                onChange={onChange}
                onFocus={onFocus}
                onBlur={onBlur}
                className={cn(
                    'block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900',
                    'focus:ring-secondary-300 focus:border-primary-200 focus:border-blue-500 focus:border-transparent focus:ring-2 focus:outline-none',
                    {
                        'border-red-500 focus:border-red-500 focus:ring-red-500': hasError,
                    },
                    inputClass,
                )}
            ></textarea>

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
