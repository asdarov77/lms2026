<template>
  <div>
    <h2 class="text-h5 mb-4">Настройки профиля</h2>
    <v-form ref="profileForm" v-model="profileValid">
      <v-row>
        <v-col cols="12" md="6">
          <v-text-field
            v-model="profile.first_name"
            label="Имя"
            :rules="[v => !!v || 'Обязательное поле']"
            required
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field
            v-model="profile.last_name"
            label="Фамилия"
            :rules="[v => !!v || 'Обязательное поле']"
            required
          ></v-text-field>
        </v-col>
        <v-col cols="12">
          <v-text-field
            v-model="profile.email"
            label="Email"
            :rules="[
              v => !!v || 'Обязательное поле',
              v => /.+@.+\..+/.test(v) || 'Некорректный email'
            ]"
            required
          ></v-text-field>
        </v-col>
        <v-col cols="12">
          <v-text-field
            v-model="profile.phone"
            label="Телефон"
            :rules="[v => !v || /^\+?[\d\s-()]+$/.test(v) || 'Некорректный номер']"
          ></v-text-field>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn
            color="primary"
            @click="saveProfile"
            :loading="saving"
            :disabled="!profileValid"
          >
            Сохранить
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const profileForm = ref(null)
const profileValid = ref(false)
const saving = ref(false)

const profile = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
})

onMounted(() => {
  if (authStore.user) {
    profile.value = {
      first_name: authStore.user.first_name,
      last_name: authStore.user.last_name,
      email: authStore.user.email,
      phone: authStore.user.phone || ''
    }
  }
})

const saveProfile = async () => {
  if (!profileValid.value) return
  
  saving.value = true
  try {
    await authStore.updateProfile(profile.value)
    // Show success message
  } catch (error) {
    // Show error message
  } finally {
    saving.value = false
  }
}
</script> 