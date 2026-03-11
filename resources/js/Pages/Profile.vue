<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content">
          <h1 class="text-h4 font-weight-bold white--text">
            Профиль
          </h1>
          <div class="text-subtitle-1 white--text mt-1">
            Информация о вашем профиле и статистика обучения
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
      <v-row>
        <!-- Profile Card -->
        <v-col cols="12" md="4">
          <v-card elevation="2" class="profile-card">
            <v-card-text class="text-center">
              <v-avatar size="120" class="mb-4">
                <v-img
                  :src="user.avatar || '/images/default-avatar.png'"
                  alt="Profile Avatar"
                ></v-img>
              </v-avatar>
              <h2 class="text-h5 font-weight-bold">{{ user.name }}</h2>
              <div class="text-subtitle-1 grey--text">{{ user.email }}</div>
              <div class="text-caption grey--text">{{ user.role }}</div>
              
              <v-divider class="my-4"></v-divider>
              
              <div class="d-flex justify-space-between text-caption">
                <div>
                  <v-icon small class="mr-1">mdi-calendar</v-icon>
                  Присоединился: {{ formatDate(user.created_at) }}
                </div>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-btn
                color="primary"
                block
                @click="editProfile"
              >
                Редактировать профиль
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>

        <!-- Stats and Courses -->
        <v-col cols="12" md="8">
          <!-- Stats Cards -->
          <v-row class="mb-6">
            <v-col cols="12" sm="6" md="3">
              <v-card elevation="2" class="stats-card">
                <v-card-text class="text-center">
                  <v-icon size="48" color="primary" class="mb-2">mdi-book-open-variant</v-icon>
                  <h2 class="text-h4 font-weight-bold">{{ stats.courses_count }}</h2>
                  <div class="text-subtitle-1">Курсов</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card elevation="2" class="stats-card">
                <v-card-text class="text-center">
                  <v-icon size="48" color="primary" class="mb-2">mdi-clipboard-check</v-icon>
                  <h2 class="text-h4 font-weight-bold">{{ stats.completed_courses }}</h2>
                  <div class="text-subtitle-1">Завершено</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card elevation="2" class="stats-card">
                <v-card-text class="text-center">
                  <v-icon size="48" color="primary" class="mb-2">mdi-clock-outline</v-icon>
                  <h2 class="text-h4 font-weight-bold">{{ stats.total_hours }}</h2>
                  <div class="text-subtitle-1">Часов обучения</div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card elevation="2" class="stats-card">
                <v-card-text class="text-center">
                  <v-icon size="48" color="primary" class="mb-2">mdi-star</v-icon>
                  <h2 class="text-h4 font-weight-bold">{{ stats.average_grade }}</h2>
                  <div class="text-subtitle-1">Средняя оценка</div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Current Courses -->
          <v-card elevation="2" class="mb-6">
            <v-card-title>
              Текущие курсы
              <v-spacer></v-spacer>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Поиск"
                single-line
                hide-details
                class="shrink"
              ></v-text-field>
            </v-card-title>
            <v-data-table
              :headers="courseHeaders"
              :items="currentCourses"
              :search="search"
              :loading="loading"
            >
              <template v-slot:item.progress="{ item }">
                <v-progress-linear
                  :value="item.progress"
                  height="20"
                  color="primary"
                >
                  <template v-slot:default="{ value }">
                    <strong>{{ Math.ceil(value) }}%</strong>
                  </template>
                </v-progress-linear>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn
                  icon
                  small
                  @click="viewCourse(item)"
                >
                  <v-icon>mdi-eye</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-card>

          <!-- Recent Activity -->
          <v-card elevation="2">
            <v-card-title>Последняя активность</v-card-title>
            <v-timeline dense>
              <v-timeline-item
                v-for="(activity, i) in recentActivity"
                :key="i"
                small
                :color="getActivityColor(activity.type)"
              >
                <div class="d-flex justify-space-between">
                  <div>
                    <div class="text-subtitle-1">{{ activity.title }}</div>
                    <div class="text-caption">{{ activity.description }}</div>
                  </div>
                  <div class="text-caption grey--text">
                    {{ formatTime(activity.created_at) }}
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-card>
        </v-col>
      </v-row>

      <!-- Edit Profile Dialog -->
      <v-dialog v-model="editDialog" max-width="600">
        <v-card>
          <v-card-title class="text-h5">
            Редактирование профиля
          </v-card-title>
          <v-card-text>
            <v-form ref="editForm" v-model="valid">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editProfile.first_name"
                    label="Имя"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="editProfile.last_name"
                    label="Фамилия"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editProfile.email"
                    label="Email"
                    :rules="[
                      v => !!v || 'Обязательное поле',
                      v => /.+@.+\..+/.test(v) || 'Некорректный email'
                    ]"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editProfile.phone"
                    label="Телефон"
                    :rules="[v => !v || /^\+?[\d\s-()]+$/.test(v) || 'Некорректный номер']"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-file-input
                    v-model="avatarFile"
                    label="Аватар"
                    prepend-icon="mdi-camera"
                    accept="image/*"
                    @change="handleAvatarChange"
                  ></v-file-input>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              text
              @click="editDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              :loading="saving"
              @click="saveProfile"
            >
              Сохранить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </v-container>
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'Profile',
  data() {
    return {
      user: {
        name: '',
        email: '',
        role: '',
        avatar: null,
        created_at: null,
      },
      stats: {
        courses_count: 0,
        completed_courses: 0,
        total_hours: 0,
        average_grade: 0,
      },
      currentCourses: [],
      recentActivity: [],
      search: '',
      loading: false,
      editDialog: false,
      valid: false,
      saving: false,
      editProfile: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
      },
      avatarFile: null,
      courseHeaders: [
        { title: 'Курс', key: 'title' },
        { title: 'Прогресс', key: 'progress' },
        { title: 'Оценка', key: 'grade' },
        { title: 'Действия', key: 'actions', sortable: false },
      ],
    };
  },
  methods: {
    async fetchProfile() {
      this.loading = true;
      try {
        const response = await httpClient.get('/api/profile');
        const data = response.data;
        
        this.user = data.user;
        this.stats = data.stats;
        this.currentCourses = data.current_courses;
        this.recentActivity = data.recent_activity;
      } catch (error) {
        console.error('Error fetching profile:', error);
      } finally {
        this.loading = false;
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
    formatTime(date) {
      return new Date(date).toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    getActivityColor(type) {
      const colors = {
        'course_started': 'primary',
        'course_completed': 'success',
        'assignment_submitted': 'info',
        'grade_received': 'warning',
      };
      return colors[type] || 'grey';
    },
    editProfile() {
      this.editProfile = { ...this.user };
      this.editDialog = true;
    },
    async saveProfile() {
      if (!this.$refs.editForm.validate()) {
        return;
      }

      this.saving = true;
      try {
        const formData = new FormData();
        Object.keys(this.editProfile).forEach(key => {
          formData.append(key, this.editProfile[key]);
        });
        if (this.avatarFile) {
          formData.append('avatar', this.avatarFile);
        }

        const response = await httpClient.post('/api/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        this.user = response.data;
        this.editDialog = false;
      } catch (error) {
        console.error('Error saving profile:', error);
      } finally {
        this.saving = false;
      }
    },
    handleAvatarChange(file) {
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          this.user.avatar = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    viewCourse(course) {
      this.$router.push(`/courses/${course.id}`);
    },
  },
  mounted() {
    this.fetchProfile();
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

.profile-card {
  transition: transform 0.2s;
}

.profile-card:hover {
  transform: translateY(-4px);
}

.stats-card {
  transition: transform 0.2s;
}

.stats-card:hover {
  transform: translateY(-4px);
}
</style> 