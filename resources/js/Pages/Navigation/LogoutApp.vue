<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const logoutDialog = ref(false)
const loading = ref(false)

const confirmLogout = () => {
  logoutDialog.value = true
}

const logout = async () => {
  loading.value = true
  try {
    await authStore.logout()
    window.location.href = '/login'
  } catch (error) {
    console.error('Ошибка при выходе из системы:', error)
  } finally {
    loading.value = false
    logoutDialog.value = false
  }
}
</script>

<template>
  <v-list-item
    prepend-icon="mdi-logout"
    title="Выйти"
    value="logout"
    @click="confirmLogout"
    active-color="error"
    class="logout-item"
  ></v-list-item>

  <v-dialog v-model="logoutDialog" max-width="400px">
    <v-card>
      <v-card-title class="text-h5 bg-primary text-white py-3">
        Подтверждение выхода
      </v-card-title>
      <v-card-text class="pt-4">
        <p>Вы действительно хотите выйти из системы?</p>
      </v-card-text>
      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn
          color="grey"
          variant="text"
          @click="logoutDialog = false"
        >
          Отмена
        </v-btn>
        <v-btn
          color="error"
          variant="text"
          :loading="loading"
          @click="logout"
        >
          Выйти
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.logout-item {
  margin-top: auto;
}
</style>
