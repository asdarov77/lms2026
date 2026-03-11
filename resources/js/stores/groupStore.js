import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useGroupStore = defineStore('group', {
  state: () => ({
    groups: [],
    loading: false,
    error: null
  }),

  getters: {
    getGroups: (state) => state.groups,
    
    getGroupById: (state) => (id) => {
      return state.groups.find(group => group.id === id)
    },

    getActiveGroups: (state) => {
      return state.groups.filter(group => group.is_active === true)
    },

    getGroupsByStatus: (state) => (status) => {
      return state.groups.filter(group => group.is_active === status)
    }
  },

  actions: {
    async fetchGroups() {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        console.log('Fetching groups... Token present:', !!authStore.token);
        
        // Добавляем вывод заголовков для диагностики авторизации
        console.log('Auth headers:', api.defaults.headers.common);
        
        // Пробуем разные возможные пути API для групп
        let response;
        let errorMessages = [];
        
        try {
          console.log('Attempting to fetch groups from /groups');
          response = await api.get('/groups');
          console.log('Successfully fetched from /groups');
        } catch (err) {
          console.error('Error fetching from /groups:', err.message);
          errorMessages.push(`/groups: ${err.message}`);
          
          // Пробуем альтернативный маршрут
          try {
            console.log('Attempting to fetch groups from /group/list');
            response = await api.get('/group/list');
            console.log('Successfully fetched from /group/list');
          } catch (err2) {
            console.error('Error fetching from /group/list:', err2.message);
            errorMessages.push(`/group/list: ${err2.message}`);
            
            // Пробуем еще один альтернативный маршрут
            try {
              console.log('Attempting to fetch groups from /api/groups');
              response = await axios.get('/api/groups', {
                headers: api.defaults.headers.common
              });
              console.log('Successfully fetched from /api/groups');
            } catch (err3) {
              console.error('Error fetching from /api/groups:', err3.message);
              errorMessages.push(`/api/groups: ${err3.message}`);
              throw new Error(`Failed to fetch groups from multiple endpoints: ${errorMessages.join(', ')}`);
            }
          }
        }
        
        console.log('Full API response:', response);
        
        // Более подробный анализ структуры ответа
        if (response.data) {
          console.log('Response data structure:', Object.keys(response.data));
          
          if (Array.isArray(response.data)) {
            console.log('Response is direct array with', response.data.length, 'items');
            this.groups = response.data;
          } else if (response.data.data && Array.isArray(response.data.data)) {
            console.log('Response has nested data array with', response.data.data.length, 'items');
            this.groups = response.data.data;
          } else if (response.data.current_page && response.data.data && Array.isArray(response.data.data)) {
            // Обработка пагинированного ответа Laravel
            console.log('Response is paginated with', response.data.data.length, 'items');
            this.groups = response.data.data;
          } else if (typeof response.data === 'object') {
            // Пытаемся найти массив в любом поле объекта
            console.log('Searching for array data in response object...');
            let foundArray = false;
            
            for (const [key, value] of Object.entries(response.data)) {
              if (Array.isArray(value)) {
                console.log(`Found array in field "${key}" with ${value.length} items`);
                this.groups = value;
                foundArray = true;
                break;
              }
            }
            
            if (!foundArray) {
              console.error('Could not find array data in response:', response.data);
              this.groups = [];
            }
          } else {
            console.error('Invalid groups data format:', response.data);
            this.groups = [];
          }
        } else {
          console.error('No data in response');
          this.groups = [];
        }
        
        console.log('Processed groups:', this.groups);
        return this.groups;
      } catch (error) {
        console.error('Error fetching groups:', error);
        console.error('Error details:', error.response || error.message);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createGroup(groupData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        // Ensure the data format matches exactly what the Laravel backend expects
        const sanitizedData = {
          groupname: groupData.groupname,
          groupdescription: groupData.groupdescription,
          is_active: groupData.is_active === undefined ? true : Boolean(groupData.is_active),
          max_users: groupData.max_users ? parseInt(groupData.max_users) : null,
          settings: groupData.settings || {}
        };
        
        console.log('Creating group with sanitized data:', JSON.stringify(sanitizedData));
        
        // Ensure authorization headers are properly set
        if (!api.defaults.headers.common['Authorization'] && authStore.token) {
          console.log('Adding missing Authorization header');
          api.defaults.headers.common['Authorization'] = `Bearer ${authStore.token}`;
        }
        
        console.log('Request headers:', api.defaults.headers.common);
        
        // Use a more direct approach to ensure proper request handling
        const response = await api.post('/groups', sanitizedData);
        console.log('Create group API response:', response);
        
        // Add the new group to the state
        if (response.data) {
          // Make a deep copy to avoid reactivity issues
          this.groups.push(JSON.parse(JSON.stringify(response.data)));
          console.log('Group added to store:', response.data);
        }
        
        return response.data;
      } catch (error) {
        console.error('Error creating group:', error);
        if (error.response) {
          console.error('Server response:', error.response.status, error.response.data);
          this.error = error.response.data?.message || 'Server error: ' + error.response.status;
        } else {
          console.error('Request error:', error.message);
          this.error = error.message;
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateGroup(id, groupData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        // Sanitize data to prevent circular references
        const sanitizedData = {
          groupname: groupData.groupname,
          groupdescription: groupData.groupdescription,
          is_active: groupData.is_active === undefined ? true : !!groupData.is_active,
          max_users: groupData.max_users || null,
          settings: groupData.settings || {}
        };
        
        console.log('Updating group with sanitized data:', sanitizedData);
        const response = await api.put(`/groups/${id}`, sanitizedData);
        
        const index = this.groups.findIndex(group => group.id === id);
        if (index !== -1) {
          this.groups[index] = response.data;
        }
        return response.data;
      } catch (error) {
        console.error('Error updating group:', error);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteGroup(id) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        await api.delete(`/groups/${id}`);
        this.groups = this.groups.filter(group => group.id !== id);
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async addUsersToGroup(groupId, userIds) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        await api.post(`/groups/${groupId}/add-users`, { user_ids: userIds });
        // Refresh group data after adding users
        await this.fetchGroups();
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeUsersFromGroup(groupId, userIds) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        await api.delete(`/groups/${groupId}/remove-users`, { data: { user_ids: userIds } });
        // Refresh group data after removing users
        await this.fetchGroups();
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    clearError() {
      this.error = null;
    }
  }
}); 