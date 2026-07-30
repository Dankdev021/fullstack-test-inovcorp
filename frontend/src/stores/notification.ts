import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Toast } from '../types'

export const useNotificationStore = defineStore('notifications', () => {
  const toasts = ref<Toast[]>([])
  let nextId = 1

  function show(message: string, type: Toast['type']): void {
    const id = nextId++
    toasts.value.push({ id, message, type })

    window.setTimeout(() => remove(id), 4000)
  }

  function success(message: string): void {
    show(message, 'success')
  }

  function error(message: string): void {
    show(message, 'error')
  }

  function remove(id: number): void {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  return {
    toasts,
    success,
    error,
    remove,
  }
})
