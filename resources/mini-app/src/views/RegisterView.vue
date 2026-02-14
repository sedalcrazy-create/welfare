<template>
  <div class="min-h-screen bg-gray-50 pb-6">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-10">
      <div class="px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900">ثبت‌نام</h1>
        <p class="text-sm text-gray-500 mt-1">اطلاعات خود را تکمیل کنید</p>
      </div>
    </div>

    <!-- Form -->
    <div class="p-4">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Employee Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            کد پرسنلی <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.employee_code"
            type="text"
            required
            class="input"
            placeholder="مثال: 123456"
          />
        </div>

        <!-- National Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            کد ملی <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.national_code"
            type="text"
            required
            maxlength="10"
            class="input"
            placeholder="10 رقم"
          />
        </div>

        <!-- Full Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            نام و نام خانوادگی <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.full_name"
            type="text"
            required
            class="input"
            placeholder="مثال: علی احمدی"
          />
        </div>

        <!-- Mobile -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            موبایل <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.mobile"
            type="tel"
            required
            class="input"
            placeholder="09123456789"
          />
        </div>

        <!-- Province -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            استان/واحد <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.province_id"
            required
            class="input"
          >
            <option value="">انتخاب کنید</option>
            <option v-for="province in provinces" :key="province.id" :value="province.id">
              {{ province.name }}
            </option>
          </select>
        </div>

        <!-- Service Years -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            سنوات خدمت (سال)
          </label>
          <input
            v-model.number="form.service_years"
            type="number"
            min="0"
            max="50"
            class="input"
            placeholder="مثال: 10"
          />
        </div>

        <!-- Is Isargar -->
        <div class="flex items-center gap-2">
          <input
            v-model="form.is_isargar"
            type="checkbox"
            id="is_isargar"
            class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary"
          />
          <label for="is_isargar" class="text-sm font-medium text-gray-700">
            ایثارگر هستم
          </label>
        </div>

        <!-- Error message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
          <p class="text-red-800 text-sm">{{ error }}</p>
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          :disabled="loading"
          class="btn btn-primary w-full btn-lg"
        >
          <span v-if="loading" class="spinner"></span>
          <span v-else>ثبت‌نام</span>
        </button>
      </form>

      <!-- Info -->
      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-blue-800 text-sm font-medium mb-1">توجه</p>
            <p class="text-blue-700 text-sm">
              پس از ثبت‌نام، اطلاعات شما توسط مدیر استانی بررسی و تأیید خواهد شد.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePersonnelStore } from '@/stores/personnel'

const router = useRouter()
const personnelStore = usePersonnelStore()

const form = ref({
  employee_code: '',
  national_code: '',
  full_name: '',
  mobile: '',
  province_id: '',
  service_years: 0,
  is_isargar: false
})

const provinces = ref([])
const loading = ref(false)
const error = ref(null)

async function handleSubmit() {
  loading.value = true
  error.value = null

  const success = await personnelStore.register(form.value)

  if (success) {
    window.$toast?.show('ثبت‌نام با موفقیت انجام شد', 'success')
    router.push({ name: 'pending' })
  } else {
    error.value = personnelStore.error
  }

  loading.value = false
}

onMounted(async () => {
  await personnelStore.fetchProvinces()
  provinces.value = personnelStore.provinces
})
</script>
