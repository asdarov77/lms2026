<template>
  <AppLayout>
    <div class="debug-info pa-2 mb-2 bg-blue-lighten-5">
      <strong>Component Debug:</strong> Users.vue loaded
    </div>
    <v-container fluid>
      <!-- Header -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <h1 class="text-h4 font-weight-bold primary--text">Управление пользователями</h1>
        </v-col>
        <v-col cols="12" md="6" class="d-flex justify-end align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="openAddUserDialog"
            v-if="isAdmin"
          >
            Добавить пользователя
          </v-btn>
        </v-col>
      </v-row>

      <!-- Search and Filters -->
      <v-card class="mb-4 pa-3">
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="filters.search"
              label="Поиск"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
              @update:model-value="debouncedSearch"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filters.role"
              :items="roleOptions"
              label="Роль"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
            ></v-select>
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filters.group"
              :items="groupOptions"
              item-title="groupname"
              item-value="id"
              label="Группа"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
              clearable
            ></v-select>
          </v-col>
          <v-col cols="12" md="2" class="d-flex align-center">
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

      <!-- Loading and Error States -->
      <suspense>
        <template #default>
          <users-table
            v-if="!error"
            :users="filteredUsers"
            :loading="loading"
            @edit="editUser"
            @delete="confirmDelete"
            @change-password="changePassword"
          />
        </template>
        <template #fallback>
          <loading-state />
        </template>
        <template #error="{ error }">
          <error-display 
            :error="error"
            title="Ошибка загрузки пользователей"
            @retry="fetchUsers"
          />
        </template>
      </suspense>

      <!-- Dialogs -->
      <user-form-dialog
        v-model="dialogs.user"
        :user="selectedUser"
        :loading="loading"
        @save="saveUser"
      />

      <password-dialog
        v-model="dialogs.password"
        :user="selectedUser"
        :loading="loading"
        @save="updatePassword"
      />

      <confirm-dialog
        v-model="dialogs.delete"
        :title="'Удаление пользователя'"
        :message="'Вы действительно хотите удалить пользователя ' + selectedUser?.fio + '?'"
        :loading="loading"
        @confirm="deleteUser"
      />

      <!-- Notifications -->
      <v-snackbar
        v-model="notification.show"
        :color="notification.color"
        :timeout="3000"
        location="top"
      >
        {{ notification.text }}
        <template v-slot:actions>
          <v-btn
            color="white"
            variant="text"
            @click="notification.show = false"
          >
            Закрыть
          </v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/userStore';
import AppLayout from '@/Layouts/AppLayout.vue';
import LoadingState from '@/components/LoadingState.vue';
import ErrorDisplay from '@/components/ErrorDisplay.vue';
import UsersTable from '@/components/Users/UsersTable.vue';
import UserFormDialog from '@/components/Users/UserFormDialog.vue';
import PasswordDialog from '@/components/Users/PasswordDialog.vue';
import ConfirmDialog from '@/components/Common/ConfirmDialog.vue';

// Custom debounce implementation
function debounce(fn, delay) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Stores
const authStore = useAuthStore();
const userStore = useUserStore();

// State
const loading = ref(false);
const error = ref(null);

const filters = ref({
  search: '',
  role: 'Все роли',
  group: null
});

const dialogs = ref({
  user: false,
  password: false,
  delete: false
});

const notification = ref({
  show: false,
  text: '',
  color: 'success'
});

const selectedUser = ref(null);

// Computed
const isAdmin = computed(() => authStore.user?.role === 'Администратор');

const roleOptions = computed(() => [
  'Все роли',
  'Администратор',
  'Пользователь',
  'Инструктор'
]);

const groupOptions = computed(() => userStore.groups);

const filteredUsers = computed(() => {
  let users = userStore.users;

  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase();
    users = users.filter(user => 
      user.fio.toLowerCase().includes(searchLower) ||
      user.email?.toLowerCase().includes(searchLower)
    );
  }

  if (filters.value.role !== 'Все роли') {
    users = users.filter(user => user.role === filters.value.role);
  }

  if (filters.value.group) {
    users = users.filter(user => user.group_id === filters.value.group);
  }

  return users;
});

// Methods
const debouncedSearch = debounce(() => {
  fetchUsers();
}, 300);

const showNotification = (text, color = 'success') => {
  notification.value = { show: true, text, color };
};

const clearFilters = () => {
  filters.value = {
    search: '',
    role: 'Все роли',
    group: null
  };
};

const fetchUsers = async () => {
  try {
    loading.value = true;
    error.value = null;
    await userStore.fetchUsers();
    await userStore.fetchGroups();
  } catch (err) {
    error.value = err;
    showNotification(err.message, 'error');
  } finally {
    loading.value = false;
  }
};

const openAddUserDialog = () => {
  selectedUser.value = null;
  dialogs.value.user = true;
};

const editUser = (user) => {
  selectedUser.value = { ...user };
  dialogs.value.user = true;
};

const changePassword = (user) => {
  selectedUser.value = user;
  dialogs.value.password = true;
};

const confirmDelete = (user) => {
  selectedUser.value = user;
  dialogs.value.delete = true;
};

const saveUser = async (userData) => {
  try {
    loading.value = true;
    const isNew = !userData.id;
    await userStore.saveUser(userData);
    showNotification(`Пользователь успешно ${isNew ? 'создан' : 'обновлен'}`);
    dialogs.value.user = false;
    await fetchUsers();
  } catch (err) {
    showNotification(err.message, 'error');
  } finally {
    loading.value = false;
  }
};

const updatePassword = async (passwordData) => {
  try {
    loading.value = true;
    await userStore.updatePassword(selectedUser.value.id, passwordData);
    showNotification('Пароль успешно обновлен');
    dialogs.value.password = false;
  } catch (err) {
    showNotification(err.message, 'error');
  } finally {
    loading.value = false;
  }
};

const deleteUser = async () => {
  try {
    loading.value = true;
    await userStore.deleteUser(selectedUser.value.id);
    showNotification('Пользователь успешно удален');
    dialogs.value.delete = false;
    await fetchUsers();
  } catch (err) {
    showNotification(err.message, 'error');
  } finally {
    loading.value = false;
  }
};

// Lifecycle
onMounted(fetchUsers);

// Watchers
watch([() => filters.value.role, () => filters.value.group], fetchUsers);
</script>

<style>
.v-data-table :deep(th) {
  background-color: var(--v-background-base) !important;
  color: var(--v-primary-base) !important;
  font-weight: bold !important;
}

.debug-info {
  display: none;
}
</style>

<!-- 
INTEGRATION NOTES:
1. Users component works together with Groups.vue and Progress.vue
2. Users data is used in the Progress.vue to display student progress
3. Users can be assigned to groups in this component
4. Connected via router at /users and accessible from the left side menu
5. Progress data for users/students is viewable at /learning-progress
--> 