<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/auth';

// Router and auth setup
const router = useRouter();
const authStore = useAuthStore();
const isAdmin = ref(authStore.isAdmin);

// Form data
const form = ref({
  title: '',
  description: '',
  course_id: null,
  due_date: null,
  max_score: 100,
  file_requirement: false,
  group_ids: []
});

// Supporting data
const courses = ref([]);
const groups = ref([]);
const loading = ref(false);
const errors = ref({});
const submitSuccess = ref(false);

// Fetch required data
const fetchData = async () => {
  loading.value = true;
  try {
    // Placeholder - replace with actual API calls
    // const coursesResponse = await fetch('/api/courses');
    // courses.value = await coursesResponse.json();
    
    // const groupsResponse = await fetch('/api/groups');
    // groups.value = await groupsResponse.json();
    
    // Sample data
    courses.value = [
      { id: 1, title: 'Основы программирования' },
      { id: 2, title: 'Базы данных' },
      { id: 3, title: 'Веб-разработка' }
    ];
    
    groups.value = [
      { id: 1, name: 'Группа A' },
      { id: 2, name: 'Группа B' },
      { id: 3, name: 'Группа C' }
    ];
  } catch (err) {
    console.error('Error fetching data:', err);
  } finally {
    loading.value = false;
  }
};

// Handle form submission
const submitForm = async () => {
  loading.value = true;
  errors.value = {};
  
  try {
    // Validate form
    if (!form.value.title) {
      errors.value.title = 'Название задания обязательно';
    }
    if (!form.value.course_id) {
      errors.value.course_id = 'Выберите курс';
    }
    if (!form.value.due_date) {
      errors.value.due_date = 'Срок сдачи обязателен';
    }
    if (!form.value.group_ids.length) {
      errors.value.group_ids = 'Выберите хотя бы одну группу';
    }
    
    // If there are validation errors, stop submission
    if (Object.keys(errors.value).length) {
      loading.value = false;
      return;
    }
    
    // Placeholder - replace with actual API call
    // const response = await fetch('/api/assignments', {
    //   method: 'POST',
    //   headers: {
    //     'Content-Type': 'application/json',
    //     'Authorization': `Bearer ${authStore.token}`
    //   },
    //   body: JSON.stringify(form.value)
    // });
    // 
    // if (!response.ok) {
    //   throw new Error('Failed to create assignment');
    // }
    
    // Show success and redirect
    submitSuccess.value = true;
    setTimeout(() => {
      router.push('/assignments');
    }, 1500);
  } catch (err) {
    console.error('Error creating assignment:', err);
    errors.value.general = 'Произошла ошибка при создании задания';
  } finally {
    loading.value = false;
  }
};

// Lifecycle hook
onMounted(() => {
  if (!isAdmin.value) {
    router.push('/dashboard');
    return;
  }
  fetchData();
});
</script>

<template>
  <AppLayout title="Создание задания">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="text-h4 py-4">Создание нового задания</v-card-title>
            <v-card-text>
              <v-form @submit.prevent="submitForm">
                <v-alert
                  v-if="errors.general"
                  type="error"
                  class="mb-4"
                  closable
                >
                  {{ errors.general }}
                </v-alert>
                
                <v-alert
                  v-if="submitSuccess"
                  type="success"
                  class="mb-4"
                  closable
                >
                  Задание успешно создано!
                </v-alert>
                
                <v-row>
                  <v-col cols="12" sm="12" md="6">
                    <v-text-field
                      v-model="form.title"
                      label="Название задания"
                      required
                      :error-messages="errors.title"
                      @input="errors.title = ''"
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12" sm="12" md="6">
                    <v-select
                      v-model="form.course_id"
                      :items="courses"
                      item-title="title"
                      item-value="id"
                      label="Выберите курс"
                      required
                      :error-messages="errors.course_id"
                      @update:model-value="errors.course_id = ''"
                    ></v-select>
                  </v-col>
                  
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Описание задания"
                      rows="4"
                    ></v-textarea>
                  </v-col>
                  
                  <v-col cols="12" sm="6">
                    <v-date-picker
                      v-model="form.due_date"
                      label="Срок сдачи"
                      :error-messages="errors.due_date"
                      @update:model-value="errors.due_date = ''"
                    ></v-date-picker>
                  </v-col>
                  
                  <v-col cols="12" sm="6">
                    <v-select
                      v-model="form.group_ids"
                      :items="groups"
                      item-title="name"
                      item-value="id"
                      label="Назначить группам"
                      multiple
                      chips
                      :error-messages="errors.group_ids"
                      @update:model-value="errors.group_ids = ''"
                    ></v-select>
                  </v-col>
                  
                  <v-col cols="12" sm="6">
                    <v-text-field
                      v-model="form.max_score"
                      label="Максимальный балл"
                      type="number"
                      min="0"
                    ></v-text-field>
                  </v-col>
                  
                  <v-col cols="12" sm="6">
                    <v-switch
                      v-model="form.file_requirement"
                      label="Требуется загрузка файла"
                      color="primary"
                    ></v-switch>
                  </v-col>
                </v-row>
                
                <v-row>
                  <v-col cols="12" class="d-flex justify-end gap-4">
                    <v-btn
                      color="secondary"
                      variant="outlined"
                      @click="router.push('/assignments')"
                      :disabled="loading"
                    >
                      Отмена
                    </v-btn>
                    <v-btn
                      color="primary"
                      type="submit"
                      :loading="loading"
                      :disabled="submitSuccess"
                    >
                      Создать задание
                    </v-btn>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

<style scoped>
.v-card-title {
  font-weight: 700;
}
</style> 