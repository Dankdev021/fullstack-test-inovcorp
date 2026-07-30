import { getCurrentScope, onScopeDispose } from 'vue'

export function useDebounce<T extends (...args: never[]) => void>(callback: T, delay = 350) {
  let timeout: ReturnType<typeof setTimeout> | undefined

  function cancel(): void {
    if (timeout) {
      clearTimeout(timeout)
      timeout = undefined
    }
  }

  function execute(...args: Parameters<T>): void {
    cancel()
    timeout = setTimeout(() => callback(...args), delay)
  }

  if (getCurrentScope()) {
    onScopeDispose(cancel)
  }

  return {
    execute,
    cancel,
  }
}
