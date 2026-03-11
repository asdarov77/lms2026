import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from './auth';

export const useEnrollmentStore = defineStore('enrollment', () => {
  const enrollments = ref([]);
  const loading = ref(false);
  const error = ref(null);
  
  // Get enrollments from API
  async function fetchEnrollments() {
    loading.value = true;
    error.value = null;
    
    const authStore = useAuthStore();
    const api = authStore.getApi();
    
    try {
      const response = await api.get('/learning');
      enrollments.value = response.data.data || response.data || [];
      return enrollments.value;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Get enrollment by ID
  function getEnrollmentById(id) {
    if (!enrollments.value.length) {
      return null;
    }
    
    const enrollment = enrollments.value.find(item => item.id === parseInt(id));
    return enrollment;
  }
  
  // Create a new enrollment
  async function createEnrollment(enrollmentData) {
    loading.value = true;
    error.value = null;
    
    const authStore = useAuthStore();
    const api = authStore.getApi();
    
    try {
      const response = await api.post('/group/learning', enrollmentData);
      
      // Refresh enrollments list after creating
      await fetchEnrollments();
      
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  // Update an existing enrollment
  async function updateEnrollment(enrollmentData) {
    loading.value = true;
    error.value = null;
    
    const authStore = useAuthStore();
    const api = authStore.getApi();
    
    try {
      // Ensure enrollment exists
      if (!enrollmentData.id) {
        throw new Error('ID записи не указан');
      }
      
      const response = await api.put(`/learning/${enrollmentData.id}`, enrollmentData);
      
      // Update the enrollment in the local store
      const index = enrollments.value.findIndex(item => item.id === enrollmentData.id);
      if (index !== -1) {
        enrollments.value[index] = { ...enrollments.value[index], ...enrollmentData };
      }
      
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  // Delete an enrollment
  async function deleteEnrollment(id) {
    loading.value = true;
    error.value = null;
    
    const authStore = useAuthStore();
    const api = authStore.getApi();
    
    try {
      const response = await api.delete(`/learning/${id}`);
      
      // Refresh enrollments list after deleting
      await fetchEnrollments();
      
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }
  
  // Getters as computed properties
  const getEnrollments = enrollments;
  const isLoading = loading;
  const getError = error;
  
  // Clear error state
  function clearError() {
    error.value = null;
  }
  
  return {
    // State
    enrollments,
    loading,
    error,
    
    // Actions
    fetchEnrollments,
    getEnrollmentById,
    createEnrollment,
    updateEnrollment,
    deleteEnrollment,
    clearError,
    
    // Getters
    getEnrollments,
    isLoading,
    getError
  };
}); 