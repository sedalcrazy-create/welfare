import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/axios'
import { useAuthStore } from './auth'

export const usePersonnelStore = defineStore('personnel', () => {
  const personnelData = ref(null)
  const quotas = ref({})
  const provinces = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Fetch personnel data with quotas
  async function fetchPersonnel() {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/personnel/me')

      if (response.data.success) {
        personnelData.value = response.data.data
        quotas.value = response.data.data.quotas || {}
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات'
    } finally {
      loading.value = false
    }
  }

  // Fetch provinces
  async function fetchProvinces() {
    try {
      const response = await api.get('/personnel/provinces')
      if (response.data.success) {
        provinces.value = response.data.data
      }
    } catch (err) {
      console.error('Failed to fetch provinces:', err)
    }
  }

  // Register new personnel
  async function register(formData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/personnel/register', formData)

      if (response.data.success) {
        const authStore = useAuthStore()
        authStore.updatePersonnelStatus('pending')
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در ثبت‌نام'

      // Handle validation errors
      if (err.response?.data?.errors) {
        const errors = err.response.data.errors
        const firstError = Object.values(errors)[0]
        error.value = Array.isArray(firstError) ? firstError[0] : firstError
      }

      return false
    } finally {
      loading.value = false
    }
  }

  // Update personnel data
  async function update(formData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch('/personnel/update', formData)

      if (response.data.success) {
        await fetchPersonnel()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در بروزرسانی'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    personnelData,
    quotas,
    provinces,
    loading,
    error,
    fetchPersonnel,
    fetchProvinces,
    register,
    update
  }
})
