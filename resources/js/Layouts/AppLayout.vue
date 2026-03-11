<template>
  <v-app>
    <LeftSideMenu :drawer="drawer" @update:drawer="drawer = $event" />
    
    <v-app-bar
      color="primary"
      density="compact"
      class="app-bar"
      elevation="1"
    >
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      
      <v-toolbar-title>{{ pageTitle }}</v-toolbar-title>
      
      <v-spacer></v-spacer>
      
      <!-- Поиск -->
      <v-btn
        icon
        @click="searchDialog = true"
      >
        <v-icon>mdi-magnify</v-icon>
      </v-btn>
      
      <!-- Уведомления -->
      <v-badge
        :content="notifications.length"
        :model-value="notifications.length > 0"
        color="error"
        offset-x="5"
        offset-y="5"
      >
        <v-btn icon @click="notificationDrawer = true">
          <v-icon>mdi-bell-outline</v-icon>
        </v-btn>
      </v-badge>
      
      <!-- Выпадающее меню пользователя -->
      <v-menu
        location="bottom end"
        transition="slide-y-transition"
      >
        <template v-slot:activator="{ props }">
          <v-btn
            class="ml-2"
            v-bind="props"
            variant="text"
          >
            <v-avatar size="32" color="primary" class="mr-2" v-if="!user?.avatar">
              <span class="text-caption text-white">{{ userInitials }}</span>
            </v-avatar>
            <v-avatar size="32" class="mr-2" v-else>
              <v-img :src="user.avatar"></v-img>
            </v-avatar>
            <span class="d-none d-sm-inline">{{ userName }}</span>
            <v-icon>mdi-chevron-down</v-icon>
          </v-btn>
        </template>
        <v-list density="compact" width="200">
          <v-list-item to="/settings" prepend-icon="mdi-account-outline">
            <v-list-item-title>Профиль</v-list-item-title>
          </v-list-item>
          <v-list-item to="/settings/notifications" prepend-icon="mdi-bell-outline">
            <v-list-item-title>Уведомления</v-list-item-title>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item @click="logout" prepend-icon="mdi-logout">
            <v-list-item-title>Выйти</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- Панель уведомлений -->
    <v-navigation-drawer
      v-model="notificationDrawer"
      location="right"
      temporary
      width="350"
    >
      <v-list-item
        class="bg-primary text-white"
        title="Уведомления"
      >
        <template v-slot:append>
          <v-btn
            icon
            variant="text"
            color="white"
            @click="notificationDrawer = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </template>
      </v-list-item>
      
      <v-divider></v-divider>
      
      <v-list v-if="notifications.length > 0">
        <v-list-item
          v-for="notification in notifications"
          :key="notification.id"
          :title="notification.title"
          :subtitle="notification.message"
          :prepend-icon="getNotificationIcon(notification.type)"
          lines="two"
          class="notification-item"
        >
          <template v-slot:append>
            <span class="text-caption">{{ formatNotificationTime(notification.time) }}</span>
          </template>
        </v-list-item>
      </v-list>
      
      <div v-else class="pa-4 text-center">
        <v-icon size="64" color="grey-lighten-1">mdi-bell-off-outline</v-icon>
        <p class="text-body-1 mt-2 text-grey">У вас нет новых уведомлений</p>
      </div>
    </v-navigation-drawer>

    <!-- Диалог поиска -->
    <v-dialog
      v-model="searchDialog"
      max-width="600"
    >
      <v-card>
        <v-card-text class="pa-4">
          <v-text-field
            v-model="searchQuery"
            label="Поиск по системе"
            variant="outlined"
            prepend-inner-icon="mdi-magnify"
            autofocus
            @keydown.esc="searchDialog = false"
            hide-details
          ></v-text-field>
          
          <v-list v-if="searchResults.length > 0" class="mt-4">
            <v-list-item
              v-for="result in searchResults"
              :key="result.id"
              :title="result.title"
              :subtitle="result.subtitle"
              :to="result.path"
              @click="searchDialog = false"
            ></v-list-item>
          </v-list>
          
          <div v-else-if="searchQuery" class="mt-4 text-center py-4">
            <v-icon size="48" color="grey-lighten-1">mdi-file-search-outline</v-icon>
            <p class="text-body-1 mt-2 text-grey">Ничего не найдено по запросу "{{ searchQuery }}"</p>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-main>
      <v-container fluid class="main-container pa-6">
        <slot></slot>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import LeftSideMenu from '@/Components/_LeftSideMenu.vue'
import { useAuthStore } from '@/stores/auth'
import { formatNotificationTime } from '@/utils/dateUtils'

const authStore = useAuthStore()
const route = useRoute()

const drawer = ref(true)
const notificationDrawer = ref(false)
const searchDialog = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const user = computed(() => authStore.user)

