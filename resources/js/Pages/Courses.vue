<template>
  <v-container fluid class="courses-page">
    <v-row>
      <!-- Left Sidebar - Classes and Categories -->
      <v-col cols="12" md="3">
        <v-card class="mb-4">
          <v-card-title class="bg-primary text-white">
            <v-icon class="mr-2">mdi-airplane</v-icon>
            Классы
          </v-card-title>
          <v-card-text>
            <div v-if="loadingClasses" class="text-center py-4">
              <v-progress-circular indeterminate color="primary"></v-progress-circular>
            </div>
            <div v-else>
              <v-chip
                v-for="aircraft in aircrafts"
                :key="aircraft.id"
                :color="selectedAircraft === aircraft.id ? 'primary' : 'default'"
                :variant="selectedAircraft === aircraft.id ? 'flat' : 'outlined'"
                class="ma-1"
                @click="selectAircraft(aircraft.id)"
              >
                {{ aircraft.path }}
              </v-chip>
              <v-alert v-if="!aircrafts.length" type="info" density="compact" class="mt-2">
                Нет классов в базе данных
              </v-alert>
            </div>
          </v-card-text>
        </v-card>

        <v-card>
          <v-card-title class="bg-secondary text-white">
            <v-icon class="mr-2">mdi-book-outline</v-icon>
            Специальности
          </v-card-title>
          <v-card-text>
            <v-list density="compact" v-if="filteredCategories.length">
              <v-list-item
                :class="{ 'bg-primary-lighten-5': !selectedCategory }"
                @click="selectCategory(null)"
              >
                <v-list-item-title>Все специальности</v-list-item-title>
              </v-list-item>
              <v-list-item
                v-for="category in filteredCategories"
                :key="category.id"
                :class="{ 'bg-primary-lighten-5': selectedCategory === category.id }"
                @click="selectCategory(category.id)"
              >
                <v-list-item-title>{{ category.title }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip size="x-small">{{ category.courses_count || 0 }}</v-chip>
                </template>
              </v-list-item>
            </v-list>
            <v-alert v-else type="info" density="compact">
              Выберите класс для отображения специальностей
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Main Content - Courses -->
      <v-col cols="12" md="9">
        <!-- Search and Filter Section -->
        <v-card class="mb-4">
          <v-card-text>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="search"
                  prepend-inner-icon="mdi-magnify"
                  label="Поиск курсов"
                  single-line
                  hide-details
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="d-flex align-center">
                <v-switch
                  v-model="showVisibleOnly"
                  label="Показать только видимые"
                  hide-details
                  density="compact"
                  color="primary"
                ></v-switch>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Courses Grid -->
        <v-row v-if="loadingCourses">
          <v-col cols="12" class="text-center py-10">
            <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
          </v-col>
        </v-row>

        <v-row v-else-if="filteredCourses.length">
          <v-col
            v-for="course in filteredCourses"
            :key="course.id"
            cols="12"
            sm="6"
            md="4"
            class="mb-4"
          >
            <v-card class="h-100 d-flex flex-column">
              <v-img
                :src="course.image || placeholderImage"
                height="140"
                cover
                class="flex-grow-0"
              >
                <template v-slot:placeholder>
                  <v-row class="fill-height ma-0" align="center" justify="center">
                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                  </v-row>
                </template>
              </v-img>

              <v-card-title class="text-body-1 font-weight-bold pb-0">
                {{ course.title }}
              </v-card-title>

              <v-card-subtitle class="pb-2">
                {{ getCategoryName(course) }}
              </v-card-subtitle>

              <v-card-text class="flex-grow-1">
                <p class="text-body-2 text-truncate-2">
                  {{ course.short_description || course.description || 'Нет описания' }}
                </p>
              </v-card-text>

              <v-divider></v-divider>

              <v-card-actions class="pa-2">
                <v-chip
                  :color="course.visible ? 'success' : 'grey'"
                  size="small"
                  class="mr-2"
                >
                  {{ course.visible ? 'Видимый' : 'Скрытый' }}
                </v-chip>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  variant="tonal"
                  size="small"
                  @click.stop="openCourseLearning(course)"
                  prepend-icon="mdi-school"
                >
                  Обучение
                </v-btn>
                <v-btn
                  icon
                  size="small"
                  color="primary"
                  @click.stop="openCourseManifest(course)"
                  class="ml-1"
                >
                  <v-icon>mdi-open-in-new</v-icon>
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>

        <v-row v-else>
          <v-col cols="12" class="text-center py-10">
            <v-icon size="64" color="grey">mdi-book-off-outline</v-icon>
            <p class="text-h6 text-grey mt-4">Курсы не найдены</p>
            <p class="text-body-2 text-grey">
              Выберите класс и специальность или измените параметры поиска
            </p>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Snackbar for notifications -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
      {{ snackbar.text }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const apiUrl = import.meta.env.VITE_APP_URL || 'http://localhost:8000'

// Reactive state
const loading = ref(false)
const loadingClasses = ref(false)
const loadingCourses = ref(false)
const search = ref('')
const showVisibleOnly = ref(true)
const aircrafts = ref([])
const categories = ref([])
const courses = ref([])
const selectedAircraft = ref(null)
const selectedCategory = ref(null)
const snackbar = ref({ show: false, color: 'success', text: '' })

const placeholderImage = 'https://via.placeholder.com/300x140?text=Курс'

// Computed properties
const filteredCategories = computed(() => {
  if (!selectedAircraft.value) return []
  return categories.value.filter(cat => cat.aircraft_id === selectedAircraft.value)
})

const filteredCourses = computed(() => {
  let result = courses.value

  // Filter by aircraft
  if (selectedAircraft.value) {
    result = result.filter(course => course.aircraft_id === selectedAircraft.value)
  }

  // Filter by category
  if (selectedCategory.value) {
    result = result.filter(course => {
      if (!course.categories) return false
      return course.categories.some(cat => cat.id === selectedCategory.value)
    })
  }

  // Filter by search
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(course =>
      course.title.toLowerCase().includes(searchLower) ||
      (course.description && course.description.toLowerCase().includes(searchLower))
    )
  }

  // Filter by visibility
  if (showVisibleOnly.value) {
    result = result.filter(course => course.visible === 1 || course.visible === true)
  }

  return result
})

const isAdmin = computed(() => authStore.isAdmin)

// Methods
const showNotification = (text, color = 'success') => {
  snackbar.value = { show: true, color, text }
}

const getCategoryName = (course) => {
  if (course.categories && course.categories.length > 0) {
    return course.categories.map(cat => cat.title).join(', ')
  }
  return 'Без категории'
}

const fetchAircrafts = async () => {
  loadingClasses.value = true
  try {
    const response = await fetch(`${apiUrl}/api/classes`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })
    if (response.ok) {
      aircrafts.value = await response.json()
    }
  } catch (error) {
    console.error('Error fetching aircrafts:', error)
  } finally {
    loadingClasses.value = false
  }
}

