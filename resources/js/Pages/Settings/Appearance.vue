<template>
  <div>
    <h2 class="text-h5 mb-4">Внешний вид</h2>
    <v-form ref="appearanceForm" v-model="appearanceValid">
      <v-row>
        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              Язык интерфейса
            </v-card-title>
            <v-card-text>
              <v-select
                v-model="appearance.language"
                :items="languages"
                label="Выберите язык"
                variant="outlined"
              ></v-select>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              Часовой пояс
            </v-card-title>
            <v-card-text>
              <v-select
                v-model="appearance.timezone"
                :items="timezones"
                label="Выберите часовой пояс"
                variant="outlined"
              ></v-select>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              Формат даты
            </v-card-title>
            <v-card-text>
              <v-select
                v-model="appearance.date_format"
                :items="dateFormats"
                label="Выберите формат даты"
                variant="outlined"
              ></v-select>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="text-subtitle-1">
              Тема оформления
            </v-card-title>
            <v-card-text>
              <v-radio-group v-model="appearance.theme">
                <v-radio
                  label="Светлая"
                  value="light"
                  color="primary"
                ></v-radio>
                <v-radio
                  label="Темная"
                  value="dark"
                  color="primary"
                ></v-radio>
                <v-radio
                  label="Системная"
                  value="system"
                  color="primary"
                ></v-radio>
              </v-radio-group>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn
            color="primary"
            @click="saveAppearance"
            :loading="saving"
            :disabled="!appearanceValid"
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
const appearanceForm = ref(null)
const appearanceValid = ref(false)
const saving = ref(false)

const appearance = ref({
  language: 'ru',
  timezone: 'Europe/Moscow',
  date_format: 'DD.MM.YYYY',
  theme: 'light'
})

const languages = [
  { text: 'Русский', value: 'ru' },
  { text: 'English', value: 'en' }
]

const timezones = [
  { text: 'Москва (UTC+3)', value: 'Europe/Moscow' },
  { text: 'Киев (UTC+2)', value: 'Europe/Kiev' }
]

const dateFormats = [
  { text: 'DD.MM.YYYY', value: 'DD.MM.YYYY' },
  { text: 'YYYY-MM-DD', value: 'YYYY-MM-DD' }
]

onMounted(() => {
  if (authStore.user) {
    appearance.value = {
      language: authStore.user.settings?.language ?? 'ru',
      timezone: authStore.user.settings?.timezone ?? 'Europe/Moscow',
      date_format: authStore.user.settings?.date_format ?? 'DD.MM.YYYY',
      theme: authStore.user.settings?.theme ?? 'light'
    }
  }
})

const saveAppearance = async () => {
  if (!appearanceValid.value) return
  
  saving.value = true
  try {
    await authStore.updateAppearance(appearance.value)
    // Show success message
  } catch (error) {
    // Show error message
  } finally {
    saving.value = false
  }
}
</script> 