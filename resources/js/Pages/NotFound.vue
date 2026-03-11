<template>
  <v-container class="fill-height">
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="6" lg="4">
        <v-card class="text-center">
          <v-card-title class="text-h2 mb-4">
            404
          </v-card-title>
          
          <v-card-text>
            <div class="text-h5 mb-4">
              Страница не найдена
            </div>
            <p class="text-body-1 mb-6">
              Извините, запрашиваемая страница не существует или была перемещена.
            </p>
            
            <v-btn
              color="primary"
              size="large"
              to="/"
              prepend-icon="mdi-home"
            >
              На главную
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
// No additional logic needed for this component
</script>

<style scoped>
.fill-height {
  min-height: 100vh;
}

.v-card {
  border-radius: 12px;
  padding: 24px;
}

.text-h2 {
  font-size: 72px !important;
  font-weight: 700;
  color: var(--v-theme-primary);
}
</style> 