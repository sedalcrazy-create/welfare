<template>
  <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-gray-50">
    <!-- Animated Icon -->
    <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-6 animate-pulse">
      <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>

    <!-- Title -->
    <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center">
      در انتظار تأیید
    </h1>

    <!-- Description -->
    <p class="text-gray-600 text-center mb-8 max-w-sm">
      اطلاعات شما توسط مدیر استانی در حال بررسی است. پس از تأیید، می‌توانید از سیستم استفاده کنید.
    </p>

    <!-- Refresh Button -->
    <button
      @click="checkStatus"
      :disabled="loading"
      class="btn btn-primary"
    >
      <span v-if="loading" class="spinner"></span>
      <span v-else>بروزرسانی وضعیت</span>
    </button>

    <!-- Info Card -->
    <div class="w-full max-w-sm mt-8">
      <div class="card">
        <div class="card-body">
          <h3 class="font-semibold text-gray-900 mb-3">اطلاعات شما</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">نام:</span>
              <span class="font-medium">{{ authStore.user?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">وضعیت:</span>
              <span class="badge badge-warning">در انتظار تأیید</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Info -->
    <div class="mt-6 text-center">
      <p class="text-gray-500 text-sm">
        در صورت نیاز به راهنمایی با پشتیبانی تماس بگیرید
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)

async function checkStatus() {
  loading.value = true

  try {
    await authStore.fetchMe()

    if (authStore.isApproved) {
      window.$toast?.show('حساب شما تأیید شد!', 'success')
      router.push({ name: 'home' })
    } else {
      window.$toast?.show('هنوز تأیید نشده است', 'info')
    }
  } catch (err) {
    window.$toast?.show('خطا در بروزرسانی', 'error')
  } finally {
    loading.value = false
  }
}
</script>
