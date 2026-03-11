<template>
  <v-navigation-drawer
    v-model="drawer"
    :rail="rail"
    permanent
    @click="rail = false"
    class="app-sidebar"
    :width="280"
  >
    <!-- Logo Section -->
    <div class="sidebar-header" @click="rail = false">
      <div class="logo-container">
        <v-avatar size="42" color="primary" class="logo-avatar">
          <v-icon size="24" color="white">mdi-school</v-icon>
        </v-avatar>
        <div v-if="!rail" class="logo-text">
          <span class="app-title">LMS</span>
          <span class="app-subtitle">Обучение</span>
        </div>
      </div>
      <v-btn
        v-if="!rail"
        variant="text"
        size="small"
        :icon="rail ? 'mdi-chevron-right' : 'mdi-chevron-left'"
        @click.stop="rail = !rail"
        class="collapse-btn"
      ></v-btn>
    </div>

    <v-divider class="my-2"></v-divider>

    <!-- Main Navigation -->
    <v-list density="compact" nav class="nav-list">
      <!-- ГЛАВНАЯ -->
      <v-list-item
        prepend-icon="mdi-view-dashboard-outline"
        title="Главная"
        to="/dashboard"
        :active="route.path === '/dashboard'"
        rounded="lg"
        class="nav-item"
      ></v-list-item>

      <!-- ОБУЧЕНИЕ -->
      <div class="nav-section">
        <div v-if="!rail" class="section-title">Обучение</div>

        <v-list-item
          prepend-icon="mdi-bookshelf"
          title="Мои курсы"
          to="/courses/my"
          :active="route.path === '/courses/my'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-calendar-outline"
          title="Расписание"
          to="/schedule"
          :active="route.path === '/schedule'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-chart-timeline-variant"
          title="Прогресс"
          to="/learning-progress"
          :active="route.path === '/learning-progress'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-clipboard-check-outline"
          title="Задания"
          to="/assignments"
          :active="route.path === '/assignments'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>
      </div>

      <!-- УПРАВЛЕНИЕ (для админов) -->
      <div class="nav-section" v-if="isAdmin">
        <div v-if="!rail" class="section-title">Администрирование</div>

        <v-list-item
          prepend-icon="mdi-account-multiple-outline"
          title="Пользователи"
          to="/users"
          :active="route.path === '/users'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-account-group-outline"
          title="Группы"
          to="/groups"
          :active="route.path === '/groups'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-book-account-outline"
          title="Запись на курсы"
          to="/group-courses"
          :active="route.path === '/group-courses'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-book-open-outline"
          title="Все курсы"
          to="/courses"
          :active="route.path === '/courses'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-cloud-upload"
          title="Импорт курсов"
          to="/import-courses"
          :active="route.path === '/import-courses'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-airplane"
          title="Классы"
          to="/classes"
          :active="route.path === '/classes'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>
      </div>

      <!-- ОТЧЁТЫ -->
      <div class="nav-section" v-if="isAdmin">
        <v-list-item
          prepend-icon="mdi-chart-box-outline"
          title="Отчёты"
          to="/reports/analytics"
          :active="route.path.includes('/reports')"
          rounded="lg"
          class="nav-item"
        ></v-list-item>
      </div>

      <!-- СООБЩЕНИЯ -->
      <div class="nav-section">
        <v-list-item
          prepend-icon="mdi-message-outline"
          title="Сообщения"
          to="/messages"
          :active="route.path === '/messages'"
          rounded="lg"
          class="nav-item"
        >
          <template v-slot:append v-if="unreadMessages > 0">
            <v-badge
              :content="unreadMessages"
              color="error"
              inline
            ></v-badge>
          </template>
        </v-list-item>
      </div>

      <!-- НАСТРОЙКИ -->
      <div class="nav-section">
        <div v-if="!rail" class="section-title">Личное</div>

        <v-list-item
          prepend-icon="mdi-account-cog-outline"
          title="Профиль"
          to="/settings"
          :active="route.path === '/settings'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-information-outline"
          title="О системе"
          to="/about"
          :active="route.path === '/about'"
          rounded="lg"
          class="nav-item"
        ></v-list-item>
      </div>
    </v-list>

  </v-navigation-drawer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({
  drawer: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['update:drawer'])

const route = useRoute()
const authStore = useAuthStore()

const drawer = computed({
  get: () => true,
  set: (value) => emit('update:drawer', value)
})

const rail = ref(false)
const unreadMessages = ref(0)

const user = computed(() => authStore.user)
const isAdmin = computed(() => authStore.isAdmin)
</script>

<style scoped>
.app-sidebar {
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
  border-right: none !important;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 12px;
  background: rgba(255, 255, 255, 0.03);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.app-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.5px;
}

.app-subtitle {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.collapse-btn {
  color: rgba(255, 255, 255, 0.7) !important;
}

.nav-list {
  padding: 8px 12px;
}

.nav-section {
  margin-bottom: 16px;
}

.section-title {
  font-size: 0.7rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.4);
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 8px 12px 4px;
}

.nav-item {
  margin-bottom: 2px;
  color: rgba(255, 255, 255, 0.75) !important;
  transition: all 0.2s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.08) !important;
  color: white !important;
  transform: translateX(4px);
}

.nav-item.v-list-item--active {
  background: linear-gradient(90deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.1) 100%) !important;
  color: white !important;
  border-left: 3px solid #667eea;
}

.nav-item.v-list-item--active::before {
  opacity: 0;
}

/* Custom scrollbar */
.app-sidebar::-webkit-scrollbar {
  width: 6px;
}

.app-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.app-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
}

.app-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Rail mode styles */
:deep(.v-navigation-drawer--rail) {
  width: 72px !important;
}

:deep(.v-navigation-drawer--rail .sidebar-header) {
  justify-content: center;
  padding: 16px 8px;
}

:deep(.v-navigation-drawer--rail .logo-container) {
  justify-content: center;
}
</style>
