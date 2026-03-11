<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content d-flex align-center">
          <div>
            <h1 class="text-h4 font-weight-bold white--text">
              Мои курсы
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Просмотр и управление вашими курсами
            </div>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
      <!-- Filters -->
      <v-row class="mb-6">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="search"
            label="Поиск курсов"
            prepend-icon="mdi-magnify"
            clearable
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="filterStatus"
            :items="statuses"
            label="Статус"
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="sortBy"
            :items="sortOptions"
            label="Сортировка"
          ></v-select>
        </v-col>
      </v-row>

      <!-- Course Grid -->
      <v-row v-if="filteredCourses.length">
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
            elevation="2"
            @click="openCourse(course.id)"
          >
            <v-img
              :src="course.thumbnail_url"
              height="200"
              class="course-thumbnail"
              cover
            >
              <v-chip
                v-if="course.status"
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
              <div class="d-flex align-center mb-2">
                <v-icon small class="mr-1">mdi-clock-outline</v-icon>
                <span class="text-caption">{{ course.duration }} часов</span>
              </div>
              <div class="d-flex align-center mb-2">
                <v-icon small class="mr-1">mdi-account-group</v-icon>
                <span class="text-caption">{{ course.students_count }} студентов</span>
              </div>
              <v-progress-linear
                :value="course.progress"
                height="8"
                color="primary"
                class="mt-2"
              ></v-progress-linear>
              <div class="text-caption text-right mt-1">
                {{ course.progress }}% завершено
              </div>
            </v-card-text>
            <v-card-actions>
              <v-btn
                color="primary"
                text
                @click.stop="continueCourse(course.id)"
              >
                Продолжить
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn
                icon
                @click.stop="showCourseDetails(course)"
              >
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <!-- Empty State -->
      <v-row v-else>
        <v-col cols="12" class="text-center">
          <v-icon size="64" color="grey">mdi-book-open-variant</v-icon>
          <h2 class="text-h5 mt-4">У вас пока нет курсов</h2>
          <p class="text-body-1 grey--text">
            Начните обучение, выбрав курс из каталога
          </p>
          <v-btn
            color="primary"
            class="mt-4"
            @click="$router.push('/courses')"
          >
            Перейти в каталог
          </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- Course Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="500">
      <v-card v-if="selectedCourse">
        <v-card-title class="text-h5">
          {{ selectedCourse.title }}
        </v-card-title>
        <v-card-text>
          <v-list>
            <v-list-item>
              <v-list-item-icon>
                <v-icon>mdi-calendar</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Дата начала</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(selectedCourse.start_date) }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <v-list-item>
              <v-list-item-icon>
                <v-icon>mdi-progress-check</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Прогресс</v-list-item-title>
                <v-list-item-subtitle>{{ selectedCourse.completed_lessons }}/{{ selectedCourse.total_lessons }} уроков</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <v-list-item>
              <v-list-item-icon>
                <v-icon>mdi-certificate</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Сертификат</v-list-item-title>
                <v-list-item-subtitle>
                  <v-btn
                    v-if="selectedCourse.progress === 100"
                    color="primary"
                    text
                    small
                    @click="downloadCertificate(selectedCourse.id)"
                  >
                    Скачать
                  </v-btn>
                  <span v-else>Доступен после завершения курса</span>
                </v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            @click="detailsDialog = false"
          >
            Закрыть
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'CourseMy',
  data() {
    return {
      courses: [],
      search: '',
      filterStatus: null,
      sortBy: 'progress',
      statuses: ['В процессе', 'Завершен', 'Отложен'],
      sortOptions: [
        { text: 'По прогрессу', value: 'progress' },
        { text: 'По дате начала', value: 'start_date' },
        { text: 'По названию', value: 'title' },
      ],
      detailsDialog: false,
      selectedCourse: null,
    };
  },
  computed: {
    filteredCourses() {
      let filtered = [...this.courses];

      // Apply search filter
      if (this.search) {
        const searchLower = this.search.toLowerCase();
        filtered = filtered.filter(course =>
          course.title.toLowerCase().includes(searchLower) ||
          course.description.toLowerCase().includes(searchLower)
        );
      }

      // Apply status filter
      if (this.filterStatus) {
        filtered = filtered.filter(course => course.status === this.filterStatus);
      }

      // Apply sorting
      filtered.sort((a, b) => {
        if (this.sortBy === 'progress') {
          return b.progress - a.progress;
        } else if (this.sortBy === 'start_date') {
          return new Date(b.start_date) - new Date(a.start_date);
        } else {
          return a.title.localeCompare(b.title);
        }
      });

      return filtered;
    },
  },
  methods: {
    async fetchCourses() {
      try {
        const response = await httpClient.get('/api/courses/my');
        this.courses = response.data;
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    getStatusColor(status) {
      const colors = {
        'В процессе': 'primary',
        'Завершен': 'success',
        'Отложен': 'warning',
      };
      return colors[status] || 'grey';
    },
    openCourse(courseId) {
      this.$router.push(`/courses/${courseId}`);
    },
    continueCourse(courseId) {
      this.$router.push(`/courses/${courseId}/continue`);
    },
    showCourseDetails(course) {
      this.selectedCourse = course;
      this.detailsDialog = true;
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
    async downloadCertificate(courseId) {
      try {
        const response = await httpClient.get(`/api/courses/${courseId}/certificate`, {
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `certificate-${courseId}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        console.error('Error downloading certificate:', error);
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
  height: 100%;
  transition: transform 0.2s;
}

.course-card:hover {
  transform: translateY(-4px);
}

.course-thumbnail {
  position: relative;
}
</style> 