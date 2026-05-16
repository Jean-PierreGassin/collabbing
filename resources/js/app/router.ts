import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import DashboardPage from '@/pages/DashboardPage.vue';
import FeedbackPage from '@/pages/FeedbackPage.vue';
import HomePage from '@/pages/HomePage.vue';
import IdeasPage from '@/pages/IdeasPage.vue';
import PricingPage from '@/pages/PricingPage.vue';
import ResourcesPage from '@/pages/ResourcesPage.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
    meta: { title: 'Home' },
  },
  {
    path: '/app',
    redirect: '/',
  },
  {
    path: '/app/ideas',
    name: 'ideas',
    component: IdeasPage,
    meta: { title: 'Ideas' },
  },
  {
    path: '/app/dashboard',
    name: 'dashboard',
    component: DashboardPage,
    meta: { title: 'Dashboard' },
  },
  {
    path: '/app/resources',
    name: 'resources',
    component: ResourcesPage,
    meta: { title: 'Resources' },
  },
  {
    path: '/resources/feedback',
    name: 'feedback',
    component: FeedbackPage,
    meta: { title: 'Feedback' },
  },
  {
    path: '/resources/pricing',
    name: 'pricing',
    component: PricingPage,
    meta: { title: 'Pricing' },
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach((to) => {
  const pageTitle = typeof to.meta.title === 'string' ? to.meta.title : 'App';
  document.title = `${pageTitle} | Collabbing`;
});
