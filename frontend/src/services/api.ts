import type { ValidationErrors } from '../types'

const baseUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/$/, '')

export class ApiError extends Error {
  readonly status: number
  readonly validationErrors: ValidationErrors

  constructor(
    message: string,
    status: number,
    validationErrors: ValidationErrors = {},
  ) {
    super(message)
    this.status = status
    this.validationErrors = validationErrors
  }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  let response: Response

  try {
    response = await fetch(`${baseUrl}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
      },
    })
  } catch {
    throw new ApiError('Não foi possível conectar à API.', 0)
  }

  if (response.status === 204) {
    return undefined as T
  }

  const body = await response.json()

  if (!response.ok) {
    throw new ApiError(
      body.message || 'Não foi possível concluir a solicitação.',
      response.status,
      body.errors,
    )
  }

  return body as T
}

export const api = {
  get<T>(path: string): Promise<T> {
    return request<T>(path)
  },
  post<T>(path: string, data: unknown): Promise<T> {
    return request<T>(path, {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },
  patch<T>(path: string, data: unknown): Promise<T> {
    return request<T>(path, {
      method: 'PATCH',
      body: JSON.stringify(data),
    })
  },
  delete(path: string): Promise<void> {
    return request<void>(path, {
      method: 'DELETE',
    })
  },
}
