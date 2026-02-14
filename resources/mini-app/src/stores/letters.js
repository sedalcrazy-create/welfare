import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/axios'

export const useLettersStore = defineStore('letters', () => {
  const letters = ref([])
  const centers = ref([])
  const quotas = ref({})
  const loading = ref(false)
  const error = ref(null)

  // Fetch all letters
  async function fetchLetters(status = null) {
    loading.value = true
    error.value = null

    try {
      const params = status ? { status } : {}
      const response = await api.get('/letters', { params })

      if (response.data.success) {
        letters.value = response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت معرفی‌نامه‌ها'
    } finally {
      loading.value = false
    }
  }

  // Fetch centers
  async function fetchCenters() {
    try {
      const response = await api.get('/centers')
      if (response.data.success) {
        centers.value = response.data.data
      }
    } catch (err) {
      console.error('Failed to fetch centers:', err)
    }
  }

  // Fetch quotas
  async function fetchQuotas() {
    try {
      const response = await api.get('/quotas')
      if (response.data.success) {
        quotas.value = response.data.data
      }
    } catch (err) {
      console.error('Failed to fetch quotas:', err)
    }
  }

  // Create new letter
  async function createLetter(formData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/letters', formData)

      if (response.data.success) {
        await fetchLetters()
        await fetchQuotas()
        return response.data.data
      }

      return null
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در صدور معرفی‌نامه'
      return null
    } finally {
      loading.value = false
    }
  }

  // Get letter details
  async function getLetter(letterId) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/letters/${letterId}`)

      if (response.data.success) {
        return response.data.data
      }

      return null
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت جزئیات'
      return null
    } finally {
      loading.value = false
    }
  }

  // Cancel letter
  async function cancelLetter(letterId) {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete(`/letters/${letterId}/cancel`)

      if (response.data.success) {
        await fetchLetters()
        await fetchQuotas()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در لغو معرفی‌نامه'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    letters,
    centers,
    quotas,
    loading,
    error,
    fetchLetters,
    fetchCenters,
    fetchQuotas,
    createLetter,
    getLetter,
    cancelLetter
  }
})
