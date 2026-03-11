<template>
  <v-flex xs12 sm8 md4>
    <v-card class="elevation-12 mx-auto">
      <v-toolbar color="primary">        
        <v-toolbar-title>Добавить группу </v-toolbar-title>        
      </v-toolbar>
      <v-card-text>
        <v-form @submit.prevent="submitForm">
          <v-text-field
            label="наименование группы"
            type="text"
            v-model="groupname"
            :rules="[v => !!v || 'Обязательно']"
            required
          ></v-text-field>
          <v-text-field
            label="описание группы"
            type="text"
            v-model="groupdescription"
          ></v-text-field>

          <v-switch
            v-model="is_active"
            label="Активна"
            color="primary"
            hint="Группа будет активна для назначения пользователей"
            persistent-hint
          ></v-switch>

          <v-text-field
            label="Максимальное количество пользователей"
            type="number"
            v-model.number="max_users"
            hint="Оставьте пустым для неограниченного количества"
            persistent-hint
          ></v-text-field>

          <v-container class="text-red" v-if="errors.length">
            <p v-for="error in errors" :key="error">
              {{ error }}
            </p>
          </v-container>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="submitForm" color="primary" :loading="loading">Сохранить</v-btn>
      </v-card-actions>
    </v-card>
  </v-flex>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import httpClient from '@/api/httpClient'

const router = useRouter()

const groupname = ref('')
const groupdescription = ref('')
const is_active = ref(true)
const max_users = ref(null)
const errors = ref([])
const loading = ref(false)

const submitForm = async () => {
  if (!groupname.value) {
    errors.value = ['Название группы обязательно']
    return
  }
  
  loading.value = true
  errors.value = []
  
  try {
    const token = localStorage.getItem('token')
    const response = await httpClient.post('/api/groups', {
      groupname: groupname.value,
      groupdescription: groupdescription.value,
      is_active: is_active.value,
      max_users: max_users.value
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    console.log('Group created:', response.data)
    router.push('/groups/list')
  } catch (error) {
    errors.value = ['Ошибка при создании группы: ' + (error.response?.data?.message || error.message)]
  } finally {
    loading.value = false
  }
}
</script>

<style></style>
