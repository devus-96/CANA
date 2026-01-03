import { cn } from '@/lib/utils';
import { CheckBoxOptionType } from '@/types';

interface RadiosFieldProps {
    name?: string;
    label?: string;
    options: CheckBoxOptionType[];
    onCheckedChange: (item: CheckBoxOptionType, checked: boolean) => void;
    error?: string | string[];

    className?: string;
    optionClassName?: string;
    value: any;
    labelClass?: string;
}

export default function RadiosField({ label, options, name, onCheckedChange, error, className, value, labelClass }: RadiosFieldProps) {
    const hasError = error != undefined && error.length != 0;

    return (
        <div>
            {label != undefined && <label className={cn('mb-1 block text-base font-medium text-gray-900', labelClass)}>{label}</label>}
            <div className={cn('space-y-2', className)}>
                {options.map((option: any, index: number) => (
                    <div className="flex" key={`radiofield${name}${index}`}>
                        <div className="flex h-5 items-center">
                            <input
                                onChange={(e: any) => {
                                    onCheckedChange(option, e.target.checked);
                                }}
                                id={`${name}-radio-${index}`}
                                aria-describedby={`${name}-checkbox`}
                                type="radio"
                                name={`${name}`}
                                checked={value == option.value}
                                className={cn('h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500')}
                            />
                        </div>
                        <div className="ms-2">
                            <label htmlFor={`${name}-radio-${index}`} className="font-medium">
                                {option.label}
                            </label>
                            <p className="font-normal text-gray-600">{option.description}</p>
                        </div>
                    </div>
                ))}
            </div>
            {hasError && (
                <div className="pl-1 text-red-500">
                    {Array.isArray(error) ? (
                        <ul>
                            {error.map((item, index) => (
                                <li key={`input${name}error${index}`}> {item}</li>
                            ))}
                        </ul>
                    ) : (
                        <span> {error}</span>
                    )}
                </div>
            )}
        </div>
    );
}
