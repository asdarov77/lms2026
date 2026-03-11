import axios from 'axios';
import router from '../router';
import { TokenService } from '../services/storage.service';
import { UserService } from '../services/user.service';

const httpClient = axios.create({
  baseURL: `${import.meta.env.VITE_APP_URL}/api`,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
});

// Add interceptor to get CSRF token
httpClient.interceptors.request.use(async (config) => {
  if (!TokenService.getToken()) {
    try {
      await axios.get(`${import.meta.env.VITE_APP_URL}/sanctum/csrf-cookie`, {
        withCredentials: true
      });
    } catch (error) {
      console.error('Failed to get CSRF cookie:', error);
    }
  }
  return config;
});

const getAuthToken = () => TokenService.getToken();

const authInterceptor = config => {
  const token = getAuthToken();
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`;
  }
  return config;
};

httpClient.interceptors.request.use(authInterceptor);

const errorInterceptor = error => {
  if (error.response) {
    console.error('Response error:', {
      status: error.response.status,
      data: error.response.data,
      headers: error.response.headers
    });
  } else if (error.request) {
    console.error('Request error:', {
      url: error.request.responseURL,
      status: error.request.status,
      statusText: error.request.statusText
    });
  } else {
    console.error('Error:', error.message);
  }
  return Promise.reject(error);
};

const responseInterceptor = response => {
  console.log('Response interceptor:', response);
  return response;
};

httpClient.interceptors.response.use(responseInterceptor, errorInterceptor);

export default httpClient;