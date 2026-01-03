import { useLoading } from "@/hooks/use-interact";
import { apiClient } from "../http";
import { ID, Pagination } from "./type";

export const fakePagination = (data: any[]): Pagination => {
  return {
    current_page: 1,
    data: data,
    first_page_url: "",
    from: 0,
    last_page: 1,
    last_page_url: "",
    links: [],
    next_page_url: "",
    path: "",
    per_page: data.length,
    prev_page_url: null,
    to: data.length,
    total: data.length,
  };
};

export const paginateArray = <T = any>(
  items: T[],
  currentPage: number = 1,
  perPage: number = 10,
  basePath: string = ""
): Pagination<T> => {
  const total = items.length;
  const lastPage = Math.max(Math.ceil(total / perPage), 1);
  const clampedPage = Math.min(Math.max(currentPage, 1), lastPage);
  const from = (clampedPage - 1) * perPage;
  const to = Math.min(from + perPage, total);

  const paginatedItems = items.slice(from, to);

  const buildUrl = (page: number) =>
    `${basePath}?page=${page}&perPage=${perPage}`;

  return {
    current_page: clampedPage,
    data: paginatedItems,
    first_page_url: buildUrl(1),
    from: from + 1,
    last_page: lastPage,
    last_page_url: buildUrl(lastPage),
    links: [], // Optional: can build a list of page links here
    next_page_url: clampedPage < lastPage ? buildUrl(clampedPage + 1) : null,
    path: basePath,
    per_page: perPage,
    prev_page_url: clampedPage > 1 ? buildUrl(clampedPage - 1) : null,
    to: to,
    total: total,
  };
};

export function updatePrimitive<T>({
  data,
  index,
  id,
  options,
}: {
  id: ID;
  index: string;
  data: Partial<T> | FormData;
  options?: any;
}): Promise<T> {
  if (data instanceof FormData) {
    return apiClient()
      .post(`/${index}/${id}`, data, {
        ...options?.request,
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((res) => {
        return Promise.resolve(res.data);
      });
  } else {
    return apiClient()
      .put(`/${index}/${id}`, data, options)
      .then((res) => {
        return Promise.resolve(res.data);
      });
  }
}

export function createPrimitive<T>({
  index,
  data,
  options,
}: {
  index: string;
  data: Partial<T> | FormData;
  options?: any;
}): Promise<T> {
  const client = apiClient();

  if (data instanceof FormData) {
    return client
      .post(`/${index}`, data, {
        ...options?.request,
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((res) => Promise.resolve(res.data));
  } else {
    return client
      .post(`/${index}`, data, options)
      .then((res) => Promise.resolve(res.data));
  }
}

export function destroyPrimitive<T>({
  index,
  id,
  options,
}: {
  index: string;
  id: ID;
  options?: any;
}): Promise<any> {
  return apiClient()
    .delete(`/${index}/${id}`, options?.request)
    .then((res) => res.data);
}
export const withLoading = async <T>(
  key: string,
  fn: () => Promise<T>
): Promise<T> => {
  const { start, stop } = useLoading.getState();
  start(key);
  try {
    const result = await fn();
    stop(key);
    return result;
  } catch (err) {
    stop(key);
    return Promise.reject(err);
  }
};

export const loadingKey = (
  operation: "create" | "update" | "destroy",
  index: string,
  id?: ID
) => {
  return id ? `${operation}_${index}_${id}` : `${operation}_${index}`;
};
