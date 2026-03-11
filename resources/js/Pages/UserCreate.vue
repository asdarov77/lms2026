<template>
  <v-container fluid class="pa-0">
    <!-- Header Section with Parallax Effect -->
    <v-row class="header-section ma-0">
      <v-col cols="12" class="pa-0">
        <div class="header-content d-flex align-center">
          <v-btn
            icon
            color="white"
            class="mr-4"
            @click="$router.push('/users')"
          >
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
          <div>
            <h1 class="text-h4 font-weight-bold white--text">
              Создание пользователя
            </h1>
            <div class="text-subtitle-1 white--text mt-1">
              Добавление нового пользователя
            </div>
          </div>
        </div>
      </v-col>
    </v-row>

    <!-- Main Content -->
    <v-row class="mt-6">
      <!-- Left Column -->
      <v-col cols="12" md="4">
        <!-- User Profile Card -->
        <v-card class="mb-6 profile-card" elevation="4">
          <v-card-text class="text-center pa-6">
            <v-avatar
              size="120"
              color="primary"
              class="mb-4"
            >
              <v-icon size="64" color="white">mdi-account-plus</v-icon>
            </v-avatar>
            <h2 class="text-h5 font-weight-bold mb-2">
              Новый пользователь
            </h2>
            <v-chip
              color="primary"
              size="small"
              class="mb-4"
            >
              Создание
            </v-chip>
          </v-card-text>
        </v-card>

        <!-- Action Buttons -->
        <v-card class="mb-6" elevation="4">
          <v-card-text class="pa-4">
            <v-btn
              color="primary"
              block
              class="mb-4"
              @click="createUser"
              :loading="saving"
              :disabled="!formValid"
            >
              <v-icon left>mdi-content-save</v-icon>
              Создать пользователя
            </v-btn>
            <v-btn
              color="error"
              variant="outlined"
              block
              @click="$router.push('/users')"
            >
              <v-icon left>mdi-close</v-icon>
              Отмена
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Right Column -->
      <v-col cols="12" md="8">
        <v-card elevation="4">
          <v-card-text class="pa-6">
            <v-form v-model="formValid" ref="form">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.first_name"
                    label="Имя"
                    :rules="[v => !!v || 'Имя обязательно']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.last_name"
                    label="Фамилия"
                    :rules="[v => !!v || 'Фамилия обязательна']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="user.email"
                    label="Email"
                    :rules="[
                      v => !!v || 'Email обязателен',
                      v => /.+@.+\..+/.test(v) || 'Email должен быть валидным'
                    ]"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.password"
                    label="Пароль"
                    type="password"
                    :rules="[v => !!v || 'Пароль обязателен']"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="user.password_confirmation"
                    label="Подтверждение пароля"
                    type="password"
                    :rules="[
                      v => !!v || 'Подтверждение пароля обязательно',
                      v => v === user.password || 'Пароли не совпадают'
                    ]"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-select
                    v-model="user.role"
                    :items="roles"
                    label="Роль"
                    :rules="[v => !!v || 'Роль обязательна']"
                    required
                  ></v-select>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import httpClient from '@/api/httpClient';

export default {
  name: 'UserCreate',
  data() {
    return {
      user: {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
      },
      roles: ['Администратор', 'Пользователь', 'Инструктор'],
      formValid: false,
      saving: false,
    };
  },
  methods: {
    async createUser() {
      if (!this.$refs.form.validate()) return;
      
      this.saving = true;
      try {
        await httpClient.post('/api/users', this.user);
        this.$router.push('/users');
      } catch (error) {
        console.error('Error creating user:', error);
        // Handle error appropriately
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
.header-section {
  background: linear-gradient(45deg, #1976d2, #2196f3);
  min-height: 200px;
  position: relative;
}

.header-content {
  padding: 40px;
  position: relative;
  z-index: 1;
}

.profile-card {
  border-radius: 8px;
}
</style> 