// http.ts

import axios, { AxiosHeaders, AxiosInstance, AxiosRequestHeaders } from 'axios';

export function client(customHeaders: Partial<AxiosRequestHeaders> = {}): AxiosInstance {

    const defaultHeaders: Partial<AxiosRequestHeaders> = {
        Accept: 'application/json',
    };

    return axios.create({
        headers: {
            ...defaultHeaders,
            ...customHeaders,
        },
        withCredentials: true,
        withXSRFToken: true
    });
}

let httpInstance: AxiosInstance | undefined;

export const apiClient = (): AxiosInstance => {
    if (httpInstance) {
        return httpInstance;
    }

    const headers = AxiosHeaders.from({
        Accept: 'application/json',
    });

    const instance = axios.create({
        headers,
        withCredentials: true,
        withXSRFToken: true,
        baseURL: 'http://api.monsite-dev.com',
    });

    instance.interceptors.response.use(
       (res) => {
          console.info(`RESPONSE (${res.config.url}) => `, res);

          return res;
        },
        (error) => {
          console.info(`RESPONSE-ERROR (${error.config.url}) => `, error);

          throw error;
        }
    );

    httpInstance = instance;

    return instance;
};

export const getHttpClient = client;
