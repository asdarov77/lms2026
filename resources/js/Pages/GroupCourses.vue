<template>
  <AppLayout>
    <v-container fluid>
      <!-- Header -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <h1 class="text-h4 font-weight-bold primary--text">Запись групп на курсы</h1>
        </v-col>
        <v-col cols="12" md="6" class="d-flex justify-end align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="openAddEnrollmentDialog"
            v-if="isAdmin"
          >
            Записать группу на курс
          </v-btn>
        </v-col>
      </v-row>

      <!-- Search and Filters -->
      <v-card class="mb-4 pa-3">
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              label="Поиск"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="groupFilter"
              :items="groupItems"
              item-title="groupname"
              item-value="id"
              label="Группа"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="courseFilter"
              :items="courseItems"
              item-title="title"
              item-value="id"
              label="Курс"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
            ></v-select>
          </v-col>
          <v-col cols="12" class="d-flex align-center">
            <v-btn
              color="primary"
              variant="outlined"
              size="small"
              @click="clearFilters"
              class="ml-auto"
            >
              Сбросить
            </v-btn>
          </v-col>
        </v-row>
      </v-card>

      <!-- Loading State -->
      <loading-state v-if="loading" />

      <!-- Error State -->
      <error-display 
        v-else-if="error" 
        title="Ошибка загрузки данных" 
        :message="error"
        :error="error"
        :loading="false"
        @retry="fetchEnrollments"
      />

      <!-- Enrollments Table -->
      <v-card v-if="!loading && !error">
        <v-data-table
          :headers="headers"
          :items="filteredEnrollments"
          :loading="loading"
          :items-per-page="10"
          class="elevation-1"
        >
          <template v-slot:item.group="{ item }">
            {{ getGroupName(item.group_id) }}
          </template>
          
          <template v-slot:item.course="{ item }">
            {{ getCourseName(item.course_id) }}
          </template>
          
          <template v-slot:item.progress="{ item }">
            <v-progress-linear
              :model-value="item.progress || 0"
              :color="getProgressColor(item.progress || 0)"
              height="20"
              striped
            >
              <template v-slot:default="{ value }">
                <span class="text-caption font-weight-medium">{{ Math.ceil(value) }}%</span>
              </template>
            </v-progress-linear>
          </template>

          <template v-slot:item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              size="small"
              class="text-caption"
            >
              {{ getStatusText(item.status) }}
            </v-chip>
          </template>

          <template v-slot:item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex justify-center">
              <v-btn
                icon="mdi-information"
                size="small"
                color="info"
                variant="text"
                @click="viewEnrollment(item)"
              ></v-btn>
              <v-btn
                icon="mdi-chart-line"
                size="small"
                color="primary"
                variant="text"
                @click="viewProgress(item)"
              ></v-btn>
              <v-btn
                icon="mdi-pencil"
                size="small"
                color="warning"
                variant="text"
                @click="editEnrollment(item)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="small"
                color="error"
                variant="text"
                @click="confirmDelete(item)"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </v-card>

      <!-- Add Enrollment Dialog -->
      <v-dialog v-model="enrollmentDialog" max-width="800px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white py-3">
            Записать группу на курс
          </v-card-title>
          <v-card-text class="pt-4">
            <v-form ref="enrollmentForm" v-model="enrollmentFormValid">
              <v-row>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="enrollmentForm.group_id"
                    :items="groupItems"
                    item-title="groupname"
                    item-value="id"
                    label="Группа"
                    :rules="[v => !!v || 'Выберите группу']"
                    required
                    :return-object="false"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="enrollmentForm.aircraft_id"
                    :items="aircraftItems"
                    item-title="path"
                    item-value="id"
                    label="Класс (воздушное судно)"
                    :rules="[v => !!v || 'Выберите класс']"
                    required
                    :return-object="false"
                    @update:model-value="onAircraftChange"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="enrollmentForm.category_id"
                    :items="filteredCategoryItems"
                    item-title="title"
                    item-value="id"
                    label="Специальность"
                    :rules="[v => !!v || 'Выберите специальность']"
                    required
                    :return-object="false"
                    :disabled="!enrollmentForm.aircraft_id"
                    @update:model-value="onCategoryChange"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="enrollmentForm.course_id"
                    :items="filteredCourseItems"
                    item-title="title"
                    item-value="id"
                    label="Курс"
                    :rules="[v => !!v || 'Выберите курс']"
                    required
                    :return-object="false"
                    :disabled="!enrollmentForm.category_id"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="enrollmentForm.teacher"
                    :items="instructors"
                    item-title="fio"
                    item-value="fio"
                    label="Преподаватель"
                    :rules="[v => !!v || 'Выберите преподавателя']"
                    required
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="enrollmentForm.typeOfLesson"
                    :items="lessonTypeOptions"
                    item-title="title"
                    item-value="value"
                    label="Тип обучения"
                    :rules="[v => !!v || 'Выберите тип обучения']"
                    required
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="enrollmentForm.study_from"
                    label="Дата начала"
                    type="date"
                    :rules="[v => !!v || 'Выберите дату начала']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="enrollmentForm.study_to"
                    label="Дата окончания"
                    type="date"
                    :rules="[v => !!v || 'Выберите дату окончания']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="enrollmentForm.notes"
                    label="Примечание"
                    placeholder="Дополнительная информация"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="enrollmentDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              variant="text"
              :loading="saving"
              :disabled="!enrollmentFormValid"
              @click="saveEnrollment"
            >
              Сохранить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- View Enrollment Dialog -->
      <v-dialog v-model="viewDialog" max-width="600px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white py-3">
            Информация о записи
          </v-card-title>
          <v-card-text class="pt-4">
            <v-list lines="two">
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-account-group</v-icon>
                </template>
                <v-list-item-title>Группа</v-list-item-title>
                <v-list-item-subtitle>{{ getGroupName(selectedEnrollment?.group_id) }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-book-open-variant</v-icon>
                </template>
                <v-list-item-title>Курс</v-list-item-title>
                <v-list-item-subtitle>{{ getCourseName(selectedEnrollment?.course_id) }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon :color="getStatusColor(selectedEnrollment?.status)">mdi-checkbox-marked-circle</v-icon>
                </template>
                <v-list-item-title>Статус</v-list-item-title>
                <v-list-item-subtitle>{{ getStatusText(selectedEnrollment?.status) }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-chart-line</v-icon>
                </template>
                <v-list-item-title>Прогресс</v-list-item-title>
                <v-list-item-subtitle>
                  <v-progress-linear
                    :model-value="selectedEnrollment?.progress || 0"
                    :color="getProgressColor(selectedEnrollment?.progress || 0)"
                    height="20"
                    striped
                    class="mt-2"
                  >
                    <template v-slot:default="{ value }">
                      <span class="text-caption font-weight-medium">{{ Math.ceil(value) }}%</span>
                    </template>
                  </v-progress-linear>
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-calendar</v-icon>
                </template>
                <v-list-item-title>Дата записи</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(selectedEnrollment?.created_at) }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item v-if="selectedEnrollment?.notes">
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-note-text</v-icon>
                </template>
                <v-list-item-title>Примечание</v-list-item-title>
                <v-list-item-subtitle>{{ selectedEnrollment?.notes }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              variant="text"
              @click="viewDialog = false"
            >
              Закрыть
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Progress Detail Dialog -->
      <v-dialog v-model="progressDialog" max-width="800px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white py-3">
            Прогресс группы по курсу
          </v-card-title>
          <v-card-text class="pt-4">
            <div class="d-flex align-center mb-4">
              <div>
                <h3 class="text-subtitle-1 font-weight-bold">Группа: {{ getGroupName(selectedEnrollment?.group_id) }}</h3>
                <h3 class="text-subtitle-1 font-weight-bold">Курс: {{ getCourseName(selectedEnrollment?.course_id) }}</h3>
              </div>
              <v-spacer></v-spacer>
              <v-chip
                :color="getStatusColor(selectedEnrollment?.status)"
                size="small"
              >
                {{ getStatusText(selectedEnrollment?.status) }}
              </v-chip>
            </div>
            
            <v-divider class="mb-4"></v-divider>
            
            <h3 class="text-h6 mb-3">Общий прогресс группы</h3>
            <v-progress-linear
              :model-value="selectedEnrollment?.progress || 0"
              :color="getProgressColor(selectedEnrollment?.progress || 0)"
              height="24"
              striped
              class="mb-6"
            >
              <template v-slot:default="{ value }">
                <span class="text-body-2 font-weight-medium">{{ Math.ceil(value) }}%</span>
              </template>
            </v-progress-linear>
            
            <h3 class="text-h6 mb-3">Прогресс по студентам</h3>
            <div v-if="progressByStudents.length > 0">
              <v-list lines="two">
                <v-list-item v-for="(student, index) in progressByStudents" :key="index">
                  <template v-slot:prepend>
                    <v-avatar color="primary" size="36">
                      <span class="text-caption text-white">{{ getInitials(student.name) }}</span>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ student.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-progress-linear
                      :model-value="student.progress"
                      :color="getProgressColor(student.progress)"
                      height="20"
                      striped
                      class="mt-2"
                    >
                      <template v-slot:default="{ value }">
                        <span class="text-caption font-weight-medium">{{ Math.ceil(value) }}%</span>
                      </template>
                    </v-progress-linear>
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </div>
            <p v-else class="text-body-1 text-grey text-center py-4">
              Информация о прогрессе студентов не доступна
            </p>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              variant="text"
              @click="progressDialog = false"
            >
              Закрыть
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Delete Confirmation Dialog -->
      <v-dialog v-model="deleteDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 bg-error text-white py-3">
            Подтверждение отмены записи
          </v-card-title>
          <v-card-text class="pt-4">
            <p class="mb-0">Вы действительно хотите отменить запись группы на курс?</p>
            <p class="text-body-1 mt-2">
              <strong>Группа:</strong> {{ getGroupName(enrollmentToDelete?.group_id) }}<br>
              <strong>Курс:</strong> {{ getCourseName(enrollmentToDelete?.course_id) }}
            </p>
            <p class="text-error font-weight-bold mt-2">Внимание! Весь прогресс обучения будет потерян.</p>
            <p class="text-error font-weight-bold mt-2">Это действие невозможно отменить.</p>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="deleteDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              :loading="deleting"
              @click="deleteEnrollment"
            >
              Удалить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Snackbar for notifications -->
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        :timeout="3000"
      >
        {{ snackbar.text }}
      </v-snackbar>
    </v-container>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useGroupStore } from '@/stores/groupStore'
import { useCourseStore } from '@/stores/courseStore'
import { useEnrollmentStore } from '@/stores/enrollmentStore'
import AppLayout from '@/Layouts/AppLayout.vue'
import LoadingState from '@/components/LoadingState.vue';
import ErrorDisplay from '@/components/ErrorDisplay.vue';
import { format } from 'date-fns'
import { ru } from 'date-fns/locale'
import { useRouter } from 'vue-router'

export default {
  components: {
    AppLayout,
    LoadingState,
    ErrorDisplay
  },
  setup() {
    const authStore = useAuthStore()
    const groupStore = useGroupStore()
    const courseStore = useCourseStore()
    const enrollmentStore = useEnrollmentStore()
    const isAdmin = computed(() => authStore.isAdmin)
    const router = useRouter()

    // State
    const enrollments = ref([])
    const groups = ref([])
    const courses = ref([])
    const aircrafts = ref([])
    const categories = ref([])
    const instructors = ref([])
    const loading = computed(() => groupStore.loading || courseStore.loading || enrollmentStore.loading)
    const error = ref(null)
    const saving = ref(false)
    const deleting = ref(false)
    const search = ref('')
    const groupFilter = ref(null)
    const courseFilter = ref(null)
    const enrollmentDialog = ref(false)
    const viewDialog = ref(false)
    const progressDialog = ref(false)
    const deleteDialog = ref(false)
    const enrollmentFormValid = ref(false)
    const selectedEnrollment = ref(null)
    const enrollmentToDelete = ref(null)
    const snackbar = ref({
      show: false,
      color: 'success',
      text: ''
    })

    // Lesson type options
    const lessonTypeOptions = [
      { title: 'Основной курс', value: 'Основной курс' },
      { title: 'Дополнительный курс', value: 'Дополнительный курс' },
      { title: 'Практические занятия', value: 'Практические занятия' },
      { title: 'Теоретические занятия', value: 'Теоретические занятия' },
      { title: 'Экзамен', value: 'Экзамен' }
    ]

    // Form data
    const enrollmentForm = ref({
      group_id: null,
      aircraft_id: null,
      category_id: null,
      course_id: null,
      status: 'active',
      teacher: '',
      typeOfLesson: '',
      study_from: '',
      study_to: '',
      notes: ''
    })

    // Table headers
    const headers = [
      { title: 'ID', key: 'id', align: 'start', width: '5%' },
      { title: 'Группа', key: 'group', align: 'start', width: '20%' },
      { title: 'Курс', key: 'course', align: 'start', width: '20%' },
      { title: 'Прогресс', key: 'progress', align: 'start', width: '20%' },
      { title: 'Статус', key: 'status', align: 'start', width: '10%' },
      { title: 'Дата записи', key: 'created_at', align: 'start', width: '15%' },
      { title: 'Действия', key: 'actions', align: 'center', sortable: false, width: '10%' }
    ]

    // Options
    const statusOptions = [
      { title: 'Активна', value: 'active' },
      { title: 'Завершена', value: 'completed' },
      { title: 'Приостановлена', value: 'paused' },
      { title: 'Отменена', value: 'canceled' }
    ]

    // Computed
    const filteredEnrollments = computed(() => {
      return enrollments.value.filter(enrollment => {
        // Search filter
        const groupName = getGroupName(enrollment.group_id).toLowerCase()
        const courseName = getCourseName(enrollment.course_id).toLowerCase()
        const searchTerm = search.value.toLowerCase()
        const matchesSearch = groupName.includes(searchTerm) || 
                              courseName.includes(searchTerm) ||
                              getStatusText(enrollment.status).toLowerCase().includes(searchTerm)
        
        // Group filter
        const matchesGroup = !groupFilter.value || enrollment.group_id === groupFilter.value
        
        // Course filter
        const matchesCourse = !courseFilter.value || enrollment.course_id === courseFilter.value
        
        return matchesSearch && matchesGroup && matchesCourse
      })
    })

    const groupItems = computed(() => {
      return [
        { groupname: 'Все группы', id: null },
        ...groups.value
      ]
    })

    const courseItems = computed(() => {
      return [
        { title: 'Все курсы', id: null },
        ...courses.value
      ]
    })

    const aircraftItems = computed(() => {
      return aircrafts.value
    })

    const filteredCategoryItems = computed(() => {
      if (!enrollmentForm.value.aircraft_id) return []
      return categories.value.filter(cat => cat.aircraft_id === enrollmentForm.value.aircraft_id)
    })

    const filteredCourseItems = computed(() => {
      if (!enrollmentForm.value.category_id) return []
      return courses.value.filter(course => {
        if (enrollmentForm.value.aircraft_id && course.aircraft_id !== enrollmentForm.value.aircraft_id) return false
        if (!course.categories) return false
        return course.categories.some(cat => cat.id === enrollmentForm.value.category_id)
      })
    })

    // Mock data for student progress
    const progressByStudents = computed(() => {
      if (!selectedEnrollment.value) return []
      
      // In real app, this would be fetched from backend
      // This is just mock data for demonstration
      const group = groups.value.find(g => g.id === selectedEnrollment.value.group_id)
      if (!group || !group.users) return []
      
      return group.users.map(user => ({
        id: user.id,
        name: user.fio,
        progress: Math.floor(Math.random() * 100) // Mock progress data
      }))
    })

    // Methods
    const fetchEnrollments = async () => {
      error.value = null
      try {
        const data = await enrollmentStore.fetchEnrollments()
        enrollments.value = data
        console.log('Enrollments loaded:', enrollments.value)
      } catch (err) {
        console.error('Error fetching enrollments:', err)
        error.value = err.response?.data?.message || err.message || 'Ошибка при загрузке записей на курсы'
        showNotification('Ошибка при загрузке записей на курсы', 'error')
      }
    }

    const openAddEnrollmentDialog = () => {
      // Get current date and date 3 months later
      const today = new Date();
      const threeMonthsLater = new Date(today);
      threeMonthsLater.setMonth(today.getMonth() + 3);
      
      // Format dates as YYYY-MM-DD for input fields
      const formatDate = (date) => {
        return date.toISOString().split('T')[0];
      };
      
      enrollmentForm.value = {
        group_id: null,
        aircraft_id: null,
        category_id: null,
        course_id: null,
        status: 'active',
        teacher: '',
        typeOfLesson: 'Основной курс',
        study_from: formatDate(today),
        study_to: formatDate(threeMonthsLater),
        notes: ''
      }
      
      // Make sure we have instructors loaded
      if (instructors.value.length === 0) {
        fetchInstructors();
      }
      
      // Make sure we have aircrafts and categories loaded
      if (aircrafts.value.length === 0) {
        fetchAircrafts();
      }
      // Categories depend on selected aircraft, so they are loaded on aircraft change
      
      enrollmentDialog.value = true;
    }

    const onAircraftChange = (aircraft) => {
      enrollmentForm.value.category_id = null;
      enrollmentForm.value.course_id = null;
      fetchCategories();
    }

    const onCategoryChange = (category) => {
      enrollmentForm.value.course_id = null;
    }

    const viewEnrollment = (enrollment) => {
      selectedEnrollment.value = enrollment
      viewDialog.value = true
    }

    const viewProgress = (enrollment) => {
      selectedEnrollment.value = enrollment
      progressDialog.value = true
    }

    const confirmDelete = (enrollment) => {
      enrollmentToDelete.value = enrollment
      deleteDialog.value = true
    }

    const saveEnrollment = async () => {
      saving.value = true;
      try {
        const payload = {
          group_id: enrollmentForm.value.group_id,
          course_id: [enrollmentForm.value.course_id], // Array to match server expectation
          category_id: enrollmentForm.value.category_id,
          teacher: enrollmentForm.value.teacher,
          typeOfLesson: enrollmentForm.value.typeOfLesson,
          study_from: enrollmentForm.value.study_from,
          study_to: enrollmentForm.value.study_to
        };
        
        // Use the enrollment store to create the enrollment
        const result = await enrollmentStore.createEnrollment(payload);
        
        enrollmentDialog.value = false;
        showNotification('Запись на курс успешно создана', 'success');
        
        // Refresh enrollments list
        await fetchEnrollments();
      } catch (error) {
        console.error('Error saving enrollment:', error);
        showNotification('Ошибка при создании записи на курс: ' + (error.message || 'Неизвестная ошибка'), 'error');
      } finally {
        saving.value = false;
      }
    };

    const deleteEnrollment = async () => {
      if (!enrollmentToDelete.value) return

      deleting.value = true
      try {
        await enrollmentStore.deleteEnrollment(enrollmentToDelete.value.id)
        deleteDialog.value = false
        showNotification('Запись успешно удалена', 'success')
      } catch (error) {
        console.error('Error deleting enrollment:', error)
        showNotification('Ошибка при удалении записи', 'error')
      } finally {
        deleting.value = false
      }
    }

    const fetchGroups = async () => {
      try {
        await groupStore.fetchGroups()
        groups.value = groupStore.getGroups
      } catch (err) {
        console.error('Error in component fetching groups:', err)
        error.value = err.response?.data?.message || err.message || 'Ошибка при загрузке групп'
        showNotification('Ошибка при загрузке групп', 'error')
      }
    }

    const fetchCourses = async () => {
      try {
        await courseStore.fetchCourses()
        courses.value = courseStore.getCourses
      } catch (err) {
        console.error('Error in component fetching courses:', err)
        error.value = err.response?.data?.message || err.message || 'Ошибка при загрузке курсов'
        showNotification('Ошибка при загрузке курсов', 'error')
      }
    }

    const fetchAircrafts = async () => {
      try {
        const apiUrl = import.meta.env.VITE_APP_URL || 'http://localhost:8000'
        const response = await fetch(`${apiUrl}/api/classes`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        if (response.ok) {
          aircrafts.value = await response.json()
        }
      } catch (err) {
        console.error('Error fetching aircrafts:', err)
      }
    }

    const fetchCategories = async () => {
      try {
        const apiUrl = import.meta.env.VITE_APP_URL || 'http://localhost:8000'
        let url = `${apiUrl}/api/categories`
        if (enrollmentForm.value.aircraft_id) {
          url += `?aircraft_id=${enrollmentForm.value.aircraft_id}`
        }

        const response = await fetch(url, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        if (response.ok) {
          categories.value = await response.json()
        }
      } catch (err) {
        console.error('Error fetching categories:', err)
      }
    }

    const fetchInstructors = async () => {
      try {
        // Get list of all users
        const response = await authStore.getApi().post('/user/list');
        // Filter users with Instructor/Teacher roles
        instructors.value = (response.data || []).filter(user => 
          user.role === 'Инструктор' || 
          user.role === 'Преподаватель' || 
          user.role === 'Teacher'
        );
        console.log('Instructors loaded:', instructors.value);
      } catch (err) {
        console.error('Error fetching instructors:', err);
        showNotification('Ошибка при загрузке списка инструкторов', 'error');
      }
    }

    const clearFilters = () => {
      search.value = ''
      groupFilter.value = null
      courseFilter.value = null
    }

    const getGroupName = (groupId) => {
      const group = groups.value.find(g => g.id === groupId)
      return group ? group.groupname : 'Неизвестная группа'
    }

    const getCourseName = (courseId) => {
      const course = courses.value.find(c => c.id === courseId)
      return course ? course.title : 'Неизвестный курс'
    }

    const getStatusText = (status) => {
      const statusMap = {
        'active': 'Активна',
        'completed': 'Завершена',
        'paused': 'Приостановлена',
        'canceled': 'Отменена'
      }
      return statusMap[status] || 'Неизвестно'
    }

    const getStatusColor = (status) => {
      const colorMap = {
        'active': 'success',
        'completed': 'info',
        'paused': 'warning',
        'canceled': 'error'
      }
      return colorMap[status] || 'grey'
    }

    const getProgressColor = (progress) => {
      if (progress < 30) return 'error'
      if (progress < 70) return 'warning'
      return 'success'
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'Н/Д'
      try {
        return format(new Date(dateString), 'dd MMMM yyyy, HH:mm', { locale: ru })
      } catch (e) {
        return dateString
      }
    }

    const getInitials = (name) => {
      if (!name) return 'Н/Д'
      
      return name.split(' ')
        .map(part => part.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('')
    }

    const showNotification = (text, color = 'success') => {
      snackbar.value = {
        show: true,
        color,
        text
      }
    }

    const editEnrollment = (enrollment) => {
      router.push({ name: 'edit-enrollment', params: { id: enrollment.id } });
    }

    // Initialization
    onMounted(async () => {
      console.log('GroupCourses component mounted')
      try {
        console.log('Loading groups...')
        await fetchGroups()
        console.log('Groups loaded:', groups.value)
        
        console.log('Loading courses...')
        await fetchCourses()
        console.log('Courses loaded:', courses.value)
        
        console.log('Loading enrollments...')
        await fetchEnrollments()
        console.log('Enrollments loaded:', enrollments.value)
        
        console.log('Loading instructors...')
        await fetchInstructors()
        console.log('Instructors loaded:', instructors.value)
      } catch (error) {
        console.error('Error loading data:', error)
      }
    })

    // Return all reactive properties and methods
    return {
      enrollments,
      groups,
      courses,
      aircrafts,
      categories,
      instructors,
      lessonTypeOptions,
      loading,
      error,
      saving,
      deleting,
      search,
      groupFilter,
      courseFilter,
      enrollmentDialog,
      viewDialog,
      progressDialog,
      deleteDialog,
      enrollmentFormValid,
      selectedEnrollment,
      enrollmentToDelete,
      snackbar,
      enrollmentForm,
      headers,
      statusOptions,
      filteredEnrollments,
      groupItems,
      courseItems,
      aircraftItems,
      filteredCategoryItems,
      filteredCourseItems,
      progressByStudents,
      isAdmin,
      fetchEnrollments,
      openAddEnrollmentDialog,
      viewEnrollment,
      viewProgress,
      confirmDelete,
      saveEnrollment,
      deleteEnrollment,
      fetchGroups,
      fetchCourses,
      fetchAircrafts,
      fetchCategories,
      fetchInstructors,
      clearFilters,
      getGroupName,
      getCourseName,
      getStatusText,
      getStatusColor,
      getProgressColor,
      formatDate,
      getInitials,
      showNotification,
      enrollmentStore,
      editEnrollment,
      onAircraftChange,
      onCategoryChange
    }
  }
}
</script>

<style scoped>
.v-data-table :deep(th) {
  background-color: #f5f5f5 !important;
  color: rgba(0, 0, 0, 0.87) !important;
  font-weight: bold !important;
}
</style> 