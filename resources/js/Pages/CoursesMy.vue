<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title class="text-h5">
            Мои курсы
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-card class="mb-4" color="primary" theme="dark">
                  <v-card-text>
                    <div class="text-h6">Активные курсы</div>
                    <div class="text-h4">{{ activeCourses.length }}</div>
                  </v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" md="4">
                <v-card class="mb-4" color="secondary" theme="dark">
                  <v-card-text>
                    <div class="text-h6">Всего курсов</div>
                    <div class="text-h4">{{ courses.length }}</div>
                  </v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" md="4">
                <v-card class="mb-4" color="success" theme="dark">
                  <v-card-text>
                    <div class="text-h6">Общий прогресс</div>
                    <div class="text-h4">{{ totalProgress }}%</div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>

            <v-data-table
              :headers="headers"
              :items="courses"
              :loading="loading"
              class="elevation-1"
            >
              <template v-slot:item.progress="{ item }">
                <v-progress-linear
                  :model-value="item.progress"
                  color="primary"
                  height="20"
                >
                  <template v-slot:default="{ value }">
                    <strong>{{ Math.ceil(value) }}%</strong>
                  </template>
                </v-progress-linear>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-btn
                  color="primary"
                  variant="text"
                  :to="`/courses/${item.id}`"
                >
                  Продолжить
                </v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const loading = ref(false)
const courses = ref([])

const headers = [
  { title: 'Название', key: 'name' },
  { title: 'Преподаватель', key: 'teacher' },
  { title: 'Дата начала', key: 'start_date' },
  { title: 'Дата окончания', key: 'end_date' },
  { title: 'Прогресс', key: 'progress' },
  { title: 'Действия', key: 'actions', sortable: false }
]

const activeCourses = computed(() => {
  return courses.value.filter(course => course.status === 'active')
})

const totalProgress = computed(() => {
  if (courses.value.length === 0) return 0
  const total = courses.value.reduce((sum, course) => sum + course.progress, 0)
  return Math.round(total / courses.value.length)
})

async function fetchCourses() {
  loading.value = true
  try {
    const response = await axios.get('/api/courses/my')
    courses.value = response.data
  } catch (error) {
    console.error('Error fetching courses:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCourses()
})
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}

.v-data-table {
  border-radius: 8px;
}

.v-progress-linear {
  border-radius: 4px;
}
</style> 