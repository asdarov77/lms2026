<template>
  <v-container class="d-flex flex-column align-center justify-center" style="min-height: 300px;">
    <div class="text-center">
      <v-progress-circular
        :size="size"
        :width="width"
        color="primary"
        indeterminate
      ></v-progress-circular>
      
      <h3 v-if="title" class="text-h5 mt-6 mb-2">{{ title }}</h3>
      <p v-if="message" class="text-body-1 text-medium-emphasis">{{ message }}</p>
    </div>
  </v-container>
</template>

<script>
export default {
  name: 'LoadingState',
  props: {
    size: {
      type: Number,
      default: 70
    },
    width: {
      type: Number,
      default: 7
    },
    title: {
      type: String,
      default: 'Загрузка данных'
    },
    message: {
      type: String,
      default: 'Пожалуйста, подождите...'
    }
  }
}
</script> 