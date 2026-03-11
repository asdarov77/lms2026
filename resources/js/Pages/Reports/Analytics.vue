<template>
  <AppLayout title="Аналитика">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h4 py-4">Аналитика образовательной платформы</v-card-title>
            
            <v-card-text>
              <v-alert
                v-if="error"
                type="error"
                class="mb-4"
                closable
              >
                {{ error }}
              </v-alert>
              
              <div v-if="loading" class="d-flex justify-center my-5">
                <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
              </div>
              
              <v-row v-else>
                <!-- Users Stats -->
                <v-col cols="12" md="4">
                  <v-card class="mb-4" elevation="2">
                    <v-card-title class="text-h6">Пользователи</v-card-title>
                    <v-card-text>
                      <v-row>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-primary">{{ userData.activeUsers }}</div>
                          <div class="text-caption">Активные</div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-success">{{ userData.newUsers }}</div>
                          <div class="text-caption">Новые</div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold">{{ userData.totalUsers }}</div>
                          <div class="text-caption">Всего</div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                
                <!-- Courses Stats -->
                <v-col cols="12" md="4">
                  <v-card class="mb-4" elevation="2">
                    <v-card-title class="text-h6">Курсы</v-card-title>
                    <v-card-text>
                      <v-row>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-primary">{{ courseData.activeCourses }}</div>
                          <div class="text-caption">Активные</div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-success">{{ courseData.completedCourses }}</div>
                          <div class="text-caption">Завершенные</div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold">{{ courseData.totalCourses }}</div>
                          <div class="text-caption">Всего</div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                
                <!-- Progress Stats -->
                <v-col cols="12" md="4">
                  <v-card class="mb-4" elevation="2">
                    <v-card-title class="text-h6">Прогресс</v-card-title>
                    <v-card-text>
                      <v-row>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-primary">{{ progressData.averageCompletion }}%</div>
                          <div class="text-caption">Ср. прогресс</div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-success">{{ progressData.topPerformers }}</div>
                          <div class="text-caption">Отличники</div>
                        </v-col>
                        <v-col cols="4" class="text-center">
                          <div class="text-h4 font-weight-bold text-error">{{ progressData.needsAttention }}</div>
                          <div class="text-caption">Отстающие</div>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
                
                <!-- Graphs and charts would go here -->
                <v-col cols="12">
                  <v-card class="mb-4" elevation="2">
                    <v-card-title class="text-h6">Активность по времени</v-card-title>
                    <v-card-text style="height: 300px" class="d-flex align-center justify-center">
                      <div class="text-subtitle-1 text-grey">Графики будут доступны в следующей версии</div>
                    </v-card-text>
                  </v-card>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-card class="mb-4" elevation="2">
                    <v-card-title class="text-h6">Популярные курсы</v-card-title>
                    <v-card-text style="height: 250px" class="d-flex align-center justify-center">
                      <div class="text-subtitle-1 text-grey">Графики будут доступны в следующей версии</div>
                    </v-card-text>
                  </v-card>
                </v-col>
                
                <v-col cols="12" md="6">
                  <v-card class="mb-4" elevation="2">
                    <v-card-title class="text-h6">Распределение оценок</v-card-title>
                    <v-card-text style="height: 250px" class="d-flex align-center justify-center">
                      <div class="text-subtitle-1 text-grey">Графики будут доступны в следующей версии</div>
                    </v-card-text>
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

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const isAdmin = ref(authStore.isAdmin);
const loading = ref(true);
const error = ref(null);

// Sample data
const userData = ref({
  activeUsers: 120,
  newUsers: 15,
  totalUsers: 350
});

const courseData = ref({
  activeCourses: 24,
  totalCourses: 45,
  completedCourses: 18
});

const progressData = ref({
  averageCompletion: 68,
  topPerformers: 45,
  needsAttention: 12
});

onMounted(async () => {
  loading.value = true;
  try {
    // Placeholder for API call
    // const response = await fetch('/api/reports/analytics');
    // const data = await response.json();
    // userData.value = data.users;
    // courseData.value = data.courses;
    // progressData.value = data.progress;
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 1000));
  } catch (err) {
    console.error('Error fetching analytics data:', err);
    error.value = 'Ошибка при загрузке аналитических данных';
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.text-h4 {
  font-weight: 700;
}
.text-h6 {
  font-weight: 600;
}
</style> 