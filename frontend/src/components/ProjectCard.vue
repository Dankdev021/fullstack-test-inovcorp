<script setup lang="ts">
import type { Project } from '../types'

defineProps<{
  project: Project
}>()
</script>

<template>
  <RouterLink
    :to="{ name: 'project-detail', params: { id: project.id } }"
    class="group flex min-h-52 flex-col rounded-md border border-border bg-white p-5 shadow-card transition duration-200 hover:-translate-y-0.5 hover:border-brand/45 hover:shadow-md"
  >
    <div class="mb-4 flex items-start justify-between gap-3">
      <span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="project.status === 'active' ? 'bg-success-soft text-success' : 'bg-surface text-subtle'"
      >
        {{ project.status === 'active' ? 'Ativo' : 'Arquivado' }}
      </span>
      <svg viewBox="0 0 24 24" class="size-5 text-subtle transition group-hover:translate-x-0.5 group-hover:text-brand" aria-hidden="true">
        <path fill="currentColor" d="m9.3 5.3 6.7 6.7-6.7 6.7-1.4-1.4 5.3-5.3-5.3-5.3 1.4-1.4Z" />
      </svg>
    </div>

    <h2 class="text-lg font-semibold leading-6 text-ink group-hover:text-brand-dark">
      {{ project.name }}
    </h2>
    <p class="mt-2 line-clamp-3 text-sm leading-6 text-muted">
      {{ project.description }}
    </p>

    <div class="mt-auto flex items-center gap-2 border-t border-border pt-4 text-sm text-subtle">
      <svg viewBox="0 0 24 24" class="size-4" aria-hidden="true">
        <path fill="currentColor" d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 5v2h10V8H7Zm0 6v2h7v-2H7Z" />
      </svg>
      <span>{{ project.tasks_count }} {{ project.tasks_count === 1 ? 'tarefa' : 'tarefas' }}</span>
    </div>
  </RouterLink>
</template>
