<template>
  <v-container fluid>
      <!-- Welcome Section -->
      <v-row class="mb-4">
        <v-col cols="12">
          <v-card class="welcome-card">
            <v-card-text class="d-flex align-center">
              <v-avatar size="64" class="mr-4">
                <v-img :src="userAvatar" alt="User Avatar"></v-img>
              </v-avatar>
              <div>
                <h2 class="text-h4 mb-2">Добро пожаловать, {{ authStore.user.fio }}</h2>
                <p class="text-subtitle-1 text-medium-emphasis">Последний вход: {{ lastLogin }}</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Quick Stats -->
      <v-row>
        <v-col cols="12" sm="6" md="3">
          <v-card class="stat-card" color="primary">
            <v-card-text>
              <div class="d-flex align-center">
                <v-icon size="40" class="mr-3">mdi-account-group</v-icon>
                <div>
                  <div class="text-h6">Пользователи</div>
                  <div class="text-h4">{{ stats.totalUsers }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="stat-card" color="secondary">
            <v-card-text>
              <div class="d-flex align-center">
                <v-icon size="40" class="mr-3">mdi-book-open-variant</v-icon>
                <div>
                  <div class="text-h6">Курсы</div>
                  <div class="text-h4">{{ stats.totalCourses }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="stat-card" color="success">
            <v-card-text>
              <div class="d-flex align-center">
                <v-icon size="40" class="mr-3">mdi-account-multiple</v-icon>
                <div>
                  <div class="text-h6">Группы</div>
                  <div class="text-h4">{{ stats.totalGroups }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="stat-card" color="info">
            <v-card-text>
              <div class="d-flex align-center">
                <v-icon size="40" class="mr-3">mdi-clipboard-check</v-icon>
                <div>
                  <div class="text-h6">Задания</div>
                  <div class="text-h4">{{ stats.totalAssignments }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Recent Activity and Tasks -->
      <v-row class="mt-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-2">mdi-clock-outline</v-icon>
              Последние активности
            </v-card-title>
            <v-card-text>
              <v-timeline density="compact">
                <v-timeline-item
                  v-for="activity in recentActivities"
                  :key="activity.id"
                  :dot-color="activity.color"
                  size="small"
                >
                  <div class="d-flex justify-space-between">
                    <div>
                      <div class="text-subtitle-2">{{ activity.title }}</div>
                      <div class="text-caption">{{ activity.description }}</div>
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{ formatDate(activity.date) }}
                    </div>
                  </div>
                </v-timeline-item>
              </v-timeline>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-2">mdi-calendar-check</v-icon>
              Ближайшие дедлайны
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="deadline in upcomingDeadlines"
                  :key="deadline.id"
                  :title="deadline.title"
                  :subtitle="formatDate(deadline.date)"
                >
                  <template v-slot:prepend>
                    <v-icon :color="getDeadlineColor(deadline.date)">mdi-calendar</v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

export default {
  setup() {
    const authStore = useAuthStore()
    const loading = ref(false)

    // Mock data - replace with actual API calls
    const stats = ref({
      totalUsers: 0,
      totalCourses: 0,
      totalGroups: 0,
      totalAssignments: 0
    })

    const recentActivities = ref([
      {
        id: 1,
        title: 'Новый пользователь',
        description: 'Зарегистрирован новый пользователь',
        date: new Date(),
        color: 'primary'
      },
      {
        id: 2,
        title: 'Обновлен курс',
        description: 'Курс "Основы программирования" обновлен',
        date: new Date(Date.now() - 3600000),
        color: 'success'
      }
    ])

    const upcomingDeadlines = ref([
      {
        id: 1,
        title: 'Задание по математике',
        date: new Date(Date.now() + 86400000)
      },
      {
        id: 2,
        title: 'Тест по программированию',
        date: new Date(Date.now() + 172800000)
      }
    ])

    const userAvatar = ref('https://randomuser.me/api/portraits/men/78.jpg')
    const lastLogin = ref(new Date().toLocaleString('ru-RU'))

    const formatDate = (date) => {
      return new Date(date).toLocaleString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getDeadlineColor = (date) => {
      const daysUntil = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24))
      if (daysUntil <= 1) return 'error'
      if (daysUntil <= 3) return 'warning'
      return 'success'
    }

    const fetchDashboardData = async () => {
      loading.value = true;
      try {
        console.log('Fetching dashboard data...');
        const api = authStore.getApi();
        const response = await api.get('/dashboard');
        console.log('Dashboard response:', response.data);
        if (response.data) {
          stats.value = {
            totalUsers: response.data.stats?.totalUsers || 0,
            totalCourses: response.data.stats?.totalCourses || 0,
            totalGroups: response.data.stats?.totalGroups || 0,
            totalAssignments: response.data.stats?.totalAssignments || 0
          };
          recentActivities.value = response.data.activities || [];
          upcomingDeadlines.value = response.data.deadlines || [];
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        // Keep using mock data if API fails
        stats.value = {
          totalUsers: 0,
          totalCourses: 0,
          totalGroups: 0,
          totalAssignments: 0
        };
      } finally {
        loading.value = false;
      }
    };

    onMounted(async () => {
      try {
        // Fetch dashboard data from API
        await fetchDashboardData()
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
        // Keep using mock data if API fails
      }
    })

    return {
      authStore,
      stats,
      recentActivities,
      upcomingDeadlines,
      userAvatar,
      lastLogin,
      formatDate,
      getDeadlineColor
    }
  }
}
</script>

<style scoped>
.welcome-card {
  background: linear-gradient(135deg, var(--v-theme-primary) 0%, var(--v-theme-secondary) 100%);
  color: white;
}

.stat-card {
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.v-card {
  height: 100%;
}
</style> 