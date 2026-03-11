<template>
  <v-container fluid class="pa-0">
    <!-- Header Section with Parallax Effect -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content d-flex align-center justify-space-between">
          <div>
            <h1 class="text-h4 font-weight-bold white--text">
              Управление пользователями
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Список всех пользователей системы
            </div>
          </div>
          <v-btn
            color="white"
            variant="outlined"
            class="add-user-btn"
            @click="openAddUserDialog"
          >
            <v-icon left>mdi-account-plus</v-icon>
            Добавить пользователя
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-row class="mt-6">
      <v-col cols="12">
        <!-- Search and Filters -->
        <v-card class="mb-6" elevation="4">
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="search"
                  label="Поиск пользователей"
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="comfortable"
                  hide-details="auto"
                  clearable
                  @click:clear="search = ''"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="roleFilter"
                  :items="roles"
                  label="Фильтр по роли"
                  prepend-inner-icon="mdi-account-key"
                  variant="outlined"
                  density="comfortable"
                  hide-details="auto"
                  clearable
                  @click:clear="roleFilter = null"
                ></v-select>
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="groupFilter"
                  :items="groups"
                  item-title="groupname"
                  item-value="id"
                  label="Фильтр по группе"
                  prepend-inner-icon="mdi-account-group"
                  variant="outlined"
                  density="comfortable"
                  hide-details="auto"
                  clearable
                  @click:clear="groupFilter = null"
                  :menu-props="{ maxHeight: '400px' }"
                  :loading="loading"
                  :error="!!error"
                  :error-messages="error"
                ></v-select>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Users Grid -->
        <v-row>
          <v-col
            v-for="user in filteredUsers"
            :key="user.id"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            xl="3"
          >
            <v-card class="user-card" elevation="4">
              <v-card-text class="text-center pa-6">
                <v-avatar
                  size="80"
                  :color="getRoleColor(user.role)"
                  class="mb-4"
                >
                  <span class="text-h4 white--text">
                    {{ user.fio?.[0] }}
                  </span>
                </v-avatar>
                <h3 class="text-h6 font-weight-bold mb-2">
                  {{ user.fio }}
                </h3>
                <v-chip
                  :color="getRoleColor(user.role)"
                  size="small"
                  class="mb-2"
                >
                  {{ user.role }}
                </v-chip>
                <div class="text-body-2 grey--text mb-4">
                  {{ user.email }}
                </div>
                <div class="d-flex align-center justify-center mb-4">
                  <v-icon color="primary" size="small" class="mr-1">mdi-account-group</v-icon>
                  <span class="text-caption">{{ getGroupName(user.group_id) || 'Нет группы' }}</span>
                </div>
              </v-card-text>
              <v-divider></v-divider>
              <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn
                  icon
                  color="primary"
                  variant="text"
                  @click="editUser(user)"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                  icon
                  color="warning"
                  variant="text"
                  @click="openChangePasswordDialog(user)"
                >
                  <v-icon>mdi-lock-reset</v-icon>
                </v-btn>
                <v-btn
                  icon
                  color="error"
                  variant="text"
                  @click="confirmDelete(user)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <!-- Add/Edit User Dialog -->
    <v-dialog v-model="userDialog" max-width="600px">
      <v-card>
        <v-card-title class="text-h5 pa-4 primary--text">
          <v-icon left color="primary">{{ isEditing ? 'mdi-account-edit' : 'mdi-account-plus' }}</v-icon>
          {{ isEditing ? 'Редактирование пользователя' : 'Добавление пользователя' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="userForm" v-model="formValid" @submit.prevent="saveUser">
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="userForm.fio"
                  label="ФИО"
                  :rules="[v => !!v || 'ФИО обязательно']"
                  required
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                  prepend-inner-icon="mdi-account"
                  hide-details="auto"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="userForm.email"
                  label="Email"
                  :rules="[
                    v => !!v || 'Email обязателен',
                    v => /.+@.+\..+/.test(v) || 'Некорректный email'
                  ]"
                  required
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                  prepend-inner-icon="mdi-email"
                  hide-details="auto"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="userForm.phone"
                  label="Телефон"
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                  prepend-inner-icon="mdi-phone"
                  hide-details="auto"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="userForm.role"
                  :items="roles"
                  label="Роль"
                  :rules="[v => !!v || 'Роль обязательна']"
                  required
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                  prepend-inner-icon="mdi-account-key"
                  hide-details="auto"
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="userForm.group_id"
                  :items="groups"
                  item-title="groupname"
                  item-value="id"
                  label="Группа"
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                  prepend-inner-icon="mdi-account-group"
                  hide-details="auto"
                ></v-select>
              </v-col>
              <v-col cols="12">
                <v-switch
                  v-model="userForm.is_active"
                  label="Активный пользователь"
                  color="primary"
                  class="mt-2"
                ></v-switch>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="userDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="primary"
            variant="text"
            @click="saveUser"
            :loading="saving"
            :disabled="!formValid"
          >
            Сохранить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Change Password Dialog -->
    <v-dialog v-model="passwordDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5 pa-4 primary--text">
          <v-icon left color="primary">mdi-lock-reset</v-icon>
          Изменение пароля
        </v-card-title>
        <v-card-text>
          <v-form ref="passwordForm" v-model="passwordFormValid" @submit.prevent="handlePasswordChange">
            <v-text-field
              v-model="passwordForm.new_password"
              label="Новый пароль"
              type="password"
              :rules="[
                v => !!v || 'Пароль обязателен',
                v => v.length >= 8 || 'Минимум 8 символов'
              ]"
              required
              variant="outlined"
              density="comfortable"
              class="mb-4"
              prepend-inner-icon="mdi-lock"
              hide-details="auto"
            ></v-text-field>
            <v-text-field
              v-model="passwordForm.confirm_password"
              label="Подтверждение пароля"
              type="password"
              :rules="[
                v => !!v || 'Подтверждение пароля обязательно',
                v => v === passwordForm.new_password || 'Пароли не совпадают'
              ]"
              required
              variant="outlined"
              density="comfortable"
              class="mb-4"
              prepend-inner-icon="mdi-lock-check"
              hide-details="auto"
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="passwordDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="primary"
            variant="text"
            @click="handlePasswordChange"
            :loading="changingPassword"
            :disabled="!passwordFormValid"
          >
            Изменить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5 pa-4 primary--text">
          <v-icon left color="primary">mdi-alert</v-icon>
          Подтверждение удаления
        </v-card-title>
        <v-card-text class="pa-4">
          Вы действительно хотите удалить пользователя
          {{ selectedUser?.fio }}?
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="deleteDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="error"
            variant="text"
            @click="handleUserDelete"
            :loading="deleting"
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      :timeout="3000"
      location="top"
    >
      {{ snackbarText }}
    </v-snackbar>
  </v-container>
