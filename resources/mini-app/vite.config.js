import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  base: '/',
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'سامانه رفاهی بانک ملی',
        short_name: 'سامانه رفاهی',
        description: 'مدیریت معرفی‌نامه مراکز رفاهی بانک ملی ایران',
        theme_color: '#00A6A6',
        icons: [
          {
            src: '/welfare/mini-app/logo-192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/welfare/mini-app/logo-512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    outDir: '../../public/mini-app',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        manualChunks: {
          'vue-vendor': ['vue', 'vue-router', 'pinia'],
        }
      }
    }
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true
      }
    }
  }
})
