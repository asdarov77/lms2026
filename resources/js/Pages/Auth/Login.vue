<template>
  <v-container class="fill-height">
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card class="elevation-12">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Форма авторизации</v-toolbar-title>
          </v-toolbar>
          
          <v-card-text>
            <v-form v-model="valid" ref="form">
              <v-text-field
                v-model="fio"
                prepend-icon="mdi-account"
                name="login"
                label="ФИО"
                type="text"
                :rules="[rules.required]"
                required
              />
              
              <v-text-field
                v-model="password"
                prepend-icon="mdi-lock"
                name="password"
                label="Пароль"
                :type="showPassword ? 'text' : 'password'"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :rules="[rules.required, rules.min]"
                @click:append="showPassword = !showPassword"
                required
              />
              
              <v-alert
                v-if="errors.length"
                type="error"
                dense
                class="mt-3"
              >
                <div v-for="(error, index) in errors" :key="index">
                  {{ error }}
                </div>
              </v-alert>
            </v-form>
          </v-card-text>
          
          <v-card-actions class="pa-4">
            <v-spacer />
            <v-btn
              color="primary"
              @click="loginForm"
              :loading="loading"
              :disabled="!valid"
            >
              Вход
            </v-btn>
          </v-card-actions>
        </v-card>
        
        <popup
          v-if="alert"
          :alert="alert"
          :alert-type="alertType"
          :message="snackbarText"
          @update:alert="alert = $event"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Popup from './Popup.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const form = ref(null)
const fio = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const alert = ref(false)
const alertType = ref('success')
const snackbarText = ref('')
const errors = ref([])
const valid = ref(false)
const redirectPath = ref('/')

// Check if there's a redirect in the query parameters
onMounted(() => {
  if (route.query.redirect) {
    redirectPath.value = route.query.redirect
  }
  
  // If user is already authenticated, redirect
  if (authStore.isAuthenticated) {
    router.push(redirectPath.value)
  }
})

const rules = {
  required: value => !!value || 'Поле обязательно',
  min: v => v.length >= 3 || 'Минимум 3 символа'
}

async function loginForm() {
  if (!form.value?.validate()) return
  
  loading.value = true
  errors.value = []
  
  try {
    const credentials = {
      fio: fio.value,
      password: password.value
    }
    
    await authStore.login(credentials)
    
    // Navigate to the redirect path or home page
    router.push(redirectPath.value)
  } catch (error) {
    console.error('Login error:', error)
    // Extract error message from response
    let errorMessage = 'Ошибка авторизации'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.message) {
      errorMessage = error.message
    }
    errors.value = [errorMessage]
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}
</style>