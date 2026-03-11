<template>
  <v-container fluid class="pa-0">
    <!-- Header Section with Parallax Effect -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content d-flex align-center">
          <v-btn
            icon
            color="white"
            class="mr-4"
            @click="$router.push('/user/list')"
          >
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
          <div>
            <h1 class="text-h4 font-weight-bold white--text">
              Редактирование пользователя
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Изменение данных пользователя
            </div>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-row class="mt-6">
      <!-- Left Column -->
      <v-col cols="12" md="4">
        <!-- User Profile Card -->
        <v-card class="mb-6 profile-card" elevation="4">
          <v-card-text class="text-center pa-6">
            <v-avatar
              size="120"
              color="primary"
              class="mb-4"
            >
              <span class="text-h4 white--text">
                {{ user.first_name?.[0] }}{{ user.last_name?.[0] }}
              </span>
            </v-avatar>
            <h2 class="text-h5 font-weight-bold mb-2">
              {{ user.first_name }} {{ user.last_name }}
            </h2>
            <v-chip
              :color="user.is_active ? 'success' : 'error'"
              size="small"
              class="mb-4"
            >
              {{ user.is_active ? 'Активный' : 'Неактивный' }}
            </v-chip>
            <div class="text-body-2 grey--text mb-4">
              {{ user.email }}
            </div>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <div class="d-flex align-center mb-4">
              <v-icon color="primary" class="mr-2">mdi-calendar</v-icon>
              <div>
                <div class="text-subtitle-2">Дата регистрации</div>
                <div class="text-body-2">{{ formatDate(user.created_at) }}</div>
              </div>
            </div>
            <div class="d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-clock-outline</v-icon>
              <div>
                <div class="text-subtitle-2">Последний вход</div>
                <div class="text-body-2">{{ formatDate(user.last_login) }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Action Buttons -->
        <v-card class="mb-6" elevation="4">
          <v-card-text class="pa-4">
            <v-btn
              color="primary"
              block
              class="mb-4"
              @click="saveUser"
              :loading="saving"
              :disabled="!formValid"
            >
              <v-icon left>mdi-content-save</v-icon>
              Сохранить изменения
            </v-btn>
            <v-btn
              color="error"
              variant="outlined"
              block
              @click="confirmDelete"
            >
              <v-icon left>mdi-delete</v-icon>
              Удалить пользователя
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Right Column -->
      <v-col cols="12" md="8">
        <!-- Main Form Section -->
        <v-card class="mb-6" elevation="4">
          <v-card-title class="text-h6 pa-4 primary--text">
            <v-icon left color="primary">mdi-account-edit</v-icon>
            Основная информация
          </v-card-title>
          <v-card-text>
            <v-form ref="userForm" v-model="formValid" @submit.prevent="saveUser">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="userForm.first_name"
                    label="Имя"
                    :rules="[v => !!v || 'Имя обязательно']"
                    required
                    variant="outlined"
                    density="comfortable"
                    class="mb-4"
                    prepend-inner-icon="mdi-account"
                    hide-details="auto"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="userForm.last_name"
                    label="Фамилия"
                    :rules="[v => !!v || 'Фамилия обязательна']"
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
                    item-title="name"
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
        </v-card>

        <!-- Password Section -->
        <v-card class="mb-6" elevation="4">
          <v-card-title class="text-h6 pa-4 primary--text">
            <v-icon left color="primary">mdi-lock-reset</v-icon>
            Изменение пароля
          </v-card-title>
          <v-card-text>
            <v-form ref="passwordForm" v-model="passwordFormValid" @submit.prevent="changePassword">
              <v-row>
                <v-col cols="12" md="6">
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
                </v-col>
                <v-col cols="12" md="6">
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
                </v-col>
                <v-col cols="12">
                  <v-btn
                    color="primary"
                    variant="text"
                    @click="changePassword"
                    :loading="changingPassword"
                    :disabled="!passwordFormValid"
                  >
                    <v-icon left>mdi-lock-reset</v-icon>
                    Изменить пароль
                  </v-btn>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5 pa-4 primary--text">
          <v-icon left color="primary">mdi-alert</v-icon>
          Подтверждение удаления
        </v-card-title>
        <v-card-text class="pa-4">
          Вы действительно хотите удалить пользователя
          {{ user.first_name }} {{ user.last_name }}?
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="deleteDialog = false">
            Отмена
          </v-btn>
          <v-btn
            color="error"
            variant="text"
            @click="deleteUser"
            :loading="deleting"
          >
            <v-icon left>mdi-delete</v-icon>
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
export default {
  name: 'UserEdit',

  data() {
    return {
      user: {},
      userForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        role: '',
        group_id: null,
        is_active: true
      },
      passwordForm: {
        new_password: '',
        confirm_password: ''
      },
      roles: ['Администратор', 'Преподаватель', 'Студент'],
      groups: [],
      formValid: false,
      passwordFormValid: false,
      saving: false,
      deleting: false,
      changingPassword: false,
      deleteDialog: false,
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success'
    }
  },

  created() {
    this.loadUser()
    this.loadGroups()
  },

  methods: {
    async loadUser() {
      try {
        const userId = this.$route.params.id
        const response = await this.$store.dispatch('Users/getUser', userId)
        this.user = response
        this.userForm = { ...response }
      } catch (error) {
        this.showNotification('Ошибка при загрузке пользователя', 'error')
      }
    },

    async loadGroups() {
      try {
        const response = await this.$store.dispatch('Groups/getGroups')
        this.groups = response
      } catch (error) {
        this.showNotification('Ошибка при загрузке групп', 'error')
      }
    },

    async saveUser() {
      if (!this.$refs.userForm.validate()) return

      this.saving = true
      try {
        await this.$store.dispatch('Users/updateUser', {
          id: this.user.id,
          ...this.userForm
        })
        this.showNotification('Пользователь успешно обновлен', 'success')
      } catch (error) {
        this.showNotification('Ошибка при обновлении пользователя', 'error')
      } finally {
        this.saving = false
      }
    },

    async changePassword() {
      if (!this.$refs.passwordForm.validate()) return

      this.changingPassword = true
      try {
        await this.$store.dispatch('Users/changePassword', {
          id: this.user.id,
          password: this.passwordForm.new_password
        })
        this.showNotification('Пароль успешно изменен', 'success')
        this.passwordForm.new_password = ''
        this.passwordForm.confirm_password = ''
      } catch (error) {
        this.showNotification('Ошибка при изменении пароля', 'error')
      } finally {
        this.changingPassword = false
      }
    },

    confirmDelete() {
      this.deleteDialog = true
    },

    async deleteUser() {
      this.deleting = true
      try {
        await this.$store.dispatch('Users/deleteUser', this.user.id)
        this.deleteDialog = false
        this.showNotification('Пользователь успешно удален', 'success')
        this.$router.push('/user/list')
      } catch (error) {
        this.showNotification('Ошибка при удалении пользователя', 'error')
      } finally {
        this.deleting = false
      }
    },

    formatDate(date) {
      if (!date) return 'Не указано'
      return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    showNotification(text, color = 'success') {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
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

.profile-card {
  transition: all 0.3s ease;
  border-radius: 16px;
  overflow: hidden;
}

.profile-card:hover {
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
</style> 