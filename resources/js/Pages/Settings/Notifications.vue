<template>
  <div>
    <h2 class="text-h5 mb-4">Уведомления</h2>
    <v-form ref="notificationsForm" v-model="notificationsValid">
      <v-row>
        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              Email уведомления
            </v-card-title>
            <v-card-text>
              <v-switch
                v-model="notifications.email"
                label="Получать уведомления по email"
                color="primary"
              ></v-switch>
              <v-text-field
                v-if="notifications.email"
                v-model="notifications.email_address"
                label="Email для уведомлений"
                :rules="[
                  v => !notifications.email || !!v || 'Обязательное поле',
                  v => !notifications.email || /.+@.+\..+/.test(v) || 'Некорректный email'
                ]"
                :required="notifications.email"
              ></v-text-field>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              SMS уведомления
            </v-card-title>
            <v-card-text>
              <v-switch
                v-model="notifications.sms"
                label="Получать SMS уведомления"
                color="primary"
              ></v-switch>
              <v-text-field
                v-if="notifications.sms"
                v-model="notifications.phone_number"
                label="Номер телефона для SMS"
                :rules="[
                  v => !notifications.sms || !!v || 'Обязательное поле',
                  v => !notifications.sms || /^\+?[\d\s-()]+$/.test(v) || 'Некорректный номер'
                ]"
                :required="notifications.sms"
              ></v-text-field>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              Push уведомления
            </v-card-title>
            <v-card-text>
              <v-switch
                v-model="notifications.push"
                label="Получать push-уведомления"
                color="primary"
              ></v-switch>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn
            color="primary"
            @click="saveNotifications"
            :loading="saving"
            :disabled="!notificationsValid"
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
const notificationsForm = ref(null)
const notificationsValid = ref(false)
const saving = ref(false)

const notifications = ref({
  email: true,
  email_address: '',
  sms: false,
  phone_number: '',
  push: true
})

onMounted(() => {
  if (authStore.user) {
    notifications.value = {
      email: authStore.user.notifications?.email ?? true,
      email_address: authStore.user.email,
      sms: authStore.user.notifications?.sms ?? false,
      phone_number: authStore.user.phone || '',
      push: authStore.user.notifications?.push ?? true
    }
  }
})

const saveNotifications = async () => {
  if (!notificationsValid.value) return
  
  saving.value = true
  try {
    await authStore.updateNotifications(notifications.value)
    // Show success message
  } catch (error) {
    // Show error message
  } finally {
    saving.value = false
  }
}
</script> 