import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/main.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')

// Bale Mini App готовности
if (window.Bale && window.Bale.WebApp) {
  window.Bale.WebApp.ready()
}
