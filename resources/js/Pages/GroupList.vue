<template>
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

    <!-- Groups Table -->
    <v-card>
      <v-data-table
        :headers="headers"
        :items="filteredGroups"
        :loading="loading"
        :items-per-page="10"
        class="elevation-1"
      >
        <template v-slot:item.is_active="{ item }">
          <v-chip
            :color="item.is_active ? 'success' : 'error'"
            size="small"
            class="text-caption"
          >
            {{ item.is_active ? 'Активна' : 'Неактивна' }}
          </v-chip>
        </template>

        <template v-slot:item.users="{ item }">
          {{ item.users ? item.users.length : 0 }} человек
        </template>

        <template v-slot:item.creator="{ item }">
          {{ item.creator ? item.creator.fio : 'Неизвестно' }}
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
            :disabled="selectedUsers.length === 0"
            @click="addUsersToGroup"
          >
            Добавить пользователей
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
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import httpClient from '@/api/httpClient'

// Simple admin check
const isAdmin = computed(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  return user.role === 'Администратор' || user.role === 'admin'
})

// Create a simple API function
const getApi = () => {
  return httpClient
}

// State
const groups = ref([])
const users = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const search = ref('')
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
  { title: 'Название', key: 'groupname', align: 'start', width: '25%' },
  { title: 'Описание', key: 'groupdescription', align: 'start', width: '25%' },
  { title: 'Статус', key: 'is_active', align: 'start', width: '10%' },
  { title: 'Участники', key: 'users', align: 'start', width: '10%' },
  { title: 'Создатель', key: 'creator', align: 'start', width: '15%' },
  { title: 'Действия', key: 'actions', align: 'center', sortable: false, width: '10%' }
]

// Computed
const filteredGroups = computed(() => {
  return groups.value.filter(group => {
    const matchesSearch = group.groupname.toLowerCase().includes(search.value.toLowerCase()) ||
                          group.groupdescription.toLowerCase().includes(search.value.toLowerCase())
    
    const matchesStatus = statusFilter.value === null || group.is_active === statusFilter.value
    
    return matchesSearch && matchesStatus
  })
})

const availableUsers = computed(() => {
  if (!selectedGroup.value || !selectedGroup.value.users) {
    return users.value
  }
  
  const currentUserIds = selectedGroup.value.users.map(u => u.id)
  return users.value.filter(user => !currentUserIds.includes(user.id))
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

// Methods
const fetchGroups = async () => {
  loading.value = true
  try {
    const response = await getApi().get('/groups')
    groups.value = response.data.data || []
    console.log('Groups loaded:', groups.value)
  } catch (error) {
    console.error('Error fetching groups:', error)
    showNotification('Ошибка при загрузке групп', 'error')
  } finally {
    loading.value = false
  }
}

const fetchUsers = async () => {
  try {
    const response = await getApi().post('/user/list')
    users.value = response.data
    console.log('Users loaded:', users.value)
  } catch (error) {
    console.error('Error fetching users:', error)
    showNotification('Ошибка при загрузке пользователей', 'error')
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

const editGroup = (group) => {
  isEditMode.value = true
  selectedGroup.value = JSON.parse(JSON.stringify(group))
  groupForm.value = {
    groupname: group.groupname,
    groupdescription: group.groupdescription,
    is_active: !!group.is_active,
    max_users: group.max_users,
    settings: group.settings || {}
  }
  groupDialog.value = true
}

const viewGroup = (group) => {
  selectedGroup.value = JSON.parse(JSON.stringify(group))
  viewDialog.value = true
}

const openManageUsersDialog = (group) => {
  selectedGroup.value = JSON.parse(JSON.stringify(group))
  selectedUsers.value = []
  usersDialog.value = true
}

const confirmDelete = (group) => {
  groupToDelete.value = group
  deleteDialog.value = true
}

const saveGroup = async () => {
  saving.value = true
  try {
    // Create a clean object with only the data fields (avoid circular references)
    const formData = {
      groupname: groupForm.value.groupname,
      groupdescription: groupForm.value.groupdescription,
      is_active: groupForm.value.is_active,
      max_users: groupForm.value.max_users,
      settings: groupForm.value.settings
    }
    if (isEditMode.value) {
      await getApi().put(`/groups/${selectedGroup.value.id}`, formData)
      showNotification('Группа успешно обновлена', 'success')
    } else {
      await getApi().post('/groups', formData)
      showNotification('Группа успешно создана', 'success')
    }
    groupDialog.value = false
    await fetchGroups()
  } catch (e) {
    console.error('Error saving group:', e)
    console.error('Error response:', e.response?.data)
    showNotification('Ошибка при сохранении группы: ' + (e.response?.data?.message || e.message), 'error')
  } finally {
    saving.value = false
  }
}

const addUsersToGroup = async () => {
  if (!selectedGroup.value || selectedUsers.value.length === 0) return

  saving.value = true
  try {
    await getApi().post(`/groups/${selectedGroup.value.id}/add-users`, {
      user_ids: selectedUsers.value
    })
    
    // Refresh the group data
    const updatedGroup = await getApi().get(`/groups/${selectedGroup.value.id}`)
    const index = groups.value.findIndex(g => g.id === selectedGroup.value.id)
    if (index !== -1) {
      groups.value[index] = updatedGroup.data
      selectedGroup.value = updatedGroup.data
    }
    
    selectedUsers.value = []
    showNotification('Пользователи успешно добавлены в группу', 'success')
  } catch (error) {
    console.error('Error adding users to group:', error)
    showNotification('Ошибка при добавлении пользователей', 'error')
  } finally {
    saving.value = false
  }
}

const removeUserFromGroup = async (user) => {
  if (!selectedGroup.value || !user) return

  saving.value = true
  try {
    await getApi().delete(`/groups/${selectedGroup.value.id}/remove-users`, {
      data: { user_ids: [user.id] }
    })
    
    // Update local data
    const index = selectedGroup.value.users.findIndex(u => u.id === user.id)
    if (index !== -1) {
      selectedGroup.value.users.splice(index, 1)
    }
    
    showNotification('Пользователь успешно удален из группы', 'success')
  } catch (error) {
    console.error('Error removing user from group:', error)
    showNotification('Ошибка при удалении пользователя из группы', 'error')
  } finally {
    saving.value = false
  }
}

const deleteGroup = async () => {
  if (!groupToDelete.value) return

  deleting.value = true
  try {
    await getApi().delete(`/groups/${groupToDelete.value.id}`)
    deleteDialog.value = false
    showNotification('Группа успешно удалена', 'success')
    await fetchGroups()
  } catch (error) {
    console.error('Error deleting group:', error)
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

// Initialization
onMounted(async () => {
  await fetchGroups()
  await fetchUsers()
})
</script>

<style scoped>
.v-data-table :deep(th) {
  background-color: #f5f5f5 !important;
  color: rgba(0, 0, 0, 0.87) !important;
  font-weight: bold !important;
}
</style>

  
