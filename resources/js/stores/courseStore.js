import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useCourseStore = defineStore('course', {
  state: () => ({
    courses: [],
    aircrafts: [],
    categories: [],
    group2learnings: [],
    aukstructures: [],
    userCourses: [],
    loading: false,
    error: null,
    importStatus: {
      loading: false,
      result: null,
      error: null
    }
  }),

  getters: {
    getCourses: (state) => state.courses,
    
    getCourseById: (state) => (id) => {
      return state.courses.find(course => course.id === id)
    },
    
    getCourseByHash: (state) => (hash) => {
      return state.courses.find(course => course.hash === hash)
    },

    getActiveCourses: (state) => {
      return state.courses.filter(course => course.is_active === true)
    },

    getCategoriesByAircraft: (state) => (aircraftId) => {
      return state.categories.filter(cat => cat.aircraft_id === aircraftId)
    },

    aircraftsMap: (state) => {
      const map = {};
      state.aircrafts.forEach(a => {
        map[a.id] = a;
      });
      return map;
    },

    categoriesMap: (state) => {
      const map = {};
      state.categories.forEach(c => {
        map[c.id] = c;
      });
      return map;
    }
  },

  actions: {
    async fetchCourses() {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        console.log('Fetching courses... Token present:', !!authStore.token);
        console.log('Auth headers:', api.defaults.headers.common);
        
        // Try different possible API endpoints for courses
        let response;
        let errorMessages = [];
        
        try {
          console.log('Attempting to fetch courses from /courses');
          response = await api.get('/courses');
          console.log('Successfully fetched from /courses');
        } catch (err) {
          console.error('Error fetching from /courses:', err.message);
          errorMessages.push(`/courses: ${err.message}`);
          
          // Try alternative route
          try {
            console.log('Attempting to fetch courses from /course/list');
            response = await api.get('/course/list');
            console.log('Successfully fetched from /course/list');
          } catch (err2) {
            console.error('Error fetching from /course/list:', err2.message);
            errorMessages.push(`/course/list: ${err2.message}`);
            
            // Try another alternative route
            try {
              console.log('Attempting to fetch courses from /api/courses');
              response = await axios.get('/api/courses', {
                headers: api.defaults.headers.common
              });
              console.log('Successfully fetched from /api/courses');
            } catch (err3) {
              console.error('Error fetching from /api/courses:', err3.message);
              errorMessages.push(`/api/courses: ${err3.message}`);
              throw new Error(`Failed to fetch courses from multiple endpoints: ${errorMessages.join(', ')}`);
            }
          }
        }
        
        console.log('Full API response:', response);
        
        // Detailed analysis of response structure
        if (response.data) {
          console.log('Response data structure:', Object.keys(response.data));
          
          if (Array.isArray(response.data)) {
            console.log('Response is direct array with', response.data.length, 'items');
            this.courses = response.data;
          } else if (response.data.data && Array.isArray(response.data.data)) {
            console.log('Response has nested data array with', response.data.data.length, 'items');
            this.courses = response.data.data;
          } else if (response.data.current_page && response.data.data && Array.isArray(response.data.data)) {
            // Handle Laravel paginated response
            console.log('Response is paginated with', response.data.data.length, 'items');
            this.courses = response.data.data;
          } else if (typeof response.data === 'object') {
            // Try to find array in any object field
            console.log('Searching for array data in response object...');
            let foundArray = false;
            
            for (const [key, value] of Object.entries(response.data)) {
              if (Array.isArray(value)) {
                console.log(`Found array in field "${key}" with ${value.length} items`);
                this.courses = value;
                foundArray = true;
                break;
              }
            }
            
            if (!foundArray) {
              console.error('Could not find array data in response:', response.data);
              this.courses = [];
            }
          } else {
            console.error('Invalid courses data format:', response.data);
            this.courses = [];
          }
        } else {
          console.error('No data in response');
          this.courses = [];
        }
        
        console.log('Processed courses:', this.courses);
        return this.courses;
      } catch (error) {
        console.error('Error fetching courses:', error);
        console.error('Error details:', error.response || error.message);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createCourse(courseData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.post('/courses', courseData);
        // Add the new course to the state
        if (response.data) {
          this.courses.push(response.data);
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateCourse(id, courseData) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.put(`/courses/${id}`, courseData);
        const index = this.courses.findIndex(course => course.id === id);
        if (index !== -1) {
          this.courses[index] = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteCourse(id) {
      this.loading = true;
      this.error = null;
      
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        await api.delete(`/courses/${id}`);
        this.courses = this.courses.filter(course => course.id !== id);
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    clearError() {
      this.error = null;
    },

    async fetchAircrafts() {
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.get('/classes');
        if (response.data) {
          this.aircrafts = Array.isArray(response.data) ? response.data : response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching aircrafts:', error);
      }
    },

    async fetchCategories() {
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.get('/categories');
        if (response.data) {
          this.categories = Array.isArray(response.data) ? response.data : response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    },

    async fetchGroup2learnings(params = {}) {
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.get('/learning', { params });
        if (response.data) {
          this.group2learnings = Array.isArray(response.data) ? response.data : response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching group2learnings:', error);
      }
    },

    async fetchUserCourses() {
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.get('/my-courses');
        if (response.data) {
          this.userCourses = Array.isArray(response.data) ? response.data : response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching user courses:', error);
      }
    },

    async importCourse(data) {
      this.importStatus = { loading: true, result: null, error: null };
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.post('/import/run', data);
        this.importStatus = { 
          loading: false, 
          result: response.data, 
          error: response.data.success ? null : response.data.message 
        };
        return response.data;
      } catch (error) {
        const errorMsg = error.response?.data?.message || error.message;
        this.importStatus = { 
          loading: false, 
          result: null, 
          error: errorMsg 
        };
        throw error;
      }
    },

    async getImportAircrafts() {
      const authStore = useAuthStore();
      const api = authStore.getApi();
      
      try {
        const response = await api.get('/import/aircrafts');
        return response.data || [];
      } catch (error) {
        console.error('Error fetching import aircrafts:', error);
        return [];
      }
    }
  }
});