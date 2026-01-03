import { Popover, PopoverContent, PopoverTrigger } from '@radix-ui/react-popover';
import { RadioGroup, RadioGroupItem } from '@radix-ui/react-radio-group';
import { ChevronDown } from 'lucide-react';
import React, { useEffect, useImperativeHandle, useState } from 'react';

type Country = {
    name: string;
    code: string;
    flag: React.ReactNode;
};

const countries: Country[] = [
    {
        name: 'Cameroon',
        code: '+237',
        flag: (
            <svg className="me-2 h-4 w-4" viewBox="0 0 20 15">
                <rect width="20" height="15" fill="#007A5E" />
                <rect x="6.67" width="6.66" height="15" fill="#FFCD00" />
                <rect x="13.33" width="6.67" height="15" fill="#CE1126" />
                <polygon fill="#FFCD00" points="10,4.5 10.95,7.8 14.05,7.8 11.55,9.7 12.5,13 10,11.1 7.5,13 8.45,9.7 5.95,7.8 9.05,7.8" />
            </svg>
        ),
    },
    {
        name: 'France',
        code: '+33',
        flag: (
            <svg className="me-2 h-4 w-4" viewBox="0 0 20 15">
                <rect width="20" height="15" fill="#fff" />
                <rect width="6.67" height="15" fill="#0055A4" />
                <rect x="13.33" width="6.67" height="15" fill="#EF4135" />
            </svg>
        ),
    },
];

interface InputPhoneProps {
    value: string;
    onChange: (value: string) => void;
    countryCode?: string;
    onCountryChange?: (code: string) => void;
}

export const PhoneInput = React.forwardRef<HTMLInputElement, InputPhoneProps>(
    ({ value, onChange, countryCode = '+1', onCountryChange, ...rest }, ref) => {
        const [open, setOpen] = useState(false);
        const [inputValue, setInputValue] = useState('');
        const [selectedCode, setSelectedCode] = useState(countryCode);

        const inputRef = React.useRef<HTMLInputElement>(null);

        // Expose the internal input's ref to the parent
        useImperativeHandle(ref, () => inputRef.current as HTMLInputElement);

        useEffect(() => {
            const raw = value.startsWith(selectedCode.replace('+', '')) ? value.slice(selectedCode.replace('+', '').length) : value;
            setInputValue(raw);
        }, [value, selectedCode]);

        const selectedCountry = countries.find((c) => c.code === selectedCode) ?? countries[0];

        const emitFullNumber = (input: string, code: string) => {
            const cleanCode = code.replace('+', '');
            const cleanInput = input.replace(/\D/g, '');
            onChange(`${cleanCode}${cleanInput}`);
        };

        const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
            const newVal = e.target.value;
            setInputValue(newVal);
            emitFullNumber(newVal, selectedCode);
        };

        const handleCountryChange = (newCode: string) => {
            setSelectedCode(newCode);
            emitFullNumber(inputValue, newCode);
            onCountryChange?.(newCode);
            setOpen(false);
        };

        return (
            <div className="flex items-center">
                <Popover open={open} onOpenChange={setOpen}>
                    <PopoverTrigger asChild>
                        <button
                            type="button"
                            className="z-10 inline-flex shrink-0 items-center rounded-s-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-200 focus:ring-4 focus:ring-gray-100 focus:outline-none"
                        >
                            {selectedCountry.flag}
                            {selectedCountry.code}
                            <ChevronDown className="ml-2 h-3 w-3" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent className="z-50 mt-2 w-52 rounded-lg border border-gray-200 bg-white shadow-lg" side="bottom" align="start">
                        <RadioGroup className="py-2 text-sm text-gray-700" value={selectedCode} onValueChange={handleCountryChange}>
                            {countries.map((country) => (
                                <RadioGroupItem
                                    key={country.code}
                                    value={country.code}
                                    className="flex w-full cursor-pointer items-center px-4 py-2 hover:bg-gray-100"
                                >
                                    {country.flag}
                                    {country.name} ({country.code})
                                </RadioGroupItem>
                            ))}
                        </RadioGroup>
                    </PopoverContent>
                </Popover>

                <input
                    type="tel"
                    ref={inputRef}
                    value={inputValue}
                    onChange={handleInputChange}
                    className="focus:ring-secondary-300 block w-full rounded-e-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:outline-none"
                    {...rest}
                />
            </div>
        );
    },
);

PhoneInput.displayName = 'PhoneInput';
