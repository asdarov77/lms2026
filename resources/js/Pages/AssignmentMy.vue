<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content">
          <h1 class="text-h4 font-weight-bold white--text">
            Мои задания
          </h1>
          <div class="text-subtitle-1 white--text mt-1">
            Просмотр и управление вашими заданиями
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
            prepend-icon="mdi-magnify"
            label="Поиск заданий"
            single-line
            hide-details
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="statusFilter"
            :items="statusOptions"
            label="Статус"
            prepend-icon="mdi-filter"
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="4">
          <v-select
            v-model="courseFilter"
            :items="courses"
            item-text="title"
            item-value="id"
            label="Курс"
            prepend-icon="mdi-book"
            clearable
          ></v-select>
        </v-col>
      </v-row>

      <!-- Assignments List -->
      <v-row>
        <v-col cols="12">
          <v-card elevation="2">
            <v-card-title>
              Список заданий
            </v-card-title>
            <v-data-table
              :headers="headers"
              :items="filteredAssignments"
              :loading="loading"
              :search="search"
              class="elevation-1"
            >
              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="getStatusColor(item.status)"
                  small
                >
                  {{ getStatusText(item.status) }}
                </v-chip>
              </template>

              <template v-slot:item.due_date="{ item }">
                {{ formatDate(item.due_date) }}
              </template>

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
                  @click="viewAssignment(item)"
                >
                  <v-icon>mdi-eye</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>

      <!-- Assignment Details Dialog -->
      <v-dialog v-model="dialog" max-width="800">
        <v-card v-if="selectedAssignment">
          <v-card-title class="text-h5">
            {{ selectedAssignment.title }}
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="6">
                <div class="text-subtitle-1 mb-2">Курс</div>
                <div>{{ selectedAssignment.course.title }}</div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="text-subtitle-1 mb-2">Срок сдачи</div>
                <div>{{ formatDate(selectedAssignment.due_date) }}</div>
              </v-col>
              <v-col cols="12">
                <div class="text-subtitle-1 mb-2">Описание</div>
                <div>{{ selectedAssignment.description }}</div>
              </v-col>
              <v-col cols="12">
                <div class="text-subtitle-1 mb-2">Прикрепленные файлы</div>
                <v-list>
                  <v-list-item
                    v-for="(file, index) in selectedAssignment.files"
                    :key="index"
                    @click="downloadFile(file)"
                  >
                    <v-list-item-icon>
                      <v-icon>mdi-file</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title>{{ file.name }}</v-list-item-title>
                    </v-list-item-content>
                    <v-list-item-action>
                      <v-btn icon>
                        <v-icon>mdi-download</v-icon>
                      </v-btn>
                    </v-list-item-action>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              text
              @click="dialog = false"
            >
              Закрыть
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
  name: 'AssignmentMy',
  data() {
    return {
      search: '',
      statusFilter: null,
      courseFilter: null,
      loading: false,
      dialog: false,
      selectedAssignment: null,
      assignments: [],
      courses: [],
      headers: [
        { text: 'Название', value: 'title' },
        { text: 'Курс', value: 'course.title' },
        { text: 'Статус', value: 'status' },
        { text: 'Срок сдачи', value: 'due_date' },
        { text: 'Прогресс', value: 'progress' },
        { text: 'Действия', value: 'actions', sortable: false },
      ],
      statusOptions: [
        { text: 'Не начато', value: 'not_started' },
        { text: 'В процессе', value: 'in_progress' },
        { text: 'На проверке', value: 'under_review' },
        { text: 'Завершено', value: 'completed' },
        { text: 'Просрочено', value: 'overdue' },
      ],
    };
  },
  computed: {
    filteredAssignments() {
      return this.assignments.filter(assignment => {
        const statusMatch = !this.statusFilter || assignment.status === this.statusFilter;
        const courseMatch = !this.courseFilter || assignment.course_id === this.courseFilter;
        return statusMatch && courseMatch;
      });
    },
  },
  methods: {
    async fetchAssignments() {
      this.loading = true;
      try {
        const response = await httpClient.get('/api/assignments/my');
        this.assignments = response.data.assignments;
        this.courses = response.data.courses;
      } catch (error) {
        console.error('Error fetching assignments:', error);
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
    getStatusColor(status) {
      const colors = {
        'not_started': 'grey',
        'in_progress': 'primary',
        'under_review': 'warning',
        'completed': 'success',
        'overdue': 'error',
      };
      return colors[status] || 'grey';
    },
    getStatusText(status) {
      const statusMap = {
        'not_started': 'Не начато',
        'in_progress': 'В процессе',
        'under_review': 'На проверке',
        'completed': 'Завершено',
        'overdue': 'Просрочено',
      };
      return statusMap[status] || status;
    },
    viewAssignment(assignment) {
      this.selectedAssignment = assignment;
      this.dialog = true;
    },
    async downloadFile(file) {
      try {
        const response = await httpClient.get(`/api/files/${file.id}/download`, {
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', file.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        console.error('Error downloading file:', error);
      }
    },
  },
  mounted() {
    this.fetchAssignments();
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