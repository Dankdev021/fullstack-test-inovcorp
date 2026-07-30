import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { PaginationMeta, Project, Task } from '../types'

const defaultPagination: PaginationMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
}

export const useProjectStore = defineStore('projects', () => {
  const projects = ref<Project[]>([])
  const tasks = ref<Task[]>([])
  const projectsPagination = ref<PaginationMeta>({ ...defaultPagination })
  const tasksPagination = ref<PaginationMeta>({ ...defaultPagination })
  const projectsLoading = ref(false)
  const tasksLoading = ref(false)
  const projectsError = ref('')
  const tasksError = ref('')
  const updatingTaskIds = ref<number[]>([])

  function findProject(id: number): Project | undefined {
    return projects.value.find((project) => project.id === id)
  }

  return {
    projects,
    tasks,
    projectsPagination,
    tasksPagination,
    projectsLoading,
    tasksLoading,
    projectsError,
    tasksError,
    updatingTaskIds,
    findProject,
  }
})
