<template>
  <div class="min-h-screen bg-gray-50 pb-6">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-10 safe-top">
      <div class="px-4 py-4">
        <div class="flex items-center gap-3">
          <RouterLink to="/letters" class="p-2 tap-highlight-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </RouterLink>
          <div>
            <h1 class="text-xl font-bold text-gray-900">جزئیات معرفی‌نامه</h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <LoadingSpinner v-if="loading" />

      <div v-else-if="letter" class="space-y-4">
        <!-- Status Badge -->
        <div class="text-center">
          <span
            class="inline-block badge text-base px-6 py-2"
            :class="{
              'badge-success': letter.status === 'active',
              'badge-gray': letter.status === 'used',
              'badge-danger': letter.status === 'cancelled'
            }"
          >
            {{ getStatusText(letter.status) }}
          </span>
        </div>

        <!-- Letter Code -->
        <div class="card">
          <div class="card-body text-center">
            <p class="text-sm text-gray-500 mb-2">کد معرفی‌نامه</p>
            <p class="text-2xl font-bold text-primary font-mono">{{ letter.letter_code }}</p>
          </div>
        </div>

        <!-- Center Info -->
        <div class="card">
          <div class="card-header">مرکز رفاهی</div>
          <div class="card-body">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ letter.center.name }}</h3>
                <p class="text-sm text-gray-500">{{ letter.center.city }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Guests List -->
        <div class="card">
          <div class="card-header">افراد همراه ({{ letter.guests?.length || 0 }} نفر)</div>
          <div class="card-body">
            <ul class="space-y-3">
              <li
                v-for="(guest, index) in letter.guests"
                :key="index"
                class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0"
              >
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ guest.name }}</p>
                  <p class="text-sm text-gray-500 font-mono">{{ guest.national_code }}</p>
                  <p v-if="guest.type !== 'self'" class="text-xs text-gray-400 mt-1">
                    {{ guest.relation || (guest.type === 'personnel' ? 'همراه پرسنل' : '') }}
                  </p>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Dates -->
        <div class="card">
          <div class="card-body space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">تاریخ صدور:</span>
              <span class="font-medium">{{ formatDate(letter.issue_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">تاریخ انقضا:</span>
              <span class="font-medium">{{ formatDate(letter.expiry_date) }}</span>
            </div>
          </div>
        </div>

        <!-- QR Code (if available) -->
        <div v-if="letter.qr_code && letter.status === 'active'" class="card">
          <div class="card-body text-center">
            <p class="text-sm text-gray-600 mb-3">QR Code معرفی‌نامه</p>
            <div class="inline-block bg-gray-100 p-4 rounded-lg">
              <div class="w-48 h-48 bg-white flex items-center justify-center">
                <!-- Placeholder for QR code -->
                <p class="text-gray-400 text-sm">QR Code</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="letter.status === 'active'" class="space-y-3">
          <button
            @click="confirmCancel"
            :disabled="cancelling"
            class="btn btn-danger w-full"
          >
            <span v-if="cancelling" class="spinner"></span>
            <span v-else>لغو معرفی‌نامه</span>
          </button>

          <p class="text-xs text-gray-500 text-center">
            پس از لغو معرفی‌نامه، سهمیه شما بازگردانده خواهد شد
          </p>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div>
              <p class="text-blue-800 text-sm font-medium mb-1">توجه</p>
              <p class="text-blue-700 text-sm">
                این معرفی‌نامه را هنگام مراجعه به مرکز رفاهی به همراه داشته باشید.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLettersStore } from '@/stores/letters'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const lettersStore = useLettersStore()

const letter = ref(null)
const loading = ref(true)
const cancelling = ref(false)

function getStatusText(status) {
  const map = {
    active: 'فعال',
    used: 'استفاده شده',
    cancelled: 'لغو شده'
  }
  return map[status] || status
}

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Intl.DateTimeFormat('fa-IR', options).format(date)
}

async function confirmCancel() {
  if (confirm('آیا از لغو این معرفی‌نامه مطمئن هستید؟')) {
    cancelling.value = true

    const success = await lettersStore.cancelLetter(letter.value.id)

    if (success) {
      window.$toast?.show('معرفی‌نامه لغو شد', 'success')
      router.push('/letters')
    } else {
      window.$toast?.show(lettersStore.error || 'خطا در لغو معرفی‌نامه', 'error')
    }

    cancelling.value = false
  }
}

onMounted(async () => {
  loading.value = true

  const letterId = route.params.id
  letter.value = await lettersStore.getLetter(letterId)

  loading.value = false
})
</script>
