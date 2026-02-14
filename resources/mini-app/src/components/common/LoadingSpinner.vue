<template>
  <div class="flex items-center justify-center p-8">
    <div class="relative">
      <!-- Spinner -->
      <div class="w-12 h-12 border-4 border-gray-200 border-t-primary rounded-full animate-spin"></div>

      <!-- Optional text -->
      <p v-if="text" class="text-center text-gray-500 text-sm mt-4">{{ text }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    default: ''
  }
})
</script>
