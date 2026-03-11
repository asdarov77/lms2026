<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content">
          <h1 class="text-h4 font-weight-bold white--text">
            Настройки
          </h1>
          <div class="text-subtitle-1 white--text mt-1">
            Управление настройками системы и профиля
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
      <v-row>
        <!-- Navigation -->
        <v-col cols="12" md="3">
          <v-card elevation="2">
            <v-list>
              <v-list-item
                v-for="(item, i) in menuItems"
                :key="i"
                :to="item.to"
                :exact="item.exact"
                active-class="primary--text"
              >
                <template v-slot:prepend>
                  <v-icon>{{ item.icon }}</v-icon>
                </template>
                <v-list-item-title>{{ item.title }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card>
        </v-col>

        <!-- Content -->
        <v-col cols="12" md="9">
          <v-card elevation="2">
            <v-card-text>
              <router-view></router-view>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const menuItems = [
  { title: 'Профиль', icon: 'mdi-account', to: '/settings', exact: true },
  { title: 'Безопасность', icon: 'mdi-lock', to: '/settings/security' },
  { title: 'Уведомления', icon: 'mdi-bell', to: '/settings/notifications' },
  { title: 'Внешний вид', icon: 'mdi-palette', to: '/settings/appearance' }
]
</script>

<style scoped>
.header-section {
  background: linear-gradient(45deg, var(--v-primary-base), var(--v-secondary-base));
  padding: 48px 0;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

.v-list-item {
  border-radius: 4px;
  margin: 4px 8px;
}

.v-list-item--active {
  background-color: rgba(var(--v-primary-base), 0.1);
}
</style> 