import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/axios'

export const useGuestsStore = defineStore('guests', () => {
  const guests = ref([])
  const personnelGuests = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Fetch all guests
  async function fetchGuests() {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/guests')
      if (response.data.success) {
        guests.value = response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت مهمانان'
    } finally {
      loading.value = false
    }
  }

  // Add new guest
  async function addGuest(formData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/guests', formData)

      if (response.data.success) {
        await fetchGuests()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در افزودن مهمان'
      return false
    } finally {
      loading.value = false
    }
  }

  // Update guest
  async function updateGuest(guestId, formData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`/guests/${guestId}`, formData)

      if (response.data.success) {
        await fetchGuests()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در بروزرسانی مهمان'
      return false
    } finally {
      loading.value = false
    }
  }

  // Delete guest
  async function deleteGuest(guestId) {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete(`/guests/${guestId}`)

      if (response.data.success) {
        await fetchGuests()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در حذف مهمان'
      return false
    } finally {
      loading.value = false
    }
  }

  // Fetch personnel guests
  async function fetchPersonnelGuests() {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/personnel-guests')
      if (response.data.success) {
        personnelGuests.value = response.data.data
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت همراهان'
    } finally {
      loading.value = false
    }
  }

  // Search personnel
  async function searchPersonnel(employeeCode) {
    error.value = null

    try {
      const response = await api.get('/personnel-guests/search', {
        params: { employee_code: employeeCode }
      })

      if (response.data.success) {
        return response.data.data
      }

      return null
    } catch (err) {
      error.value = err.response?.data?.message || 'پرسنل یافت نشد'
      return null
    }
  }

  // Add personnel as guest
  async function addPersonnelGuest(formData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/personnel-guests', formData)

      if (response.data.success) {
        await fetchPersonnelGuests()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در افزودن همراه'
      return false
    } finally {
      loading.value = false
    }
  }

  // Remove personnel guest
  async function removePersonnelGuest(personnelId) {
    loading.value = true
    error.value = null

    try {
      const response = await api.delete(`/personnel-guests/${personnelId}`)

      if (response.data.success) {
        await fetchPersonnelGuests()
        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در حذف همراه'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    guests,
    personnelGuests,
    loading,
    error,
    fetchGuests,
    addGuest,
    updateGuest,
    deleteGuest,
    fetchPersonnelGuests,
    searchPersonnel,
    addPersonnelGuest,
    removePersonnelGuest
  }
})
