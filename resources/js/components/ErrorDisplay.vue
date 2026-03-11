<template>
  <v-card class="pa-5 text-center">
    <v-icon size="large" color="error" class="mb-3">mdi-alert-circle</v-icon>
    <h3 class="text-h5 mb-2">{{ title }}</h3>
    <p class="text-body-1 mb-4">{{ message }}</p>
    
    <div v-if="error && typeof error === 'object'" class="text-left error-details mb-4">
      <p class="font-weight-bold mb-1">Детали ошибки:</p>
      <pre class="error-code pa-3">{{ errorDetails }}</pre>
    </div>
    
    <v-btn 
      color="primary" 
      @click="$emit('retry')" 
      :loading="loading"
      class="mx-2"
    >
      Повторить
    </v-btn>
    
    <v-btn 
      color="secondary" 
      @click="copyErrorToClipboard" 
      class="mx-2"
    >
      Копировать детали
    </v-btn>
  </v-card>
</template>

<script>
export default {
  name: 'ErrorDisplay',
  props: {
    title: {
      type: String,
      default: 'Ошибка'
    },
    message: {
      type: String,
      default: 'Произошла ошибка при загрузке данных.'
    },
    error: {
      type: [Object, String, Error],
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    errorDetails() {
      if (!this.error) return 'Нет деталей ошибки';
      
      if (typeof this.error === 'string') {
        return this.error;
      }
      
      if (this.error instanceof Error) {
        return this.error.message || this.error.toString();
      }
      
      try {
        // Если ошибка - объект, пробуем его форматировать
        return JSON.stringify(this.error, null, 2);
      } catch (e) {
        return String(this.error);
      }
    }
  },
  methods: {
    copyErrorToClipboard() {
      try {
        navigator.clipboard.writeText(this.errorDetails);
        alert('Детали ошибки скопированы в буфер обмена');
      } catch (e) {
        console.error('Не удалось скопировать ошибку:', e);
        alert('Не удалось скопировать. Сделайте это вручную.');
      }
    }
  }
}
</script>

<style scoped>
.error-details {
  max-width: 100%;
  overflow-x: auto;
}

.error-code {
  background-color: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  font-family: monospace;
  font-size: 14px;
  max-height: 200px;
  overflow-y: auto;
  white-space: pre-wrap;
  word-break: break-all;
}
</style> 