import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createStore } from 'vuex';
import store from './store';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import i18n from './plugins/i18n';
import axios from 'axios';

// Set default axios configuration
axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Add axios interceptor for authentication
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add response interceptor for handling auth errors globally
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response) {
      // Handle authentication errors
      if (error.response.status === 401) {
        // Clear token on 401 Unauthorized
        localStorage.removeItem('token');
        
        // Redirect to login if not already there
        if (router.currentRoute.value && router.currentRoute.value.name !== 'login') {
          router.push('/login');
        }
      }
      
      // Handle CSRF token errors
      if (error.response.status === 419) {
        // Get a fresh CSRF token
        return axios.get('/sanctum/csrf-cookie').then(() => {
          // Retry the original request
          return axios(error.config);
        });
      }
    }
    return Promise.reject(error);
  }
);

// Create Vue application
const app = createApp(App);
const pinia = createPinia();

// Use plugins
app.use(store);
app.use(pinia);
app.use(router);
app.use(vuetify);
app.use(i18n);

// Mount application
app.mount('#app');
