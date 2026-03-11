import { defineStore } from 'pinia';

// Import all stores
import { useUserStore } from './userStore';
import { useGroupStore } from './groupStore';
import { useAuthStore } from './auth';
import { useCourseStore } from './courseStore';

// Export all stores
export { useUserStore, useGroupStore, useAuthStore, useCourseStore }; 