// Получить имя и инициалы пользователя для отображения
const userName = computed(() => {
  if (!user.value) return 'Загрузка...';
  return user.value.fio || `${user.value.first_name || ''} ${user.value.last_name || ''}`.trim() || user.value.email || 'Пользователь';
});

const userInitials = computed(() => {
  if (!user.value) return '';
  if (user.value.fio) {
    const parts = user.value.fio.split(' ');
    return parts.map(part => part.charAt(0).toUpperCase()).join('').slice(0, 2);
  }
  if (user.value.first_name && user.value.last_name) {
    return (user.value.first_name.charAt(0) + user.value.last_name.charAt(0)).toUpperCase();
  }
  return user.value.email ? user.value.email.charAt(0).toUpperCase() : 'П';
});

// Определение заголовка страницы на основе текущего маршрута
const pageTitle = computed(() => {
  const path = route.path;
  if (path === '/dashboard') return 'Главная панель';
  if (path === '/users') return 'Управление пользователями';
  if (path === '/users/create') return 'Создание пользователя';
  if (path === '/groups') return 'Управление группами';
  if (path === '/groups/create') return 'Создание группы';
  if (path.includes('/groups/') && path.includes('/edit')) return 'Редактирование группы';
  if (path === '/group-courses') return 'Запись групп на курсы';
  if (path === '/courses') return 'Все курсы';
  if (path === '/courses/my') return 'Мои курсы';
  if (path === '/courses/create') return 'Создание курса';
  if (path === '/schedule') return 'Расписание занятий';
  if (path === '/learning-progress') return 'Прогресс обучения';
  if (path.includes('/settings')) {
    if (path === '/settings') return 'Настройки профиля';
    if (path === '/settings/security') return 'Настройки безопасности';
    if (path === '/settings/notifications') return 'Настройки уведомлений';
    if (path === '/settings/appearance') return 'Настройки внешнего вида';
    return 'Настройки';
  }
  return 'LMS System';
});

// Макет данных для демонстрации
const notifications = ref([
  {
    id: 1,
    title: 'Новое занятие',
    message: 'Назначено новое занятие по курсу "Основы программирования" на 15:00',
    type: 'course',
    time: new Date(Date.now() - 30 * 60 * 1000) // 30 мин назад
  },
  {
    id: 2,
    title: 'Новый участник',
    message: 'Пользователь Иванов Иван добавлен в вашу группу',
    type: 'user',
    time: new Date(Date.now() - 2 * 60 * 60 * 1000) // 2 часа назад
  },
  {
    id: 3,
    title: 'Напоминание',
    message: 'Срок выполнения задания "Проект системы" истекает через 2 дня',
    type: 'alert',
    time: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000) // 1 день назад
  }
]);

// Получить иконку в зависимости от типа уведомления
const getNotificationIcon = (type) => {
  switch(type) {
    case 'course': return 'mdi-book-open-variant';
    case 'user': return 'mdi-account';
    case 'alert': return 'mdi-alert-circle';
    default: return 'mdi-bell';
  }
};

// Поиск при изменении запроса
watch(searchQuery, (newQuery) => {
  if (!newQuery) {
    searchResults.value = [];
    return;
  }
  
  // Здесь будет запрос к API, пока используем моковые данные
  setTimeout(() => {
    if (newQuery.length > 2) {
      searchResults.value = [
        { id: 1, title: 'Основы программирования', subtitle: 'Курс', path: '/courses/1' },
        { id: 2, title: 'Группа ПМ-101', subtitle: 'Группа', path: '/groups/1' },
        { id: 3, title: 'Иванов Иван', subtitle: 'Пользователь', path: '/users/3' }
      ].filter(result => 
        result.title.toLowerCase().includes(newQuery.toLowerCase()) || 
        result.subtitle.toLowerCase().includes(newQuery.toLowerCase())
      );
    } else {
      searchResults.value = [];
    }
  }, 300);
});

// Функция выхода из системы
const logout = async () => {
  try {
    await authStore.logout();
    // После успешного выхода перенаправляем на страницу входа
    window.location.href = '/login';
  } catch (error) {
    console.error('Ошибка при выходе из системы:', error);
  }
};
</script>

<style scoped>
.app-bar {
  background: linear-gradient(90deg, #1a1a2e 0%, #16213e 100%) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.v-main {
  background-color: #f0f2f5;
  min-height: 100vh;
}

.main-container {
  padding: 24px;
  max-width: 1600px;
  margin: 0 auto;
}

.notification-item {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: rgba(var(--v-theme-primary-rgb), 0.05);
}

@media (max-width: 600px) {
  .main-container {
    padding: 16px;
  }
}
</style> 