import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref(null)

  // Simple getApi without console.log
  let apiInstance = null
  
  const getApi = () => {
    if (apiInstance) {
      if (token.value) {
        apiInstance.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      }
      return apiInstance
    }
    
    apiInstance = axios.create({
      baseURL: '/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })

    if (token.value) {
      apiInstance.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }

    apiInstance.interceptors.response.use(
      response => response,
      error => {
        if (error.response && error.response.status === 401) {
          token.value = null;
          user.value = null;
          localStorage.removeItem('token');
          if (router.currentRoute.value.name !== 'login') {
            router.push('/login');
          }
        }
        return Promise.reject(error);
      }
    );

    return apiInstance
  }

  const isAuthenticated = computed(() => {
    return !!token.value && !!user.value
  })
  
  const isAdmin = computed(() => {
    if (!user.value) return false;
    const role = user.value.role;
    return role === 'Администратор' || role === 'admin' || role === 'Admin'
  })
  
  const isTeacher = computed(() => {
    return user.value?.role === 'Преподаватель' || user.value?.role === 'Инструктор'
  })
  
  const isStudent = computed(() => {
    return user.value?.role === 'Обучаемый' || user.value?.role === 'Студент'
  })

  async function login(credentials) {
    loading.value = true
    error.value = null
    
    try {
      const response = await getApi().post('/login', credentials)
      
      token.value = response.data.token
      user.value = response.data.user
      
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await getApi().get('/logout')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.push('/login')
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    
    try {
      const response = await getApi().get('/user')
      user.value = response.data
      localStorage.setItem('user', JSON.stringify(response.data))
      return response.data
    } catch (err) {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      throw err
    }
  }

  async function initializeAuth() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')
    
    if (storedToken) {
      token.value = storedToken
      if (storedUser) {
        user.value = JSON.parse(storedUser)
      }
      try {
        await fetchUser()
      } catch (err) {
        console.error('Failed to initialize auth:', err)
      }
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    isTeacher,
    isStudent,
    login,
    logout,
    fetchUser,
    clearError,
    getApi,
    initializeAuth
  }
})
