<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/auth';

// Router and auth setup
const router = useRouter();
const authStore = useAuthStore();
const isAdmin = ref(authStore.isAdmin);

// Data
const assignments = ref([]);
const loading = ref(true);
const error = ref(null);

// Fetch assignments data
const fetchAssignments = async () => {
  loading.value = true;
  try {
    // Placeholder - replace with actual API call
    // const response = await fetch('/api/assignments');
    // assignments.value = await response.json();
    assignments.value = [
      { id: 1, title: 'Задание 1', course: 'Основы программирования', dueDate: '2025-05-15', status: 'Назначено' },
      { id: 2, title: 'Задание 2', course: 'Базы данных', dueDate: '2025-05-20', status: 'Выполнено' },
      { id: 3, title: 'Задание 3', course: 'Веб-разработка', dueDate: '2025-05-25', status: 'Просрочено' },
    ];
  } catch (err) {
    console.error('Error fetching assignments:', err);
    error.value = 'Ошибка при загрузке заданий.';
  } finally {
    loading.value = false;
  }
};

// Lifecycle hook
onMounted(() => {
  fetchAssignments();
});
</script>

<template>
  <AppLayout title="Все задания">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title class="d-flex justify-space-between align-center py-4">
              <h1 class="text-h4">Все задания</h1>
              <v-btn
                v-if="isAdmin"
                color="primary"
                prepend-icon="mdi-plus"
                @click="router.push('/assignments/create')"
              >
                Создать задание
              </v-btn>
            </v-card-title>

            <v-card-text>
              <v-data-table-server
                v-if="!loading && !error && assignments.length > 0"
                :headers="[
                  { title: 'Название', key: 'title', sortable: true },
                  { title: 'Курс', key: 'course', sortable: true },
                  { title: 'Срок сдачи', key: 'dueDate', sortable: true },
                  { title: 'Статус', key: 'status', sortable: true },
                  { title: 'Действия', key: 'actions', sortable: false }
                ]"
                :items="assignments"
                class="elevation-1"
              >
                <template v-slot:item.status="{ item }">
                  <v-chip
                    :color="item.status === 'Выполнено' ? 'success' : item.status === 'Просрочено' ? 'error' : 'primary'"
                    :text="item.status"
                  ></v-chip>
                </template>
                <template v-slot:item.actions="{ item }">
                  <v-btn
                    icon
                    size="small"
                    variant="text"
                    @click="router.push(`/assignments/${item.id}`)"
                  >
                    <v-icon>mdi-eye</v-icon>
                  </v-btn>
                  <v-btn
                    v-if="isAdmin"
                    icon
                    size="small"
                    variant="text"
                    @click="router.push(`/assignments/${item.id}/edit`)"
                  >
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
              </v-data-table-server>

              <v-alert
                v-else-if="error"
                type="error"
                title="Ошибка"
                text="error"
              ></v-alert>

              <v-alert
                v-else-if="!loading && assignments.length === 0"
                type="info"
                title="Нет заданий"
                text="Заданий не найдено. Создайте новое задание."
              ></v-alert>

              <div v-else class="d-flex justify-center my-4">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>

<style scoped>
.v-card-title h1 {
  font-weight: 700;
}
</style> 