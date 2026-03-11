<template>
  <v-container fluid class="my-courses-container">
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 mb-4">Мои курсы</h1>
      </v-col>
    </v-row>

    <v-row v-if="loading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
      </v-col>
    </v-row>

    <v-row v-else-if="error">
      <v-col cols="12">
        <v-alert type="error" dismissible>{{ error }}</v-alert>
      </v-col>
    </v-row>

    <v-row v-else-if="userCourses.length === 0">
      <v-col cols="12">
        <v-alert type="info">
          У вас пока нет записанных курсов. Обратитесь к администратору для записи на курсы.
        </v-alert>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12" mb-2 class="d-flex align-center">
        <v-select
          v-model="selectedAircraft"
          :items="aircraftList"
          label="Фильтр по классу"
          clearable
          variant="outlined"
          density="compact"
          class="mr-4"
          style="max-width: 300px;"
        ></v-select>
        
        <v-switch
          v-model="showAllStatuses"
          label="Показать ожидающие и завершённые"
          color="primary"
          density="compact"
          hide-details
        ></v-switch>
        
        <v-spacer></v-spacer>
        
        <v-chip
          v-if="!showAllStatuses"
          color="info"
          size="small"
          variant="tonal"
        >
          Показано активных: {{ activeCoursesCount }} из {{ userCourses.length }}
        </v-chip>
      </v-col>
    </v-row>

    <v-row v-for="aircraftGroup in filteredCourses" :key="aircraftGroup.aircraft">
      <v-col cols="12">
        <v-card class="mb-4">
          <v-card-title class="bg-primary text-white">
            <v-icon class="mr-2">mdi-airplane</v-icon>
            {{ aircraftGroup.aircraft }}
          </v-card-title>
          
          <v-card-text>
            <v-expansion-panels>
              <v-expansion-panel
                v-for="courseItem in aircraftGroup.courses"
                :key="courseItem.course.id"
              >
                <v-expansion-panel-title>
                  <div class="d-flex align-center justify-space-between width-100">
                    <div>
                      <span class="text-h6">{{ courseItem.course.title }}</span>
                      <v-chip
                        v-if="courseItem.category"
                        color="secondary"
                        size="small"
                        class="ml-2"
                      >
                        {{ courseItem.category.title }}
                      </v-chip>
                    </div>
                    <div class="d-flex align-center">
                      <v-chip
                        v-if="courseItem.typeOfLesson"
                        size="small"
                        class="mr-2"
                      >
                        {{ courseItem.typeOfLesson }}
                      </v-chip>
                      <v-chip
                        size="small"
                        :color="getStatusColor(courseItem.enrollment_status)"
                        class="mr-2"
                      >
                        {{ getStatusText(courseItem.enrollment_status) }}
                      </v-chip>
                      <v-chip
                        v-if="courseItem.study_from"
                        size="small"
                        :color="isCourseActive(courseItem) ? 'success' : 'grey'"
                      >
                        {{ formatDate(courseItem.study_from) }} - {{ formatDate(courseItem.study_to) }}
                      </v-chip>
                    </div>
                  </div>
                </v-expansion-panel-title>
                
                <v-expansion-panel-text>
                  <v-alert
                    v-if="courseItem.enrollment_status === 'pending'"
                    type="info"
                    variant="tonal"
                    density="compact"
                    class="mb-3"
                  >
                    Курс начнётся {{ formatDate(courseItem.study_from) }}
                  </v-alert>
                  
                  <v-alert
                    v-if="courseItem.enrollment_status === 'completed'"
                    type="success"
                    variant="tonal"
                    density="compact"
                    class="mb-3"
                  >
                    Курс завершён {{ formatDate(courseItem.study_to) }}
                  </v-alert>
                  
                  <div v-if="courseItem.course.short_description" class="mb-3">
                    {{ courseItem.course.short_description }}
                  </div>
                  
                  <v-treeview
                    v-if="courseItem.aukstructures && courseItem.aukstructures.length"
                    :items="buildTree(courseItem.aukstructures, courseItem.category_id)"
                    activatable
                    item-key="id"
                    item-title="title"
                    item-children="children"
                  >
                    <template v-slot:prepend="{ item }">
                      <v-icon>
                        {{ getItemIcon(item.type) }}
                      </v-icon>
                    </template>
                    
                    <template v-slot:append="{ item }">
                      <v-btn
                        v-if="item.id"
                        icon="mdi-open-in-new"
                        size="small"
                        variant="text"
                        :disabled="!courseItem.is_accessible"
                        @click.stop="openCourse(courseItem.course.hash, item.id)"
                      ></v-btn>
                    </template>
                  </v-treeview>
                  
                  <div v-else class="text-grey">
                    Структура курса загружается...
                  </div>
                  
                  <v-btn
                    v-if="courseItem.is_accessible"
                    color="primary"
                    class="mt-3"
                    variant="tonal"
                    @click="openCourse(courseItem.course.hash)"
                  >
                    <v-icon start>mdi-play</v-icon>
                    Начать обучение
                  </v-btn>
                  
                  <v-btn
                    v-else-if="courseItem.enrollment_status === 'pending'"
                    color="primary"
                    class="mt-3"
                    variant="tonal"
                    disabled
                  >
                    <v-icon start>mdi-clock-outline</v-icon>
                    Курс скоро будет доступен
                  </v-btn>
                  
                  <v-btn
                    v-else-if="courseItem.enrollment_status === 'completed'"
                    color="success"
                    class="mt-3"
                    variant="tonal"
                    @click="openCourse(courseItem.course.hash)"
                  >
                    <v-icon start>mdi-refresh</v-icon>
                    Повторить материалы
                  </v-btn>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useCourseStore } from '../stores/courseStore';

