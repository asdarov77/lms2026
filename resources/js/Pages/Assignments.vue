<template>
  <v-container fluid>
    <!-- Header Section -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <h1 class="text-h4 font-weight-bold primary--text">Задания</h1>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end">
        <v-btn
          color="primary"
          @click="openAddAssignmentDialog"
          prepend-icon="mdi-plus"
          v-if="isAdmin"
        >
          Добавить задание
        </v-btn>
      </v-col>
    </v-row>

    <!-- Search and Filter Section -->
    <v-card class="mb-4">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Поиск заданий"
              single-line
              hide-details
              variant="outlined"
              density="compact"
              class="mb-4"
              @input="handleSearch"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="courseFilter"
              :items="courses"
              label="Курс"
              variant="outlined"
              density="compact"
              class="mb-4"
              @update:model-value="handleFilter"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="statusFilter"
              :items="statuses"
              label="Статус"
              variant="outlined"
              density="compact"
              class="mb-4"
              @update:model-value="handleFilter"
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Assignments List -->
    <v-card>
      <v-data-table
        :headers="headers"
        :items="filteredAssignments"
        :loading="loading"
        :items-per-page="10"
        class="elevation-1"
      >
        <!-- Title Column -->
        <template v-slot:item.title="{ item }">
          <div class="d-flex align-center">
            <v-icon
              :color="getStatusColor(item.status)"
              size="small"
              class="mr-2"
            >
              {{ getStatusIcon(item.status) }}
            </v-icon>
            <div>
              <div class="text-subtitle-2">{{ item.title }}</div>
              <div class="text-caption grey--text">{{ item.course }}</div>
            </div>
          </div>
        </template>

        <!-- Deadline Column -->
        <template v-slot:item.deadline="{ item }">
          <div class="d-flex align-center">
            <v-icon
              :color="isOverdue(item.deadline) ? 'error' : 'primary'"
              size="small"
              class="mr-1"
            >
              mdi-calendar-clock
            </v-icon>
            <span :class="{ 'error--text': isOverdue(item.deadline) }">
              {{ formatDate(item.deadline) }}
            </span>
          </div>
        </template>

        <!-- Status Column -->
        <template v-slot:item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
          >
            {{ item.status }}
          </v-chip>
        </template>

        <!-- Actions Column -->
        <template v-slot:item.actions="{ item }">
          <v-tooltip text="Открыть">
            <template v-slot:activator="{ props }">
              <v-btn
                icon
                size="small"
                color="primary"
                v-bind="props"
                @click="openAssignment(item)"
                class="mr-2"
              >
                <v-icon>mdi-eye</v-icon>
              </v-btn>
            </template>
          </v-tooltip>

          <v-tooltip text="Редактировать">
            <template v-slot:activator="{ props }">
              <v-btn
                icon
                size="small"
                color="warning"
                v-bind="props"
                @click="openEditAssignmentDialog(item)"
                class="mr-2"
                v-if="isAdmin"
              >
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
          </v-tooltip>

          <v-tooltip text="Удалить">
            <template v-slot:activator="{ props }">
              <v-btn
                icon
                size="small"
                color="error"
                v-bind="props"
                @click="confirmDelete(item)"
                v-if="isAdmin"
              >
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Add/Edit Assignment Dialog -->
    <v-dialog v-model="assignmentDialog" max-width="600px">
      <v-card>
        <v-card-title class="text-h5">
          {{ isEditing ? 'Редактировать задание' : 'Добавить задание' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="assignmentForm" v-model="formValid">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="assignmentForm.title"
                  label="Название задания"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-select
                  v-model="assignmentForm.course_id"
                  :items="courses"
                  item-title="title"
                  item-value="id"
                  label="Курс"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-select>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="assignmentForm.description"
                  label="Описание"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="assignmentForm.deadline"
                  label="Срок сдачи"
                  type="datetime-local"
                  :rules="[v => !!v || 'Обязательное поле']"
                  required
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="assignmentDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="primary"
            variant="text"
            @click="saveAssignment"
            :loading="saving"
            :disabled="!formValid"
          >
            Сохранить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">
          Подтверждение удаления
        </v-card-title>
        <v-card-text>
          Вы действительно хотите удалить задание "{{ selectedAssignment?.title }}"?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="deleteDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="error"
            variant="text"
            @click="deleteAssignment"
            :loading="deleting"
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      :timeout="3000"
    >
      {{ snackbarText }}
    </v-snackbar>
  </v-container>
</template>

<script>
export default {
  name: 'Assignments',

  data() {
    return {
      loading: false,
      saving: false,
      deleting: false,
      search: '',
      courseFilter: null,
      statusFilter: null,
      assignments: [],
      courses: [],
      statuses: [
        'Новое',
        'В процессе',
        'На проверке',
        'Завершено',
        'Просрочено'
      ],
      headers: [
        { title: 'Задание', key: 'title', sortable: true },
        { title: 'Срок сдачи', key: 'deadline', sortable: true },
        { title: 'Статус', key: 'status', sortable: true },
        { title: 'Действия', key: 'actions', sortable: false, align: 'end' }
      ],
      assignmentDialog: false,
      deleteDialog: false,
      isEditing: false,
      formValid: false,
      selectedAssignment: null,
      assignmentForm: {
        title: '',
        course_id: null,
        description: '',
        deadline: ''
      },
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success'
    }
  },

  computed: {
    isAdmin() {
      return this.$store.state.Auth.user?.role === 'Администратор'
    },

    filteredAssignments() {
      return this.assignments.filter(assignment => {
        const matchesSearch = assignment.title.toLowerCase().includes(this.search.toLowerCase())
        const matchesCourse = !this.courseFilter || assignment.course_id === this.courseFilter
        const matchesStatus = !this.statusFilter || assignment.status === this.statusFilter
        return matchesSearch && matchesCourse && matchesStatus
      })
    }
  },

  created() {
    this.loadAssignments()
    this.loadCourses()
  },

  methods: {
    async loadAssignments() {
      this.loading = true
      try {
        const response = await this.$store.dispatch('Assignments/getAssignments')
        this.assignments = response
      } catch (error) {
        this.showNotification('Ошибка при загрузке заданий', 'error')
      } finally {
        this.loading = false
      }
    },

    async loadCourses() {
      try {
        const response = await this.$store.dispatch('Courses/getCourses')
        this.courses = response
      } catch (error) {
        this.showNotification('Ошибка при загрузке курсов', 'error')
      }
    },

    handleSearch() {
      // Implement search logic
    },

    handleFilter() {
      // Implement filter logic
    },

    openAssignment(assignment) {
      this.$router.push(`/assignments/${assignment.id}`)
    },

    openAddAssignmentDialog() {
      this.isEditing = false
      this.assignmentForm = {
        title: '',
        course_id: null,
        description: '',
        deadline: ''
      }
      this.assignmentDialog = true
    },

    openEditAssignmentDialog(assignment) {
      this.isEditing = true
      this.selectedAssignment = assignment
      this.assignmentForm = {
        title: assignment.title,
        course_id: assignment.course_id,
        description: assignment.description,
        deadline: assignment.deadline
      }
      this.assignmentDialog = true
    },

    confirmDelete(assignment) {
      this.selectedAssignment = assignment
      this.deleteDialog = true
    },

    async saveAssignment() {
      if (!this.$refs.assignmentForm.validate()) return

      this.saving = true
      try {
        if (this.isEditing) {
          await this.$store.dispatch('Assignments/updateAssignment', {
            id: this.selectedAssignment.id,
            ...this.assignmentForm
          })
        } else {
          await this.$store.dispatch('Assignments/createAssignment', this.assignmentForm)
        }
        this.assignmentDialog = false
        this.loadAssignments()
        this.showNotification(
          this.isEditing ? 'Задание обновлено' : 'Задание добавлено',
          'success'
        )
      } catch (error) {
        this.showNotification('Ошибка при сохранении задания', 'error')
      } finally {
        this.saving = false
      }
    },

    async deleteAssignment() {
      this.deleting = true
      try {
        await this.$store.dispatch('Assignments/deleteAssignment', this.selectedAssignment.id)
        this.deleteDialog = false
        this.loadAssignments()
        this.showNotification('Задание удалено', 'success')
      } catch (error) {
        this.showNotification('Ошибка при удалении задания', 'error')
      } finally {
        this.deleting = false
      }
    },

    getStatusColor(status) {
      const colors = {
        'Новое': 'primary',
        'В процессе': 'warning',
        'На проверке': 'info',
        'Завершено': 'success',
        'Просрочено': 'error'
      }
      return colors[status] || 'grey'
    },

    getStatusIcon(status) {
      const icons = {
        'Новое': 'mdi-star',
        'В процессе': 'mdi-progress-clock',
        'На проверке': 'mdi-clipboard-check',
        'Завершено': 'mdi-check-circle',
        'Просрочено': 'mdi-alert'
      }
      return icons[status] || 'mdi-circle'
    },

    formatDate(date) {
      return new Date(date).toLocaleString('ru-RU')
    },

    isOverdue(date) {
      return new Date(date) < new Date() && this.getStatusColor(date) !== 'success'
    },

    showNotification(text, color = 'success') {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    }
  }
}
</script>

<style scoped>
.v-data-table {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.v-card {
  border-radius: 8px;
}

.v-btn {
  text-transform: none;
}

.v-chip {
  font-weight: 500;
}
</style> 