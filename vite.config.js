import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vuetify from 'vite-plugin-vuetify';
import vue from '@vitejs/plugin-vue';
//import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
plugins: [
laravel({ input: ['resources/js/app.js'],
 refresh: true,
 }), vue({ template: {
 transformAssetUrls: {
 base: null,
 includeAbsolute: false,
 }, }, }), vuetify({ autoImport: true, // Автоматический импорт компонентов Vuetify
 }), ], resolve: {
 alias: {
 '@': '/resources/js',
 }, }, });