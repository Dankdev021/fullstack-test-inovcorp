<script setup lang="ts">
import { computed } from 'vue'
import type { Task, TaskStatus } from '../types'

const props = defineProps<{
  task: Task
  updating?: boolean
}>()

const emit = defineEmits<{
  changeStatus: [status: TaskStatus]
  delete: []
}>()

const statusLabels: Record<TaskStatus, string> = {
  todo: 'A fazer',
  in_progress: 'Em andamento',
  done: 'Concluída',
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
    class="relative rounded-md border bg-white p-4 shadow-card transition duration-200 hover:shadow-md sm:p-5"
    :class="visuallyOverdue ? 'border-danger/45' : 'border-border'"
  >
    <span
      v-if="updating"
      class="absolute right-3 top-3 size-4 animate-spin rounded-full border-2 border-brand/25 border-t-brand"
      aria-label="Atualizando"
    />

    <div class="flex items-start gap-3 pr-5">
      <span
        class="mt-1 h-10 w-1 shrink-0 rounded-full"
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
          class="mt-3 text-base font-semibold leading-6 text-ink"
          :class="{ 'line-through opacity-65': task.status === 'done' }"
        >
          {{ task.title }}
        </h3>
        <p v-if="task.description" class="mt-1.5 text-sm leading-5 text-muted">
          {{ task.description }}
        </p>
      </div>
    </div>

    <div class="mt-4 grid gap-3 border-t border-border pt-4 sm:grid-cols-[1fr_auto] sm:items-end">
      <div>
        <label :for="`task-status-${task.id}`" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-subtle">
          Status
        </label>
        <select
          :id="`task-status-${task.id}`"
          class="field-control py-2"
          :value="task.status"
          :disabled="updating"
          @change="emit('changeStatus', ($event.target as HTMLSelectElement).value as TaskStatus)"
        >
          <option v-for="(label, value) in statusLabels" :key="value" :value="value">
            {{ label }}
          </option>
        </select>
      </div>

      <div class="flex items-center justify-between gap-3 sm:justify-end">
        <span class="text-xs font-medium" :class="visuallyOverdue ? 'text-danger' : 'text-subtle'">
          {{ formattedDueDate }}
        </span>
        <button
          type="button"
          class="grid size-9 place-items-center rounded-sm text-subtle transition hover:bg-danger-soft hover:text-danger disabled:opacity-50"
          :disabled="updating"
          aria-label="Excluir tarefa"
          @click="emit('delete')"
        >
          <svg viewBox="0 0 24 24" class="size-4" aria-hidden="true">
            <path fill="currentColor" d="M8 4V2h8v2h5v2h-2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6H3V4h5Zm-1 2v14h10V6H7Zm2 3h2v8H9V9Zm4 0h2v8h-2V9Z" />
          </svg>
        </button>
      </div>
    </div>
  </article>
</template>
