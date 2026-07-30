<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import BaseModal from '../components/BaseModal.vue'
import TaskCard from '../components/TaskCard.vue'
import { useDebounce } from '../composables/useDebounce'
import { useProjects } from '../composables/useProjects'
import { useTask } from '../composables/useTask'
import { ApiError } from '../services/api'
import type {
  CreateTaskInput,
  TaskFilters,
  TaskStatus,
  ValidationErrors,
} from '../types'

const route = useRoute()
const projectId = computed(() => Number(route.params.id))
const { projects, loading: projectsLoading, fetchProjects, findProject } = useProjects()
const {
  tasks,
  pagination,
  loading,
  error,
  updatingTaskIds,
  fetchTasks,
  createTask,
  updateTaskStatus,
  deleteTask,
} = useTask()

const project = computed(() => findProject(projectId.value))
const modalOpen = ref(false)
const submitting = ref(false)
const validationErrors = ref<ValidationErrors>({})
const filters = reactive<TaskFilters>({
  status: '',
  priority: '',
})
const form = reactive<CreateTaskInput>({
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  due_date: null,
})

const debouncedFetch = useDebounce(() => {
  void fetchTasks(projectId.value, filters)
}, 350)

watch(
  () => [filters.status, filters.priority],
  () => debouncedFetch.execute(),
)

function openModal(): void {
  validationErrors.value = {}
  modalOpen.value = true
}

function closeModal(): void {
  if (!submitting.value) {
    modalOpen.value = false
  }
}

async function submit(): Promise<void> {
  submitting.value = true
  validationErrors.value = {}

  try {
    await createTask(projectId.value, {
      ...form,
      due_date: form.due_date || null,
    })
    Object.assign(form, {
      title: '',
      description: '',
      status: 'todo',
      priority: 'medium',
      due_date: null,
    })
    modalOpen.value = false
    await fetchTasks(projectId.value, filters)
  } catch (error) {
    if (error instanceof ApiError) {
      validationErrors.value = error.validationErrors
    }
  } finally {
    submitting.value = false
  }
}

async function changeStatus(taskId: number, status: TaskStatus): Promise<void> {
  await updateTaskStatus(taskId, status)

  if (filters.status) {
    await fetchTasks(projectId.value, filters)
  }
}

async function removeTask(taskId: number): Promise<void> {
  if (window.confirm('Deseja realmente excluir esta tarefa?')) {
    await deleteTask(taskId)
  }
}

onMounted(async () => {
  if (projects.value.length === 0) {
    await fetchProjects(1, 100)
  }

  await fetchTasks(projectId.value, filters)
})
</script>