const apiUrl = import.meta.env.VITE_APP_URL || 'http://localhost:8000';

export default {
  name: 'MyCourses',
  
  data() {
    return {
      loading: true,
      error: null,
      userCourses: [],
      selectedAircraft: null,
      showAllStatuses: false,
    };
  },
  
  computed: {
    ...mapState(useCourseStore, ['aircrafts']),
    
    aircraftList() {
      const aircrafts = new Set();
      this.userCourses.forEach(item => {
        if (item.course.aircraft) {
          aircrafts.add(item.course.aircraft.path);
        }
      });
      return Array.from(aircrafts);
    },
    
    activeCoursesCount() {
      return this.userCourses.filter(item => item.is_accessible).length;
    },
    
    filteredCourses() {
      let courses = this.userCourses;
      
      if (!this.showAllStatuses) {
        courses = courses.filter(item => item.is_accessible);
      }
      
      if (this.selectedAircraft) {
        courses = courses.filter(item => 
          item.course.aircraft && item.course.aircraft.path === this.selectedAircraft
        );
      }
      
      const grouped = {};
      courses.forEach(item => {
        const aircraft = item.course.aircraft?.path || 'Другие';
        if (!grouped[aircraft]) {
          grouped[aircraft] = {
            aircraft: aircraft,
            courses: []
          };
        }
        grouped[aircraft].courses.push(item);
      });
      
      return Object.values(grouped);
    }
  },
  
  async mounted() {
    await this.fetchUserCourses();
  },
  
  methods: {
    ...mapActions(useCourseStore, ['fetchUserCoursesAction']),
    
    async fetchUserCourses() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await fetch(`${apiUrl}/api/my-courses?include_all=${this.showAllStatuses}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error('Не удалось загрузить курсы');
        }
        
        this.userCourses = await response.json();
        
        for (const item of this.userCourses) {
          if (item.course && item.course.id) {
            await this.loadAukstructures(item);
          }
        }
      } catch (err) {
        this.error = err.message;
        console.error('Ошибка загрузки курсов:', err);
      } finally {
        this.loading = false;
      }
    },
    
    async loadAukstructures(item) {
      try {
        const response = await fetch(
          `${apiUrl}/api/course/${item.course.id}/aukstructures`,
          {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
          }
        );
        
        if (response.ok) {
          const aukstructures = await response.json();
          item.aukstructures = aukstructures;
        }
      } catch (err) {
        console.error('Ошибка загрузки структуры курса:', err);
      }
    },
    
    buildTree(aukstructures, categoryId) {
      if (!aukstructures || aukstructures.length === 0) {
        return [];
      }
      
      const filtered = aukstructures.filter(auk => {
        if (!auk.categories) return true;
        const cats = auk.categories.split(',').map(c => c.trim());
        return cats.includes(String(categoryId));
      });
      
      const map = {};
      const roots = [];
      
      filtered.forEach(item => {
        map[item.id] = { ...item, children: [] };
      });
      
      filtered.forEach(item => {
        if (item.parent_id && map[item.parent_id]) {
          map[item.parent_id].children.push(map[item.id]);
        } else {
          roots.push(map[item.id]);
        }
      });
      
      return roots;
    },
    
    getItemIcon(type) {
      const icons = {
        0: 'mdi-book',
        1: 'mdi-folder',
        2: 'mdi-file-document',
        3: 'mdi-book-open-page-variant'
      };
      return icons[type] || 'mdi-file';
    },
    
    openCourse(hash, aukstructureId = null) {
      const baseUrl = `${apiUrl}/api/private/${hash}/index.html`;
      window.open(baseUrl, '_blank');
    },
    
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('ru-RU');
    },
    
    isCourseActive(course) {
      return course.is_accessible === true;
    },
    
    getStatusColor(status) {
      const colors = {
        'active': 'success',
        'pending': 'warning',
        'completed': 'info',
        'paused': 'grey',
        'canceled': 'error'
      };
      return colors[status] || 'grey';
    },
    
    getStatusText(status) {
      const texts = {
        'active': 'Активен',
        'pending': 'Ожидает',
        'completed': 'Завершён',
        'paused': 'Приостановлен',
        'canceled': 'Отменён'
      };
      return texts[status] || status;
    }
  }
};
</script>

<style scoped>
.my-courses-container {
  padding: 20px;
}

.width-100 {
  width: 100%;
}
</style>
