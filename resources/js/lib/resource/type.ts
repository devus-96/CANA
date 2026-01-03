export interface Pagination<T = any> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: any[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export type ID = string | number;
export type Operation = 'create' | 'destroy' | 'update';

export interface OperationOptions {
    addFirst?: boolean;
    sync?: boolean;
    header?: any;
}

interface AbstractResourceStore<T> {
    current?: T;
    // Local State Change
    transform: (item: T) => T;
    setCurrent: (inputs: Partial<T> | undefined) => void;
    add: (input: T, firstPosition?: boolean) => void;
    sync: (data: Partial<T>, predicate: (item: T) => boolean) => void;
    syncWithId: (data: Partial<T>) => void;
    // Request to Server.
    // Exec a query that is provide.
    // It receive a high level modelQuery.
    fetch: (params?: any) => Promise<any>;
    create: (data: Partial<T> | FormData, options?: OperationOptions) => Promise<T>;
    update: (id: ID, data: Partial<T> | FormData, options?: OperationOptions) => Promise<T>;
    updateCurrent: (data: Partial<T>, options?: OperationOptions) => Promise<T>;
    destroyCurrent: (options?: OperationOptions) => Promise<any>;
    // Loading Mangement.
    loading: (operation: Operation, id?: ID) => boolean;
    loadingCurrent: (operation: Operation) => boolean;
}

export interface BaseResourceStore<T> extends AbstractResourceStore<T> {
    pagination: Pagination;
    items: T[];
    // Client side update functions.
    setItems: (inputs: T[]) => void;
    remove: (index: number) => void;
    filter: (predicate: (resource: T, index?: number) => boolean) => void;
    // Request to server
    fetchOne: (id: ID) => Promise<any>;
    destroy: (id: ID, options?: OperationOptions) => Promise<any>;
}

export interface BaseGroupedResourceStore<T> extends AbstractResourceStore<T> {
    groupId: string;
    items: Record<ID, T[]>;
    pagination: Record<ID, Pagination>;
    // Local State.
    fetchGrouped: (params?: any) => Promise<any>; // this imply that there is grouped-item route in the backend.
    setItems: (inputs: Record<ID, T[]>) => void;
    setGroup: (groupId: ID, inputs: T[]) => void;
    getGroupId: (item: Partial<T>) => ID;
    remove: (groupId: ID, index: number) => void;
    filter: (groupId: ID, predicate: (item: T, index?: number) => boolean) => void;
    // Request to API
    destroy: (groupId: ID, id: ID, options?: OperationOptions) => Promise<any>;
}

export type ResourceStore<T> = Partial<BaseResourceStore<T>>;

export type GroupedResourceStore<T> = Partial<BaseGroupedResourceStore<T>>;
