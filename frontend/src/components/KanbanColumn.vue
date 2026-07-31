<script setup lang="ts">
import { ref } from 'vue'
import type { Task, TaskPriority, TaskStatus } from '../types'
import TaskCard from './TaskCard.vue'

const props = defineProps<{
  title: string
  status: TaskStatus
  tasks: Task[]
  updatingTaskIds: number[]
  accentClass: string
}>()

const emit = defineEmits<{
  dropTask: [status: TaskStatus]
  dragTask: [taskId: number, event: DragEvent]
  dragEnd: []
  changeStatus: [taskId: number, status: TaskStatus]
  changePriority: [taskId: number, priority: TaskPriority]
  deleteTask: [taskId: number]
}>()

const isDragOver = ref(false)

function leaveColumn(event: DragEvent): void {
  const column = event.currentTarget as HTMLElement
  const nextElement = event.relatedTarget as Node | null

  if (!nextElement || !column.contains(nextElement)) {
    isDragOver.value = false
  }
}

function dropTask(): void {
  isDragOver.value = false
  emit('dropTask', props.status)
}
</script>

<template>
  <section
    class="flex h-full w-[min(82vw,19rem)] shrink-0 flex-col overflow-hidden rounded-lg border bg-slate-100/80 transition sm:w-80"
    :class="isDragOver ? 'border-brand bg-brand-soft/60 shadow-inner' : 'border-border/80'"
    @dragenter.prevent="isDragOver = true"
    @dragover.prevent
    @dragleave="leaveColumn"
    @drop.prevent="dropTask"
  >
    <header class="flex items-center gap-2 px-3 py-3">
      <span class="size-2.5 rounded-full" :class="accentClass" />
      <h2 class="text-xs font-bold uppercase tracking-wide text-muted">{{ title }}</h2>
      <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-subtle shadow-sm">
        {{ tasks.length }}
      </span>
    </header>

    <TransitionGroup name="kanban-card" tag="div" class="flex min-h-0 flex-1 flex-col gap-3 overflow-y-auto overscroll-contain px-2 pb-3 [scrollbar-gutter:stable]">
      <TaskCard
        v-for="task in tasks"
        :key="task.id"
        :task="task"
        :updating="updatingTaskIds.includes(task.id)"
        @drag-start="emit('dragTask', task.id, $event)"
        @drag-end="emit('dragEnd')"
        @change-status="emit('changeStatus', task.id, $event)"
        @change-priority="emit('changePriority', task.id, $event)"
        @delete="emit('deleteTask', task.id)"
      />
      <div
        v-if="tasks.length === 0"
        :key="`${status}-empty`"
        class="grid min-h-28 place-items-center rounded-md border border-dashed border-border bg-white/60 px-4 text-center text-xs text-subtle"
      >
        Arraste uma tarefa para esta coluna
      </div>
    </TransitionGroup>
  </section>
</template>

<style scoped>
.kanban-card-enter-active,
.kanban-card-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.kanban-card-enter-from,
.kanban-card-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
