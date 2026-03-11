<template>
  <v-container fluid class="fill-height">
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="6" lg="4">
        <v-card class="elevation-12">
          <v-card-title class="text-center text-h5 py-4">
            Регистрация нового пользователя
          </v-card-title>

          <v-card-text>
            <v-form ref="registerForm" v-model="formValid" @submit.prevent="register">
              <!-- Basic Information -->
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.first_name"
                    label="Имя"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    class="mb-4"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.last_name"
                    label="Фамилия"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    class="mb-4"
                  ></v-text-field>
                </v-col>
              </v-row>

              <v-text-field
                v-model="form.email"
                label="Email"
                :rules="[
                  v => !!v || 'Обязательное поле',
                  v => /.+@.+\..+/.test(v) || 'Некорректный email'
                ]"
                required
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                class="mb-4"
              ></v-text-field>

              <!-- Role and Group -->
              <v-row>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.role"
                    :items="roles"
                    label="Роль"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    class="mb-4"
                  ></v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.group_id"
                    :items="groups"
                    item-title="name"
                    item-value="id"
                    label="Группа"
                    :rules="[v => !!v || 'Обязательное поле']"
                    required
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    class="mb-4"
                  ></v-select>
                </v-col>
              </v-row>

              <!-- Password -->
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.password"
                    label="Пароль"
                    :rules="[
                      v => !!v || 'Обязательное поле',
                      v => v.length >= 8 || 'Минимум 8 символов',
                      v => /[A-Z]/.test(v) || 'Минимум 1 заглавная буква',
                      v => /[a-z]/.test(v) || 'Минимум 1 строчная буква',
                      v => /[0-9]/.test(v) || 'Минимум 1 цифра'
                    ]"
                    type="password"
                    required
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    class="mb-4"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.password_confirmation"
                    label="Подтверждение пароля"
                    :rules="[
                      v => !!v || 'Обязательное поле',
                      v => v === form.password || 'Пароли не совпадают'
                    ]"
                    type="password"
                    required
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                    class="mb-4"
                  ></v-text-field>
                </v-col>
              </v-row>

              <!-- Additional Information -->
              <v-textarea
                v-model="form.bio"
                label="О пользователе"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                class="mb-4"
                rows="3"
                auto-grow
              ></v-textarea>

              <v-text-field
                v-model="form.phone"
                label="Телефон"
                variant="outlined"
                density="comfortable"
                hide-details="auto"
                class="mb-4"
              ></v-text-field>

              <!-- Terms and Conditions -->
              <v-checkbox
                v-model="form.terms"
                label="Я согласен с условиями использования"
                :rules="[v => !!v || 'Необходимо принять условия']"
                required
                hide-details="auto"
                class="mb-4"
              ></v-checkbox>
            </v-form>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              color="grey"
              variant="text"
              to="/users"
            >
              Отмена
            </v-btn>
            <v-btn
              color="primary"
              variant="text"
              @click="register"
              :loading="loading"
              :disabled="!formValid"
            >
              Зарегистрировать
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      :timeout="3000"
    >
      {{ snackbarText }}
    </v-snackbar>
  </v-container>
</template>

<script>
export default {
  name: 'Register',

  data() {
    return {
      formValid: false,
      loading: false,
      form: {
        first_name: '',
        last_name: '',
        email: '',
        role: '',
        group_id: null,
        password: '',
        password_confirmation: '',
        bio: '',
        phone: '',
        terms: false
      },
      roles: [
        'Администратор',
        'Преподаватель',
        'Студент'
      ],
      groups: [],
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success'
    }
  },

  created() {
    this.loadGroups()
  },

  methods: {
    async loadGroups() {
      try {
        const response = await this.$store.dispatch('Groups/getGroups')
        this.groups = response
      } catch (error) {
        this.showNotification('Ошибка при загрузке списка групп', 'error')
      }
    },

    async register() {
      if (!this.$refs.registerForm.validate()) return

      this.loading = true
      try {
        await this.$store.dispatch('Users/createUser', this.form)
        this.showNotification('Пользователь успешно зарегистрирован', 'success')
        this.$router.push('/users')
      } catch (error) {
        this.showNotification(
          error.response?.data?.message || 'Ошибка при регистрации пользователя',
          'error'
        )
      } finally {
        this.loading = false
      }
    },

    showNotification(text, color = 'success') {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    }
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}

.v-text-field,
.v-select,
.v-textarea {
  background-color: white;
}

.v-container {
  background-color: #f5f5f5;
}
</style>
