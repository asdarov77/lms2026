<template>
  <v-app>
    <!-- App Bar -->
    <v-app-bar 
      color="primary" 
      app 
      elevation="2"
    >
      <v-app-bar-nav-icon 
        @click="drawer = !drawer" 
        color="white"
      ></v-app-bar-nav-icon>

      <v-app-bar-title class="text-white font-weight-bold">
        LMS 2025
      </v-app-bar-title>

      <v-spacer></v-spacer>

      <!-- Search -->
      <v-text-field
        v-if="isDesktop"
        v-model="search"
        prepend-inner-icon="mdi-magnify"
        label="Поиск..."
        hide-details
        density="compact"
        class="mx-4"
        bg-color="primary-lighten-1"
        color="white"
        variant="solo"
        style="max-width: 300px"
        @keyup.enter="performSearch"
      ></v-text-field>

      <v-spacer></v-spacer>

      <!-- Notifications -->
      <v-btn 
        icon
        color="white"
        class="mr-2"
      >
        <v-badge 
          :content="unreadNotifications" 
          :value="unreadNotifications" 
          color="error"
        >
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>

      <!-- User Menu -->
      <v-menu 
        min-width="200px" 
        location="bottom end"
      >
        <template v-slot:activator="{ props }">
          <v-btn 
            v-bind="props"
            class="text-white"
            variant="text"
          >
            <v-avatar
              size="32"
              class="mr-2"
            >
              <v-img :src="userAvatar" v-if="userAvatar"></v-img>
              <v-icon v-else>mdi-account-circle</v-icon>
            </v-avatar>
            {{ userName }}
            <v-icon right>mdi-chevron-down</v-icon>
          </v-btn>
        </template>
        <v-card>
          <v-list>
            <v-list-item :to="{ name: 'settings-profile' }">
              <template v-slot:prepend>
                <v-icon>mdi-account</v-icon>
              </template>
              <v-list-item-title>Мой профиль</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{ name: 'settings' }">
              <template v-slot:prepend>
                <v-icon>mdi-cog</v-icon>
              </template>
              <v-list-item-title>Настройки</v-list-item-title>
            </v-list-item>
            <v-divider></v-divider>
            <v-list-item @click="logout">
              <template v-slot:prepend>
                <v-icon>mdi-logout</v-icon>
              </template>
              <v-list-item-title>Выйти</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer
      v-model="drawer"
      app
      class="elevation-4"
      :rail="isDesktop && drawerMini"
      @click="drawerMini = false"
    >
      <v-list>
        <v-list-item
          class="py-2"
          :to="{ name: 'dashboard' }"
        >
          <template v-slot:prepend>
            <v-icon>mdi-view-dashboard</v-icon>
          </template>
          <v-list-item-title>Дашборд</v-list-item-title>
        </v-list-item>
        
        <v-divider class="my-2"></v-divider>
        
        <v-list-subheader>Обучение</v-list-subheader>
        
        <v-list-item
          class="py-2"
          :to="{ name: 'courses.list' }"
        >
          <template v-slot:prepend>
            <v-icon>mdi-book-open-variant</v-icon>
          </template>
          <v-list-item-title>Курсы</v-list-item-title>
        </v-list-item>
        
        <v-list-item
          class="py-2"
          :to="{ name: 'learning-progress' }"
        >
          <template v-slot:prepend>
            <v-icon>mdi-chart-line</v-icon>
          </template>
          <v-list-item-title>Прогресс</v-list-item-title>
        </v-list-item>
        
        <v-list-item
          class="py-2"
          :to="{ name: 'dashboard' }"
        >
          <template v-slot:prepend>
            <v-icon>mdi-clipboard-text</v-icon>
          </template>
          <v-list-item-title>Задания</v-list-item-title>
        </v-list-item>
        
        <v-divider v-if="hasAdminAccess" class="my-2"></v-divider>
        
        <v-list-subheader v-if="hasAdminAccess">Администрирование</v-list-subheader>
        
        <v-list-item
          v-if="hasAdminAccess"
          class="py-2"
          :to="{ name: 'users' }"
        >
          <template v-slot:prepend>
            <v-icon>mdi-account-multiple</v-icon>
          </template>
          <v-list-item-title>Пользователи</v-list-item-title>
        </v-list-item>
        
        <v-list-item
          v-if="hasAdminAccess"
          class="py-2"
          :to="{ name: 'groups' }"
        >
          <template v-slot:prepend>
            <v-icon>mdi-account-group</v-icon>
          </template>
          <v-list-item-title>Группы</v-list-item-title>
        </v-list-item>
      </v-list>
      
      <template v-slot:append v-if="isDesktop">
        <div class="pa-2">
          <v-btn
            block
            @click="drawerMini = !drawerMini"
          >
            <v-icon>
              {{ drawerMini ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
            </v-icon>
          </v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
      <slot></slot>
    </v-main>

    <!-- Footer -->
    <v-footer
      app
      class="text-center d-flex flex-column"
    >
      <div>
        <strong>LMS 2025</strong> | {{ new Date().getFullYear() }}
      </div>
      <div class="text-caption">Система управления обучением</div>
    </v-footer>

    <!-- Snackbar for notifications -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="5000"
    >
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          Закрыть
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>
export default {
  name: 'MainLayout',
  data() {
    return {
      drawer: true,
      drawerMini: false,
      search: '',
      unreadNotifications: 5,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      }
    };
  },
  computed: {
    isDesktop() {
      return this.$vuetify.display.mdAndUp;
    },
    userName() {
      // Get current user name from your auth store
      return 'Пользователь'; // Replace with actual user name
    },
    userAvatar() {
      // Get current user avatar from your auth store
      return null; // Replace with actual user avatar
    },
    hasAdminAccess() {
      // Check if current user has admin access from your auth store
      return true; // Replace with actual admin check
    }
  },
  methods: {
    logout() {
      // Implement your logout logic here
      console.log('Logout clicked');
    },
    performSearch() {
      if (this.search.trim()) {
        // Implement your search logic here
        console.log('Searching for:', this.search);
      }
    },
    showNotification(text, color = 'success') {
      this.snackbar = {
        show: true,
        text,
        color
      };
    }
  }
};
</script>

<style scoped>
.v-navigation-drawer--rail {
  transition: width 0.3s ease;
}
</style> 