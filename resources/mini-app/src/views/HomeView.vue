<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-gradient-to-br from-primary to-primary-dark text-white safe-top">
      <div class="px-4 py-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm opacity-90">سلام،</p>
            <h1 class="text-xl font-bold">{{ authStore.personnel?.full_name }}</h1>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 text-center">
            <p class="text-2xl font-bold">{{ totalQuota }}</p>
            <p class="text-xs opacity-90 mt-1">کل سهمیه</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 text-center">
            <p class="text-2xl font-bold">{{ usedQuota }}</p>
            <p class="text-xs opacity-90 mt-1">استفاده شده</p>
          </div>
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 text-center">
            <p class="text-2xl font-bold">{{ remainingQuota }}</p>
            <p class="text-xs opacity-90 mt-1">باقیمانده</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 py-6 space-y-6">
      <!-- Quick Actions -->
      <div class="grid grid-cols-2 gap-3">
        <!-- New Letter -->
        <RouterLink
          to="/letters/new"
          class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 active:scale-95 transition-transform tap-highlight-none"
        >
          <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-900">معرفی‌نامه جدید</p>
        </RouterLink>

        <!-- My Guests -->
        <RouterLink
          to="/guests"
          class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 active:scale-95 transition-transform tap-highlight-none"
        >
          <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-900">مهمانان من</p>
        </RouterLink>
      </div>

      <!-- Quotas by Center -->
      <div>
        <h2 class="text-lg font-bold text-gray-900 mb-3">سهمیه مراکز</h2>
        <div class="space-y-3">
          <div
            v-for="(quota, centerSlug) in quotas"
            :key="centerSlug"
            class="card"
          >
            <div class="card-body">
              <div class="flex items-center justify-between mb-3">
                <div>
                  <h3 class="font-semibold text-gray-900">{{ quota.center_name }}</h3>
                  <p class="text-xs text-gray-500 mt-0.5">
                    {{ quota.used }}/{{ quota.total }} استفاده شده
                  </p>
                </div>
                <div
                  class="text-2xl font-bold"
                  :class="quota.remaining > 0 ? 'text-primary' : 'text-gray-400'"
                >
                  {{ quota.remaining }}
                </div>
              </div>

              <!-- Progress Bar -->
              <div class="w-full bg-gray-100 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all"
                  :class="quota.remaining > 0 ? 'bg-primary' : 'bg-gray-300'"
                  :style="{ width: `${quota.total > 0 ? (quota.used / quota.total * 100) : 0}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Letters -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-bold text-gray-900">آخرین معرفی‌نامه‌ها</h2>
          <RouterLink to="/letters" class="text-primary text-sm font-medium">
            همه
          </RouterLink>
        </div>

        <LoadingSpinner v-if="loading" />

        <EmptyState
          v-else-if="recentLetters.length === 0"
          title="معرفی‌نامه‌ای وجود ندارد"
          description="با ثبت اولین معرفی‌نامه خود شروع کنید"
        >
          <template #action>
            <RouterLink to="/letters/new" class="btn btn-primary btn-sm">
              صدور معرفی‌نامه
            </RouterLink>
          </template>
        </EmptyState>

        <div v-else class="space-y-3">
          <RouterLink
            v-for="letter in recentLetters"
            :key="letter.id"
            :to="`/letters/${letter.id}`"
            class="card active:scale-98 transition-transform tap-highlight-none block"
          >
            <div class="card-body">
              <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">{{ letter.center.name }}</p>
                  <p class="text-xs text-gray-500 mt-1">کد: {{ letter.letter_code }}</p>
                </div>
                <span
                  class="badge"
                  :class="{
                    'badge-success': letter.status === 'active',
                    'badge-gray': letter.status === 'used',
                    'badge-danger': letter.status === 'cancelled'
                  }"
                >
                  {{ getStatusText(letter.status) }}
                </span>
              </div>
              <div class="flex items-center gap-4 text-xs text-gray-500">
                <span>{{ letter.guests_count }} نفر</span>
                <span>{{ formatDate(letter.created_at) }}</span>
              </div>
            </div>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { usePersonnelStore } from '@/stores/personnel'
import { useLettersStore } from '@/stores/letters'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const authStore = useAuthStore()
const personnelStore = usePersonnelStore()
const lettersStore = useLettersStore()

const loading = ref(true)

const quotas = computed(() => lettersStore.quotas)
const recentLetters = computed(() => lettersStore.letters.slice(0, 3))

const totalQuota = computed(() => {
  return Object.values(quotas.value).reduce((sum, q) => sum + q.total, 0)
})

const usedQuota = computed(() => {
  return Object.values(quotas.value).reduce((sum, q) => sum + q.used, 0)
})

const remainingQuota = computed(() => {
  return Object.values(quotas.value).reduce((sum, q) => sum + q.remaining, 0)
})

function getStatusText(status) {
  const map = {
    active: 'فعال',
    used: 'استفاده شده',
    cancelled: 'لغو شده'
  }
  return map[status] || status
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Intl.DateTimeFormat('fa-IR', options).format(date)
}

onMounted(async () => {
  loading.value = true

  try {
    await Promise.all([
      personnelStore.fetchPersonnel(),
      lettersStore.fetchQuotas(),
      lettersStore.fetchLetters()
    ])
  } catch (err) {
    console.error('Failed to load home data:', err)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.active\:scale-98:active {
  transform: scale(0.98);
}
</style>