</template>

<script>
import axios from '@/api/httpClient'
import { useUserStore } from '@/stores/userStore'
import { mapActions } from 'vuex'

export default {
  name: 'UserList',

  setup() {
    const userStore = useUserStore()
    return { userStore }
  },

  data() {
    return {
      search: '',
      roleFilter: null,
      groupFilter: null,
      roles: ['Администратор', 'Преподаватель', 'Студент'],
      groups: [],
      users: [],
      loading: true,
      error: null,
      userDialog: false,
      passwordDialog: false,
      deleteDialog: false,
      isEditing: false,
      formValid: false,
      passwordFormValid: false,
      saving: false,
      deleting: false,
      changingPassword: false,
      selectedUser: null,
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success',
      userForm: {
        fio: '',
        email: '',
        phone: '',
        role: '',
        group_id: null,
        is_active: true
      },
      passwordForm: {
        new_password: '',
        confirm_password: ''
      }
    }
  },

  computed: {
    filteredUsers() {
      if (!Array.isArray(this.users)) {
        return []
      }
      return this.users.filter(user => {
        const matchesSearch = !this.search || 
          (user.fio?.toLowerCase().includes(this.search.toLowerCase()) ||
          user.email?.toLowerCase().includes(this.search.toLowerCase()))
        
        const matchesRole = !this.roleFilter || user.role === this.roleFilter
        const matchesGroup = !this.groupFilter || user.group_id === this.groupFilter
        
        return matchesSearch && matchesRole && matchesGroup
      })
    }
  },

  async created() {
    try {
      this.loading = true
      await Promise.all([
        this.loadUsers(),
        this.loadGroups()
      ])
    } catch (error) {
      console.error('Error loading data:', error)
      this.error = 'Ошибка при загрузке данных'
      this.showNotification('Ошибка при загрузке данных', 'error')
    } finally {
      this.loading = false
    }
  },

  methods: {
    ...mapActions('User', [
      'fetchUsers',
      'fetchGroups',
      'createUser',
      'updateUser',
      'deleteUser',
      'changePassword'
    ]),

    getRoleColor(role) {
      const colors = {
        'Администратор': 'primary',
        'Преподаватель': 'warning',
        'Студент': 'success'
      }
      return colors[role] || 'grey'
    },

    async loadGroups() {
      try {
        await this.userStore.fetchGroups()
        
        const groupsData = this.userStore.groups
        console.log('Groups from store:', groupsData)
        
        if (groupsData && Array.isArray(groupsData)) {
          this.groups = groupsData
          console.log('Groups loaded successfully:', this.groups)
        } else {
          if (this.groups && this.groups.length > 0) {
            console.log('Groups already loaded:', this.groups)
          } else {
            console.error('Invalid groups data structure:', groupsData)
            this.groups = []
            this.error = 'Некорректный формат данных групп'
          }
        }
      } catch (error) {
        console.error('Error loading groups:', error)
        this.groups = []
        this.error = 'Ошибка при загрузке групп'
        this.showNotification('Ошибка при загрузке групп', 'error')
      }
    },

    async loadUsers() {
      try {
        this.loading = true
        
        await this.userStore.fetchUsers()
        
        const usersData = this.userStore.users
        console.log('Users from store:', usersData)
        
        if (usersData && Array.isArray(usersData)) {
          this.users = usersData
          console.log('Users loaded successfully:', this.users)
        } else {
          if (this.users && this.users.length > 0) {
            console.log('Users already loaded:', this.users)
          } else {
            console.error('Invalid users data:', usersData)
            this.users = []
            this.error = 'Некорректный формат данных пользователей'
          }
        }
      } catch (error) {
        console.error('Error loading users:', error)
        this.users = []
        this.error = 'Ошибка при загрузке пользователей'
        this.showNotification('Ошибка при загрузке пользователей', 'error')
      } finally {
        this.loading = false
      }
    },

    openAddUserDialog() {
      this.isEditing = false
      this.selectedUser = null
      this.userForm = {
        fio: '',
        email: '',
        phone: '',
        role: '',
        group_id: null,
        is_active: true
      }
      this.userDialog = true
    },

    editUser(user) {
      this.isEditing = true
      this.selectedUser = JSON.parse(JSON.stringify(user))
      this.userForm = { ...user }
      this.userDialog = true
    },

    openChangePasswordDialog(user) {
      this.selectedUser = JSON.parse(JSON.stringify(user))
      this.passwordForm = {
        new_password: '',
        confirm_password: ''
      }
      this.passwordDialog = true
    },

    async saveUser() {
      if (!this.$refs.userForm.validate()) return

      this.saving = true
      try {
        if (this.isEditing) {
          await this.updateUser({
            id: this.selectedUser.id,
            ...this.userForm
          })
          this.showNotification('Пользователь успешно обновлен', 'success')
        } else {
          await this.createUser(this.userForm)
          this.showNotification('Пользователь успешно создан', 'success')
        }
        this.userDialog = false
        this.loadUsers()
      } catch (error) {
        console.error('Error saving user:', error)
        this.showNotification('Ошибка при сохранении пользователя', 'error')
      } finally {
        this.saving = false
      }
    },

    async handlePasswordChange() {
      if (!this.$refs.passwordForm.validate()) return

      this.changingPassword = true
      try {
        await this.changePassword({
          id: this.selectedUser.id,
          password: this.passwordForm.new_password
        })
        this.showNotification('Пароль успешно изменен', 'success')
        this.passwordDialog = false
      } catch (error) {
        console.error('Error changing password:', error)
        this.showNotification('Ошибка при изменении пароля', 'error')
      } finally {
        this.changingPassword = false
      }
    },

    confirmDelete(user) {
      this.selectedUser = JSON.parse(JSON.stringify(user))
      this.deleteDialog = true
    },

    async handleUserDelete() {
      this.deleting = true
      try {
        await this.deleteUser(this.selectedUser.id)
        this.deleteDialog = false
        this.showNotification('Пользователь успешно удален', 'success')
        this.loadUsers()
      } catch (error) {
        console.error('Error deleting user:', error)
        this.showNotification('Ошибка при удалении пользователя', 'error')
      } finally {
        this.deleting = false
      }
    },

    showNotification(text, color = 'success') {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    },

    getGroupName(groupId) {
      const group = this.groups.find(g => g.id === groupId)
      return group?.groupname || 'Нет группы'
    }
  }
}
</script>

