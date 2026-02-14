<template>
  <Transition name="toast">
    <div
      v-if="visible"
      class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full mx-4"
    >
      <div
        class="rounded-lg shadow-lg px-4 py-3 flex items-center gap-3"
        :class="typeClass"
      >
        <!-- Icon -->
        <div class="flex-shrink-0">
          <svg v-if="type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg v-else-if="type === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
        </div>

        <!-- Message -->
        <p class="flex-1 text-sm font-medium">{{ message }}</p>

        <!-- Close button -->
        <button
          @click="hide"
          class="flex-shrink-0 tap-highlight-none"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'

const visible = ref(false)
const message = ref('')
const type = ref('info') // success, error, info
let timeout = null

const typeClass = computed(() => {
  if (type.value === 'success') {
    return 'bg-green-500 text-white'
  } else if (type.value === 'error') {
    return 'bg-red-500 text-white'
  } else {
    return 'bg-blue-500 text-white'
  }
})

function show(msg, msgType = 'info', duration = 3000) {
  message.value = msg
  type.value = msgType
  visible.value = true

  if (timeout) {
    clearTimeout(timeout)
  }

  timeout = setTimeout(() => {
    hide()
  }, duration)
}

function hide() {
  visible.value = false
  if (timeout) {
    clearTimeout(timeout)
    timeout = null
  }
}

// Expose methods to be called from parent
defineExpose({ show, hide })

// Global toast instance (will be set in main.js)
if (!window.$toast) {
  window.$toast = { show, hide }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translate(-50%, -20px);
}

.toast-leave-to {
  opacity: 0;
  transform: translate(-50%, -10px);
}
</style>
