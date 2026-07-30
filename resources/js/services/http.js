import axios from 'axios';

const http = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

export function errorMessage(error, fallback) {
    return error.response?.data?.message ?? fallback;
}

export default http;
