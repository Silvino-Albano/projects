import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index.js';
import { createPinia } from 'pinia';
import { i18n }      from './i18n'  // ← ✅ tambah ini

const app = createApp(App);
app.use(router);
app.use(i18n)        // ← ✅ tambah ini
app.use(createPinia());
app.mount('#app');