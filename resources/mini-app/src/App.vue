<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Router View with transition -->
    <router-view v-slot="{ Component }">
      <transition name="page" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>

    <!-- Bottom Navigation (for authenticated users) -->
    <BottomNav v-if="showBottomNav" />

    <!-- Toast Notifications -->
    <Teleport to="body">
      <Toast />
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BottomNav from '@/components/layout/BottomNav.vue'
import Toast from '@/components/common/Toast.vue'

const route = useRoute()
const authStore = useAuthStore()

// Show bottom nav only for authenticated, approved users and not on login/register/pending
const showBottomNav = computed(() => {
  return authStore.isAuthenticated &&
         authStore.isApproved &&
         !['login', 'register', 'pending'].includes(route.name)
})

onMounted(() => {
  // Initialize auth from localStorage
  authStore.init()
})
</script>

<style scoped>
/* Page transitions */
.page-enter-active,
.page-leave-active {
  transition: all 0.25s ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateX(10px);
}

.page-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
</style>
