<template>
  <AppLayout title="Отчеты по студентам">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h4 py-4">Отчеты по студентам</v-card-title>
            
            <v-card-text>
              <v-alert
                v-if="error"
                type="error"
                class="mb-4"
                closable
              >
                {{ error }}
              </v-alert>
              
      <!-- Filters -->
              <v-row class="mb-4">
                <v-col cols="12" md="6">
          <v-text-field
            v-model="search"
            label="Поиск студентов"
            prepend-icon="mdi-magnify"
            clearable
                    hide-details
          ></v-text-field>
        </v-col>
                
                <v-col cols="12" md="3">
          <v-select
                    v-model="filters.performanceLevel"
                    :items="[
                      { title: 'Все уровни', value: 'all' },
                      { title: 'Высокий', value: 'high' },
                      { title: 'Средний', value: 'medium' },
                      { title: 'Низкий', value: 'low' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Уровень успеваемости"
                    hide-details
          ></v-select>
        </v-col>
                
                <v-col cols="12" md="3">
          <v-select
                    v-model="filters.sortBy"
                    :items="[
                      { title: 'По успеваемости', value: 'performance' },
                      { title: 'По имени', value: 'name' },
                      { title: 'По оценке', value: 'grade' },
                      { title: 'По активности', value: 'activity' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Сортировка"
                    hide-details
          ></v-select>
        </v-col>
      </v-row>

              <div v-if="loading" class="d-flex justify-center my-5">
                <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
              </div>
              
              <v-table v-else-if="filteredStudents.length > 0">
                <thead>
                  <tr>
                    <th class="text-left">Студент</th>
                    <th class="text-left">Группа</th>
                    <th class="text-center">Курсы</th>
                    <th class="text-center">Ср. оценка</th>
                    <th class="text-center">Прогресс</th>
                    <th class="text-center">Уровень</th>
                    <th class="text-center">Последняя активность</th>
                    <th class="text-center">Действия</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in filteredStudents" :key="student.id">
                    <td>
                      <div class="d-flex align-center">
                        <v-avatar size="32" color="primary" class="mr-2">
                          <span class="text-white">{{ student.name.split(' ').map(n => n[0]).join('') }}</span>
                        </v-avatar>
                        <div>
                          <div>{{ student.name }}</div>
                          <div class="text-caption text-grey">{{ student.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td>{{ student.group }}</td>
                    <td class="text-center">{{ student.completedCourses }}/{{ student.courses }}</td>
                    <td class="text-center">
                      <v-rating
                        :model-value="student.averageGrade"
                        length="5"
                        size="small"
                        readonly
                        half-increments
                        color="amber"
                      ></v-rating>
                      <div class="text-caption">{{ student.averageGrade }} / 5</div>
                    </td>
                    <td class="text-center">
            <v-progress-linear
                        :model-value="student.totalProgress"
                        :color="student.totalProgress < 50 ? 'error' : 
                                student.totalProgress < 80 ? 'warning' : 'success'"
              height="20"
                        rounded
            >
                        <template v-slot:default>
                          {{ student.totalProgress }}%
              </template>
            </v-progress-linear>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getPerformanceLevel(student.totalProgress).color"
                        size="small"
                      >
                        {{ getPerformanceLevel(student.totalProgress).text }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <div>{{ formatDate(student.lastActivity) }}</div>
                      <div class="text-caption">
                        {{ getDaysSinceLastActivity(student.lastActivity) }} дней назад
                      </div>
                    </td>
                    <td class="text-center">
            <v-btn
              icon
                        size="small"
                        variant="text"
                        color="primary"
                        @click="router.push(`/students/${student.id}/progress`)"
                      >
                        <v-icon>mdi-chart-line</v-icon>
                      </v-btn>
                      <v-btn
                        v-if="isAdmin"
                        icon
                        size="small"
                        variant="text"
                        color="primary"
                        @click="router.push(`/students/${student.id}`)"
                      >
                        <v-icon>mdi-account</v-icon>
            </v-btn>
                    </td>
                  </tr>
                </tbody>
              </v-table>
              
              <v-alert
                v-else-if="!loading"
                type="info"
                class="mt-4"
              >
                Студенты не найдены. Попробуйте изменить параметры поиска.
              </v-alert>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
    </v-container>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const isAdmin = ref(authStore.isAdmin);
const loading = ref(true);
const error = ref(null);
const students = ref([]);
const search = ref('');
const filters = ref({
  performanceLevel: 'all',
  sortBy: 'performance'
});

// Fetch students data
const fetchStudents = async () => {
  loading.value = true;
  try {
    // Placeholder for API call
    // const response = await fetch('/api/reports/students');
    // students.value = await response.json();
    
    // Sample data
    await new Promise(resolve => setTimeout(resolve, 800));
    students.value = [
      {
        id: 1,
        name: 'Иванов Иван',
        email: 'ivanov@example.com',
        group: 'Группа A',
        courses: 3,
        completedCourses: 2,
        averageGrade: 4.8,
        totalProgress: 92,
        lastActivity: '2025-04-19'
      },
      {
        id: 2,
        name: 'Петров Петр',
        email: 'petrov@example.com',
        group: 'Группа B',
        courses: 4,
        completedCourses: 1,
        averageGrade: 3.5,
        totalProgress: 45,
        lastActivity: '2025-04-15'
      },
      {
        id: 3,
        name: 'Сидорова Анна',
        email: 'sidorova@example.com',
        group: 'Группа A',
        courses: 5,
        completedCourses: 4,
        averageGrade: 4.9,
        totalProgress: 95,
        lastActivity: '2025-04-20'
      },
      {
        id: 4,
        name: 'Кузнецов Дмитрий',
        email: 'kuznetsov@example.com',
        group: 'Группа C',
        courses: 2,
        completedCourses: 0,
        averageGrade: 2.8,
        totalProgress: 25,
        lastActivity: '2025-04-10'
      },
      {
        id: 5,
        name: 'Соколова Елена',
        email: 'sokolova@example.com',
        group: 'Группа B',
        courses: 3,
        completedCourses: 2,
        averageGrade: 4.2,
        totalProgress: 78,
        lastActivity: '2025-04-18'
      }
    ];
  } catch (err) {
    console.error('Error fetching students data:', err);
    error.value = 'Ошибка при загрузке данных о студентах';
  } finally {
    loading.value = false;
  }
};

// Filter and sort students
const filteredStudents = computed(() => {
  let result = [...students.value];

      // Apply search filter
  if (search.value) {
    const searchTerm = search.value.toLowerCase();
    result = result.filter(student => 
      student.name.toLowerCase().includes(searchTerm) ||
      student.email.toLowerCase().includes(searchTerm) ||
      student.group.toLowerCase().includes(searchTerm)
    );
  }
  
  // Apply performance level filter
  if (filters.value.performanceLevel !== 'all') {
    switch (filters.value.performanceLevel) {
      case 'high':
        result = result.filter(student => student.totalProgress >= 80);
        break;
      case 'medium':
        result = result.filter(student => student.totalProgress >= 50 && student.totalProgress < 80);
        break;
      case 'low':
        result = result.filter(student => student.totalProgress < 50);
        break;
    }
  }
  
  // Apply sorting
  switch (filters.value.sortBy) {
    case 'name':
      result.sort((a, b) => a.name.localeCompare(b.name));
      break;
    case 'grade':
      result.sort((a, b) => b.averageGrade - a.averageGrade);
      break;
    case 'activity':
      result.sort((a, b) => new Date(b.lastActivity) - new Date(a.lastActivity));
      break;
    case 'performance':
    default:
      result.sort((a, b) => b.totalProgress - a.totalProgress);
      break;
  }
  
  return result;
});

// Calculate performance level
const getPerformanceLevel = (progress) => {
  if (progress >= 80) return { color: 'success', text: 'Высокий' };
  if (progress >= 50) return { color: 'warning', text: 'Средний' };
  return { color: 'error', text: 'Низкий' };
};

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
    day: 'numeric'
  });
};

// Calculate days since last activity
const getDaysSinceLastActivity = (dateString) => {
  const lastActivity = new Date(dateString);
  const today = new Date();
  const diffTime = Math.abs(today - lastActivity);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

onMounted(() => {
  fetchStudents();
});
</script>

<style scoped>
.text-h4 {
  font-weight: 700;
}
</style> 