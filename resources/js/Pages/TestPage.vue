<template>
  <AppLayout>
    <div class="test-container">
      <h1 class="text-h3 my-4">Test Page</h1>
      <p class="text-body-1 mb-4">This is a simple test page to verify routing and layout functionality.</p>
      
      <v-alert type="info" class="my-4">
        If you can see this alert, the component rendering is working correctly.
      </v-alert>
      
      <v-card class="pa-4 my-4">
        <v-card-title>Test Card</v-card-title>
        <v-card-text>
          This card is a simple test of Vuetify components.
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary">Test Button</v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </AppLayout>
</template>

<script>
import { defineComponent } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

export default defineComponent({
  name: 'TestPage',
  components: {
    AppLayout
  }
});
</script>

<style scoped>
.test-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
  background-color: #f5f5f5;
  border-radius: 8px;
}
</style> 