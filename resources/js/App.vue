<template>
    <v-app>
        <!-- Debug info at top of page -->
        <div style="position: fixed; top: 0; left: 0; z-index: 9999; background: rgba(0,0,0,0.7); color: white; padding: 5px;">
            Debug: App.vue mounted - Router is ready
        </div>
        
        <router-view v-slot="{ Component }">
            <div style="position: fixed; top: 20px; left: 0; z-index: 9999; background: rgba(0,0,0,0.7); color: white; padding: 5px; font-size: 10px;" v-if="Component">
                Component loaded: {{ Component.__name || Component.name || 'Unknown' }}
            </div>
            <Suspense>
                <template #default>
                    <component :is="Component" />
                </template>
                <template #fallback>
                    <div class="pa-5 text-center">
                        <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        <p class="mt-3">Loading...</p>
                    </div>
                </template>
            </Suspense>
        </router-view>
    </v-app>
</template>

<script>
export default {
    name: 'App'
}
</script>

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Roboto', sans-serif;
}

#app {
    height: 100vh;
}

.v-application {
    font-family: 'Roboto', sans-serif;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
