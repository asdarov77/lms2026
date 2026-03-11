import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    roles: [],
    groups: [],
    loading: false,
    error: null,
  }),

  getters: {
    getUsers: (state) => state.users,
    getRoles: (state) => state.roles,
    getGroups: (state) => state.groups,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
  },

  actions: {
    async fetchUsers() {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        console.log('Fetching users... Token present:', !!authStore.token);
        console.log('Auth headers:', api.defaults.headers.common);
        
        const response = await api.post('/user/list');
        console.log('Full users API response:', response);
        
        if (response.data) {
          if (Array.isArray(response.data)) {
            this.users = response.data;
          } else if (response.data.data && Array.isArray(response.data.data)) {
            this.users = response.data.data;
          } else {
            console.error('Invalid users data format:', response.data);
            this.users = [];
          }
        } else {
          console.error('No data in users response');
          this.users = [];
        }
        
        console.log('Processed users:', this.users);
        return this.users;
      } catch (error) {
        console.error('Error fetching users:', error);
        console.error('Users error details:', error.response || error.message);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchRoles() {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.get('/roles');
        this.roles = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchGroups() {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        console.log('Fetching groups for user store...');
        const response = await api.get('/groups');
        console.log('Groups response in user store:', response.data);
        
        if (response.data && Array.isArray(response.data)) {
          this.groups = response.data;
        } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
          this.groups = response.data.data;
        } else {
          console.error('Invalid groups data format:', response.data);
          this.groups = [];
        }
        
        return this.groups;
      } catch (error) {
        console.error('Error fetching groups in user store:', error);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async saveUser(userData) {
      if (userData.id) {
        return this.updateUser(userData.id, userData);
      } else {
        return this.createUser(userData);
      }
    },

    async createUser(userData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        // Создаем плоскую копию данных пользователя без вложенных объектов
        const simplifiedUserData = {
          fio: userData.fio,
          email: userData.email,
          password: userData.password,
          password_confirmation: userData.password_confirmation,
          role: userData.role,
          group_id: userData.group_id,
          phonenumber: userData.phonenumber,
          organization: userData.organization,
          position: userData.position
        };
        
        // Проверяем, что group_id - это число, а не объект
        if (typeof simplifiedUserData.group_id === 'object' && simplifiedUserData.group_id !== null) {
          console.log('group_id является объектом, извлекаем ID:', simplifiedUserData.group_id);
          simplifiedUserData.group_id = simplifiedUserData.group_id.id;
        }
        
        console.log('Sending user data:', simplifiedUserData);
        const response = await api.post('/register', simplifiedUserData);
        
        // Refresh user list after adding a user
        await this.fetchUsers();
        return response.data;
      } catch (error) {
        console.error('Error creating user:', error);
        console.error('Server response:', error.response?.data);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateUser(userId, userData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      console.log('updateUser вызван с ID:', userId, 'и данными:', userData);
      
      try {
        // Проверяем валидность входных данных
        if (!userId) {
          throw new Error('ID пользователя не указан');
        }
        
        if (!userData || Object.keys(userData).length === 0) {
          throw new Error('Данные пользователя пусты');
        }
        
        // Создаем плоскую копию данных пользователя без вложенных объектов
        const simplifiedUserData = {
          fio: userData.fio,
          email: userData.email,
          role: userData.role,
          group_id: userData.group_id,
          phonenumber: userData.phonenumber,
          organization: userData.organization,
          position: userData.position
        };
        
        // Отладочное логирование перед отправкой
        console.log('ID пользователя для обновления:', userId);
        console.log('Отправляемые данные для обновления:', simplifiedUserData);
        
        // Дополнительно логируем исходные данные формы
        console.log('Исходные данные формы:', userData);
        
        // Проверяем, что group_id - это число, а не объект
        if (typeof simplifiedUserData.group_id === 'object' && simplifiedUserData.group_id !== null) {
          console.log('group_id является объектом, извлекаем ID:', simplifiedUserData.group_id);
          simplifiedUserData.group_id = simplifiedUserData.group_id.id;
        }
        
        // Отправляем запрос к API
        const response = await api.patch(`/user/${userId}`, simplifiedUserData);
        console.log('Ответ сервера при обновлении пользователя:', response.data);
        
        // Обновляем пользователя в локальном хранилище
        const index = this.users.findIndex(user => user.id === userId);
        if (index !== -1) {
          // Сохраняем предыдущие данные, которые могут отсутствовать в ответе API
          const previousUserData = this.users[index];
          console.log('Предыдущие данные пользователя:', previousUserData);
          
          // Создаем новый объект пользователя, объединяя старые данные и обновления
          const updatedUser = {
            ...previousUserData,
            ...response.data,
            // Явно сохраняем эти поля из формы, если они отсутствуют в ответе
            fio: response.data.fio || userData.fio || previousUserData.fio,
            email: response.data.email || userData.email || previousUserData.email,
            role: response.data.role || userData.role || previousUserData.role,
            phonenumber: response.data.phonenumber || userData.phonenumber || previousUserData.phonenumber,
            organization: response.data.organization || userData.organization || previousUserData.organization,
            position: response.data.position || userData.position || previousUserData.position,
            group_id: response.data.group_id || userData.group_id || previousUserData.group_id
          };
          
          console.log('Обновленные данные пользователя:', updatedUser);
          this.users[index] = updatedUser;
        } else {
          console.warn(`Пользователь с ID ${userId} не найден в локальном хранилище, добавляем новую запись`);
          // Если пользователя нет в хранилище, добавляем его
          this.users.push(response.data);
        }
        
        return response.data;
      } catch (error) {
        console.error('Ошибка при обновлении пользователя:', error);
        console.error('Детали ошибки:', error.response?.data || error.message);
        this.error = error.response?.data?.message || error.message;
        
        // Дополнительно логируем подробности HTTP-ошибки
        if (error.response) {
          console.error('HTTP статус:', error.response.status);
          console.error('HTTP заголовки:', error.response.headers);
          console.error('HTTP данные:', error.response.data);
        }
        
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteUser(userId) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        await api.delete(`/user/${userId}`);
        this.users = this.users.filter(user => user.id !== userId);
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async changePassword(userId, passwordData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        await api.put(`/user/chpass/${userId}`, passwordData);
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async getUser(userId) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        console.log('Fetching single user by ID:', userId);
        const response = await api.get(`/user/list/${userId}`);
        console.log('User data retrieved:', response.data);
        
        return response.data;
      } catch (error) {
        console.error('Error fetching user:', error);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    clearError() {
      this.error = null;
    },
  },
}); 