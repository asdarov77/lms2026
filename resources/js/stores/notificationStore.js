import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0
  }),

  actions: {
    addNotification(notification) {
      this.notifications.unshift({
        id: Date.now(),
        ...notification,
        read: false
      })
      this.unreadCount++
    },

    markAsRead(id) {
      const notification = this.notifications.find(n => n.id === id)
      if (notification && !notification.read) {
        notification.read = true
        this.unreadCount--
      }
    },

    markAllAsRead() {
      this.notifications.forEach(n => n.read = true)
      this.unreadCount = 0
    },

    clearNotifications() {
      this.notifications = []
      this.unreadCount = 0
    }
  }
}) 