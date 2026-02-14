import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const personnel = ref(null)
  const token = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const hasPersonnel = computed(() => !!personnel.value)
  const personnelStatus = computed(() => personnel.value?.status || null)
  const isApproved = computed(() => personnelStatus.value === 'approved')
  const isPending = computed(() => personnelStatus.value === 'pending')

  // Initialize from localStorage
  function init() {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('user')

    if (savedToken) {
      token.value = savedToken
    }

    if (savedUser) {
      try {
        const userData = JSON.parse(savedUser)
        user.value = userData.user
        personnel.value = userData.personnel
      } catch (e) {
        console.error('Failed to parse saved user data:', e)
      }
    }
  }

  // Verify with Bale
  async function verify(initData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/auth/verify', { initData })

      if (response.data.success) {
        const { token: authToken, user: userData, personnel: personnelData } = response.data.data

        token.value = authToken
        user.value = userData
        personnel.value = personnelData

        localStorage.setItem('auth_token', authToken)
        localStorage.setItem('user', JSON.stringify({
          user: userData,
          personnel: personnelData
        }))

        return true
      }

      return false
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در احراز هویت'
      return false
    } finally {
      loading.value = false
    }
  }

  // Get current user
  async function fetchMe() {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/auth/me')

      if (response.data.success) {
        const { user: userData, personnel: personnelData } = response.data.data

        user.value = userData
        personnel.value = personnelData

        localStorage.setItem('user', JSON.stringify({
          user: userData,
          personnel: personnelData
        }))
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'خطا در دریافت اطلاعات کاربر'
    } finally {
      loading.value = false
    }
  }

  // Logout
  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = null
      user.value = null
      personnel.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  }

  // Update personnel status (after registration or approval)
  function updatePersonnelStatus(newStatus) {
    if (personnel.value) {
      personnel.value.status = newStatus
      localStorage.setItem('user', JSON.stringify({
        user: user.value,
        personnel: personnel.value
      }))
    }
  }

  return {
    user,
    personnel,
    token,
    loading,
    error,
    isAuthenticated,
    hasPersonnel,
    personnelStatus,
    isApproved,
    isPending,
    init,
    verify,
    fetchMe,
    logout,
    updatePersonnelStatus
  }
})
