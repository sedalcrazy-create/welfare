<template>
  <div class="min-h-screen bg-gray-50 pb-6">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-10 safe-top">
      <div class="px-4 py-4">
        <div class="flex items-center gap-3">
          <RouterLink to="/" class="p-2 tap-highlight-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </RouterLink>
          <div>
            <h1 class="text-xl font-bold text-gray-900">معرفی‌نامه جدید</h1>
            <p class="text-sm text-gray-500 mt-0.5">مرحله {{ step }}/{{ totalSteps }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Step 1: Select Center -->
        <div v-if="step === 1" class="fade-in space-y-3">
          <h2 class="font-semibold text-gray-900 mb-4">انتخاب مرکز رفاهی</h2>

          <div
            v-for="center in centers"
            :key="center.id"
            class="card cursor-pointer tap-highlight-none transition-all"
            :class="{
              'ring-2 ring-primary bg-primary/5': form.center_id === center.id,
              'opacity-50': !hasQuotaForCenter(center.slug)
            }"
            @click="selectCenter(center)"
          >
            <div class="card-body">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">{{ center.name }}</h3>
                  <p class="text-sm text-gray-500 mt-1">{{ center.city }} - {{ center.stay_duration }} شب</p>

                  <div class="mt-3 flex items-center gap-4 text-xs">
                    <span class="text-gray-600">{{ center.unit_count }} واحد</span>
                    <span class="text-gray-600">{{ center.bed_count }} تخت</span>
                  </div>

                  <!-- Quota Info -->
                  <div class="mt-2">
                    <p
                      class="text-sm font-medium"
                      :class="hasQuotaForCenter(center.slug) ? 'text-green-600' : 'text-red-600'"
                    >
                      سهمیه: {{ getCenterQuota(center.slug)?.remaining || 0 }}
                    </p>
                  </div>
                </div>

                <div v-if="form.center_id === center.id" class="w-6 h-6 bg-primary rounded-full flex items-center justify-center flex-shrink-0 ml-3">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Select Guests -->
        <div v-if="step === 2" class="fade-in space-y-4">
          <h2 class="font-semibold text-gray-900 mb-4">انتخاب افراد همراه</h2>

          <!-- Include Self -->
          <div class="card">
            <div class="card-body">
              <label class="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="includeSelf"
                  @change="toggleSelf"
                  class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">خودم</p>
                  <p class="text-sm text-gray-500">{{ personnelData?.full_name }}</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Regular Guests -->
          <div v-if="guests.length > 0">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">مهمانان</h3>
            <div class="space-y-2">
              <div v-for="guest in guests" :key="guest.id" class="card">
                <div class="card-body">
                  <label class="flex items-center gap-3 cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="isGuestSelected(guest.id, 'guest')"
                      @change="toggleGuest(guest.id, 'guest')"
                      class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary"
                    />
                    <div class="flex-1">
                      <p class="font-semibold text-gray-900">{{ guest.full_name }}</p>
                      <p class="text-sm text-gray-500">{{ guest.relation }}</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Personnel Guests -->
          <div v-if="personnelGuests.length > 0">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">همراهان پرسنل</h3>
            <div class="space-y-2">
              <div v-for="guest in personnelGuests" :key="guest.id" class="card border-blue-100">
                <div class="card-body">
                  <label class="flex items-center gap-3 cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="isGuestSelected(guest.id, 'personnel')"
                      @change="toggleGuest(guest.id, 'personnel')"
                      class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-600"
                    />
                    <div class="flex-1">
                      <p class="font-semibold text-gray-900">{{ guest.full_name }}</p>
                      <p class="text-sm text-blue-600">{{ guest.relation }}</p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Count -->
          <div class="bg-primary/10 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ totalSelectedGuests }}</p>
            <p class="text-sm text-gray-600 mt-1">نفر انتخاب شده</p>
          </div>
        </div>

        <!-- Step 3: Confirm -->
        <div v-if="step === 3" class="fade-in space-y-4">
          <h2 class="font-semibold text-gray-900 mb-4">تأیید نهایی</h2>

          <div class="card">
            <div class="card-body space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">مرکز:</span>
                <span class="font-semibold">{{ selectedCenter?.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">تعداد افراد:</span>
                <span class="font-semibold">{{ totalSelectedGuests }} نفر</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">مدت اقامت:</span>
                <span class="font-semibold">{{ selectedCenter?.stay_duration }} شب</span>
              </div>
            </div>
          </div>

          <!-- Selected Guests List -->
          <div class="card">
            <div class="card-header">افراد همراه</div>
            <div class="card-body">
              <ul class="space-y-2 text-sm">
                <li v-if="includeSelf" class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  {{ personnelData?.full_name }} (خودم)
                </li>
                <li v-for="guest in getSelectedGuestsList()" :key="guest.id" class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  {{ guest.name }} ({{ guest.type === 'guest' ? guest.relation : 'همراه پرسنل' }})
                </li>
              </ul>
            </div>
          </div>

          <!-- Error -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-red-800 text-sm">{{ error }}</p>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex gap-3 pt-4">
          <button
            v-if="step > 1"
            type="button"
            @click="step--"
            class="btn btn-secondary flex-1"
          >
            قبلی
          </button>

          <button
            v-if="step < totalSteps"
            type="button"
            @click="nextStep"
            :disabled="!canProceed"
            class="btn btn-primary flex-1"
          >
            بعدی
          </button>

          <button
            v-if="step === totalSteps"
            type="submit"
            :disabled="loading || !canProceed"
            class="btn btn-primary flex-1"
          >
            <span v-if="loading" class="spinner"></span>
            <span v-else>صدور معرفی‌نامه</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePersonnelStore } from '@/stores/personnel'
import { useGuestsStore } from '@/stores/guests'
import { useLettersStore } from '@/stores/letters'

const router = useRouter()
const personnelStore = usePersonnelStore()
const guestsStore = useGuestsStore()
const lettersStore = useLettersStore()

const step = ref(1)
const totalSteps = 3
const loading = ref(false)
const error = ref(null)

const form = ref({
  center_id: null,
  guests: []
})

const includeSelf = ref(true)

const centers = computed(() => lettersStore.centers)
const guests = computed(() => guestsStore.guests)
const personnelGuests = computed(() => guestsStore.personnelGuests)
const personnelData = computed(() => personnelStore.personnelData)
const quotas = computed(() => lettersStore.quotas)

const selectedCenter = computed(() => {
  return centers.value.find(c => c.id === form.value.center_id)
})

const totalSelectedGuests = computed(() => {
  let count = includeSelf.value ? 1 : 0
  count += form.value.guests.length
  return count
})

const canProceed = computed(() => {
  if (step.value === 1) {
    return form.value.center_id !== null
  }
  if (step.value === 2) {
    return totalSelectedGuests.value > 0
  }
  return true
})

function selectCenter(center) {
  if (hasQuotaForCenter(center.slug)) {
    form.value.center_id = center.id
  }
}

function getCenterQuota(slug) {
  return quotas.value[slug]
}

function hasQuotaForCenter(slug) {
  const quota = getCenterQuota(slug)
  return quota && quota.remaining > 0
}

function toggleSelf() {
  includeSelf.value = !includeSelf.value
}

function isGuestSelected(guestId, type) {
  return form.value.guests.some(g => g.guest_id === guestId && g.type === type) ||
         form.value.guests.some(g => g.personnel_id === guestId && g.type === type)
}

function toggleGuest(guestId, type) {
  const index = form.value.guests.findIndex(g => {
    if (type === 'guest') {
      return g.guest_id === guestId && g.type === 'guest'
    } else {
      return g.personnel_id === guestId && g.type === 'personnel'
    }
  })

  if (index !== -1) {
    form.value.guests.splice(index, 1)
  } else {
    if (type === 'guest') {
      form.value.guests.push({ type: 'guest', guest_id: guestId })
    } else {
      form.value.guests.push({ type: 'personnel', personnel_id: guestId })
    }
  }
}

function getSelectedGuestsList() {
  const list = []

  form.value.guests.forEach(selectedGuest => {
    if (selectedGuest.type === 'guest') {
      const guest = guests.value.find(g => g.id === selectedGuest.guest_id)
      if (guest) {
        list.push({ ...guest, type: 'guest', name: guest.full_name })
      }
    } else {
      const guest = personnelGuests.value.find(g => g.id === selectedGuest.personnel_id)
      if (guest) {
        list.push({ ...guest, type: 'personnel', name: guest.full_name })
      }
    }
  })

  return list
}

function nextStep() {
  if (canProceed.value) {
    step.value++
  }
}

async function handleSubmit() {
  loading.value = true
  error.value = null

  // Build final guests array
  const finalGuests = []

  if (includeSelf.value) {
    finalGuests.push({ type: 'self' })
  }

  finalGuests.push(...form.value.guests)

  const data = {
    center_id: form.value.center_id,
    guests: finalGuests
  }

  const result = await lettersStore.createLetter(data)

  if (result) {
    window.$toast?.show('معرفی‌نامه با موفقیت صادر شد', 'success')
    router.push(`/letters/${result.id}`)
  } else {
    error.value = lettersStore.error
  }

  loading.value = false
}

onMounted(async () => {
  await Promise.all([
    lettersStore.fetchCenters(),
    lettersStore.fetchQuotas(),
    guestsStore.fetchGuests(),
    guestsStore.fetchPersonnelGuests(),
    personnelStore.fetchPersonnel()
  ])
})
</script>
