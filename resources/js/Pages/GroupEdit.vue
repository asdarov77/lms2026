<template>
  <v-container fluid class="pa-0">
    <!-- Header Section -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content">
          <h1 class="text-h4 font-weight-bold white--text">
            Редактирование группы
          </h1>
          <div class="text-subtitle-1 white--text mt-1">
            Изменение параметров группы пользователей
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-container class="mt-6">
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="2" class="pa-4">
            <v-form ref="form" v-model="valid">
              <v-text-field
                v-model="group.name"
                label="Название группы"
                :rules="[v => !!v || 'Обязательное поле']"
                required
              ></v-text-field>

              <v-textarea
                v-model="group.description"
                label="Описание группы"
                rows="3"
              ></v-textarea>

              <v-select
                v-model="group.permissions"
                :items="availablePermissions"
                label="Права доступа"
                multiple
                chips
                deletable-chips
              ></v-select>
            </v-form>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="2" class="pa-4">
            <v-card-title>Участники группы</v-card-title>
            <v-card-text>
              <v-select
                v-model="selectedUsers"
                :items="users"
                item-text="name"
                item-value="id"
                label="Выберите пользователей"
                multiple
                chips
                deletable-chips
              ></v-select>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Action Buttons -->
      <v-row class="mt-4">
        <v-col cols="12" class="text-right">
          <v-btn
            color="grey"
            class="mr-4"
            @click="$router.back()"
          >
            Отмена
          </v-btn>
          <v-btn
            color="primary"
            :loading="saving"
            :disabled="!valid"
            @click="updateGroup"
          >
            Сохранить
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </v-container>
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'GroupEdit',
  data() {
    return {
      valid: false,
      saving: false,
      group: {
        name: '',
        description: '',
        permissions: [],
      },
      users: [],
      selectedUsers: [],
      availablePermissions: [
        { text: 'Управление пользователями', value: 'manage_users' },
        { text: 'Управление курсами', value: 'manage_courses' },
        { text: 'Управление заданиями', value: 'manage_assignments' },
        { text: 'Просмотр отчетов', value: 'view_reports' },
        { text: 'Управление группами', value: 'manage_groups' },
      ],
    };
  },
  methods: {
    async fetchGroup() {
      try {
        const response = await httpClient.get(`/api/groups/${this.$route.params.id}`);
        this.group = response.data.group;
        this.selectedUsers = response.data.users.map(user => user.id);
      } catch (error) {
        console.error('Error fetching group:', error);
      }
    },
    async fetchUsers() {
      try {
        const response = await httpClient.get('/api/users');
        this.users = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    async updateGroup() {
      if (!this.$refs.form.validate()) {
        return;
      }

      this.saving = true;
      try {
        const groupData = {
          ...this.group,
          users: this.selectedUsers,
        };

        await httpClient.put(`/api/groups/${this.$route.params.id}`, groupData);
        this.$router.push('/groups');
      } catch (error) {
        console.error('Error updating group:', error);
      } finally {
        this.saving = false;
      }
    },
  },
  mounted() {
    this.fetchGroup();
    this.fetchUsers();
  },
};
</script>

<style scoped>
.header-section {
  background: linear-gradient(45deg, #1976d2, #2196f3);
  min-height: 120px;
  position: relative;
}

.header-content {
  padding: 40px;
  position: relative;
  z-index: 1;
}
</style> 