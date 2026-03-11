<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content d-flex align-center justify-space-between">
          <div class="d-flex align-center">
            <h1 class="text-h4 font-weight-bold white--text">
              Курсы
            </h1>
          </div>
          <v-btn
            color="white"
            class="text-primary"
            @click="$router.push('/courses/create')"
          >
            <v-icon left>mdi-plus</v-icon>
            Создать курс
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
      <!-- Search and Filters -->
      <v-row class="mb-6">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="Поиск курсов"
            single-line
            hide-details
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="category"
            :items="categories"
            label="Категория"
            clearable
            hide-details
          ></v-select>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="status"
            :items="statuses"
            label="Статус"
            clearable
            hide-details
          ></v-select>
        </v-col>
      </v-row>

      <!-- Courses Grid -->
      <v-row>
        <v-col
          v-for="course in filteredCourses"
          :key="course.id"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <v-card
            class="course-card"
            elevation="4"
            @click="$router.push(`/courses/${course.id}/edit`)"
          >
            <v-img
              :src="course.thumbnail || '/images/default-course.jpg'"
              height="200"
              cover
            >
              <v-chip
                :color="getStatusColor(course.status)"
                class="ma-2"
                small
              >
                {{ course.status }}
              </v-chip>
            </v-img>
            <v-card-title class="text-h6">
              {{ course.title }}
            </v-card-title>
            <v-card-text>
              <div class="text-subtitle-2 mb-2">
                {{ course.category }}
              </div>
              <div class="text-body-2 text-truncate">
                {{ course.description }}
              </div>
              <div class="d-flex align-center mt-4">
                <v-icon small class="mr-1">mdi-account-group</v-icon>
                <span class="text-caption">{{ course.students_count }} студентов</span>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                icon
                color="primary"
                @click.stop="$router.push(`/courses/${course.id}/edit`)"
              >
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Empty State -->
      <v-row v-if="filteredCourses.length === 0">
        <v-col cols="12" class="text-center">
          <v-icon size="64" color="grey">mdi-book-open-variant</v-icon>
          <h2 class="text-h5 mt-4">Курсы не найдены</h2>
          <p class="text-body-1 grey--text">
            Попробуйте изменить параметры поиска или создать новый курс
          </p>
        </v-col>
      </v-row>
    </v-container>
  </v-container>
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'CourseList',
  data() {
    return {
      courses: [],
      search: '',
      category: null,
      status: null,
      categories: ['Программирование', 'Дизайн', 'Маркетинг', 'Бизнес'],
      statuses: ['Активный', 'Черновик', 'Архив'],
      loading: false,
    };
  },
  computed: {
    filteredCourses() {
      return this.courses.filter(course => {
        const matchesSearch = !this.search || 
          course.title.toLowerCase().includes(this.search.toLowerCase()) ||
          course.description.toLowerCase().includes(this.search.toLowerCase());
        const matchesCategory = !this.category || course.category === this.category;
        const matchesStatus = !this.status || course.status === this.status;
        return matchesSearch && matchesCategory && matchesStatus;
      });
    },
  },
  methods: {
    getStatusColor(status) {
      const colors = {
        'Активный': 'success',
        'Черновик': 'warning',
        'Архив': 'grey',
      };
      return colors[status] || 'primary';
    },
    async fetchCourses() {
      this.loading = true;
      try {
        const response = await httpClient.get('/api/courses');
        this.courses = response.data;
      } catch (error) {
        console.error('Error fetching courses:', error);
      } finally {
        this.loading = false;
      }
    },
  },
  mounted() {
    this.fetchCourses();
  },
};
</script>

<style scoped>
.header-section {
  background: linear-gradient(45deg, #1976d2, #2196f3);
  min-height: 120px;
  position: relative;
}

.header-content {
  padding: 40px;
  position: relative;
  z-index: 1;
}

.course-card {
  cursor: pointer;
  transition: transform 0.2s;
}

.course-card:hover {
  transform: translateY(-5px);
}
</style> 