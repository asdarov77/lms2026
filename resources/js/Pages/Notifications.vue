<template>
  <v-container fluid>
    <!-- Header Section -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <h1 class="text-h4 font-weight-bold primary--text">Уведомления</h1>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end">
        <v-btn
          color="primary"
          @click="markAllAsRead"
          prepend-icon="mdi-check-all"
        >
          Отметить все как прочитанные
        </v-btn>
      </v-col>
    </v-row>

    <!-- Filter Section -->
    <v-card class="mb-4">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="typeFilter"
              :items="notificationTypes"
              label="Тип уведомления"
              variant="outlined"
              density="compact"
              class="mb-4"
              @update:model-value="handleFilter"
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="statusFilter"
              :items="statuses"
              label="Статус"
              variant="outlined"
              density="compact"
              class="mb-4"
              @update:model-value="handleFilter"
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Notifications List -->
    <v-card>
      <v-list>
        <v-list-item
          v-for="notification in filteredNotifications"
          :key="notification.id"
          :class="{ 'unread': !notification.read }"
          @click="handleNotificationClick(notification)"
        >
          <template v-slot:prepend>
            <v-icon
              :color="getNotificationColor(notification.type)"
              size="large"
              class="mr-2"
            >
              {{ getNotificationIcon(notification.type) }}
            </v-icon>
          </template>

          <v-list-item-title class="text-subtitle-1">
            {{ notification.title }}
          </v-list-item-title>

          <v-list-item-subtitle class="text-body-2">
            {{ notification.message }}
          </v-list-item-subtitle>

          <template v-slot:append>
            <div class="d-flex align-center">
              <span class="text-caption grey--text mr-2">
                {{ formatDate(notification.created_at) }}
              </span>
              <v-chip
                v-if="!notification.read"
                color="primary"
                size="small"
              >
                Новое
              </v-chip>
            </div>
          </template>
        </v-list-item>

        <v-list-item v-if="filteredNotifications.length === 0">
          <v-list-item-title class="text-center">
            Нет уведомлений
          </v-list-item-title>
        </v-list-item>
      </v-list>
    </v-card>

    <!-- Notification Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="600px">
      <v-card>
        <v-card-title class="text-h5">
          {{ selectedNotification?.title }}
        </v-card-title>
        <v-card-text>
          <div class="text-body-1 mb-4">
            {{ selectedNotification?.message }}
          </div>
          <div class="text-caption grey--text">
            {{ formatDate(selectedNotification?.created_at) }}
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="text"
            @click="detailsDialog = false"
          >
            Закрыть
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      :timeout="3000"
    >
      {{ snackbarText }}
    </v-snackbar>
  </v-container>
</template>

<script>
export default {
  name: 'Notifications',

  data() {
    return {
      typeFilter: null,
      statusFilter: null,
      notifications: [],
      notificationTypes: [
        'Все',
        'Задания',
        'Курсы',
        'Системные',
        'Сообщения'
      ],
      statuses: [
        'Все',
        'Прочитанные',
        'Непрочитанные'
      ],
      detailsDialog: false,
      selectedNotification: null,
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success'
    }
  },

  computed: {
    filteredNotifications() {
      return this.notifications.filter(notification => {
        const matchesType = !this.typeFilter || this.typeFilter === 'Все' || notification.type === this.typeFilter
        const matchesStatus = !this.statusFilter || this.statusFilter === 'Все' ||
          (this.statusFilter === 'Прочитанные' && notification.read) ||
          (this.statusFilter === 'Непрочитанные' && !notification.read)
        return matchesType && matchesStatus
      })
    }
  },

  created() {
    this.loadNotifications()
  },

  methods: {
    async loadNotifications() {
      try {
        const response = await this.$store.dispatch('Notifications/getNotifications')
        this.notifications = response
      } catch (error) {
        this.showNotification('Ошибка при загрузке уведомлений', 'error')
      }
    },

    handleFilter() {
      // Implement filter logic
    },

    async handleNotificationClick(notification) {
      if (!notification.read) {
        await this.markAsRead(notification.id)
      }
      this.selectedNotification = notification
      this.detailsDialog = true
    },

    async markAsRead(notificationId) {
      try {
        await this.$store.dispatch('Notifications/markAsRead', notificationId)
        this.loadNotifications()
      } catch (error) {
        this.showNotification('Ошибка при обновлении статуса уведомления', 'error')
      }
    },

    async markAllAsRead() {
      try {
        await this.$store.dispatch('Notifications/markAllAsRead')
        this.loadNotifications()
        this.showNotification('Все уведомления отмечены как прочитанные', 'success')
      } catch (error) {
        this.showNotification('Ошибка при обновлении статуса уведомлений', 'error')
      }
    },

    getNotificationColor(type) {
      const colors = {
        'Задания': 'warning',
        'Курсы': 'primary',
        'Системные': 'info',
        'Сообщения': 'success'
      }
      return colors[type] || 'grey'
    },

    getNotificationIcon(type) {
      const icons = {
        'Задания': 'mdi-clipboard-check',
        'Курсы': 'mdi-book-open-variant',
        'Системные': 'mdi-information',
        'Сообщения': 'mdi-message-text'
      }
      return icons[type] || 'mdi-bell'
    },

    formatDate(date) {
      return new Date(date).toLocaleString('ru-RU')
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
.v-card {
  border-radius: 8px;
}

.v-list-item {
  border-radius: 8px;
  margin-bottom: 8px;
}

.v-list-item.unread {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.v-list-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.1);
}
</style> 