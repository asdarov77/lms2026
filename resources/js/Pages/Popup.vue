<template>
  <v-overlay :model-value="alert">
    <v-alert :type="alertType" :model-value="alert">
      {{ snackbarText }}
    </v-alert>
  </v-overlay>  
</template>

<script>
export default {
  name: 'popup',
  
  props: {
    snackbarText: {
      type: String,
      required: true
    },
    alert: {
      type: Boolean,
      required: true
    },
    alertType: {
      type: String,
      required: true
    }
  },

  watch: {
    alert(new_val) {
      if (new_val) {
        setTimeout(() => {
          this.$emit('update:alert', false);
        }, 3000);
      }
    }
  }
};
</script>

<style scoped>
.v-overlay {
  display: flex;	
  justify-content: center;	
  top: 50%;
}
</style>