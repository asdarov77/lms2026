<template>
  <main-layout>
    <v-container fluid class="pa-0">
      <!-- Header Section -->
      <v-row class="header-section ma-0">
        <v-col cols="12" class="pa-0">
          <div class="header-content d-flex align-center">
            <v-btn
              icon
              color="white"
              class="mr-4"
              @click="$router.push('/courses')"
            >
              <v-icon>mdi-arrow-left</v-icon>
            </v-btn>
            <div>
              <h1 class="text-h4 font-weight-bold white--text">
                Создание курса
              </h1>
              <div class="text-subtitle-1 white--text mt-1">
                Добавление нового курса
              </div>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Error State -->
      <error-display 
        v-if="error" 
        title="Ошибка при создании курса" 
        :message="errorMessage"
        :error="error"
        :loading="retryLoading"
        @retry="clearErrors"
        class="mt-6"
      />

      <!-- Main Content -->
      <v-container v-else class="mt-6">
        <v-row>
          <!-- Left Column -->
          <v-col cols="12" md="8">
            <v-card elevation="4" class="mb-6">
              <v-card-text class="pa-6">
                <v-form v-model="formValid" ref="form">
                  <v-row>
                    <v-col cols="12">
                      <v-text-field
                        v-model="course.title"
                        label="Название курса"
                        :rules="[v => !!v || 'Название обязательно']"
                        required
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-textarea
                        v-model="course.description"
                        label="Описание курса"
                        :rules="[v => !!v || 'Описание обязательно']"
                        required
                        rows="4"
                      ></v-textarea>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="course.category"
                        :items="categories"
                        label="Категория"
                        :rules="[v => !!v || 'Категория обязательна']"
                        required
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="course.level"
                        :items="levels"
                        label="Уровень сложности"
                        :rules="[v => !!v || 'Уровень обязателен']"
                        required
                      ></v-select>
                    </v-col>
                    <v-col cols="12">
                      <v-file-input
                        v-model="course.thumbnail"
                        label="Обложка курса"
                        accept="image/*"
                        prepend-icon="mdi-camera"
                        :rules="[v => !v || v.size < 2000000 || 'Размер файла должен быть меньше 2 MB']"
                      ></v-file-input>
                    </v-col>
                  </v-row>
                </v-form>
              </v-card-text>
            </v-card>

            <!-- Course Content Section -->
            <v-card elevation="4">
              <v-card-title class="text-h6">
                Содержание курса
              </v-card-title>
              <v-card-text class="pa-6">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="newSection.title"
                      label="Название раздела"
                      @keyup.enter="addSection"
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-list>
                  <v-list-item
                    v-for="(section, index) in course.sections"
                    :key="index"
                    class="mb-4"
                  >
                    <v-list-item-content>
                      <v-list-item-title class="text-h6">
                        {{ section.title }}
                      </v-list-item-title>
                      <v-list-item-subtitle>
                        {{ section.lessons.length }} уроков
                      </v-list-item-subtitle>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn
                        icon
                        color="error"
                        @click="removeSection(index)"
                      >
                        <v-icon>mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Right Column -->
          <v-col cols="12" md="4">
            <v-card elevation="4" class="mb-6">
              <v-card-text class="pa-6">
                <v-select
                  v-model="course.status"
                  :items="statuses"
                  label="Статус"
                  :rules="[v => !!v || 'Статус обязателен']"
                  required
                ></v-select>
                <v-text-field
                  v-model="course.duration"
                  label="Длительность (часы)"
                  type="number"
                  :rules="[v => !!v || 'Длительность обязательна']"
                  required
                ></v-text-field>
                <v-text-field
                  v-model="course.price"
                  label="Цена"
                  type="number"
                  prefix="₽"
                  :rules="[v => !!v || 'Цена обязательна']"
                  required
                ></v-text-field>
              </v-card-text>
            </v-card>

            <!-- Action Buttons -->
            <v-card elevation="4">
              <v-card-text class="pa-4">
                <v-btn
                  color="primary"
                  block
                  class="mb-4"
                  @click="createCourse"
                  :loading="saving"
                  :disabled="!formValid"
                >
                  <v-icon left>mdi-content-save</v-icon>
                  Создать курс
                </v-btn>
                <v-btn
                  color="error"
                  variant="outlined"
                  block
                  @click="$router.push('/courses')"
                >
                  <v-icon left>mdi-close</v-icon>
                  Отмена
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-container>
  </main-layout>
</template>

<script>
import httpClient from '@/api/httpClient';
import ErrorDisplay from '../components/ErrorDisplay.vue';
import MainLayout from '../Layouts/MainLayout.vue';

export default {
  name: 'CourseCreate',
  components: {
    ErrorDisplay,
    MainLayout
  },
  data() {
    return {
      course: {
        title: '',
        description: '',
        category: '',
        level: '',
        thumbnail: null,
        status: 'Черновик',
        duration: 0,
        price: 0,
        sections: [],
      },
      newSection: {
        title: '',
        lessons: [],
      },
      categories: ['Программирование', 'Дизайн', 'Маркетинг', 'Бизнес'],
      levels: ['Начинающий', 'Средний', 'Продвинутый'],
      statuses: ['Черновик', 'Активный', 'Архив'],
      formValid: false,
      saving: false,
      error: null,
      errorMessage: 'Не удалось создать курс. Пожалуйста, проверьте введенные данные и попробуйте снова.',
      retryLoading: false,
    };
  },
  methods: {
    addSection() {
      if (this.newSection.title) {
        this.course.sections.push({
          title: this.newSection.title,
          lessons: [],
        });
        this.newSection.title = '';
      }
    },
    removeSection(index) {
      this.course.sections.splice(index, 1);
    },
    clearErrors() {
      this.error = null;
      this.retryLoading = false;
    },
    async createCourse() {
      if (!this.$refs.form.validate()) return;
      
      this.saving = true;
      this.error = null;
      
      try {
        const formData = new FormData();
        Object.keys(this.course).forEach(key => {
          if (key === 'thumbnail' && this.course[key]) {
            formData.append('thumbnail', this.course[key]);
          } else if (key === 'sections') {
            formData.append(key, JSON.stringify(this.course[key]));
          } else {
            formData.append(key, this.course[key]);
          }
        });

        const response = await httpClient.post('/api/courses', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        // Redirect to the course list on success
        this.$router.push({
          path: '/courses',
          query: { newCourse: response.data.id }
        });
      } catch (error) {
        console.error('Error creating course:', error);
        this.error = error;
        this.saving = false;
        
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        }
      }
    },
  },
};
</script>

<style scoped>
.header-section {
  background: linear-gradient(135deg, var(--v-primary-base) 0%, var(--v-secondary-base) 100%);
  padding: 32px;
  border-radius: 0 0 8px 8px;
}

.header-content {
  min-height: 80px;
}
</style> 