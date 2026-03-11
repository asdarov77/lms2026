import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vuetify from 'vite-plugin-vuetify';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
//import tailwindcss from '@tailwindcss/vite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
  plugins: [
  laravel({ input: ['resources/js/app.js'],
   refresh: true,
   }), vue({ template: {
   transformAssetUrls: {
   base: null,
   includeAbsolute: false,
   }, }, }), vuetify({ autoImport: true, // Автоматический импорт компонентов Vuetify
   }), ],
  resolve: {
  alias: {
  '@': resolve(__dirname, 'resources/js'),
  },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: false,
  }
});