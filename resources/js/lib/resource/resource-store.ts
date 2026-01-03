import { useLoading } from '@/hooks/use-interact';
import { create } from 'zustand';
import { apiClient } from '../http';
import { createPrimitive, destroyPrimitive, fakePagination, loadingKey, updatePrimitive, withLoading } from './primitives';
import { BaseResourceStore, ID, Operation, Pagination } from './type';

export const initResourceStore = <T>(index: string, set: any, get: any): BaseResourceStore<T> => {

    return {
        items: [],
        pagination: fakePagination([]) as Pagination,

        fetch: (params: any = {}) => {
            return apiClient()
                .get(`${index}`, { params })
                .then((res) => {
                    if (res.data.hasOwnProperty('current_page')) {
                        set(() => {
                            return {
                                pagination: res.data,
                                items: res.data.data,
                            };
                        });
                        return Promise.resolve(res.data.data);
                    } else {
                        set(() => {
                            return { items: res.data };
                        });
                        return Promise.resolve(res.data);
                    }
                });
        },

        fetchOne: (id: ID) => {
            return apiClient()
                .get(`${index}/${id}`)
                .then((res) => {
                    get().setCurrent(res.data);
                    return Promise.resolve(res.data);
                });
        },

        transform: (item: T) => item,

        setCurrent: (inputs: Partial<T> | undefined) =>
            set((state: any) => ({
                current:
                    inputs == undefined
                        ? undefined
                        : get().transform({
                              ...state.current,
                              ...inputs,
                          }),
            })),
        setItems: (items: T[]) => {
            set(() => ({
                items: items.map((item) => get().transform(item)),
            }));
        },
        add: (item: T, addFirst: boolean = false) => {
            const { items, setItems } = get();
            setItems(addFirst ? [item, ...items] : [...items, item]);
        },
        filter: (predicate: (item: T, index: number) => boolean) => {
            const { items, setItems } = get();
            setItems(items.filter(predicate));
        },

        remove: (index: number) => {
            get().filter((_: T, i: number) => i != index);
        },

        sync: (data: Partial<T>, predicate: (item: T) => boolean) => {
            const { items, setItems } = get();
            setItems(items.map((item: T) => (predicate(item) ? { ...item, ...data } : item)));
        },

        syncWithId: (data: Partial<T>) => {
            get().sync(data, (item: T) => (item as any).id == (data as any).id);
        },

        update: (id: ID, data: Partial<T> | FormData, options?: any) => {
            return withLoading(`update_${index}_${id}`, async () => {
                const updated = await updatePrimitive<T>({ data, index, id, options });
                if (options?.sync !== false) {
                    get().syncWithId(updated);
                }
                return updated;
            });
        },

        create: (data: Partial<T> | FormData, options?: any) => {
            return withLoading(`create_${index}`, async () => {
                const created = await createPrimitive<T>({ index, data, options });
                if (options?.sync !== false) {
                    get().add(created, options?.addFirst === true);
                }
                return created;
            });
        },

        destroy: (id: ID, options?: any) => {
            return withLoading(loadingKey('destroy', index, id), async () => {
                const deleted = await destroyPrimitive({ index, id, options });
                if (options?.sync !== false) {
                    get().filter((item: T) => (item as any).id !== id);
                }
                return deleted;
            });
        },

        loading: (operation: Operation, id?: ID) => {
            return useLoading.getState().status[loadingKey(operation, index, id)];
        },
        updateCurrent: (data: Partial<T>, options?: any) => {
            const current = get().current;
            if (!current) {
                return Promise.reject('No current element selected.');
            }
            return get().update(current.id, data, options);
        },

        destroyCurrent: (options?: any) => {
            const current = get().current;
            if (!current) {
                return Promise.reject('No current element selected.');
            }

            return get().destroy(current.id, options);
        },

        loadingCurrent: (operation: Operation) => {
            const current = get().current;
            if (!current) {
                return false;
            } else {
                return useLoading.getState().status[loadingKey(operation, index, current.id)];
            }
        },
    };
};

export const createResourceStore = <T, S = {}>(index: string, stateSlice?: (set: any, get: any) => S) => {
    return create<BaseResourceStore<T> & S>((set, get) => ({
        ...initResourceStore<T>(index, set, get),
        ...(stateSlice ? stateSlice(set, get) : ({} as S)),
    }));
};
