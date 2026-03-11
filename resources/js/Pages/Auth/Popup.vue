<template>
  <v-dialog
    v-model="dialog"
    :max-width="maxWidth"
    :persistent="persistent"
    @keydown.esc="close"
  >
    <v-card>
      <v-card-title class="text-h5">
        {{ title }}
      </v-card-title>

      <v-card-text>
        {{ message }}
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          text
          @click="close"
        >
          {{ buttonText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  alert: {
    type: Boolean,
    default: false
  },
  alertType: {
    type: String,
    default: 'info'
  },
  message: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    default: 'Уведомление'
  },
  buttonText: {
    type: String,
    default: 'OK'
  },
  maxWidth: {
    type: [String, Number],
    default: 400
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:alert'])

const dialog = computed({
  get: () => props.alert,
  set: (value) => emit('update:alert', value)
})

function close() {
  dialog.value = false
}
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}

.v-card-title {
  padding: 16px;
  font-weight: 500;
}

.v-card-text {
  padding: 16px;
  color: rgba(0, 0, 0, 0.87);
}

.v-card-actions {
  padding: 8px 16px;
}
</style> 