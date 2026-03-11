<template>
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
              Редактирование курса
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Изменение данных курса
            </div>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
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
                    <v-img
                      v-if="course.thumbnail_url"
                      :src="course.thumbnail_url"
                      height="200"
                      class="mt-4"
                      contain
                    ></v-img>
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
                @click="updateCourse"
                :loading="saving"
                :disabled="!formValid"
              >
                <v-icon left>mdi-content-save</v-icon>
                Сохранить изменения
              </v-btn>
              <v-btn
                color="error"
                variant="outlined"
                block
                @click="confirmDelete"
              >
                <v-icon left>mdi-delete</v-icon>
                Удалить курс
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5">
          Подтверждение удаления
        </v-card-title>
        <v-card-text>
          Вы уверены, что хотите удалить этот курс? Это действие нельзя отменить.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            @click="deleteDialog = false"
          >
            Отмена
          </v-btn>
          <v-btn
            color="error"
            text
            @click="deleteCourse"
            :loading="deleting"
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'CourseEdit',
  data() {
    return {
      course: {
        title: '',
        description: '',
        category: '',
        level: '',
        thumbnail: null,
        thumbnail_url: null,
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
      deleting: false,
      deleteDialog: false,
    };
  },
  methods: {
    async fetchCourse() {
      try {
        const response = await httpClient.get(`/api/courses/${this.$route.params.id}`);
        this.course = response.data;
      } catch (error) {
        console.error('Error fetching course:', error);
        this.$router.push('/courses');
      }
    },
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
    async updateCourse() {
      if (!this.$refs.form.validate()) return;
      
      this.saving = true;
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

        await httpClient.put(`/api/courses/${this.$route.params.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        this.$router.push('/courses');
      } catch (error) {
        console.error('Error updating course:', error);
      } finally {
        this.saving = false;
      }
    },
    confirmDelete() {
      this.deleteDialog = true;
    },
    async deleteCourse() {
      this.deleting = true;
      try {
        await httpClient.delete(`/api/courses/${this.$route.params.id}`);
        this.$router.push('/courses');
      } catch (error) {
        console.error('Error deleting course:', error);
      } finally {
        this.deleting = false;
        this.deleteDialog = false;
      }
    },
  },
  mounted() {
    this.fetchCourse();
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
</style> 