export type ProjectStatus = 'active' | 'archived'
export type TaskStatus = 'todo' | 'in_progress' | 'in_testing' | 'done'
export type TaskPriority = 'low' | 'medium' | 'high'

export interface Project {
  id: number
  name: string
  description: string
  status: ProjectStatus
  tasks_count: number
  created_at: string
  updated_at: string
}

export interface Task {
  id: number
  project_id: number
  title: string
  description: string | null
  status: TaskStatus
  priority: TaskPriority
  due_date: string | null
  is_overdue: boolean
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
}

export interface ResourceResponse<T> {
  data: T
}

export interface CreateProjectInput {
  name: string
  description: string
  status: ProjectStatus
}

export interface CreateTaskInput {
  title: string
  description: string
  status: TaskStatus
  priority: TaskPriority
  due_date: string | null
}

export interface TaskFilters {
  status: TaskStatus | ''
  priority: TaskPriority | ''
}

export interface ValidationErrors {
  [field: string]: string[]
}

export interface Toast {
  id: number
  message: string
  type: 'success' | 'error'
}
