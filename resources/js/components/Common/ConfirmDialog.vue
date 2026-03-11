<template>
  <v-dialog v-model="dialog" max-width="500px" @update:model-value="handleDialogUpdate">
    <v-card>
      <v-card-title class="text-h5 bg-error text-white py-3">
        {{ title }}
      </v-card-title>
      <v-card-text class="pt-4">
        <p class="mb-0">{{ message }}</p>
        <p class="text-error font-weight-bold mt-4">Это действие невозможно отменить.</p>
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
          color="error"
          variant="text"
          :loading="loading"
          @click="handleConfirm"
        >
          Удалить
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const handleDialogUpdate = (value) => {
  if (!value) {
    closeDialog();
  }
};

const closeDialog = () => {
  dialog.value = false;
};

const handleConfirm = () => {
  emit('confirm');
};
</script> 