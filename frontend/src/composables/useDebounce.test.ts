import { afterEach, describe, expect, it, vi } from 'vitest'
import { useDebounce } from './useDebounce'

describe('useDebounce', () => {
  afterEach(() => {
    vi.useRealTimers()
  })

  it('executa apenas a chamada mais recente após o intervalo', () => {
    vi.useFakeTimers()
    const callback = vi.fn()
    const { execute } = useDebounce(callback, 300)

    execute()
    execute()
    execute()

    expect(callback).not.toHaveBeenCalled()

    vi.advanceTimersByTime(300)

    expect(callback).toHaveBeenCalledTimes(1)
  })

  it('cancela uma execução pendente', () => {
    vi.useFakeTimers()
    const callback = vi.fn()
    const { execute, cancel } = useDebounce(callback, 300)

    execute()
    cancel()
    vi.advanceTimersByTime(300)

    expect(callback).not.toHaveBeenCalled()
  })
})
