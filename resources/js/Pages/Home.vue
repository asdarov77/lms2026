<template>
  <div>
    <component :is="viewComponent" v-if="viewComponent"></component>
    <p v-if="authStore.user">Добро пожаловать, {{ authStore.user.fio }}</p>
    <p v-else>Пожалуйста, войдите в систему</p>
  </div>
</template>

<script setup>
import { shallowRef, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import UserPage from "./User/UserPage.vue"
import DashboardPage from "./DashboardPage.vue"

const authStore = useAuthStore()
const viewComponent = shallowRef(null)

const roleComponentMapping = {
  'Обучаемый': UserPage,
  'Администратор': DashboardPage,
  'Инструктор': DashboardPage
}

watch(() => authStore.user, (newUser) => {
  if (newUser && newUser.role) {
    viewComponent.value = roleComponentMapping[newUser.role] || null
  } else {
    viewComponent.value = null
  }
}, { immediate: true })
</script>