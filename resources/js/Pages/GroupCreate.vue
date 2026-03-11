<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content">
          <h1 class="text-h4 font-weight-bold white--text">
            Создание группы
          </h1>
          <div class="text-subtitle-1 white--text mt-1">
            Создание новой группы пользователей
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="2" class="pa-4">
            <v-form ref="form" v-model="valid" @submit.prevent="createGroup">
              <v-text-field
                v-model="group.name"
                label="Название группы"
                :rules="[
                  v => !!v || 'Обязательное поле',
                  v => v.length >= 3 || 'Минимум 3 символа',
                  v => v.length <= 50 || 'Максимум 50 символов'
                ]"
                required
                variant="outlined"
                class="mb-4"
              ></v-text-field>

              <v-textarea
                v-model="group.description"
                label="Описание группы"
                :rules="[
                  v => !v || v.length <= 500 || 'Максимум 500 символов'
                ]"
                rows="3"
                variant="outlined"
                class="mb-4"
              ></v-textarea>

              <v-select
                v-model="group.permissions"
                :items="availablePermissions"
                label="Права доступа"
                multiple
                chips
                deletable-chips
                variant="outlined"
                class="mb-4"
              ></v-select>

              <v-select
                v-model="group.status"
                :items="statusOptions"
                label="Статус группы"
                variant="outlined"
                class="mb-4"
              ></v-select>
            </v-form>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="2" class="pa-4">
            <v-card-title class="text-h6">Участники группы</v-card-title>
            <v-card-text>
              <v-select
                v-model="selectedUsers"
                :items="users"
                item-text="name"
                item-value="id"
                label="Выберите пользователей"
                multiple
                chips
                deletable-chips
                variant="outlined"
                :loading="loadingUsers"
              ></v-select>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Action Buttons -->
      <v-row class="mt-4">
        <v-col cols="12" class="text-right">
          <v-btn
            color="grey"
            class="mr-4"
            @click="$router.back()"
            :disabled="saving"
          >
            Отмена
          </v-btn>
          <v-btn
            color="primary"
            :loading="saving"
            :disabled="!valid"
            @click="createGroup"
          >
            Создать
          </v-btn>
        </v-col>
      </v-row>

      <!-- Snackbar for Notifications -->
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
  </v-container>
</template>

<script>
import { useNotificationStore } from '@/stores/notificationStore'
import { useGroupStore } from '@/stores/groupStore'
import { useUserStore } from '@/stores/userStore'

export default {
  name: 'GroupCreate',

  setup() {
    const notificationStore = useNotificationStore()
    const groupStore = useGroupStore()
    const userStore = useUserStore()
    return { notificationStore, groupStore, userStore }
  },

  data() {
    return {
      valid: false,
      saving: false,
      loadingUsers: false,
      group: {
        name: '',
        description: '',
        permissions: [],
        status: 'active'
      },
      users: [],
      selectedUsers: [],
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      availablePermissions: [
        { title: 'Управление пользователями', value: 'manage-users' },
        { title: 'Управление группами', value: 'manage-groups' },
        { title: 'Управление курсами', value: 'manage-courses' },
        { title: 'Управление заданиями', value: 'manage-assignments' },
        { title: 'Просмотр отчетов', value: 'view-reports' }
      ],
      statusOptions: [
        { title: 'Активна', value: 'active' },
        { title: 'Неактивна', value: 'inactive' },
        { title: 'Архивная', value: 'archived' }
      ]
    }
  },

  methods: {
    async fetchUsers() {
      this.loadingUsers = true
      try {
        await this.userStore.fetchUsers()
        this.users = this.userStore.users.map(user => ({
          id: user.id,
          name: `${user.firstName} ${user.lastName}`
        }))
      } catch (error) {
        this.showNotification('Ошибка при загрузке пользователей', 'error')
      } finally {
        this.loadingUsers = false
      }
    },

    async createGroup() {
      if (!this.$refs.form.validate()) return
      
      this.saving = true
      try {
        const groupData = {
          ...this.group,
          users: this.selectedUsers
        }
        
        await this.groupStore.createGroup(groupData)
        this.showNotification('Группа успешно создана', 'success')
        this.$router.push({ name: 'groups.index' })
      } catch (error) {
        this.showNotification('Ошибка при создании группы', 'error')
      } finally {
        this.saving = false
      }
    },

    showNotification(text, type = 'success') {
      this.snackbar.text = text
      this.snackbar.color = type
      this.snackbar.show = true
      
      this.notificationStore.addNotification({
        text,
        type,
        icon: type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'
      })
    }
  },

  mounted() {
    this.fetchUsers()
  }
}
</script>

<style scoped>
.header-section {
  background: linear-gradient(45deg, #1976d2, #2196f3);
  padding: 2rem 0;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.v-card {
  border-radius: 8px;
}
</style> 