const fetchCategories = async () => {
  try {
    let url = `${apiUrl}/api/categories`
    if (selectedAircraft.value) {
      url += `?aircraft_id=${selectedAircraft.value}`
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
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const fetchCourses = async () => {
  loadingCourses.value = true
  try {
    let url = `${apiUrl}/api/courses`
    if (selectedAircraft.value) {
      url += `?aircraft_id=${selectedAircraft.value}`
    }
    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })
    if (response.ok) {
      courses.value = await response.json()
    }
  } catch (error) {
    console.error('Error fetching courses:', error)
  } finally {
    loadingCourses.value = false
  }
}

const selectAircraft = async (aircraftId) => {
  if (selectedAircraft.value === aircraftId) {
    selectedAircraft.value = null
  } else {
    selectedAircraft.value = aircraftId
  }
  selectedCategory.value = null
  await Promise.all([
    fetchCategories(),
    fetchCourses()
  ])
}

const selectCategory = async (categoryId) => {
  selectedCategory.value = categoryId
}

const openCourseManifest = (course) => {
  window.open(`/courses/itemmani?idEdit=${course.id}`, '_blank')
}

const openCourseLearning = (course) => {
  const idCategory = selectedCategory.value || course?.categories?.[0]?.id
  router.push({
    name: 'courses.learn',
    params: { id: course.id },
    query: { idCategory }
  })
}

// Initial load
onMounted(async () => {
  await Promise.all([
    fetchAircrafts(),
    fetchCategories(),
    fetchCourses()
  ])
})
</script>

<style scoped>
.courses-page {
  padding: 16px;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.v-card {
  border-radius: 8px;
  transition: transform 0.2s;
}

.v-card:hover {
  transform: translateY(-2px);
}

.bg-primary-lighten-5 {
  background-color: rgba(var(--v-theme-primary-rgb), 0.1);
}
</style>
