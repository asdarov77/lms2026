<template>
  <app-layout>
    <v-container fluid>
      <!-- Header -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <h1 class="text-h4 font-weight-bold primary--text">Прогресс обучения</h1>
        </v-col>
      </v-row>

      <!-- Filters -->
      <v-card class="mb-4 pa-3">
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="groupFilter"
              :items="groups"
              item-title="groupname"
              item-value="id"
              label="Группа"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              class="mb-2"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="courseFilter"
              :items="courses"
              item-title="title"
              item-value="id"
              label="Курс"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              class="mb-2"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="periodFilter"
              :items="periodOptions"
              item-title="title"
              item-value="value"
              label="Период"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
            ></v-select>
          </v-col>
        </v-row>
      </v-card>

      <!-- Loading State -->
      <loading-state v-if="loading" />

      <!-- Error State -->
      <error-display 
        v-else-if="error" 
        title="Ошибка загрузки данных прогресса" 
        :message="errorMessage"
        :error="error"
        :loading="retryLoading"
        @retry="fetchProgressData"
      />

      <!-- Progress Summary Card -->
      <template v-else>
        <v-card class="mb-4">
          <v-card-title class="d-flex align-center">
            <v-icon color="primary" class="mr-2">mdi-chart-line</v-icon>
            Сводка по прогрессу обучения
          </v-card-title>
          
          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-card class="pa-3" variant="outlined" color="primary">
                  <div class="text-center">
                    <div class="text-subtitle-1 font-weight-bold mb-1">Общий прогресс</div>
                    <div class="d-flex justify-center align-center">
                      <v-progress-circular
                        :model-value="overallProgress"
                        :size="100"
                        :width="12"
                        :color="getProgressColor(overallProgress)"
                      >
                        <strong>{{ overallProgress }}%</strong>
                      </v-progress-circular>
                    </div>
                  </div>
                </v-card>
              </v-col>
              <v-col cols="12" md="4">
                <v-card class="pa-3" variant="outlined">
                  <div class="text-subtitle-1 font-weight-bold mb-3">Активность по дням недели</div>
                  <v-progress-linear
                    v-for="(day, index) in weekdayActivity"
                    :key="index"
                    :model-value="day.value"
                    :color="day.color"
                    height="20"
                    class="mb-2"
                  >
                    <template v-slot:default>
                      <strong>{{ day.name }}</strong>
                    </template>
                  </v-progress-linear>
                </v-card>
              </v-col>
              <v-col cols="12" md="4">
                <v-card class="pa-3" variant="outlined">
                  <div class="text-center mb-2">
                    <div class="text-subtitle-1 font-weight-bold">Статистика</div>
                  </div>
                  <v-list density="compact">
                    <v-list-item>
                      <template v-slot:prepend>
                        <v-icon color="green">mdi-account-check</v-icon>
                      </template>
                      <v-list-item-title>{{ completedCount }} студентов завершили</v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                      <template v-slot:prepend>
                        <v-icon color="orange">mdi-account-clock</v-icon>
                      </template>
                      <v-list-item-title>{{ inProgressCount }} студентов в процессе</v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                      <template v-slot:prepend>
                        <v-icon color="red">mdi-account-alert</v-icon>
                      </template>
                      <v-list-item-title>{{ notStartedCount }} студентов не начали</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Progress Details -->
        <v-card>
          <v-tabs v-model="activeTab">
            <v-tab value="groups">По группам</v-tab>
            <v-tab value="students">По студентам</v-tab>
            <v-tab value="courses">По курсам</v-tab>
          </v-tabs>

          <v-card-text>
            <v-window v-model="activeTab">
              <!-- Groups Progress Tab -->
              <v-window-item value="groups">
                <v-data-table
                  :headers="groupHeaders"
                  :items="groupsProgress"
                  :loading="loading"
                >
                  <template v-slot:item.progress="{ item }">
                    <v-progress-linear
                      :model-value="item.progress"
                      :color="getProgressColor(item.progress)"
                      height="20"
                      striped
                    >
                      <template v-slot:default="{ value }">
                        <span class="text-caption font-weight-medium">{{ Math.ceil(value) }}%</span>
                      </template>
                    </v-progress-linear>
                  </template>

                  <template v-slot:item.actions="{ item }">
                    <v-btn
                      icon="mdi-eye"
                      size="small"
                      color="info"
                      variant="text"
                      @click="viewGroupProgress(item)"
                    ></v-btn>
                  </template>
                </v-data-table>
              </v-window-item>

              <!-- Students Progress Tab -->
              <v-window-item value="students">
                <v-data-table
                  :headers="studentHeaders"
                  :items="studentsProgress"
                  :loading="loading"
                >
                  <template v-slot:item.group="{ item }">
                    {{ getGroupName(item.group_id) }}
                  </template>

                  <template v-slot:item.progress="{ item }">
                    <v-progress-linear
                      :model-value="item.progress"
                      :color="getProgressColor(item.progress)"
                      height="20"
                      striped
                    >
                      <template v-slot:default="{ value }">
                        <span class="text-caption font-weight-medium">{{ Math.ceil(value) }}%</span>
                      </template>
                    </v-progress-linear>
                  </template>

                  <template v-slot:item.last_activity="{ item }">
                    {{ formatDate(item.last_activity) }}
                  </template>

                  <template v-slot:item.actions="{ item }">
                    <v-btn
                      icon="mdi-eye"
                      size="small"
                      color="info"
                      variant="text"
                      @click="viewStudentProgress(item)"
                    ></v-btn>
                  </template>
                </v-data-table>
              </v-window-item>

              <!-- Courses Progress Tab -->
              <v-window-item value="courses">
                <v-data-table
                  :headers="courseHeaders"
                  :items="coursesProgress"
                  :loading="loading"
                >
                  <template v-slot:item.category="{ item }">
                    {{ getCategoryName(item.category_id) }}
                  </template>

                  <template v-slot:item.progress="{ item }">
                    <v-progress-linear
                      :model-value="item.progress"
                      :color="getProgressColor(item.progress)"
                      height="20"
                      striped
                    >
                      <template v-slot:default="{ value }">
                        <span class="text-caption font-weight-medium">{{ Math.ceil(value) }}%</span>
                      </template>
                    </v-progress-linear>
                  </template>

                  <template v-slot:item.actions="{ item }">
                    <v-btn
                      icon="mdi-eye"
                      size="small"
                      color="info"
                      variant="text"
                      @click="viewCourseProgress(item)"
                    ></v-btn>
                  </template>
                </v-data-table>
              </v-window-item>
            </v-window>
          </v-card-text>
        </v-card>
      </template>

      <!-- Progress Details Dialog -->
      <v-dialog v-model="detailsDialog" max-width="800px">
        <v-card>
          <v-card-title class="bg-primary text-white py-3">
            {{ detailsTitle }}
          </v-card-title>
          <v-card-text class="py-4">
            <p v-if="!selectedDetails">Загрузка данных...</p>
            
            <template v-else>
              <!-- Render details based on selected item type -->
              <div v-if="detailsType === 'group'">
                <div class="d-flex justify-space-between align-center mb-4">
                  <h3 class="text-h6">{{ selectedDetails.groupname }}</h3>
                  <v-chip :color="getProgressColor(selectedDetails.progress)">
                    {{ selectedDetails.progress }}% завершено
                  </v-chip>
                </div>
                
                <v-divider class="mb-4"></v-divider>
                
                <h3 class="text-subtitle-1 font-weight-bold mb-3">Студенты группы</h3>
                <v-list>
                  <v-list-item v-for="student in selectedDetails.students" :key="student.id">
                    <template v-slot:prepend>
                      <v-avatar size="32" color="primary">
                        <span class="text-caption text-white">{{ getInitials(student.fio) }}</span>
                      </v-avatar>
                    </template>
                    <v-list-item-title>{{ student.fio }}</v-list-item-title>
                    <v-list-item-subtitle>
                      <v-progress-linear
                        :model-value="student.progress"
                        :color="getProgressColor(student.progress)"
                        height="8"
                        class="mt-2"
                      ></v-progress-linear>
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </div>
            </template>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              variant="text"
              @click="detailsDialog = false"
            >
              Закрыть
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </app-layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { format } from 'date-fns'
import { ru } from 'date-fns/locale'
import ErrorDisplay from '@/components/ErrorDisplay.vue'
import LoadingState from '@/components/LoadingState.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const authStore = useAuthStore()

