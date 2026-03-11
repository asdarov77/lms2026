<script setup>
import { useI18n } from "vue-i18n";
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import LogoutApp from "./LogoutApp.vue";
import { useAuthStore } from '../../stores/auth'

const { t } = useI18n({ useScope: "global" });
const route = useRoute();

// Simplify the approach for menu groups
const isUsersOpen = ref(false);
const isGroupsOpen = ref(false);
const isCoursesOpen = ref(false);
const isLearningOpen = ref(false);

// Update open state based on route
const updateOpenState = () => {
  const path = route.path;
  isUsersOpen.value = path.includes('/users');
  isGroupsOpen.value = path.includes('/groups') || path === '/group-courses';
  isCoursesOpen.value = path.includes('/courses');
  isLearningOpen.value = path === '/group-courses' || path === '/schedule' || path === '/learning-progress';
};

// Watch for route changes and update menu state
watch(() => route.path, updateOpenState, { immediate: true });

const props = defineProps({
  drawer: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['update:drawer'])

const authStore = useAuthStore()
const user = computed(() => authStore.user)

// Get user roles from the user object
const userRoles = computed(() => {
  if (!user.value) return [];
  
  // Check if user has roles array
  if (user.value.roles && Array.isArray(user.value.roles)) {
    return user.value.roles.map(role => role.name);
  }
  
  // Fallback to single role property if exists
  if (user.value.role) {
    return [user.value.role];
  }
  
  return [];
});

// Debug - force enable all sections (remove in production)
const debugAll = true;

// Check if user has specific role
const hasRole = (role) => {
  return userRoles.value.includes(role);
};

// Role-based permissions - use authStore directly
const isAdmin = computed(() => debugAll || authStore.isAdmin);
const isTeacher = computed(() => debugAll || authStore.isTeacher);
const isStudent = computed(() => debugAll || authStore.isStudent);
const canManageUsers = computed(() => debugAll || isAdmin.value);
const canManageGroups = computed(() => debugAll || isAdmin.value || isTeacher.value);

const rail = ref(false)
const loading = ref(false)
const error = ref(null)

const localDrawer = computed({
  get: () => props.drawer,
  set: (value) => emit('update:drawer', value)
})

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

onMounted(async () => {
  if (!user.value) {
    loading.value = true
    try {
      await authStore.fetchUser()
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }
  
  // Initialize menu open states
  updateOpenState();
})
</script>

<template>
  <v-navigation-drawer
    v-model="localDrawer"
    :rail="rail"
    permanent
    @update:rail="rail = $event"
    class="left-side-menu"
  >
    <v-list-item
      :prepend-avatar="user?.avatar"
      :title="userName"
      :subtitle="user?.email || ''"
    >
      <template v-slot:prepend v-if="!user?.avatar">
        <v-avatar color="primary">
          <span class="text-white">{{ userInitials }}</span>
        </v-avatar>
      </template>
      <template v-slot:append>
        <v-btn
          variant="text"
          icon="mdi-chevron-left"
          @click.stop="rail = !rail"
        ></v-btn>
      </template>
    </v-list-item>

    <v-divider class="my-2"></v-divider>

    <v-list density="compact" nav class="py-1">
      <v-list-item
        prepend-icon="mdi-view-dashboard-outline"
        title="Главная панель"
        value="dashboard"
        to="/dashboard"
        :active="route.path === '/dashboard'"
        active-color="primary"
      ></v-list-item>

      <!-- Управление пользователями - только для администраторов -->
      <v-list-group v-model="isUsersOpen" v-if="canManageUsers">
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-account-group-outline"
            title="Пользователи"
            :active="route.path.includes('/users')"
            active-color="primary"
          ></v-list-item>
        </template>

        <v-list-item
          title="Управление пользователями"
          value="users"
          to="/users"
          :active="route.path === '/users'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          title="Создать пользователя"
          value="users/create"
          to="/users/create"
          :active="route.path === '/users/create'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
      </v-list-group>

      <!-- Управление группами - для администраторов и преподавателей -->
      <v-list-group v-model="isGroupsOpen" v-if="canManageGroups">
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-account-multiple-outline"
            title="Группы"
            :active="route.path.includes('/groups') || route.path === '/group-courses'"
            active-color="primary"
          ></v-list-item>
        </template>

        <v-list-item
          title="Управление группами"
          value="groups"
          to="/groups"
          :active="route.path === '/groups'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          title="Запись групп на курсы"
          value="group-courses"
          to="/group-courses"
          :active="route.path === '/group-courses'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
      </v-list-group>

      <!-- Курсы - доступны для всех -->
      <v-list-group v-model="isCoursesOpen">
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-book-outline"
            title="Курсы"
            :active="route.path.includes('/courses')"
            active-color="primary"
          ></v-list-item>
        </template>

        <v-list-item
          title="Все курсы"
          value="courses"
          to="/courses"
          :active="route.path === '/courses'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          title="Мои курсы"
          value="my-courses"
          to="/my-courses"
          :active="route.path === '/my-courses'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          v-if="canManageGroups"
          title="Создать курс"
          value="courses/create"
          to="/courses/create"
          :active="route.path === '/courses/create'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          v-if="canManageUsers"
          title="Импорт курсов"
          value="import-courses"
          to="/import-courses"
          :active="route.path === '/import-courses'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          v-if="canManageUsers"
          title="Классы (воздушные суда)"
          value="classes"
          to="/classes"
          :active="route.path === '/classes'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
      </v-list-group>

      <!-- Обучение - для преподавателей и админов -->
      <v-list-group v-model="isLearningOpen" v-if="canManageGroups">
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-school-outline"
            title="Обучение"
            :active="['/group-courses', '/schedule', '/learning-progress'].includes(route.path)"
            active-color="primary"
          ></v-list-item>
        </template>

        <v-list-item
          title="Расписание занятий"
          value="schedule"
          to="/schedule"
          :active="route.path === '/schedule'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
        <v-list-item
          title="Прогресс обучения"
          value="learning-progress"
          to="/learning-progress"
          :active="route.path === '/learning-progress'"
          active-color="primary"
          class="menu-sub-item"
        ></v-list-item>
      </v-list-group>

      <!-- Настройки - для всех -->
      <v-list-item
        prepend-icon="mdi-cog-outline"
        title="Настройки"
        value="settings"
        to="/settings"
        :active="route.path.includes('/settings')"
        active-color="primary"
      ></v-list-item>
    </v-list>

    <template v-slot:append>
      <v-alert
        v-if="error"
        type="error"
        closable
        class="mx-3 mb-2 mt-auto"
        density="compact"
        @click:close="error = null"
      >
        {{ error }}
      </v-alert>

      <v-divider class="my-2"></v-divider>
      <logout-app />
    </template>

    <v-progress-linear
      v-if="loading"
      indeterminate
      color="primary"
    ></v-progress-linear>
  </v-navigation-drawer>
</template>

<style scoped>
.left-side-menu {
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.v-list-item {
  border-radius: 8px;
  margin: 2px 8px;
  transition: all 0.2s ease;
}

.menu-sub-item {
  margin-left: 14px !important;
  font-size: 0.95rem;
}

.v-list-item--active {
  font-weight: 600;
}

.v-list-item--active:not(.v-list-group__header) {
  background-color: rgba(var(--v-theme-primary-rgb), 0.1);
}

.v-list-group__header.v-list-item--active {
  color: rgb(var(--v-theme-primary-rgb));
}

.v-divider {
  opacity: 0.6;
}

/* Анимация для стрелки раскрытия группы */
.v-list-group--prepend .v-list-group__header .v-icon {
  transition: transform 0.2s;
}

.v-list-group--prepend.v-list-group--open .v-list-group__header .v-icon {
  transform: rotate(90deg);
}
</style>