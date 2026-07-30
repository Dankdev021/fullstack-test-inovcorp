<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useNotificationStore } from '../stores/notification'

const store = useNotificationStore()
const { toasts } = storeToRefs(store)
</script>

<template>
  <div class="pointer-events-none fixed inset-x-4 bottom-4 z-[60] flex flex-col items-end gap-2 sm:left-auto sm:w-96">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex w-full items-start gap-3 rounded-sm border bg-white p-4 shadow-overlay"
        :class="toast.type === 'success' ? 'border-success/30' : 'border-danger/30'"
        role="status"
      >
        <span
          class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full text-sm font-bold text-white"
          :class="toast.type === 'success' ? 'bg-success' : 'bg-danger'"
        >
          {{ toast.type === 'success' ? '✓' : '!' }}
        </span>
        <p class="flex-1 text-sm leading-5 text-ink">{{ toast.message }}</p>
        <button
          type="button"
          class="text-lg leading-none text-subtle hover:text-ink"
          aria-label="Fechar mensagem"
          @click="store.remove(toast.id)"
        >
          ×
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