// State
const loading = ref(true)
const groups = ref([])
const courses = ref([])
const categories = ref([])
const groupFilter = ref(null)
const courseFilter = ref(null)
const periodFilter = ref('month')
const activeTab = ref('groups')
const detailsDialog = ref(false)
const detailsTitle = ref('')
const detailsType = ref(null)
const selectedDetails = ref(null)
const error = ref(null)
const errorMessage = ref('Не удалось загрузить данные прогресса. Пожалуйста, попробуйте снова.')
const retryLoading = ref(false)

// Mock data (в реальном приложении данные будут загружаться с сервера)
const groupsProgress = ref([])
const studentsProgress = ref([])
const coursesProgress = ref([])

// Computed values
const overallProgress = computed(() => {
  if (!groupsProgress.value.length) return 0
  const total = groupsProgress.value.reduce((sum, group) => sum + group.progress, 0)
  return Math.round(total / groupsProgress.value.length)
})

const completedCount = computed(() => {
  return studentsProgress.value.filter(student => student.progress >= 100).length
})

const inProgressCount = computed(() => {
  return studentsProgress.value.filter(student => student.progress > 0 && student.progress < 100).length
})

const notStartedCount = computed(() => {
  return studentsProgress.value.filter(student => student.progress === 0).length
})

// Headers for tables
const groupHeaders = [
  { title: 'ID', key: 'id', align: 'start', width: '5%' },
  { title: 'Название группы', key: 'groupname', align: 'start', width: '30%' },
  { title: 'Кол-во студентов', key: 'student_count', align: 'start', width: '15%' },
  { title: 'Прогресс', key: 'progress', align: 'start', width: '30%' },
  { title: 'Действия', key: 'actions', align: 'center', sortable: false, width: '10%' }
]

