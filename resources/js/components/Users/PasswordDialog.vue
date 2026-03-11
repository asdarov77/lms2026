<template>
  <v-dialog v-model="dialog" max-width="500px" @update:model-value="handleDialogUpdate">
    <v-card>
      <v-card-title class="text-h5 bg-primary text-white py-3">
        Изменить пароль
      </v-card-title>
      <v-card-text class="pt-4">
        <v-form ref="form" v-model="formValid" @submit.prevent="handleSubmit">
          <v-text-field
            v-model="formData.password"
            label="Новый пароль"
            :rules="[
              v => !!v || 'Обязательное поле',
              v => v.length >= 3 || 'Минимум 3 символа'
            ]"
            type="password"
            required
            class="mt-4"
          ></v-text-field>
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
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

const form = ref(null);
const formValid = ref(false);
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const formData = ref({
  password: '',
  password_confirmation: ''
});

const handleDialogUpdate = (value) => {
  if (!value) {
    closeDialog();
  }
};

const closeDialog = () => {
  form.value?.reset();
  formData.value = {
    password: '',
    password_confirmation: ''
  };
  dialog.value = false;
};

const handleSubmit = async () => {
  if (!formValid.value) return;
  emit('save', formData.value);
};
</script> 