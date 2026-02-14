import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory('/mini-app/'),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue'),
      meta: { requiresAuth: true, requiresApproved: true }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/RegisterView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/pending',
      name: 'pending',
      component: () => import('@/views/PendingView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/guests',
      name: 'guests',
      component: () => import('@/views/GuestsView.vue'),
      meta: { requiresAuth: true, requiresApproved: true }
    },
    {
      path: '/letters',
      name: 'letters',
      component: () => import('@/views/LettersView.vue'),
      meta: { requiresAuth: true, requiresApproved: true }
    },
    {
      path: '/letters/new',
      name: 'new-letter',
      component: () => import('@/views/NewLetterView.vue'),
      meta: { requiresAuth: true, requiresApproved: true }
    },
    {
      path: '/letters/:id',
      name: 'letter-detail',
      component: () => import('@/views/LetterDetailView.vue'),
      meta: { requiresAuth: true, requiresApproved: true }
    },
    {
      path: '/quotas',
      name: 'quotas',
      component: () => import('@/views/QuotasView.vue'),
      meta: { requiresAuth: true, requiresApproved: true }
    }
  ]
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Guest routes (only for non-authenticated users)
  if (to.meta.guest && authStore.isAuthenticated) {
    if (!authStore.hasPersonnel) {
      return next({ name: 'register' })
    }
    if (authStore.isPending) {
      return next({ name: 'pending' })
    }
    return next({ name: 'home' })
  }

  // Routes that require authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }

  // Routes that require personnel registration
  if (to.meta.requiresAuth && authStore.isAuthenticated) {
    if (!authStore.hasPersonnel && to.name !== 'register') {
      return next({ name: 'register' })
    }

    // Routes that require approval
    if (to.meta.requiresApproved && authStore.isPending && to.name !== 'pending') {
      return next({ name: 'pending' })
    }
  }

  next()
})

export default router
