import { storeToRefs } from 'pinia'
import { api, ApiError } from '../services/api'
import { useNotificationStore } from '../stores/notification'
import { useProjectStore } from '../stores/project'
import type {
  CreateTaskInput,
  PaginatedResponse,
  ResourceResponse,
  Task,
  TaskFilters,
  TaskPriority,
  TaskStatus,
} from '../types'

type TaskUpdatePayload = {
  status?: TaskStatus
  priority?: TaskPriority
}

export function useTask() {
  const store = useProjectStore()
  const notifications = useNotificationStore()
  const {
    tasks,
    tasksPagination,
    tasksLoading,
    tasksError,
    updatingTaskIds,
  } = storeToRefs(store)
  let latestRequest = 0

  async function fetchTasks(projectId: number, filters: TaskFilters, page = 1): Promise<void> {
    const requestId = ++latestRequest
    const query = new URLSearchParams({
      page: String(page),
      per_page: '200',
    })

    if (filters.status) {
      query.set('status', filters.status)
    }

    if (filters.priority) {
      query.set('priority', filters.priority)
    }

    store.tasksLoading = true
    store.tasksError = ''

    try {
      const response = await api.get<PaginatedResponse<Task>>(
        `/projects/${projectId}/tasks?${query.toString()}`,
      )

      if (requestId === latestRequest) {
        store.tasks = response.data
        store.tasksPagination = response.meta
      }
    } catch (error) {
      if (requestId === latestRequest) {
        const message = error instanceof ApiError ? error.message : 'Não foi possível carregar as tarefas.'
        store.tasksError = message
        notifications.error(message)
      }
    } finally {
      if (requestId === latestRequest) {
        store.tasksLoading = false
      }
    }
  }

  async function createTask(projectId: number, input: CreateTaskInput): Promise<Task> {
    try {
      const response = await api.post<ResourceResponse<Task>>(
        `/projects/${projectId}/tasks`,
        input,
      )
      store.tasks.unshift(response.data)
      store.tasksPagination.total += 1

      const project = store.findProject(projectId)
      if (project) {
        project.tasks_count += 1
      }

      notifications.success('Tarefa criada com sucesso.')

      return response.data
    } catch (error) {
      const message = error instanceof ApiError ? error.message : 'Não foi possível criar a tarefa.'
      notifications.error(message)
      throw error
    }
  }

  async function updateTask(taskId: number, payload: TaskUpdatePayload): Promise<void> {
    const index = store.tasks.findIndex((task) => task.id === taskId)

    if (index < 0 || (payload.status === undefined && payload.priority === undefined)) {
      return
    }

    const previous = { ...store.tasks[index] }
    store.tasks[index] = { ...previous, ...payload }
    store.updatingTaskIds.push(taskId)

    try {
      const response = await api.patch<ResourceResponse<Task>>(`/tasks/${taskId}`, payload)
      store.tasks[index] = response.data
      notifications.success(payload.priority !== undefined && payload.status === undefined
        ? 'Prioridade atualizada.'
        : 'Status atualizado.')
    } catch (error) {
      store.tasks[index] = previous
      const message = error instanceof ApiError
        ? error.message
        : 'Não foi possível atualizar a tarefa.'
      notifications.error(`${message} A alteração foi desfeita.`)
    } finally {
      store.updatingTaskIds = store.updatingTaskIds.filter((id) => id !== taskId)
    }
  }

  async function updateTaskStatus(taskId: number, status: TaskStatus): Promise<void> {
    await updateTask(taskId, { status })
  }

  async function updateTaskPriority(taskId: number, priority: TaskPriority): Promise<void> {
    await updateTask(taskId, { priority })
  }

  async function deleteTask(taskId: number): Promise<void> {
    const task = store.tasks.find((item) => item.id === taskId)
    store.updatingTaskIds.push(taskId)

    try {
      await api.delete(`/tasks/${taskId}`)
      store.tasks = store.tasks.filter((task) => task.id !== taskId)
      store.tasksPagination.total = Math.max(0, store.tasksPagination.total - 1)

      if (task) {
        const project = store.findProject(task.project_id)

        if (project) {
          project.tasks_count = Math.max(0, project.tasks_count - 1)
        }
      }

      notifications.success('Tarefa excluída.')
    } catch (error) {
      const message = error instanceof ApiError ? error.message : 'Não foi possível excluir a tarefa.'
      notifications.error(message)
    } finally {
      store.updatingTaskIds = store.updatingTaskIds.filter((id) => id !== taskId)
    }
  }

  return {
    tasks,
    pagination: tasksPagination,
    loading: tasksLoading,
    error: tasksError,
    updatingTaskIds,
    fetchTasks,
    createTask,
    updateTask,
    updateTaskStatus,
    updateTaskPriority,
    deleteTask,
  }
}