<template>
  <main class="container-page">
    <RouterLink to="/" class="btn-ghost -ml-3 mb-4">
      <svg viewBox="0 0 24 24" class="size-4" aria-hidden="true">
        <path fill="currentColor" d="m14.7 5.3 1.4 1.4-5.3 5.3 5.3 5.3-1.4 1.4L8 12l6.7-6.7Z" />
      </svg>
      Voltar para projetos
    </RouterLink>

    <div v-if="projectsLoading && !project" class="h-28 animate-pulse rounded-md bg-white shadow-card" />

    <section v-else-if="project" class="rounded-md border border-border bg-white p-5 shadow-card sm:p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-3">
            <span
              class="rounded-full px-2.5 py-1 text-xs font-semibold"
              :class="project.status === 'active' ? 'bg-success-soft text-success' : 'bg-surface text-subtle'"
            >
              {{ project.status === 'active' ? 'Ativo' : 'Arquivado' }}
            </span>
            <span class="text-sm text-subtle">{{ project.tasks_count }} tarefas</span>
          </div>
          <h1 class="mt-3 text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ project.name }}</h1>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-muted sm:text-base">{{ project.description }}</p>
        </div>
        <button type="button" class="btn-primary w-full shrink-0 sm:w-auto" @click="openModal">
          <span class="text-xl leading-none">+</span>
          Nova tarefa
        </button>
      </div>
    </section>

    <section v-else class="rounded-md border border-danger/30 bg-danger-soft p-6 text-center">
      <h1 class="text-lg font-semibold text-danger">Projeto não encontrado</h1>
      <RouterLink to="/" class="btn-secondary mt-4">Voltar para projetos</RouterLink>
    </section>

    <section v-if="project" class="mt-6">
      <div class="rounded-md border border-border bg-white p-4 shadow-card">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
          <div class="flex-1">
            <label for="filter-status" class="field-label">Filtrar por status</label>
            <select id="filter-status" v-model="filters.status" class="field-control">
              <option value="">Todos os status</option>
              <option value="todo">A fazer</option>
              <option value="in_progress">Em andamento</option>
              <option value="done">Concluída</option>
            </select>
          </div>
          <div class="flex-1">
            <label for="filter-priority" class="field-label">Filtrar por prioridade</label>
            <select id="filter-priority" v-model="filters.priority" class="field-control">
              <option value="">Todas as prioridades</option>
              <option value="low">Baixa</option>
              <option value="medium">Média</option>
              <option value="high">Alta</option>
            </select>
          </div>
          <button
            v-if="filters.status || filters.priority"
            type="button"
            class="btn-secondary"
            @click="filters.status = ''; filters.priority = ''"
          >
            Limpar filtros
          </button>
        </div>
      </div>

      <div class="mt-5" aria-live="polite">
        <div v-if="loading" class="grid gap-4 lg:grid-cols-2">
          <div v-for="item in 4" :key="item" class="h-56 animate-pulse rounded-md border border-border bg-white p-5 shadow-card">
            <div class="h-6 w-16 rounded-full bg-slate-200" />
            <div class="mt-5 h-5 w-2/3 rounded bg-slate-200" />
            <div class="mt-3 h-4 w-full rounded bg-slate-100" />
            <div class="mt-2 h-4 w-4/5 rounded bg-slate-100" />
          </div>
        </div>

        <div v-else-if="error" class="rounded-md border border-danger/30 bg-danger-soft p-6 text-center">
          <p class="font-semibold text-danger">{{ error }}</p>
          <button type="button" class="btn-secondary mt-4" @click="fetchTasks(projectId, filters)">Tentar novamente</button>
        </div>

        <div v-else-if="tasks.length === 0" class="rounded-md border border-dashed border-border bg-white px-5 py-12 text-center">
          <span class="mx-auto grid size-12 place-items-center rounded-full bg-brand-soft text-brand">
            <svg viewBox="0 0 24 24" class="size-6" aria-hidden="true">
              <path fill="currentColor" d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 5v2h10V8H7Zm0 6v2h7v-2H7Z" />
            </svg>
          </span>
          <h2 class="mt-4 text-lg font-semibold text-ink">
            {{ filters.status || filters.priority ? 'Nenhuma tarefa encontrada' : 'Nenhuma tarefa criada' }}
          </h2>
          <p class="mt-1 text-sm text-muted">
            {{ filters.status || filters.priority ? 'Ajuste os filtros para ver outros resultados.' : 'Crie a primeira tarefa deste projeto.' }}
          </p>
        </div>

        <TransitionGroup v-else name="tasks" tag="div" class="grid gap-4 lg:grid-cols-2">
          <TaskCard
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            :updating="updatingTaskIds.includes(task.id)"
            @change-status="changeStatus(task.id, $event)"
            @delete="removeTask(task.id)"
          />
        </TransitionGroup>
      </div>

      <nav v-if="pagination.last_page > 1" class="mt-8 flex items-center justify-center gap-3" aria-label="Paginação das tarefas">
        <button
          type="button"
          class="btn-secondary"
          :disabled="pagination.current_page === 1"
          @click="fetchTasks(projectId, filters, pagination.current_page - 1)"
        >
          Anterior
        </button>
        <span class="text-sm text-muted">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
        <button
          type="button"
          class="btn-secondary"
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchTasks(projectId, filters, pagination.current_page + 1)"
        >
          Próxima
        </button>
      </nav>
    </section>

    <BaseModal :open="modalOpen" title="Criar tarefa" @close="closeModal">
      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label for="task-title" class="field-label">Título</label>
          <input
            id="task-title"
            v-model.trim="form.title"
            class="field-control"
            maxlength="160"
            placeholder="Ex.: Revisar conteúdo da página"
            required
          >
          <p v-if="validationErrors.title" class="mt-1 text-xs text-danger">{{ validationErrors.title[0] }}</p>
        </div>

        <div>
          <label for="task-description" class="field-label">Descrição</label>
          <textarea
            id="task-description"
            v-model.trim="form.description"
            class="field-control min-h-24 resize-y"
            maxlength="5000"
            placeholder="Detalhes da tarefa"
          />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="task-status" class="field-label">Status</label>
            <select id="task-status" v-model="form.status" class="field-control">
              <option value="todo">A fazer</option>
              <option value="in_progress">Em andamento</option>
              <option value="done">Concluída</option>
            </select>
          </div>
          <div>
            <label for="task-priority" class="field-label">Prioridade</label>
            <select id="task-priority" v-model="form.priority" class="field-control">
              <option value="low">Baixa</option>
              <option value="medium">Média</option>
              <option value="high">Alta</option>
            </select>
          </div>
        </div>

        <div>
          <label for="task-due-date" class="field-label">Prazo</label>
          <input id="task-due-date" v-model="form.due_date" class="field-control" type="date">
          <p v-if="validationErrors.due_date" class="mt-1 text-xs text-danger">{{ validationErrors.due_date[0] }}</p>
        </div>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
          <button type="button" class="btn-secondary" :disabled="submitting" @click="closeModal">Cancelar</button>
          <button type="submit" class="btn-primary" :disabled="submitting">
            <span v-if="submitting" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white" />
            {{ submitting ? 'Criando...' : 'Criar tarefa' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </main>
</template>

<style scoped>
.tasks-enter-active,
.tasks-leave-active {
  transition: opacity 200ms ease, transform 200ms ease;
}

.tasks-enter-from,
.tasks-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
