<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-10 safe-top">
      <div class="px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900">مهمانان</h1>
        <p class="text-sm text-gray-500 mt-1">{{ totalGuests }} مهمان</p>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Add Button -->
      <button
        @click="showAddModal = true"
        class="btn btn-primary w-full mb-4"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        افزودن مهمان جدید
      </button>

      <!-- Loading -->
      <LoadingSpinner v-if="loading" />

      <!-- Empty State -->
      <EmptyState
        v-else-if="totalGuests === 0"
        title="هنوز مهمانی ندارید"
        description="برای صدور معرفی‌نامه، مهمانان خود را اضافه کنید"
      />

      <!-- Guests List -->
      <div v-else class="space-y-4">
        <!-- Regular Guests -->
        <div v-if="guests.length > 0">
          <h2 class="text-sm font-semibold text-gray-700 mb-2">مهمانان</h2>
          <div class="space-y-2">
            <div
              v-for="guest in guests"
              :key="guest.id"
              class="card"
            >
              <div class="card-body">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ guest.full_name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ guest.relation }}</p>
                    <p class="text-xs text-gray-400 mt-1 font-mono">{{ guest.national_code }}</p>
                  </div>
                  <button
                    @click="confirmDelete(guest.id)"
                    class="text-red-500 p-2 tap-highlight-none"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Personnel Guests -->
        <div v-if="personnelGuests.length > 0">
          <h2 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
            </svg>
            همراهان پرسنل
          </h2>
          <div class="space-y-2">
            <div
              v-for="guest in personnelGuests"
              :key="guest.id"
              class="card border-blue-100"
            >
              <div class="card-body">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ guest.full_name }}</h3>
                    <p class="text-sm text-blue-600 mt-1">{{ guest.relation }}</p>
                    <p class="text-xs text-gray-400 mt-1 font-mono">کد پرسنلی: {{ guest.employee_code }}</p>
                  </div>
                  <button
                    @click="confirmDeletePersonnel(guest.id)"
                    class="text-red-500 p-2 tap-highlight-none"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Guest Modal (Simple fullscreen) -->
    <Teleport to="body">
      <div v-if="showAddModal" class="fixed inset-0 bg-white z-50 overflow-y-auto">
        <div class="min-h-screen p-4">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">افزودن مهمان</h2>
            <button @click="showAddModal = false" class="p-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="handleAddGuest" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                کد ملی <span class="text-red-500">*</span>
              </label>
              <input v-model="newGuest.national_code" type="text" required maxlength="10" class="input" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                نام کامل <span class="text-red-500">*</span>
              </label>
              <input v-model="newGuest.full_name" type="text" required class="input" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                نسبت <span class="text-red-500">*</span>
              </label>
              <select v-model="newGuest.relation" required class="input">
                <option value="">انتخاب کنید</option>
                <option value="spouse">همسر</option>
                <option value="child">فرزند</option>
                <option value="parent">والدین</option>
                <option value="sibling">خواهر/برادر</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">موبایل</label>
              <input v-model="newGuest.phone" type="tel" class="input" />
            </div>

            <div v-if="guestsStore.error" class="bg-red-50 border border-red-200 rounded-lg p-3">
              <p class="text-red-800 text-sm">{{ guestsStore.error }}</p>
            </div>

            <div class="flex gap-3">
              <button type="button" @click="showAddModal = false" class="btn btn-secondary flex-1">
                انصراف
              </button>
              <button type="submit" :disabled="guestsStore.loading" class="btn btn-primary flex-1">
                <span v-if="guestsStore.loading" class="spinner"></span>
                <span v-else>ذخیره</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useGuestsStore } from '@/stores/guests'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const guestsStore = useGuestsStore()

const loading = ref(true)
const showAddModal = ref(false)
const newGuest = ref({
  national_code: '',
  full_name: '',
  relation: '',
  phone: ''
})

const guests = computed(() => guestsStore.guests)
const personnelGuests = computed(() => guestsStore.personnelGuests)
const totalGuests = computed(() => guests.value.length + personnelGuests.value.length)

async function handleAddGuest() {
  const success = await guestsStore.addGuest(newGuest.value)

  if (success) {
    window.$toast?.show('مهمان با موفقیت اضافه شد', 'success')
    showAddModal.value = false
    newGuest.value = { national_code: '', full_name: '', relation: '', phone: '' }
  }
}

function confirmDelete(guestId) {
  if (confirm('آیا از حذف این مهمان مطمئن هستید؟')) {
    guestsStore.deleteGuest(guestId).then(success => {
      if (success) {
        window.$toast?.show('مهمان حذف شد', 'success')
      }
    })
  }
}

function confirmDeletePersonnel(personnelId) {
  if (confirm('آیا از حذف این همراه مطمئن هستید؟')) {
    guestsStore.removePersonnelGuest(personnelId).then(success => {
      if (success) {
        window.$toast?.show('همراه حذف شد', 'success')
      }
    })
  }
}

onMounted(async () => {
  loading.value = true
  await Promise.all([
    guestsStore.fetchGuests(),
    guestsStore.fetchPersonnelGuests()
  ])
  loading.value = false
})
</script>
