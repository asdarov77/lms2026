<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/auth';

// Router and auth setup
const router = useRouter();
const authStore = useAuthStore();
const user = ref(authStore.user);

// Data
const myAssignments = ref([]);
const loading = ref(true);
const error = ref(null);

// Fetch user's assignments
const fetchMyAssignments = async () => {
  loading.value = true;
  try {
    // Placeholder - replace with actual API call
    // const response = await fetch(`/api/users/${user.value.id}/assignments`);
    // myAssignments.value = await response.json();
    
    // Sample data
    myAssignments.value = [
      { 
        id: 1, 
        title: 'Задание 1', 
        course: 'Основы программирования',
        dueDate: '2025-05-15', 
        status: 'Назначено',
        progress: 0
      },
      { 
        id: 2, 
        title: 'Задание 2', 
        course: 'Базы данных',
        dueDate: '2025-05-20', 
        status: 'В процессе',
        progress: 30
      },
      { 
        id: 3, 
        title: 'Задание 3', 
        course: 'Веб-разработка',
        dueDate: '2025-05-25', 
        status: 'Выполнено',
        progress: 100
      },
    ];
  } catch (err) {
    console.error('Error fetching assignments:', err);
    error.value = 'Ошибка при загрузке заданий.';
  } finally {
    loading.value = false;
  }
};

// Calculate days remaining until due date
const getDaysRemaining = (dueDate) => {
  const today = new Date();
  const due = new Date(dueDate);
  const diffTime = due - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};

// Lifecycle hook
onMounted(() => {
  fetchMyAssignments();
});
</script>

<template>
  <AppLayout title="Мои задания">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h4 py-4">Мои задания</v-card-title>
            
            <v-card-text>
              <v-alert
                v-if="error"
                type="error"
                class="mb-4"
                closable
              >
                {{ error }}
              </v-alert>
              
              <div v-if="loading" class="d-flex justify-center my-4">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>
              
              <v-alert
                v-else-if="myAssignments.length === 0"
                type="info"
                class="mb-4"
              >
                У вас нет назначенных заданий.
              </v-alert>
              
              <v-row v-else>
                <v-col v-for="assignment in myAssignments" :key="assignment.id" cols="12" md="6" lg="4">
                  <v-card elevation="2" class="h-100">
                    <v-card-title class="d-flex justify-space-between">
                      <span>{{ assignment.title }}</span>
                      <v-chip
                        :color="assignment.status === 'Выполнено' ? 'success' : 
                                assignment.status === 'В процессе' ? 'primary' : 
                                assignment.status === 'Просрочено' ? 'error' : 'warning'"
                        class="ml-2"
                      >
                        {{ assignment.status }}
                      </v-chip>
                    </v-card-title>
                    
                    <v-card-subtitle>{{ assignment.course }}</v-card-subtitle>
                    
                    <v-card-text>
                      <div class="d-flex align-center justify-space-between mb-2">
                        <span class="text-caption text-grey">Прогресс:</span>
                        <span class="text-caption">{{ assignment.progress }}%</span>
                      </div>
                      
                      <v-progress-linear
                        :model-value="assignment.progress"
                        :color="assignment.progress < 30 ? 'error' : 
                                assignment.progress < 70 ? 'warning' : 'success'"
                        height="8"
                        rounded
                      ></v-progress-linear>
                      
                      <v-divider class="my-4"></v-divider>
                      
                      <div class="d-flex justify-space-between align-center mt-3">
                        <div>
                          <div class="text-caption">Срок сдачи:</div>
                          <div>{{ new Date(assignment.dueDate).toLocaleDateString() }}</div>
                        </div>
                        
                        <v-chip
                          v-if="assignment.status !== 'Выполнено'"
                          :color="getDaysRemaining(assignment.dueDate) < 3 ? 'error' : 
                                  getDaysRemaining(assignment.dueDate) < 7 ? 'warning' : 'success'"
                          size="small"
                        >
                          {{ getDaysRemaining(assignment.dueDate) }} дней осталось
                        </v-chip>
                      </div>
                    </v-card-text>
                    
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="primary"
                        variant="text"
                        @click="router.push(`/assignments/${assignment.id}`)"
                      >
                        Подробнее
                      </v-btn>
                      <v-btn
                        v-if="assignment.status !== 'Выполнено'"
                        color="primary"
                        @click="router.push(`/assignments/${assignment.id}/submit`)"
                      >
                        Сдать задание
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

<style scoped>
.v-card-title {
  font-weight: 700;
}
</style> 