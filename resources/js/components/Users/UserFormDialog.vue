<template>
  <v-dialog v-model="dialog" max-width="600px" @update:model-value="handleDialogUpdate">
    <v-card>
      <v-card-title class="text-h5 bg-primary text-white py-3">
        {{ isEditMode ? 'Редактировать пользователя' : 'Добавить пользователя' }}
      </v-card-title>
      <v-card-text class="pt-4">
        <v-form ref="form" v-model="formValid" @submit.prevent="handleSubmit">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="formData.fio"
                label="ФИО"
                :rules="[v => !!v || 'Обязательное поле']"
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12" v-if="!isEditMode">
              <v-text-field
                v-model="formData.password"
                label="Пароль"
                :rules="[
                  v => !!v || 'Обязательное поле',
                  v => v.length >= 3 || 'Минимум 3 символа'
                ]"
                type="password"
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12" v-if="!isEditMode">
              <v-text-field
                v-model="formData.password_confirmation"
                label="Подтверждение пароля"
                :rules="[
                  v => !!v || 'Обязательное поле',
                  v => v === formData.password || 'Пароли не совпадают'
                ]"
                type="password"
                required
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-select
                v-model="formData.role"
                :items="roleOptions"
                label="Роль"
                :rules="[v => !!v || 'Обязательное поле']"
                required
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-select
                v-model="formData.group_id"
                :items="groupOptions"
                item-title="groupname"
                item-value="id"
                label="Группа"
                :rules="[v => !!v || 'Обязательное поле']"
                required
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formData.email"
                label="Email"
                type="email"
                :rules="emailRules"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="formData.phonenumber"
                label="Телефон"
                :rules="phoneRules"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="formData.organization"
                label="Организация"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="formData.position"
                label="Должность"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn
          color="grey"
          variant="text"
          @click="closeDialog"
          :disabled="loading"
        >
          Отмена
        </v-btn>
        <v-btn
          color="primary"
          variant="text"
          :loading="loading"
          :disabled="!formValid"
          @click="handleSubmit"
        >
          Сохранить
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useUserStore } from '@/stores/userStore';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

const userStore = useUserStore();
const form = ref(null);
const formValid = ref(false);
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const isEditMode = computed(() => !!props.user);

const formData = ref({
  fio: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'Пользователь',
  group_id: null,
  phonenumber: '',
  organization: '',
  position: ''
});

const roleOptions = [
  'Администратор',
  'Пользователь',
  'Инструктор'
];

const groupOptions = computed(() => userStore.groups);

const emailRules = [
  v => !v || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Неверный формат email'
];

const phoneRules = [
  v => !v || /^[\d\s+()-]+$/.test(v) || 'Неверный формат телефона'
];

const handleDialogUpdate = (value) => {
  if (!value) {
    closeDialog();
  }
};

const closeDialog = () => {
  form.value?.reset();
  dialog.value = false;
};

const handleSubmit = async () => {
  if (!formValid.value) return;
  
  const userData = { ...formData.value };
  if (isEditMode.value) {
    userData.id = props.user.id;
  }
  
  emit('save', userData);
};

watch(() => props.user, (newUser) => {
  if (newUser) {
    formData.value = {
      fio: newUser.fio || '',
      email: newUser.email || '',
      password: '',
      password_confirmation: '',
      role: newUser.role || 'Пользователь',
      group_id: newUser.group_id || null,
      phonenumber: newUser.phonenumber || '',
      organization: newUser.organization || '',
      position: newUser.position || ''
    };
  } else {
    formData.value = {
      fio: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'Пользователь',
      group_id: null,
      phonenumber: '',
      organization: '',
      position: ''
    };
  }
}, { immediate: true });
</script> 