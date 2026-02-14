<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-10 safe-top">
      <div class="px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900">سهمیه مراکز</h1>
        <p class="text-sm text-gray-500 mt-1">سهمیه شما در مراکز رفاهی</p>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Summary Cards -->
      <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-gradient-to-br from-primary to-primary-dark text-white rounded-xl p-4 text-center">
          <p class="text-2xl font-bold">{{ totalQuota }}</p>
          <p class="text-xs opacity-90 mt-1">کل</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 text-white rounded-xl p-4 text-center">
          <p class="text-2xl font-bold">{{ usedQuota }}</p>
          <p class="text-xs opacity-90 mt-1">استفاده شده</p>
        </div>
        <div class="bg-gradient-to-br from-green-400 to-green-600 text-white rounded-xl p-4 text-center">
          <p class="text-2xl font-bold">{{ remainingQuota }}</p>
          <p class="text-xs opacity-90 mt-1">باقیمانده</p>
        </div>
      </div>

      <!-- Centers List -->
      <div class="space-y-4">
        <div
          v-for="(quota, centerSlug) in quotas"
          :key="centerSlug"
          class="card"
        >
          <div class="card-body">
            <!-- Center Icon & Name -->
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-gray-900">{{ quota.center_name }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">
                  {{ quota.used }} از {{ quota.total }} استفاده شده
                </p>
              </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3 mb-3">
              <div class="text-center">
                <p class="text-lg font-bold text-gray-900">{{ quota.total }}</p>
                <p class="text-xs text-gray-500">کل سهمیه</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-yellow-600">{{ quota.used }}</p>
                <p class="text-xs text-gray-500">استفاده شده</p>
              </div>
              <div class="text-center">
                <p
                  class="text-lg font-bold"
                  :class="quota.remaining > 0 ? 'text-green-600' : 'text-red-600'"
                >
                  {{ quota.remaining }}
                </p>
                <p class="text-xs text-gray-500">باقیمانده</p>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-3">
              <div
                class="h-3 rounded-full transition-all"
                :class="quota.remaining > 0 ? 'bg-primary' : 'bg-red-500'"
                :style="{ width: `${quota.total > 0 ? (quota.used / quota.total * 100) : 0}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info -->
      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-blue-800 text-sm font-medium mb-1">توجه</p>
            <p class="text-blue-700 text-sm">
              سهمیه شما توسط مدیر سیستم تعیین شده است. در صورت نیاز به افزایش سهمیه، با مدیر استانی خود تماس بگیرید.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useLettersStore } from '@/stores/letters'

const lettersStore = useLettersStore()

const quotas = computed(() => lettersStore.quotas)

const totalQuota = computed(() => {
  return Object.values(quotas.value).reduce((sum, q) => sum + q.total, 0)
})

const usedQuota = computed(() => {
  return Object.values(quotas.value).reduce((sum, q) => sum + q.used, 0)
})

const remainingQuota = computed(() => {
  return Object.values(quotas.value).reduce((sum, q) => sum + q.remaining, 0)
})

onMounted(async () => {
  await lettersStore.fetchQuotas()
})
</script>
