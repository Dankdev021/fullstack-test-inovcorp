import { createPinia, setActivePinia } from 'pinia'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { useProjectStore } from '../stores/project'
import type { Task } from '../types'
import { useTask } from './useTask'

const task: Task = {
  id: 1,
  project_id: 1,
  title: 'Validar quadro',
  description: null,
  status: 'todo',
  priority: 'medium',
  due_date: null,
  is_overdue: false,
  created_at: '2026-07-29T00:00:00.000Z',
  updated_at: '2026-07-29T00:00:00.000Z',
}

describe('useTask', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.stubGlobal('window', { setTimeout: vi.fn() })
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('faz rollback quando a atualização otimista falha', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('Falha de rede')))
    const store = useProjectStore()
    store.tasks = [{ ...task }]
    const { updateTaskStatus } = useTask()

    const request = updateTaskStatus(task.id, 'in_testing')

    expect(store.tasks[0].status).toBe('in_testing')

    await request

    expect(store.tasks[0].status).toBe('todo')
    expect(store.updatingTaskIds).toEqual([])
  })

  it('carrega até cem tarefas para o quadro', async () => {
    const fetchMock = vi.fn().mockResolvedValue(new Response(JSON.stringify({
      data: [],
      meta: {
        current_page: 1,
        last_page: 1,
        per_page: 100,
        total: 0,
      },
    }), {
      status: 200,
      headers: { 'Content-Type': 'application/json' },
    }))
    vi.stubGlobal('fetch', fetchMock)
    const { fetchTasks } = useTask()

    await fetchTasks(1, { status: '', priority: '' })

    expect(fetchMock.mock.calls[0][0]).toContain('per_page=100')
  })
})
