<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content d-flex align-center justify-space-between">
          <div>
            <h1 class="text-h4 font-weight-bold white--text">
              Задания
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Управление заданиями курса
            </div>
          </div>
          <v-btn
            color="white"
            class="text-primary"
            @click="createAssignment"
          >
            <v-icon left>mdi-plus</v-icon>
            Создать задание
          </v-btn>
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
            label="Поиск заданий"
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
            v-model="filterCourse"
            :items="courses"
            item-text="title"
            item-value="id"
            label="Курс"
            clearable
          ></v-select>
        </v-col>
      </v-row>

      <!-- Assignments Table -->
      <v-card elevation="2">
        <v-data-table
          :headers="headers"
          :items="filteredAssignments"
          :loading="loading"
          :items-per-page="10"
          class="elevation-1"
        >
          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              small
            >
              {{ item.status }}
            </v-chip>
          </template>

          <template v-slot:item.due_date="{ item }">
            {{ formatDate(item.due_date) }}
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              icon
              small
              color="primary"
              @click="editAssignment(item)"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn
              icon
              small
              color="error"
              @click="confirmDelete(item)"
            >
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card>
    </v-container>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5">
          Подтверждение удаления
        </v-card-title>
        <v-card-text>
          Вы уверены, что хотите удалить это задание? Это действие нельзя отменить.
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
            @click="deleteAssignment"
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
  name: 'AssignmentList',
  data() {
    return {
      assignments: [],
      courses: [],
      search: '',
      filterStatus: null,
      filterCourse: null,
      loading: false,
      deleting: false,
      deleteDialog: false,
      assignmentToDelete: null,
      statuses: ['Активно', 'Завершено', 'Отложено'],
      headers: [
        { text: 'Название', value: 'title', sortable: true },
        { text: 'Курс', value: 'course.title', sortable: true },
        { text: 'Срок сдачи', value: 'due_date', sortable: true },
        { text: 'Статус', value: 'status', sortable: true },
        { text: 'Действия', value: 'actions', sortable: false, align: 'end' },
      ],
    };
  },
  computed: {
    filteredAssignments() {
      let filtered = [...this.assignments];

      // Apply search filter
      if (this.search) {
        const searchLower = this.search.toLowerCase();
        filtered = filtered.filter(assignment =>
          assignment.title.toLowerCase().includes(searchLower) ||
          assignment.description.toLowerCase().includes(searchLower)
        );
      }

      // Apply status filter
      if (this.filterStatus) {
        filtered = filtered.filter(assignment => assignment.status === this.filterStatus);
      }

      // Apply course filter
      if (this.filterCourse) {
        filtered = filtered.filter(assignment => assignment.course_id === this.filterCourse);
      }

      return filtered;
    },
  },
  methods: {
    async fetchAssignments() {
      this.loading = true;
      try {
        const response = await httpClient.get('/api/assignments');
        this.assignments = response.data;
      } catch (error) {
        console.error('Error fetching assignments:', error);
      } finally {
        this.loading = false;
      }
    },
    async fetchCourses() {
      try {
        const response = await httpClient.get('/api/courses');
        this.courses = response.data;
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    getStatusColor(status) {
      const colors = {
        'Активно': 'primary',
        'Завершено': 'success',
        'Отложено': 'warning',
      };
      return colors[status] || 'grey';
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
    createAssignment() {
      this.$router.push('/assignments/create');
    },
    editAssignment(assignment) {
      this.$router.push(`/assignments/${assignment.id}/edit`);
    },
    confirmDelete(assignment) {
      this.assignmentToDelete = assignment;
      this.deleteDialog = true;
    },
    async deleteAssignment() {
      if (!this.assignmentToDelete) return;

      this.deleting = true;
      try {
        await httpClient.delete(`/api/assignments/${this.assignmentToDelete.id}`);
        await this.fetchAssignments();
        this.deleteDialog = false;
        this.assignmentToDelete = null;
      } catch (error) {
        console.error('Error deleting assignment:', error);
      } finally {
        this.deleting = false;
      }
    },
  },
  mounted() {
    this.fetchAssignments();
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