import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import AppShell from '@/components/layout/AppShell.vue';
import { startNavigationHistory } from '@/lib/navigationHistory';
import './bootstrap';
import '../css/app.css';

createInertiaApp({
  title: (title) => (title && title !== 'Collabbing' ? `${title} | Collabbing` : 'Collabbing'),
  resolve: (name) => {
    const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue', { eager: true });
    const page = pages[`./pages/${name}.vue`];

    if (!page) {
      throw new Error(`Inertia page not found: ${name}`);
    }

    page.default.layout ??= AppShell;

    return page;
  },
  setup({ el, App, props, plugin }) {
    startNavigationHistory(props.initialPage.url);

    createApp({ render: () => h(App, props) })
      .use(createPinia())
      .use(plugin)
      .mount(el);
  },
});
