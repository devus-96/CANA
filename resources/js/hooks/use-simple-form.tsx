'use client';

import { CheckBoxOptionType } from '@/types';
import { useForm } from '@inertiajs/react';

export function useSimpleForm<T extends Record<string, any>>(defaults: T, schema: any = undefined) {
    const form = useForm<T>(defaults);

    const setValue = (name: string, value: any) => {
        form.setData(name, value);
    };

    function handleChange(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) {
        const { name, value } = e.target;
        setValue(name, value);
    }
    function handleRadioChange (item: CheckBoxOptionType, e:React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) {
        const { name } = e.target;
        setValue(name, item.value);
    }
    function handleNumberChange(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) {
        const { name, value } = e.target;
        let bool = false;

        if (typeof value === 'string') {
            bool = /^\d+$/.test(value);
        }

        if (typeof value === 'number' || value == '') {
            bool = true;
        }

        if (bool) {
            setValue(name, value);
        }
    }

    function getValidValues(values: any) {
        const result = schema.safeParse(values);

        if (result.success) {
            // If the data is valid, return the entire object
            return result.data;
        } else {
            // If the data is invalid, extract valid fields
            const validFields: any = {};
            const errors = result.error.errors;

            for (const key of Object.keys(values)) {
                // Check if the field has no errors
                if (!errors.some((error: any) => error.path.includes(key))) {
                    validFields[key] = values[key];
                }
            }
            return validFields;
        }
    }

    function setErrors(errors: Record<string, string | string[]>) {
        Object.entries(errors).forEach(([Key, val]: any) => {
            form.setError(Key, val);
        });
    }

    function validateAsync() {
        if (schema != undefined) {
            const result = schema.safeParse(form.data);
            if (!result.success) {
                const errorObj: { [key: string]: string } = {};
                result.error.errors.forEach((err: any) => {
                    errorObj[err.path[0]] = err.message;
                });
                setErrors(errorObj);
                return Promise.reject(errorObj);
            } else {
                setErrors({});
                return Promise.resolve(result.data);
            }
        } else {
            return Promise.resolve(form.data);
        }
    }

    function validate() {
        form.clearErrors();

        if (schema != undefined) {
            const result = schema.safeParse(form.data);

            if (!result.success) {
                const errorObj: { [key: string]: string } = {};
                result.error.errors.forEach((err: any) => {
                    errorObj[err.path[0]] = err.message;
                });
                setErrors(errorObj);
                return { valid: false, errors: errorObj };
            } else {
                return { valid: true, values: result.data };
            }
        } else {
            return {
                valid: true,
            };
        }
    }

    function check(inputs: any = undefined) {
        if (schema != undefined) {
            const result = schema.safeParse(inputs != undefined ? inputs : form.data);
            if (!result.success) {
                const errorObj: { [key: string]: string } = {};
                result.error.errors.forEach((err: any) => {
                    errorObj[err.path[0]] = err.message;
                });

                setErrors(errorObj);
                return false;
            } else {
                setErrors({});
                return true;
            }
        } else {
            return true;
        }
    }

    function renderErrors() {
        if (Object.keys(form.errors).length == 0) return null;

        return (
            <div
                // initial={{ opacity: 0, scale: 0 }}
                // animate={{ opacity: 1, scale: 1 }}
                // transition={{
                //     duration: 0.4,
                //     scale: { type: 'spring', visualDuration: 0.4, bounce: 0.5 },
                // }}
                id="alert-border-1"
                className="mb-4 flex border-t-4 border-red-300 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-gray-800 dark:text-red-400"
                role="alert"
            >
                <div className="ms-3 text-sm font-medium">
                    {Object.entries(form.errors).map(([key, value], index) => (
                        <div key={`error${key}${index}`} className="ms-3 text-sm font-medium">
                            {value as string}
                        </div>
                    ))}
                </div>
                <button
                    onClick={() => setErrors({})}
                    type="button"
                    className="-mx-1.5 -my-1.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 p-1.5 text-red-500 hover:bg-red-200 focus:ring-2 focus:ring-red-400 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"
                    data-dismiss-target="#alert-border-1"
                    aria-label="Close"
                >
                    <span className="sr-only">Dismiss</span>
                    <svg className="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                        />
                    </svg>
                </button>
            </div>
        );
    }

    // A special function to handle easly array update.
    // If the is true the function add the value to the array hold by the name key
    // otherwise we remove the value.
    function pushToggle(name: string, value: any, condition: boolean) {
        setValue(name, condition ? [...form.data[name], value] : form.data[name].filter((el: string) => el != value));
    }

    function hasError(name: string) {
        return form.errors[name] != undefined && form.errors[name] != '';
    }

    return {
        getValidValues,
        handleNumberChange,
        handleRadioChange,
        setValue,
        handleChange,
        validate,
        check,
        validateAsync,
        hasError,
        setErrors,
        renderErrors,
        pushToggle,
        ...form,
    };
}

export const validateObject = (values: any, schema: any) => {
    if (schema != undefined) {
        const result = schema.safeParse(values);
        if (!result.success) {
            const errorObj: { [key: string]: string } = {};
            result.error.errors.forEach((err: any) => {
                errorObj[err.path[0]] = err.message;
            });
            return { valid: false, errors: errorObj };
        } else {
            return { valid: true, values: result.data };
        }
    } else {
        return { valid: true, values };
    }
};
