<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import type { Task, TaskStatus } from '../types'

const props = defineProps<{
  task: Task
  updating?: boolean
}>()

const emit = defineEmits<{
  changeStatus: [status: TaskStatus]
  delete: []
  dragStart: [event: DragEvent]
  dragEnd: []
}>()

const statusLabels: Record<TaskStatus, string> = {
  todo: 'A fazer',
  in_progress: 'Em andamento',
  in_testing: 'Em testes',
  done: 'Concluído',
}

const priorityLabels = {
  low: 'Baixa',
  medium: 'Média',
  high: 'Alta',
}

const priorityClasses = {
  low: 'bg-brand-soft text-brand-dark',
  medium: 'bg-warning-soft text-warning',
  high: 'bg-danger-soft text-danger',
}
const menuOpen = ref(false)

function closeMenu(): void {
  menuOpen.value = false
}

function changeStatus(status: TaskStatus): void {
  closeMenu()
  emit('changeStatus', status)
}

function deleteTask(): void {
  closeMenu()
  emit('delete')
}

onMounted(() => document.addEventListener('click', closeMenu))
onBeforeUnmount(() => document.removeEventListener('click', closeMenu))

const formattedDueDate = computed(() => {
  if (!props.task.due_date) {
    return 'Sem prazo'
  }

  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
  }).format(new Date(`${props.task.due_date}T00:00:00Z`))
})

const visuallyOverdue = computed(() => props.task.is_overdue && props.task.status !== 'done')
</script>

<template>
  <article
    class="group relative cursor-grab rounded-md border bg-white p-4 shadow-card transition duration-200 hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing"
    :class="[
      visuallyOverdue ? 'border-danger/45' : 'border-border',
      { 'z-30': menuOpen },
    ]"
    :draggable="!updating"
    @dragstart="emit('dragStart', $event)"
    @dragend="emit('dragEnd')"
    @contextmenu.prevent.stop="menuOpen = true"
  >
    <span
      v-if="updating"
      class="absolute right-12 top-4 size-4 animate-spin rounded-full border-2 border-brand/25 border-t-brand"
      aria-label="Atualizando"
    />

    <div class="absolute right-2 top-2 z-20" @click.stop>
      <button
        type="button"
        class="grid size-8 place-items-center rounded-sm text-subtle transition hover:bg-surface hover:text-ink"
        :disabled="updating"
        :aria-expanded="menuOpen"
        aria-label="Opções da tarefa"
        @mousedown.stop
        @click="menuOpen = !menuOpen"
      >
        <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
          <path fill="currentColor" d="M6 10a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm6 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm6 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z" />
        </svg>
      </button>

      <Transition name="menu">
        <div
          v-if="menuOpen"
          class="absolute right-0 top-9 w-44 overflow-hidden rounded-md border border-border bg-white py-1 shadow-overlay"
          @mousedown.stop
        >
          <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-subtle">Mover para</p>
          <button
            v-for="(label, status) in statusLabels"
            :key="status"
            type="button"
            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-ink transition hover:bg-brand-soft"
            :class="{ 'bg-surface font-semibold': task.status === status }"
            @click="changeStatus(status)"
          >
            <span class="size-4 text-brand">{{ task.status === status ? '✓' : '' }}</span>
            {{ label }}
          </button>
          <div class="my-1 border-t border-border" />
          <button
            type="button"
            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-danger transition hover:bg-danger-soft"
            @click="deleteTask"
          >
            Excluir tarefa
          </button>
        </div>
      </Transition>
    </div>

    <div class="flex items-start gap-3 pr-9">
      <span
        class="mt-1 h-9 w-1 shrink-0 rounded-full"
        :class="visuallyOverdue ? 'bg-danger' : task.status === 'done' ? 'bg-success' : 'bg-brand'"
      />
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
          <span
            class="rounded-full px-2.5 py-1 text-xs font-semibold"
            :class="priorityClasses[task.priority]"
          >
            {{ priorityLabels[task.priority] }}
          </span>
          <span v-if="visuallyOverdue" class="rounded-full bg-danger-soft px-2.5 py-1 text-xs font-semibold text-danger">
            Em atraso
          </span>
        </div>

        <h3
          class="mt-3 text-sm font-semibold leading-5 text-ink"
          :class="{ 'line-through opacity-65': task.status === 'done' }"
        >
          {{ task.title }}
        </h3>
        <p v-if="task.description" class="mt-1.5 text-sm leading-5 text-muted">
          {{ task.description }}
        </p>
      </div>
    </div>

    <div class="mt-4 border-t border-border pt-3">
      <span class="text-xs font-medium" :class="visuallyOverdue ? 'text-danger' : 'text-subtle'">
        {{ formattedDueDate }}
      </span>
    </div>
  </article>
</template>

<style scoped>
.menu-enter-active,
.menu-leave-active {
  transition: opacity 120ms ease, transform 120ms ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
