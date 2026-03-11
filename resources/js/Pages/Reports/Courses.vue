<template>
  <AppLayout title="Отчеты по курсам">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h4 py-4">Отчеты по курсам</v-card-title>
            
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
                    label="Поиск курсов"
                    prepend-icon="mdi-magnify"
                    clearable
                    hide-details
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="3">
                  <v-select
                    v-model="filters.status"
                    :items="[
                      { title: 'Все курсы', value: 'all' },
                      { title: 'Активные', value: 'active' },
                      { title: 'Неактивные', value: 'inactive' },
                      { title: 'Архивные', value: 'archived' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Статус"
                    hide-details
                  ></v-select>
                </v-col>
                
                <v-col cols="12" md="3">
                  <v-select
                    v-model="filters.sortBy"
                    :items="[
                      { title: 'По популярности', value: 'popularity' },
                      { title: 'По названию', value: 'title' },
                      { title: 'По проценту завершения', value: 'completion' },
                      { title: 'По среднему баллу', value: 'grade' }
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
              
              <v-table v-else-if="filteredCourses.length > 0">
                <thead>
                  <tr>
                    <th class="text-left">Название курса</th>
                    <th class="text-left">Преподаватель</th>
                    <th class="text-center">Студентов</th>
                    <th class="text-center">Завершаемость</th>
                    <th class="text-center">Средний балл</th>
                    <th class="text-center">Статус</th>
                    <th class="text-center">Действия</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="course in filteredCourses" :key="course.id">
                    <td>{{ course.title }}</td>
                    <td>{{ course.instructor }}</td>
                    <td class="text-center">{{ course.students }}</td>
                    <td class="text-center">
                      <v-progress-linear
                        :model-value="course.completionRate"
                        :color="course.completionRate < 50 ? 'error' : 
                                course.completionRate < 75 ? 'warning' : 'success'"
                        height="20"
                        rounded
                      >
                        <template v-slot:default>
                          {{ course.completionRate }}%
                        </template>
                      </v-progress-linear>
                    </td>
                    <td class="text-center">
                      <v-rating
                        :model-value="course.averageGrade"
                        length="5"
                        size="small"
                        readonly
                        half-increments
                        color="amber"
                      ></v-rating>
                      <div class="text-caption">{{ course.averageGrade }} / 5</div>
                    </td>
                    <td class="text-center">
                      <v-chip
                        :color="getCourseStatusChip(course.status).color"
                        size="small"
                      >
                        {{ getCourseStatusChip(course.status).text }}
                      </v-chip>
                    </td>
                    <td class="text-center">
                      <v-btn
                        icon
                        size="small"
                        variant="text"
                        color="primary"
                        @click="router.push(`/courses/${course.id}/details`)"
                      >
                        <v-icon>mdi-chart-bar</v-icon>
                      </v-btn>
                      <v-btn
                        v-if="isAdmin"
                        icon
                        size="small"
                        variant="text"
                        color="primary"
                        @click="router.push(`/courses/${course.id}/edit`)"
                      >
                        <v-icon>mdi-pencil</v-icon>
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
                Курсы не найдены. Попробуйте изменить параметры поиска.
              </v-alert>
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
const courses = ref([]);
const search = ref('');
const filters = ref({
  status: 'all',
  sortBy: 'popularity'
});

// Fetch courses data
const fetchCourses = async () => {
  loading.value = true;
  try {
    // Placeholder for API call
    // const response = await fetch('/api/reports/courses');
    // courses.value = await response.json();
    
    // Sample data
    await new Promise(resolve => setTimeout(resolve, 800));
    courses.value = [
      {
        id: 1,
        title: 'Основы программирования',
        instructor: 'Иванов И.И.',
        students: 45,
        completionRate: 78,
        averageGrade: 4.2,
        status: 'active'
      },
      {
        id: 2,
        title: 'Базы данных',
        instructor: 'Петров П.П.',
        students: 32,
        completionRate: 65,
        averageGrade: 3.8,
        status: 'active'
      },
      {
        id: 3,
        title: 'Веб-разработка',
        instructor: 'Сидорова А.А.',
        students: 56,
        completionRate: 82,
        averageGrade: 4.5,
        status: 'active'
      },
      {
        id: 4,
        title: 'Искусственный интеллект',
        instructor: 'Кузнецов Д.С.',
        students: 28,
        completionRate: 45,
        averageGrade: 3.9,
        status: 'inactive'
      },
      {
        id: 5,
        title: 'Машинное обучение',
        instructor: 'Соколова Е.В.',
        students: 37,
        completionRate: 55,
        averageGrade: 4.1,
        status: 'active'
      }
    ];
  } catch (err) {
    console.error('Error fetching courses data:', err);
    error.value = 'Ошибка при загрузке данных о курсах';
  } finally {
    loading.value = false;
  }
};

// Filter and sort courses
const filteredCourses = computed(() => {
  let result = [...courses.value];
  
  // Apply search filter
  if (search.value) {
    const searchTerm = search.value.toLowerCase();
    result = result.filter(course => 
      course.title.toLowerCase().includes(searchTerm) ||
      course.instructor.toLowerCase().includes(searchTerm)
    );
  }
  
  // Apply status filter
  if (filters.value.status !== 'all') {
    result = result.filter(course => course.status === filters.value.status);
  }
  
  // Apply sorting
  switch (filters.value.sortBy) {
    case 'title':
      result.sort((a, b) => a.title.localeCompare(b.title));
      break;
    case 'completion':
      result.sort((a, b) => b.completionRate - a.completionRate);
      break;
    case 'grade':
      result.sort((a, b) => b.averageGrade - a.averageGrade);
      break;
    case 'popularity':
    default:
      result.sort((a, b) => b.students - a.students);
      break;
  }
  
  return result;
});

// Generate a course status chip
const getCourseStatusChip = (status) => {
  switch (status) {
    case 'active':
      return { color: 'success', text: 'Активный' };
    case 'inactive':
      return { color: 'grey', text: 'Неактивный' };
    case 'archived':
      return { color: 'error', text: 'Архивный' };
    default:
      return { color: 'primary', text: status };
  }
};

onMounted(() => {
  fetchCourses();
});
</script>

<style scoped>
.text-h4 {
  font-weight: 700;
}
</style> 