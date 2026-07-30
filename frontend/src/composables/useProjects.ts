import { storeToRefs } from 'pinia'
import { api, ApiError } from '../services/api'
import { useNotificationStore } from '../stores/notification'
import { useProjectStore } from '../stores/project'
import type {
  CreateProjectInput,
  PaginatedResponse,
  Project,
  ResourceResponse,
} from '../types'

export function useProjects() {
  const store = useProjectStore()
  const notifications = useNotificationStore()
  const {
    projects,
    projectsPagination,
    projectsLoading,
    projectsError,
  } = storeToRefs(store)

  async function fetchProjects(page = 1, perPage = 9): Promise<void> {
    store.projectsLoading = true
    store.projectsError = ''

    try {
      const response = await api.get<PaginatedResponse<Project>>(
        `/projects?page=${page}&per_page=${perPage}`,
      )
      store.projects = response.data
      store.projectsPagination = response.meta
    } catch (error) {
      const message = error instanceof ApiError ? error.message : 'Não foi possível carregar os projetos.'
      store.projectsError = message
      notifications.error(message)
    } finally {
      store.projectsLoading = false
    }
  }

  async function createProject(input: CreateProjectInput): Promise<Project> {
    try {
      const response = await api.post<ResourceResponse<Project>>('/projects', input)
      store.projects.unshift(response.data)
      store.projectsPagination.total += 1
      notifications.success('Projeto criado com sucesso.')

      return response.data
    } catch (error) {
      const message = error instanceof ApiError ? error.message : 'Não foi possível criar o projeto.'
      notifications.error(message)
      throw error
    }
  }

  return {
    projects,
    pagination: projectsPagination,
    loading: projectsLoading,
    error: projectsError,
    fetchProjects,
    createProject,
    findProject: store.findProject,
  }
}