const studentHeaders = [
  { title: 'ID', key: 'id', align: 'start', width: '5%' },
  { title: 'ФИО', key: 'fio', align: 'start', width: '25%' },
  { title: 'Группа', key: 'group', align: 'start', width: '15%' },
  { title: 'Прогресс', key: 'progress', align: 'start', width: '25%' },
  { title: 'Последняя активность', key: 'last_activity', align: 'start', width: '20%' },
  { title: 'Действия', key: 'actions', align: 'center', sortable: false, width: '10%' }
]

const courseHeaders = [
  { title: 'ID', key: 'id', align: 'start', width: '5%' },
  { title: 'Название курса', key: 'title', align: 'start', width: '30%' },
  { title: 'Категория', key: 'category', align: 'start', width: '15%' },
  { title: 'Прогресс', key: 'progress', align: 'start', width: '30%' },
  { title: 'Действия', key: 'actions', align: 'center', sortable: false, width: '10%' }
]

// Options
const periodOptions = [
  { title: 'Неделя', value: 'week' },
  { title: 'Месяц', value: 'month' },
  { title: 'Квартал', value: 'quarter' },
  { title: 'Год', value: 'year' }
]

// Mock data for weekly activity
const weekdayActivity = [
  { name: 'Понедельник', value: 75, color: 'primary' },
  { name: 'Вторник', value: 85, color: 'success' },
  { name: 'Среда', value: 60, color: 'info' },
  { name: 'Четверг', value: 90, color: 'warning' },
  { name: 'Пятница', value: 45, color: 'error' },
  { name: 'Суббота', value: 20, color: 'grey' },
  { name: 'Воскресенье', value: 15, color: 'grey-darken-1' }
]

