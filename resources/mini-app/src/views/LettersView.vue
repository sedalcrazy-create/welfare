<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-10 safe-top">
      <div class="px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900">معرفی‌نامه‌ها</h1>
        <p class="text-sm text-gray-500 mt-1">{{ letters.length }} معرفی‌نامه</p>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Loading -->
      <LoadingSpinner v-if="loading" />

      <!-- Empty State -->
      <EmptyState
        v-else-if="letters.length === 0"
        title="معرفی‌نامه‌ای وجود ندارد"
        description="با ثبت اولین معرفی‌نامه خود شروع کنید"
      >
        <template #action>
          <RouterLink to="/letters/new" class="btn btn-primary">
            صدور معرفی‌نامه
          </RouterLink>
        </template>
      </EmptyState>

      <!-- Letters List -->
      <div v-else class="space-y-3">
        <RouterLink
          v-for="letter in letters"
          :key="letter.id"
          :to="`/letters/${letter.id}`"
          class="card active:scale-98 transition-transform tap-highlight-none block"
        >
          <div class="card-body">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-1">
                  {{ letter.center.name }}
                </h3>
                <p class="text-xs text-gray-500 font-mono">{{ letter.letter_code }}</p>
              </div>
              <span
                class="badge text-xs"
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
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ letter.guests_count }} نفر
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(letter.created_at) }}
              </span>
            </div>
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLettersStore } from '@/stores/letters'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const lettersStore = useLettersStore()

const loading = ref(true)

const letters = computed(() => lettersStore.letters)

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
  await lettersStore.fetchLetters()
  loading.value = false
})
</script>

<style scoped>
.active\:scale-98:active {
  transform: scale(0.98);
}
</style>
