<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import BaseModal from '../components/BaseModal.vue'
import ProjectCard from '../components/ProjectCard.vue'
import { useProjects } from '../composables/useProjects'
import { ApiError } from '../services/api'
import type { CreateProjectInput, ValidationErrors } from '../types'

const {
  projects,
  pagination,
  loading,
  error,
  fetchProjects,
  createProject,
} = useProjects()

const modalOpen = ref(false)
const submitting = ref(false)
const validationErrors = ref<ValidationErrors>({})
const form = reactive<CreateProjectInput>({
  name: '',
  description: '',
  status: 'active',
})

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
    await createProject({ ...form })
    Object.assign(form, {
      name: '',
      description: '',
      status: 'active',
    })
    modalOpen.value = false
  } catch (error) {
    if (error instanceof ApiError) {
      validationErrors.value = error.validationErrors
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => fetchProjects())
</script>

<template>
  <main class="container-page flex min-h-[calc(100dvh-3.5rem)] flex-col sm:min-h-[calc(100dvh-4rem)]">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-semibold text-brand">Visão geral</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-ink sm:text-4xl">Projetos</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-muted sm:text-base">
          Acompanhe o andamento dos projetos e organize as tarefas da equipe.
        </p>
      </div>
      <button type="button" class="btn-primary w-full sm:w-auto" @click="openModal">
        <span class="text-xl leading-none">+</span>
        Novo projeto
      </button>
    </div>

    <section class="mt-8 flex flex-1 flex-col" aria-live="polite">
      <div v-if="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="item in 6"
          :key="item"
          class="h-52 animate-pulse rounded-md border border-border bg-white p-5 shadow-card"
        >
          <div class="h-6 w-20 rounded-full bg-slate-200" />
          <div class="mt-5 h-5 w-2/3 rounded bg-slate-200" />
          <div class="mt-3 h-4 w-full rounded bg-slate-100" />
          <div class="mt-2 h-4 w-4/5 rounded bg-slate-100" />
        </div>
      </div>

      <div v-else-if="error" class="rounded-md border border-danger/30 bg-danger-soft p-6 text-center">
        <p class="font-semibold text-danger">{{ error }}</p>
        <button type="button" class="btn-secondary mt-4" @click="fetchProjects()">Tentar novamente</button>
      </div>

      <div v-else-if="projects.length === 0" class="rounded-md border border-dashed border-border bg-white px-5 py-14 text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-full bg-brand-soft text-2xl text-brand">+</span>
        <h2 class="mt-4 text-lg font-semibold text-ink">Nenhum projeto criado</h2>
        <p class="mt-1 text-sm text-muted">Crie o primeiro projeto para começar a organizar as tarefas.</p>
        <button type="button" class="btn-primary mt-5" @click="openModal">Criar projeto</button>
      </div>

      <TransitionGroup
        v-else
        name="list"
        tag="div"
        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
      >
        <ProjectCard v-for="project in projects" :key="project.id" :project="project" />
      </TransitionGroup>

      <nav class="mt-auto flex items-center justify-center gap-3 pt-8" aria-label="Paginação">
        <button
          type="button"
          class="btn-secondary"
          :disabled="pagination.current_page === 1"
          @click="fetchProjects(pagination.current_page - 1)"
        >
          Anterior
        </button>
        <span class="text-sm text-muted">
          Página {{ pagination.current_page }} de {{ pagination.last_page }}
        </span>
        <button
          type="button"
          class="btn-secondary"
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchProjects(pagination.current_page + 1)"
        >
          Próxima
        </button>
      </nav>
    </section>

    <BaseModal :open="modalOpen" title="Criar projeto" @close="closeModal">
      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label for="project-name" class="field-label">Nome</label>
          <input
            id="project-name"
            v-model.trim="form.name"
            class="field-control"
            type="text"
            maxlength="120"
            placeholder="Ex.: Lançamento do aplicativo"
            required
          >
          <p v-if="validationErrors.name" class="mt-1 text-xs text-danger">{{ validationErrors.name[0] }}</p>
        </div>

        <div>
          <label for="project-description" class="field-label">Descrição</label>
          <textarea
            id="project-description"
            v-model.trim="form.description"
            class="field-control min-h-28 resize-y"
            maxlength="2000"
            placeholder="Descreva o objetivo do projeto"
            required
          />
          <p v-if="validationErrors.description" class="mt-1 text-xs text-danger">{{ validationErrors.description[0] }}</p>
        </div>

        <div>
          <label for="project-status" class="field-label">Status</label>
          <select id="project-status" v-model="form.status" class="field-control">
            <option value="active">Ativo</option>
            <option value="archived">Arquivado</option>
          </select>
        </div>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
          <button type="button" class="btn-secondary" :disabled="submitting" @click="closeModal">Cancelar</button>
          <button type="submit" class="btn-primary" :disabled="submitting">
            <span v-if="submitting" class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white" />
            {{ submitting ? 'Criando...' : 'Criar projeto' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </main>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: opacity 200ms ease, transform 200ms ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
