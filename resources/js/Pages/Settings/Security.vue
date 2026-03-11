<template>
  <div>
    <h2 class="text-h5 mb-4">Безопасность</h2>
    <v-form ref="securityForm" v-model="securityValid">
      <v-row>
        <v-col cols="12">
          <v-text-field
            v-model="security.current_password"
            label="Текущий пароль"
            type="password"
            :rules="[v => !!v || 'Обязательное поле']"
            required
          ></v-text-field>
        </v-col>
        <v-col cols="12">
          <v-text-field
            v-model="security.new_password"
            label="Новый пароль"
            type="password"
            :rules="[
              v => !!v || 'Обязательное поле',
              v => v.length >= 8 || 'Пароль должен содержать минимум 8 символов'
            ]"
            required
          ></v-text-field>
        </v-col>
        <v-col cols="12">
          <v-text-field
            v-model="security.confirm_password"
            label="Подтверждение пароля"
            type="password"
            :rules="[
              v => !!v || 'Обязательное поле',
              v => v === security.new_password || 'Пароли не совпадают'
            ]"
            required
          ></v-text-field>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn
            color="primary"
            @click="changePassword"
            :loading="saving"
            :disabled="!securityValid"
          >
            Изменить пароль
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const securityForm = ref(null)
const securityValid = ref(false)
const saving = ref(false)

const security = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const changePassword = async () => {
  if (!securityValid.value) return
  
  saving.value = true
  try {
    await authStore.changePassword(security.value)
    // Clear form
    security.value = {
      current_password: '',
      new_password: '',
      confirm_password: ''
    }
    // Show success message
  } catch (error) {
    // Show error message
  } finally {
    saving.value = false
  }
}
</script> 