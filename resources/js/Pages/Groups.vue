<template>
  <AppLayout>
    <v-container fluid>
      <!-- Header -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <h1 class="text-h4 font-weight-bold primary--text">Управление группами</h1>
        </v-col>
        <v-col cols="12" md="6" class="d-flex justify-end align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            @click="openAddGroupDialog"
            v-if="isAdmin"
          >
            Добавить группу
          </v-btn>
        </v-col>
      </v-row>

      <!-- Search and Filters -->
      <v-card class="mb-4 pa-3">
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="search"
              label="Поиск"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
              @input="updateSearchDebounced"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="statusFilter"
              :items="[
                { title: 'Все', value: null },
                { title: 'Активные', value: true },
                { title: 'Неактивные', value: false }
              ]"
              item-title="title"
              item-value="value"
              label="Статус"
              variant="outlined"
              density="compact"
              hide-details
              class="mb-2"
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

      <!-- Debug Info -->
      <v-card v-if="!loading && !error" class="mb-4 pa-3">
        <pre class="text-caption">Данные для отображения: {{ filteredGroups.length }} групп</pre>
        <pre v-if="filteredGroups.length > 0" class="text-caption">Структура данных первой группы: {{ JSON.stringify(filteredGroups[0], null, 2) }}</pre>
        <pre class="text-caption">Заголовки таблицы: {{ JSON.stringify(headers, null, 2) }}</pre>
        <p v-if="!filteredGroups.length" class="text-red">Нет данных для отображения!</p>
        <v-btn @click="forcedRerender" color="info" size="small" variant="outlined" class="mt-2">
          Принудительно перерисовать
        </v-btn>
        <v-btn @click="fetchGroups" color="primary" size="small" variant="outlined" class="mt-2 ml-2">
          Обновить данные
        </v-btn>
      </v-card>

      <!-- Loading State -->
      <loading-state v-if="loading" />

      <!-- Error State -->
      <error-display 
        v-else-if="error" 
        title="Ошибка загрузки групп" 
        :message="errorMessage"
        :error="error"
        :loading="retryLoading"
        @retry="fetchGroups"
      />

      <!-- Empty State -->
      <v-card v-else-if="!filteredGroups.length && !loading" class="pa-5 text-center">
        <v-icon size="large" color="grey" class="mb-3">mdi-alert-circle-outline</v-icon>
        <h3 class="text-h5 mb-2">Нет данных для отображения</h3>
        <p class="text-body-1 mb-4">
          {{ statusFilter !== null ? 'Нет групп с выбранным статусом' : 'Группы не найдены или не созданы' }}
        </p>
        <v-btn 
          v-if="isAdmin" 
          color="primary" 
          prepend-icon="mdi-plus" 
          @click="openAddGroupDialog"
        >
          Добавить группу
        </v-btn>
        <v-btn 
          color="secondary" 
          prepend-icon="mdi-refresh" 
          @click="fetchGroups"
          class="ml-2"
        >
          Обновить
        </v-btn>
      </v-card>

      <!-- Groups Table -->
      <v-card v-if="!loading && !error && filteredGroups.length > 0">
        <div style="height: 600px; overflow: auto;">
          <v-data-table
            :headers="headers"
            :items="filteredGroups"
            :items-per-page="10"
            class="elevation-1"
            :sort-by="[{ key: 'groupname', order: 'asc' }]"
            :search="search"
          >
            <template v-slot:item.id="{ item }">
              {{ item.id }}
            </template>
            <template v-slot:item.groupname="{ item }">
              {{ item.groupname }}
            </template>
            <template v-slot:item.groupdescription="{ item }">
              {{ item.groupdescription }}
            </template>
            <template v-slot:item.is_active="{ item }">
              <v-chip
                :color="item.is_active ? 'success' : 'error'"
                :text="item.is_active ? 'Активна' : 'Неактивна'"
                variant="outlined"
              ></v-chip>
            </template>
            <template v-slot:item.users_count="{ item }">
              {{ item.users ? item.users.length : 0 }} / {{ item.max_users || 'Не ограничено' }}
            </template>
            <template v-slot:item.creator="{ item }">
              {{ item.creator ? item.creator.fio : 'Неизвестно' }}
            </template>
            <template v-slot:item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>
            <template v-slot:item.actions="{ item }">
              <div class="d-flex gap-2">
                <v-btn
                  icon="mdi-eye"
                  size="small"
                  color="info"
                  variant="text"
                  @click="viewGroup(item)"
                ></v-btn>
                <v-btn
                  icon="mdi-pencil"
                  size="small"
                  color="primary"
                  variant="text"
                  @click="editGroup(item)"
                ></v-btn>
                <v-btn
                  icon="mdi-account-multiple-plus"
                  size="small"
                  color="success"
                  variant="text"
                  @click="openManageUsersDialog(item)"
                  :disabled="!item.is_active"
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
        </div>
      </v-card>

      <!-- Add/Edit Group Dialog -->
      <v-dialog v-model="groupDialog" max-width="600px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white py-3">
            {{ isEditMode ? 'Редактировать группу' : 'Добавить группу' }}
          </v-card-title>
          <v-card-text class="pt-4">
            <v-form ref="groupForm" v-model="groupFormValid">
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="groupForm.groupname"
                    label="Название группы"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="groupForm.groupdescription"
                    label="Описание"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                    rows="3"
                  ></v-textarea>
                </v-col>
                <v-col cols="12" md="6">
                  <v-switch
                    v-model="groupForm.is_active"
                    color="success"
                    label="Активна"
                    hide-details
                  ></v-switch>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="groupForm.max_users"
                    label="Максимальное количество пользователей"
                    type="number"
                    min="1"
                    :rules="[v => v === null || v === '' || v >= 1 || 'Должно быть больше 0']"
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
              @click="groupDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              variant="text"
              :loading="saving"
              :disabled="!groupFormValid"
              @click="saveGroup"
            >
              Сохранить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Manage Users Dialog -->
      <v-dialog v-model="usersDialog" max-width="800px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white py-3">
            Управление пользователями группы {{ selectedGroup?.groupname }}
          </v-card-title>
          <v-card-text class="pt-4">
            <v-row>
              <v-col cols="12">
                <h3 class="text-h6 mb-2">Пользователи в группе</h3>
                <div v-if="selectedGroup?.users && selectedGroup.users.length > 0">
                  <v-chip-group>
                    <v-chip
                      v-for="user in selectedGroup.users"
                      :key="user.id"
                      class="ma-1"
                      closable
                      @click:close="removeUserFromGroup(user)"
                    >
                      {{ user.fio }}
                    </v-chip>
                  </v-chip-group>
                </div>
                <p v-else class="text-body-1 text-grey">В группе нет пользователей</p>
              </v-col>
            </v-row>
            <v-divider class="my-4"></v-divider>
            <v-row>
              <v-col cols="12">
                <h3 class="text-h6 mb-2">Добавить пользователей</h3>
                <v-autocomplete
                  v-model="selectedUsers"
                  :items="availableUsers"
                  item-title="fio"
                  item-value="id"
                  label="Выберите пользователей"
                  multiple
                  chips
                  :disabled="!canAddMoreUsers"
                ></v-autocomplete>
                <p v-if="!canAddMoreUsers" class="text-caption text-red mt-1">
                  Достигнут лимит пользователей для этой группы
                </p>
                <p v-else-if="maxUsersLimit" class="text-caption mt-1">
                  Лимит: {{ currentUserCount }}/{{ maxUsersLimit }} пользователей
                </p>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="usersDialog = false"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              variant="text"
              :loading="saving"
              :disabled="!selectedUsers.length"
              @click="saveUsers"
            >
              Сохранить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- View Group Dialog -->
      <v-dialog v-model="viewDialog" max-width="600px">
        <v-card>
          <v-card-title class="text-h5 bg-primary text-white py-3">
            Информация о группе
          </v-card-title>
          <v-card-text class="pt-4">
            <v-list lines="two">
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-account-group</v-icon>
                </template>
                <v-list-item-title>Название</v-list-item-title>
                <v-list-item-subtitle>{{ selectedGroup?.groupname }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-information</v-icon>
                </template>
                <v-list-item-title>Описание</v-list-item-title>
                <v-list-item-subtitle>{{ selectedGroup?.groupdescription }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon :color="selectedGroup?.is_active ? 'success' : 'error'">
                    {{ selectedGroup?.is_active ? 'mdi-check-circle' : 'mdi-close-circle' }}
                  </v-icon>
                </template>
                <v-list-item-title>Статус</v-list-item-title>
                <v-list-item-subtitle>{{ selectedGroup?.is_active ? 'Активна' : 'Неактивна' }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-account-multiple</v-icon>
                </template>
                <v-list-item-title>Пользователи</v-list-item-title>
                <v-list-item-subtitle>
                  {{ selectedGroup?.users ? selectedGroup.users.length : 0 }} человек
                  {{ selectedGroup?.max_users ? `(максимум: ${selectedGroup.max_users})` : '' }}
                </v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-account</v-icon>
                </template>
                <v-list-item-title>Создатель</v-list-item-title>
                <v-list-item-subtitle>{{ selectedGroup?.creator ? selectedGroup.creator.fio : 'Неизвестно' }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-calendar</v-icon>
                </template>
                <v-list-item-title>Дата создания</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(selectedGroup?.created_at) }}</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-update</v-icon>
                </template>
                <v-list-item-title>Последнее обновление</v-list-item-title>
                <v-list-item-subtitle>{{ formatDate(selectedGroup?.updated_at) }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
            
            <div v-if="selectedGroup?.users && selectedGroup.users.length > 0" class="mt-4">
              <h3 class="text-subtitle-1 font-weight-bold mb-2">Участники группы:</h3>
              <v-list lines="one" density="compact">
                <v-list-item v-for="user in selectedGroup.users" :key="user.id">
                  <template v-slot:prepend>
                    <v-icon size="small">mdi-account</v-icon>
                  </template>
                  <v-list-item-title>{{ user.fio }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </div>
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

      <!-- Delete Confirmation Dialog -->
      <v-dialog v-model="deleteDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 bg-error text-white py-3">
            Подтверждение удаления
          </v-card-title>
          <v-card-text class="pt-4">
            <p class="mb-0">Вы действительно хотите удалить группу <strong>{{ groupToDelete?.groupname }}</strong>?</p>
            <p class="text-error font-weight-bold mt-2">Внимание! Все пользователи группы останутся без группы.</p>
            <p class="text-error font-weight-bold mt-2">Это действие невозможно отменить.</p>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              @click="deleteDialog = false"
              :disabled="deleting"
            >
              Отмена
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              :loading="deleting"
              @click="deleteGroup"
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
        <template v-slot:actions>
          <v-btn
            color="white"
            variant="text"
            @click="snackbar.show = false"
          >
            Закрыть
          </v-btn>
        </template>
      </v-snackbar>
    </v-container>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useGroupStore } from '@/stores/groupStore'
import { useUserStore } from '@/stores/userStore'
import ErrorDisplay from '@/components/ErrorDisplay.vue'
import LoadingState from '@/components/LoadingState.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  components: {
    AppLayout,
    ErrorDisplay,
    LoadingState
  },
  setup() {
    const authStore = useAuthStore()
    const groupStore = useGroupStore()
    const userStore = useUserStore()
    const isAdmin = computed(() => authStore.isAdmin)

    // State
    const loading = ref(true)
    const error = ref(null)
    const retryLoading = ref(false)
    const saving = ref(false)
    const deleting = ref(false)
    const search = ref('')
    const userSearch = ref('')
    const statusFilter = ref(null)
    const groupDialog = ref(false)
    const usersDialog = ref(false)
    const viewDialog = ref(false)
    const deleteDialog = ref(false)
    const isEditMode = ref(false)
    const groupFormValid = ref(false)
    const selectedGroup = ref(null)
    const groupToDelete = ref(null)
    const selectedUsers = ref([])
    const snackbar = ref({
      show: false,
      color: 'success',
      text: ''
    })

    // Form data
    const groupForm = ref({
      groupname: '',
      groupdescription: '',
      is_active: true,
      max_users: null,
      settings: {}
    })

    // Table headers
    const headers = [
      { title: 'ID', key: 'id', align: 'start', width: '5%' },
      { title: 'Название', key: 'groupname', align: 'start', width: '20%' },
      { title: 'Описание', key: 'groupdescription', align: 'start', width: '25%' },
      { title: 'Статус', key: 'is_active', align: 'start', width: '10%' },
      { title: 'Участники', key: 'users_count', align: 'start', width: '10%' },
      { title: 'Создатель', key: 'creator', align: 'start', width: '15%' },
      { title: 'Дата создания', key: 'created_at', align: 'start', width: '15%' },
      { title: 'Действия', key: 'actions', align: 'center', sortable: false, width: '10%' }
    ]

    // Computed
    const filteredGroups = computed(() => {
      if (!groupStore.getGroups || groupStore.getGroups.length === 0) {
        console.log('No groups data available to filter');
        return [];
      }
      
      console.log('Filtering groups from total:', groupStore.getGroups.length);
      
      // Фильтрация групп по поисковому запросу и статусу
      const groups = groupStore.getGroups.filter(group => {
        // Фильтрация по поисковому запросу
        const matchesSearch = !search.value || 
          group.groupname?.toLowerCase().includes(search.value.toLowerCase()) ||
          group.groupdescription?.toLowerCase().includes(search.value.toLowerCase());
        
        // Фильтрация по статусу
        const matchesStatus = statusFilter.value === null || group.is_active === statusFilter.value;
        
        // Возвращаем true, если группа соответствует обоим критериям
        return matchesSearch && matchesStatus;
      });
      
      console.log('Groups after filtering:', groups.length);
      
      // Преобразуем данные в формат, подходящий для v-data-table в Vuetify 3
      return groups.map(group => {
        // Проверка наличия всех необходимых полей
        if (!group.id || !group.groupname) {
          console.warn('Group missing required fields:', group);
        }
        
        // Создаем правильно отформатированный объект для таблицы
        return {
          id: group.id,
          groupname: group.groupname || 'Без названия',
          groupdescription: group.groupdescription || 'Нет описания',
          is_active: !!group.is_active,  // Приводим к boolean
          users: group.users || [],
          users_count: group.users ? group.users.length : 0,
          max_users: group.max_users,
          creator: group.creator || null,
          created_at: group.created_at,
          updated_at: group.updated_at,
          // Добавляем дополнительные поля, если они есть
          ...group
        };
      });
    })

    const availableUsers = computed(() => {
      if (!selectedGroup.value || !selectedGroup.value.users) {
        return userStore.getUsers
      }
      
      const currentUserIds = selectedGroup.value.users.map(u => u.id)
      return userStore.getUsers.filter(user => !currentUserIds.includes(user.id))
    })

    const maxUsersLimit = computed(() => {
      return selectedGroup.value?.max_users || null
    })

    const currentUserCount = computed(() => {
      return selectedGroup.value?.users?.length || 0
    })

    const canAddMoreUsers = computed(() => {
      if (!maxUsersLimit.value) return true
      return currentUserCount.value < maxUsersLimit.value
    })

    const errorMessage = computed(() => {
      if (!error.value) return 'Не удалось загрузить список групп. Пожалуйста, попробуйте снова.'
      return typeof error.value === 'string' ? error.value : error.value.message
    })

    // Methods
    const fetchGroups = async () => {
      loading.value = true;
      error.value = null;
      retryLoading.value = false;
      console.log('Fetching groups in Groups.vue...');
      
      try {
        // Проверяем авторизацию и токен
        console.log('Auth token present:', !!authStore.token);
        console.log('User authorized:', authStore.isAuthenticated);
        console.log('Current user:', authStore.user);
        
        // Если токена нет, попробуем переинициализировать аутентификацию
        if (!authStore.token || !authStore.isAuthenticated) {
          console.log('No token or not authenticated, trying to reinitialize...');
          await authStore.initializeAuth();
          
          // Проверяем ещё раз после инициализации
          console.log('After init - Auth token present:', !!authStore.token);
          console.log('After init - User authorized:', authStore.isAuthenticated);
          
          // Если всё равно нет токена, показываем ошибку
          if (!authStore.token || !authStore.isAuthenticated) {
            console.error('Authentication failed, please login again');
            error.value = new Error('Необходима авторизация');
            showNotification('Необходимо авторизоваться', 'error');
            loading.value = false;
            return;
          }
        }
        
        // Вызываем метод fetchGroups из store
        await groupStore.fetchGroups();
        console.log('Groups fetched successfully:', groupStore.getGroups.length, 'groups');
        
        // Проверяем полученные данные
        if (groupStore.getGroups.length > 0) {
          console.log('Sample group data:', JSON.stringify(groupStore.getGroups[0], null, 2));
        } else {
          console.log('No groups found. This might be expected if there are no groups created yet.');
        }
        
        // Проверка структуры данных для отображения в таблице
        console.log('Filtered groups for table:', filteredGroups.value.length);
        if (filteredGroups.value.length > 0) {
          console.log('First filtered group:', JSON.stringify(filteredGroups.value[0], null, 2));
        }
        
      } catch (err) {
        console.error('Error in fetchGroups:', err);
        console.error('Error details:', err.response || err.message);
        error.value = err;
        
        // Если это ошибка авторизации, предлагаем обновить токен
        if (err.response && err.response.status === 401) {
          showNotification('Необходимо авторизоваться заново', 'error');
        } else {
          showNotification('Ошибка при загрузке групп: ' + (err.response?.data?.message || err.message), 'error');
        }
      } finally {
        loading.value = false;
      }
    }

    const fetchUsers = async () => {
      console.log('Fetching users in Groups.vue...');
      try {
        await userStore.fetchUsers();
        console.log('Users fetched successfully:', userStore.getUsers.length, 'users');
      } catch (err) {
        console.error('Error fetching users:', err);
        showNotification('Ошибка при загрузке пользователей', 'error');
      }
    }

    const openAddGroupDialog = () => {
      isEditMode.value = false
      groupForm.value = {
        groupname: '',
        groupdescription: '',
        is_active: true,
        max_users: null,
        settings: {}
      }
      groupDialog.value = true
    }

    const editGroup = async (group) => {
      console.log('editGroup called with:', group);
      console.log('Editing group:', group);
      // First fetch the latest data for this group
      try {
        loading.value = true;
        const response = await authStore.getApi().get(`/groups/${group.id}`);
        console.log('Latest group data:', response.data);
        
        // Use the latest data from API
        selectedGroup.value = { ...response.data };
        isEditMode.value = true;
        
        // Create a deep copy with properly mapped fields
        groupForm.value = {
          groupname: response.data.groupname || '',
          groupdescription: response.data.groupdescription || '',
          is_active: response.data.is_active === undefined ? true : !!response.data.is_active,
          max_users: response.data.max_users || null,
          settings: response.data.settings || {}
        };
        
        console.log('Form data populated:', groupForm.value);
        groupDialog.value = true;
      } catch (err) {
        console.error('Error fetching group data for editing:', err);
        showNotification('Ошибка при загрузке данных группы', 'error');
      } finally {
        loading.value = false;
      }
    }

    const viewGroup = async (group) => {
      try {
        loading.value = true;
        // Get the latest data for this group
        const response = await authStore.getApi().get(`/groups/${group.id}`);
        selectedGroup.value = response.data;
        console.log('Viewing group with latest data:', selectedGroup.value);
        viewDialog.value = true;
      } catch (err) {
        console.error('Error fetching group data for viewing:', err);
        showNotification('Ошибка при загрузке данных группы', 'error');
      } finally {
        loading.value = false;
      }
    }

    const openManageUsersDialog = async (group) => {
      try {
        loading.value = true;
        // Get fresh data for the group including users
        const response = await authStore.getApi().get(`/groups/${group.id}`);
        selectedGroup.value = response.data;
        selectedUsers.value = [];
        userSearch.value = '';
        
        console.log('Opening manage users dialog for group:', selectedGroup.value);
        usersDialog.value = true;
      } catch (err) {
        console.error('Error fetching group data for user management:', err);
        showNotification('Ошибка при загрузке данных группы', 'error');
      } finally {
        loading.value = false;
      }
    }

    const confirmDelete = (group) => {
      groupToDelete.value = group
      deleteDialog.value = true
    }

    const saveGroup = async () => {
      console.log('saveGroup called, groupFormValid:', groupFormValid.value);
      console.log('groupForm:', groupForm.value);
      console.log('isEditMode:', isEditMode.value);
      console.log('selectedGroup:', selectedGroup.value);
      
      if (!groupFormValid.value) {
        showNotification('Форма содержит ошибки. Проверьте все поля.', 'error');
        return;
      }

      saving.value = true;
      try {
        // Ensure max_users is properly formatted as a number or null
        let max_users = null;
        if (groupForm.value.max_users) {
          max_users = parseInt(groupForm.value.max_users);
          if (isNaN(max_users)) {
            max_users = null;
          }
        }
        
        // Create a clean data object without circular references
        const cleanData = {
          groupname: groupForm.value.groupname.trim(),
          groupdescription: groupForm.value.groupdescription.trim(),
          is_active: Boolean(groupForm.value.is_active),
          max_users: max_users,
          settings: groupForm.value.settings || {}
        };
        
        console.log('Saving group with data:', cleanData);
        
        let result;
        if (isEditMode.value) {
          console.log('Updating existing group with ID:', selectedGroup.value.id);
          result = await groupStore.updateGroup(selectedGroup.value.id, cleanData);
          showNotification('Группа успешно обновлена', 'success');
        } else {
          console.log('Creating new group');
          result = await groupStore.createGroup(cleanData);
          showNotification('Группа успешно создана', 'success');
        }
        
        console.log('API operation result:', result);
        groupDialog.value = false;
        
        // Ensure we get fresh data
        await fetchGroups();
      } catch (err) {
        console.error('Error saving group:', err);
        let errorMessage = 'Ошибка при сохранении группы';
        
        if (err.response) {
          // Handle specific error cases
          if (err.response.status === 422) {
            errorMessage = 'Проверьте правильность заполнения полей формы';
            console.error('Validation errors:', err.response.data);
          } else if (err.response.status === 401) {
            errorMessage = 'Необходимо авторизоваться';
            await authStore.initializeAuth();
          } else if (err.response.data && err.response.data.message) {
            errorMessage = err.response.data.message;
          }
        } else if (err.message) {
          errorMessage += ': ' + err.message;
        }
        
        showNotification(errorMessage, 'error');
      } finally {
        saving.value = false;
      }
    }

    const saveUsers = async () => {
      if (!selectedUsers.value.length) return

      saving.value = true
      try {
        console.log('Adding users to group:', selectedGroup.value.id, selectedUsers.value);
        await groupStore.addUsersToGroup(selectedGroup.value.id, selectedUsers.value)
        usersDialog.value = false
        showNotification('Пользователи успешно добавлены в группу', 'success')
        await fetchGroups()
      } catch (err) {
        console.error('Error adding users to group:', err)
        const errorMsg = err.response?.data?.message || 'Ошибка при добавлении пользователей';
        showNotification(errorMsg, 'error')
      } finally {
        saving.value = false
      }
    }

    const removeUserFromGroup = async (user) => {
      if (!selectedGroup.value || !user) return

      saving.value = true
      try {
        console.log('Removing user from group:', selectedGroup.value.id, user.id);
        await groupStore.removeUsersFromGroup(selectedGroup.value.id, [user.id])
        showNotification('Пользователь успешно удален из группы', 'success')
        await fetchGroups()
      } catch (err) {
        console.error('Error removing user from group:', err)
        const errorMsg = err.response?.data?.message || 'Ошибка при удалении пользователя из группы';
        showNotification(errorMsg, 'error')
      } finally {
        saving.value = false
      }
    }

    const deleteGroup = async () => {
      if (!groupToDelete.value) return

      deleting.value = true
      try {
        await groupStore.deleteGroup(groupToDelete.value.id)
        deleteDialog.value = false
        showNotification('Группа успешно удалена', 'success')
        await fetchGroups()
      } catch (err) {
        console.error('Error deleting group:', err)
        showNotification('Ошибка при удалении группы', 'error')
      } finally {
        deleting.value = false
      }
    }

    const clearFilters = () => {
      search.value = ''
      statusFilter.value = null
    }

    const showNotification = (text, color = 'success') => {
      snackbar.value = {
        show: true,
        color,
        text
      }
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'Н/Д'
      const date = new Date(dateString)
      return new Intl.DateTimeFormat('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date)
    }

    // Принудительная перерисовка компонента
    const forcedRerender = async () => {
      try {
        await groupStore.fetchGroups();
        console.log('Данные обновлены, принудительная перерисовка');
        // Триггер перерисовки через изменение реактивного свойства
        loading.value = true;
        setTimeout(() => {
          loading.value = false;
        }, 100);
      } catch (err) {
        console.error('Ошибка при обновлении данных:', err);
        showNotification('Ошибка при обновлении данных', 'error');
      }
    }

    // Initialization
    onMounted(async () => {
      console.log('Groups component mounted');
      try {
        console.log('Loading data...');
        await fetchGroups();
        await fetchUsers();
        console.log('Data loaded successfully');
      } catch (error) {
        console.error('Error initializing component:', error);
      }
    })

    return {
      loading,
      error,
      retryLoading,
      search,
      statusFilter,
      errorMessage,
      groupDialog,
      usersDialog,
      viewDialog,
      deleteDialog,
      selectedGroup,
      isEditMode,
      saving,
      deleting,
      groupFormValid,
      groupForm,
      headers,
      filteredGroups,
      fetchGroups,
      clearFilters,
      openAddGroupDialog,
      editGroup,
      viewGroup,
      confirmDelete,
      openManageUsersDialog,
      saveGroup,
      formatDate,
      isAdmin,
      selectedUsers,
      availableUsers,
      maxUsersLimit,
      currentUserCount,
      canAddMoreUsers,
      saveUsers,
      removeUserFromGroup,
      deleteGroup,
      showNotification,
      groupToDelete,
      snackbar,
      userSearch,
      forcedRerender
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

.user-chips {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
  padding: 8px;
}

.v-card-title.bg-primary {
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
}

.v-card-title.bg-error {
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;
}
</style> 

<!-- 
INTEGRATION NOTES:
1. Groups component works together with Users.vue and Progress.vue
2. Groups data is used in the Progress.vue to display group progress
3. Users can be assigned to groups in this component
4. Connected via router at /groups and accessible from the left side menu
5. Progress data for groups is viewable at /learning-progress
--> 