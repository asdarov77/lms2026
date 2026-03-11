<template>
  <v-card>
    <div class="table-container">
      <v-data-table
        :headers="headers"
        :items="users"
        :loading="loading"
        :items-per-page="10"
        class="elevation-1"
      >
        <template v-slot:item.fio="{ item }">
          {{ item.fio }}
        </template>
        <template v-slot:item.email="{ item }">
          {{ item.email }}
        </template>
        <template v-slot:item.role="{ item }">
          <v-chip
            :color="getRoleColor(item.role)"
            size="small"
            class="text-caption"
          >
            {{ item.role }}
          </v-chip>
        </template>
        <template v-slot:item.group="{ item }">
          <span v-if="item.group">{{ item.group.groupname }}</span>
          <span v-else class="text-grey">Не назначена</span>
        </template>
        <template v-slot:item.last_login_at="{ item }">
          {{ formatDate(item.last_login_at) }}
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="d-flex gap-2">
            <v-btn
              icon="mdi-pencil"
              size="small"
              color="primary"
              variant="text"
              @click="$emit('edit', item)"
              :disabled="isAdminUser(item)"
            ></v-btn>
            <v-btn
              icon="mdi-key"
              size="small"
              color="warning"
              variant="text"
              @click="$emit('change-password', item)"
              :disabled="isAdminUser(item)"
            ></v-btn>
            <v-btn
              icon="mdi-delete"
              size="small"
              color="error"
              variant="text"
              @click="$emit('delete', item)"
              :disabled="isAdminUser(item)"
            ></v-btn>
          </div>
        </template>
      </v-data-table>
    </div>
  </v-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  users: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['edit', 'delete', 'change-password']);

const headers = [
  { title: 'ФИО', key: 'fio', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Роль', key: 'role', sortable: true },
  { title: 'Группа', key: 'group', sortable: true },
  { title: 'Последний вход', key: 'last_login_at', sortable: true },
  { title: 'Действия', key: 'actions', sortable: false }
];

const getRoleColor = (role) => {
  switch (role) {
    case 'Администратор': return 'error';
    case 'Преподаватель': return 'primary';
    case 'Инструктор': return 'orange';
    case 'Обучаемый': return 'success';
    default: return 'grey';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'Никогда';
  return new Date(dateString).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const isAdminUser = (user) => {
  return user.id === 1 && user.role === 'Администратор';
};
</script>

<style scoped>
.table-container {
  height: 600px;
  overflow: auto;
}
</style> 