// Methods
const fetchGroups = async () => {
  try {
    const response = await authStore.getApi().get('/groups')
    groups.value = response.data.data || []
    console.log('Groups loaded:', groups.value)
    
    // Generate mock data for groups progress
    groupsProgress.value = groups.value.map(group => ({
      ...group,
      progress: Math.floor(Math.random() * 100),
      student_count: group.users ? group.users.length : 0
    }))
  } catch (error) {
    console.error('Error fetching groups:', error)
  }
}

const fetchCourses = async () => {
  try {
    const response = await authStore.getApi().get('/courses')
    courses.value = response.data || []
    console.log('Courses loaded:', courses.value)
    
    // Generate mock data for courses progress
    coursesProgress.value = courses.value.map(course => ({
      ...course,
      progress: Math.floor(Math.random() * 100)
    }))
  } catch (error) {
    console.error('Error fetching courses:', error)
  }
}

const fetchStudents = async () => {
  try {
    const response = await authStore.getApi().post('/user/list')
    const students = response.data.filter(user => user.role !== 'Администратор')
    
    // Generate mock data for students progress
    studentsProgress.value = students.map(student => ({
      ...student,
      progress: Math.floor(Math.random() * 100),
      last_activity: getRandomDate()
    }))
    
    console.log('Students loaded:', studentsProgress.value)
  } catch (error) {
    console.error('Error fetching students:', error)
  }
}

const getGroupName = (groupId) => {
  const group = groups.value.find(g => g.id === groupId)
  return group ? group.groupname : 'Неизвестная группа'
}

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId)
  return category ? category.name : 'Неизвестная категория'
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

const getRandomDate = () => {
  const today = new Date()
  const pastDays = Math.floor(Math.random() * 30)
  const date = new Date()
  date.setDate(today.getDate() - pastDays)
  return date.toISOString()
}

const viewGroupProgress = (group) => {
  detailsType.value = 'group'
  detailsTitle.value = `Прогресс группы "${group.groupname}"`
  
  // Mock data for group details
  selectedDetails.value = {
    ...group,
    students: studentsProgress.value.filter(student => student.group_id === group.id)
  }
  
  detailsDialog.value = true
}

const viewStudentProgress = (student) => {
  detailsType.value = 'student'
  detailsTitle.value = `Прогресс студента "${student.fio}"`
  selectedDetails.value = student
  detailsDialog.value = true
}

const viewCourseProgress = (course) => {
  detailsType.value = 'course'
  detailsTitle.value = `Прогресс курса "${course.title}"`
  selectedDetails.value = course
  detailsDialog.value = true
}

const fetchProgressData = async () => {
  loading.value = true
  error.value = null
  retryLoading.value = false
  
  try {
    // In a real app, this would be an actual API call
    // For now, we'll just simulate the loading of data
    await Promise.all([
      fetchGroups(),
      fetchCourses(),
      fetchStudents()
    ])
    
    loading.value = false
  } catch (error) {
    console.error('Error fetching progress data:', error)
    error.value = error
    loading.value = false
  }
}

// Initialization
onMounted(async () => {
  loading.value = true
  try {
    await fetchProgressData()
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.v-card {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
  border-radius: 8px !important;
}

.v-progress-circular {
  margin: 1rem 0;
}
</style> 

<!-- 
CHANGES SUMMARY:
1. Fixed layout - now using AppLayout consistently with the rest of the application
2. Updated component paths to use @ aliases for better maintainability
3. Improved API fetching logic to use component methods consistently
4. Fixed import statements to reference the correct components
5. The component is now properly integrated with the application router at /learning-progress
--> 