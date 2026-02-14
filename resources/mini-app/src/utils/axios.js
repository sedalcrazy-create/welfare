import axios from 'axios'

// Dynamic base URL based on hostname
const getBaseURL = () => {
  const hostname = window.location.hostname

  // If on subdomain (welfare.darmanjoo.ir), use /api/mini-app
  if (hostname === 'welfare.darmanjoo.ir') {
    return '/api/mini-app'
  }

  // Otherwise use /welfare/api/mini-app (for ria.jafamhis.ir/welfare/)
  return '/welfare/api/mini-app'
}

const api = axios.create({
  baseURL: getBaseURL(),
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// Request interceptor - Add token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - Handle errors
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response) {
      // 401 Unauthorized - Clear token and redirect to login
      if (error.response.status === 401) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        window.location.href = '/'
      }
    }
    return Promise.reject(error)
  }
)

export default api
