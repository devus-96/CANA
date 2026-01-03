import { cn } from '@/lib/utils';

export default function FloatingInputField({
    label,
    id,
    type,
    name,
    onChange,
    value,
    inputClass,
}: {
    label: string;
    id: string;
    type?: string;
    value: any;
    name: string;
    onChange: any;
    inputClass?: string;
}) {
    return (
        <div className="relative w-full bg-inherit">
            <input
                id={id ? id : `${name}flaoting`}
                onChange={onChange}
                className={cn(
                    'peer block w-full appearance-none rounded-lg border-1 border-gray-300 bg-transparent px-2.5 pt-4 pb-2.5 text-base focus:border-blue-600 focus:ring-0 focus:outline-none',
                    inputClass,
                )}
                value={value}
                type={type ? type : 'text'}
                placeholder={label}
            />
            <label
                htmlFor={id ? id : `${name}flaoting`}
                className="absolute start-1 top-2 z-10 origin-[0] -translate-y-4 scale-75 transform bg-inherit px-2 text-base text-gray-500 duration-300 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:scale-100 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75 peer-focus:px-2 peer-focus:text-blue-600 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4"
            >
                {label}
            </label>
        </div>
    );
}
