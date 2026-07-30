<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue'

const props = defineProps<{
  open: boolean
  title: string
}>()

const emit = defineEmits<{
  close: []
}>()

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    emit('close')
  }
}

watch(
  () => props.open,
  (open) => {
    document.body.style.overflow = open ? 'hidden' : ''

    if (open) {
      window.addEventListener('keydown', handleKeydown)
    } else {
      window.removeEventListener('keydown', handleKeydown)
    }
  },
)

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 grid items-end bg-ink/45 p-0 sm:place-items-center sm:p-4"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
        @mousedown.self="emit('close')"
      >
        <section class="max-h-[92vh] w-full overflow-y-auto rounded-t-lg bg-white shadow-overlay sm:max-w-lg sm:rounded-lg">
          <header class="flex items-center justify-between border-b border-border px-5 py-4">
            <h2 class="text-lg font-semibold text-ink">{{ title }}</h2>
            <button
              type="button"
              class="grid size-9 place-items-center rounded-sm text-muted transition hover:bg-surface hover:text-ink"
              aria-label="Fechar"
              @click="emit('close')"
            >
              <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                <path fill="currentColor" d="m6.7 5.3 5.3 5.3 5.3-5.3 1.4 1.4-5.3 5.3 5.3 5.3-1.4 1.4-5.3-5.3-5.3 5.3-1.4-1.4 5.3-5.3-5.3-5.3 1.4-1.4Z" />
              </svg>
            </button>
          </header>
          <div class="p-5">
            <slot />
          </div>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 180ms ease;
}

.modal-enter-active section,
.modal-leave-active section {
  transition: transform 180ms ease, opacity 180ms ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from section,
.modal-leave-to section {
  opacity: 0;
  transform: translateY(16px) scale(0.98);
}
</style>
