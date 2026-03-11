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
            @click="$router.push('/assignments')"
          >
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
          <div>
            <h1 class="text-h4 font-weight-bold white--text">
              Создание задания
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Добавление нового задания
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
                      v-model="assignment.title"
                      label="Название задания"
                      :rules="[v => !!v || 'Название обязательно']"
                      required
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="assignment.description"
                      label="Описание задания"
                      :rules="[v => !!v || 'Описание обязательно']"
                      required
                      rows="4"
                    ></v-textarea>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="assignment.course_id"
                      :items="courses"
                      item-text="title"
                      item-value="id"
                      label="Курс"
                      :rules="[v => !!v || 'Курс обязателен']"
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="assignment.type"
                      :items="types"
                      label="Тип задания"
                      :rules="[v => !!v || 'Тип обязателен']"
                      required
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-menu
                      v-model="dateMenu"
                      :close-on-content-click="false"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="assignment.due_date"
                          label="Срок сдачи"
                          prepend-icon="mdi-calendar"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :rules="[v => !!v || 'Срок сдачи обязателен']"
                          required
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        v-model="assignment.due_date"
                        @input="dateMenu = false"
                        :min="new Date().toISOString().substr(0, 10)"
                      ></v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-menu
                      v-model="timeMenu"
                      :close-on-content-click="false"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="assignment.due_time"
                          label="Время сдачи"
                          prepend-icon="mdi-clock-outline"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                          :rules="[v => !!v || 'Время сдачи обязательно']"
                          required
                        ></v-text-field>
                      </template>
                      <v-time-picker
                        v-model="assignment.due_time"
                        @input="timeMenu = false"
                        format="24hr"
                      ></v-time-picker>
                    </v-menu>
                  </v-col>
                  <v-col cols="12">
                    <v-file-input
                      v-model="assignment.attachments"
                      label="Прикрепленные файлы"
                      multiple
                      prepend-icon="mdi-paperclip"
                      :rules="[v => !v || v.length <= 5 || 'Максимум 5 файлов']"
                    ></v-file-input>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Right Column -->
        <v-col cols="12" md="4">
          <v-card elevation="4" class="mb-6">
            <v-card-text class="pa-6">
              <v-select
                v-model="assignment.status"
                :items="statuses"
                label="Статус"
                :rules="[v => !!v || 'Статус обязателен']"
                required
              ></v-select>
              <v-text-field
                v-model="assignment.points"
                label="Максимальный балл"
                type="number"
                :rules="[v => !!v || 'Балл обязателен']"
                required
              ></v-text-field>
              <v-switch
                v-model="assignment.is_required"
                label="Обязательное задание"
                color="primary"
              ></v-switch>
            </v-card-text>
          </v-card>

          <!-- Action Buttons -->
          <v-card elevation="4">
            <v-card-text class="pa-4">
              <v-btn
                color="primary"
                block
                class="mb-4"
                @click="createAssignment"
                :loading="saving"
                :disabled="!formValid"
              >
                <v-icon left>mdi-content-save</v-icon>
                Создать задание
              </v-btn>
              <v-btn
                color="error"
                variant="outlined"
                block
                @click="$router.push('/assignments')"
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
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'AssignmentCreate',
  data() {
    return {
      assignment: {
        title: '',
        description: '',
        course_id: null,
        type: '',
        due_date: null,
        due_time: null,
        status: 'Активно',
        points: 100,
        is_required: true,
        attachments: [],
      },
      courses: [],
      types: ['Тест', 'Проект', 'Эссе', 'Презентация', 'Лабораторная работа'],
      statuses: ['Активно', 'Завершено', 'Отложено'],
      formValid: false,
      saving: false,
      dateMenu: false,
      timeMenu: false,
    };
  },
  methods: {
    async fetchCourses() {
      try {
        const response = await httpClient.get('/api/courses');
        this.courses = response.data;
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    async createAssignment() {
      if (!this.$refs.form.validate()) return;
      
      this.saving = true;
      try {
        const formData = new FormData();
        Object.keys(this.assignment).forEach(key => {
          if (key === 'attachments' && this.assignment[key]) {
            this.assignment[key].forEach(file => {
              formData.append('attachments[]', file);
            });
          } else if (key === 'due_date' && this.assignment[key] && this.assignment.due_time) {
            const dateTime = new Date(this.assignment[key] + 'T' + this.assignment.due_time);
            formData.append('due_date', dateTime.toISOString());
          } else if (key !== 'due_time') {
            formData.append(key, this.assignment[key]);
          }
        });

        await httpClient.post('/api/assignments', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        this.$router.push('/assignments');
      } catch (error) {
        console.error('Error creating assignment:', error);
      } finally {
        this.saving = false;
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
</style> 