<style scoped>
.header-section {
  background: linear-gradient(135deg, var(--v-primary-base) 0%, var(--v-secondary-base) 100%);
  min-height: 200px;
  position: relative;
  overflow: hidden;
}

.header-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
  background-size: cover;
  opacity: 0.1;
}

.header-content {
  position: relative;
  z-index: 1;
  padding: 48px 24px;
}

.add-user-btn {
  transition: all 0.3s ease;
}

.add-user-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
}

.user-card {
  transition: all 0.3s ease;
  border-radius: 16px;
  overflow: hidden;
  height: 100%;
}

.user-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.v-card {
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.v-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.v-text-field,
.v-select {
  background-color: white;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.v-text-field:hover,
.v-select:hover {
  transform: translateY(-2px);
}

.v-btn {
  text-transform: none;
  letter-spacing: 0;
  transition: all 0.3s ease;
}

.v-btn:hover {
  transform: translateY(-2px);
}

.v-chip {
  font-weight: 500;
  text-transform: capitalize;
  transition: all 0.3s ease;
}

.v-chip:hover {
  transform: scale(1.05);
}

.v-snackbar {
  border-radius: 8px;
  margin: 16px;
}

.v-dialog .v-card {
  border-radius: 16px;
}

.v-dialog .v-card-title {
  background-color: var(--v-theme-primary);
  color: white;
}

.v-dialog .v-card-actions {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}

.v-avatar {
  border: 4px solid var(--v-theme-primary);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.v-avatar:hover {
  transform: scale(1.05);
}

.v-icon {
  transition: all 0.3s ease;
}

.v-icon:hover {
  transform: scale(1.1);
}

.v-switch {
  transition: all 0.3s ease;
}

.v-switch:hover {
  transform: translateX(4px);
}

/* Animation for cards */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.user-card {
  animation: fadeIn 0.5s ease-out;
}
</style>