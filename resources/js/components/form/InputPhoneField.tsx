import { cn } from '@/lib/utils';
import { FC } from 'react';
import { PhoneInput } from './PhoneInput';

interface InputFieldProps {
    id?: string;
    name?: string;
    label?: string;
    error?: string | string[];
    onChange: (value: string) => void;
    disabled?: boolean;
    value: string;
    placeholder?: string;
    className?: string;
}

export const InputPhoneField: FC<InputFieldProps> = ({ label, id, name, error, onChange, value, placeholder, disabled = false, className }) => {
    const hasError = !!error && (Array.isArray(error) ? error.length > 0 : true);

    const generatedId = id ?? `input-${name}`;

    return (
        <div className={cn('relative', className)}>
            <div>
                {label && (
                    <label htmlFor={generatedId} className={cn('mb-1.5 block text-sm font-medium text-gray-900')}>
                        {label}
                    </label>
                )}

                <PhoneInput value={value} onChange={onChange} />
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
