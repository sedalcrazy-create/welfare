<template>
  <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-gradient-to-br from-primary/10 to-primary/5">
    <!-- Logo -->
    <div class="w-24 h-24 bg-primary rounded-2xl shadow-lg flex items-center justify-center mb-6">
      <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
      </svg>
    </div>

    <!-- Title -->
    <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center">
      سامانه رفاهی
    </h1>
    <p class="text-gray-600 mb-8 text-center">
      بانک ملی ایران
    </p>

    <!-- Loading State -->
    <div v-if="loading" class="text-center">
      <div class="spinner mb-3 text-primary"></div>
      <p class="text-gray-600 text-sm">در حال ورود...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="w-full max-w-sm">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
        <p class="text-red-800 text-sm text-center">{{ error }}</p>
      </div>
      <button
        @click="handleLogin"
        class="btn btn-primary w-full"
      >
        تلاش مجدد
      </button>
    </div>

    <!-- Success Info -->
    <div v-else class="w-full max-w-sm">
      <div class="bg-primary/10 rounded-lg p-6 text-center">
        <svg class="w-12 h-12 text-primary mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-700 text-sm">
          برای ورود به سامانه، این صفحه را از بات بله باز کنید
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const error = ref(null)

async function handleLogin() {
  loading.value = true
  error.value = null

  try {
    // Get initData from Bale
    const initData = window.Bale?.WebApp?.initData

    if (!initData) {
      error.value = 'لطفاً این صفحه را از بات بله باز کنید'
      return
    }

    // Verify with backend
    const success = await authStore.verify(initData)

    if (success) {
      // Navigate based on personnel status
      if (!authStore.hasPersonnel) {
        router.push({ name: 'register' })
      } else if (authStore.isPending) {
        router.push({ name: 'pending' })
      } else {
        router.push({ name: 'home' })
      }
    } else {
      error.value = authStore.error || 'خطا در ورود'
    }
  } catch (err) {
    console.error('Login error:', err)
    error.value = 'خطا در برقراری ارتباط با سرور'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Auto-login on mount
  handleLogin()
})
</script>
