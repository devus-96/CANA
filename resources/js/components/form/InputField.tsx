import { cn } from '@/lib/utils';
import { Eye, EyeOff } from 'lucide-react';
import { ChangeEvent, FC, FocusEvent, useState } from 'react';

interface InputFieldProps {
    id?: string;
    type?: string;
    name?: string;
    label?: string;
    canToggleType?: boolean;
    error?: string | string[];
    labelClass?: string;
    inputClass?: string;
    onChange?: (e: ChangeEvent<HTMLInputElement>) => void;
    onFocus?: (e: FocusEvent<HTMLInputElement>) => void;
    onBlur?: (e: FocusEvent<HTMLInputElement>) => void;
    disabled?: boolean;
    value: string | number | readonly string[] | undefined;
    placeholder?: string;
    className?: string;
    step?: string;
    autoFocus?: boolean;
    readOnly?: boolean;
}

export const InputField: FC<InputFieldProps> = ({
    label,
    id,
    type = 'text',
    name,
    canToggleType = false,
    error,
    labelClass,
    inputClass,
    onChange,
    onFocus,
    value,
    placeholder,
    disabled = false,
    onBlur,
    className,
    step,
    autoFocus,
    readOnly,
}) => {
    const [inputType, setInputType] = useState(type);
    const hasError = !!error && (Array.isArray(error) ? error.length > 0 : true);

    const generatedId = id ?? `input-${name}`;

    return (
        <div className={cn('relative', className)}>
            <div>
                {label && (
                    <label htmlFor={generatedId} className={cn('mb-1.5 block text-sm font-medium text-gray-900', labelClass)}>
                        {label}
                    </label>
                )}

                <div className="relative">
                    <input
                        id={generatedId}
                        name={name}
                        type={inputType}
                        disabled={disabled}
                        value={value}
                        placeholder={placeholder}
                        step={step}
                        onChange={onChange}
                        onFocus={onFocus}
                        onBlur={onBlur}
                        autoFocus={autoFocus}
                        readOnly={readOnly}
                        className={cn(
                            'focus:ring-secondary-300 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:outline-none',
                            { 'border-red-500 focus:ring-red-500': hasError },
                            inputClass,
                        )}
                    />
                    {canToggleType && type === 'password' && (
                        <button
                            type="button"
                            onClick={() => setInputType(inputType === 'password' ? 'text' : 'password')}
                            className="absolute inset-y-0 right-3 flex items-center text-gray-500"
                            tabIndex={-1}
                        >
                            {inputType === 'password' ? <EyeOff size={18} /> : <Eye size={18} />}
                        </button>
                    )}
                </div>
            </div>
            {hasError && (
                <div className="mt-1 text-sm text-red-500">
                    {Array.isArray(error) ? (
                        <ul>
                            {error.map((msg, i) => (
                                <li key={`${generatedId}-error-${i}`}>{msg}</li>
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
