import axios from 'axios';
import { tokenStore } from './auth';

const baseURL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://laravel-project.test/api';

export const api = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = tokenStore.get();
  if (token) {
    config.headers = config.headers ?? {};
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
