import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import AppShell from '@/components/layout/AppShell.vue';
import './bootstrap';
import '../css/app.css';

createInertiaApp({
  title: (title) => (title ? `${title} | Collabbing` : 'Collabbing'),
  resolve: (name) => {
    const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');
    const page = pages[`./pages/${name}.vue`];

    if (!page) {
      throw new Error(`Inertia page not found: ${name}`);
    }

    return page().then(({ default: inertiaPage }) => {
      inertiaPage.layout ??= AppShell;

      return inertiaPage;
    });
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(createPinia())
      .use(plugin)
      .mount(el);
  },
});
