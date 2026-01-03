import { ReactNode } from 'react';
import { create } from 'zustand';

const SERVER_ERROR = 'Le systeme est indisponible pour le moment';

interface DisplayState {
    visible: Record<string, boolean>;
    callbacks: Record<string, any>;
    set: (key: string, value: boolean) => void;
    show: (key: string, callback?: any) => void;
    hide: (key: string, callbackData?: any) => void;
    toggle: (key: string) => void;
    reset: () => void;
    when: ({ visible, not, children }: { visible?: string; not?: string; children: ReactNode }) => any;
}

interface LoadingState {
    status: Record<string, boolean>;
    start: (key: string) => void;
    stop: (key: string) => void;
    reset: () => void;
}

interface ErrorsState {
    values: Record<string, string[]>;
    set: (key: string, value: string | string[]) => void;
    setMany: (values: any) => void;
    unset: (key: string) => void;
    catch: (error: any) => void;
    reset: () => void;
    log: (error: any) => void;
    render: () => React.ReactNode;
}

export const useLoading = create<LoadingState>((setter) => ({
    status: {},
    start: (key) => {
        console.log(key);
        setter((state) => ({
            status: {
                ...state.status,
                [key]: true,
            },
        }));
    },
    stop: (key) => {
        console.log('stop ', key);
        setter((state) => {
            const newValues: Record<string, any> = {};
            for (const k in state.status) {
                if (k != key) {
                    newValues[k] = state.status[k];
                }
            }
            return {
                status: newValues,
            };
        });
    },

    reset: () => setter(() => ({})),
}));

export const useDisplay = create<DisplayState>((set, get) => ({
    visible: {},
    callbacks: {},
    set: (key, value) => {
        set((state) => ({
            visible: {
                ...state.visible,
                [key]: value,
            },
        }));
    },
    show: (key, callback: any) => {
        set((state) => ({
            visible: {
                ...state.visible,
                [key]: true,
            },
            callbacks: {
                [key]: callback,
            },
        }));
    },

    hide: (key: string, callbackData?: any) => {
        set((state) => {
            const newValues: Record<string, any> = {};
            for (const k in state.visible) {
                if (k != key) {
                    newValues[k] = state.visible[k];
                }
            }
            return {
                visible: newValues,
            };
        });

        const callbacks = get().callbacks;
        if (callbacks[key]) {
            const callback = callbacks[key];
            callback(callbackData);
            set((state) => ({
                callbacks: Object.fromEntries(Object.entries(state.callbacks).filter((pair) => pair[0] != key)),
            }));
        }
    },

    toggle: (key) => {
        set((state) => {
            if (state.visible.hasOwnProperty(key)) {
                const newValues: Record<string, any> = {};
                for (const k in state.visible) {
                    if (k != key) {
                        newValues[k] = state.visible[k];
                    }
                }
                return {
                    visible: newValues,
                };
            } else {
                return {
                    visible: {
                        ...state.visible,
                        [key]: true,
                    },
                };
            }
        });
    },
    reset: () => set(() => ({})),
    when: ({ visible, not, children }: { visible?: string; not?: string; children: ReactNode }) => {
        if (visible != undefined) {
            if (get().visible[visible]) {
                return <>{children}</>;
            } else {
                return null;
            }
        }

        if (not != undefined) {
            if (!get().visible[not]) {
                return <>{children}</>;
            } else {
                return null;
            }
        }
    },
}));

export const useErrors = create<ErrorsState>((setter, getter) => ({
    values: {},
    set: (key, value) =>
        setter((state) => ({
            values: {
                ...state.values,
                [key]: typeof value == 'string' ? [value] : value,
            },
        })),
    setMany: (errors: any) => {
        setter((state) => ({
            values: {
                ...state.values,
                ...errors,
            },
        }));
    },
    unset: (key) =>
        setter((state) => ({
            values: Object.fromEntries(Object.entries(state.values).filter(([k, v]) => k != key)),
        })),
    catch: (error) => {
        let isServerError = true;

        if (error.response != undefined) {
            if ([422, 401, 409, 404, 400].includes(error.response.status)) {
                isServerError = false;
                let values = { error: ['Le systeme est indisponible pour le moment'] };

                if (error.response.data.hasOwnProperty('errors')) {
                    values = error.response.data.errors;

                    return setter(() => ({
                        values: values,
                    }));
                }

                if (error.response.data.hasOwnProperty('message')) {
                    if (typeof error.response.data.message == 'string') {
                        values = {
                            error: [error.response.data.message],
                        };
                    }

                    if (typeof error.response.data.message == 'object') {
                        values = error.response.data.message;
                    }

                    return setter(() => ({
                        values: values,
                    }));
                }
            }
        } else {
            if (error.message != undefined) {
                return setter(() => ({
                    values: {
                        error_message: [error.message],
                    },
                }));
            }
        }

        if (isServerError) {
            return setter(() => ({
                values: {
                    server_error: [SERVER_ERROR],
                },
            }));
        }
    },
    reset: () => {
        setter(() => ({
            values: {},
        }));
    },
    log(error: any) {
        //logError(error);
    },
    render() {
        const values = getter().values;

        if (Object.keys(values).length == 0) return null;

        return (
            <div
                // initial={{ opacity: 0, scale: 0 }}
                // animate={{ opacity: 1, scale: 1 }}
                // transition={{
                //     duration: 0.4,
                //     scale: { type: 'spring', visualDuration: 0.4, bounce: 0.5 },
                // }}
                id="alert-border-1"
                className="mb-4 flex border-t-4 border-red-300 bg-red-50 p-4 text-red-800"
                role="alert"
            >
                <div className="ms-3 text-sm font-medium">
                    {Object.entries(values).map(([key, value], index) => (
                        <div key={`error${key}${index}`} className="ms-3 text-sm font-medium">
                            {Array.isArray(value) ? (
                                <ul>
                                    {value.map((msg, i) => (
                                        <li key={`error-${i}`}>{msg}</li>
                                    ))}
                                </ul>
                            ) : (
                                <span>{value}</span>
                            )}
                        </div>
                    ))}
                </div>
                <button
                    onClick={() =>
                        setter(() => ({
                            values: {},
                        }))
                    }
                    type="button"
                    className="-mx-1.5 -my-1.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 p-1.5 text-red-500 hover:bg-red-200 focus:ring-2 focus:ring-red-400"
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
    },
}));
