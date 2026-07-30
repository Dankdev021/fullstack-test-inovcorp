# Task Manager

Monorepositório da aplicação de gerenciamento de tarefas por projeto.

## Backend

### Requisitos

- Docker
- Docker Compose

### Execução

```bash
docker compose up -d --build
```

A API ficará disponível em `http://localhost:8000`.

### Comandos

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
docker compose logs -f
docker compose down
```

### Endpoints

```text
GET    /api/projects
POST   /api/projects
GET    /api/projects/{project}/tasks
POST   /api/projects/{project}/tasks
PATCH  /api/tasks/{task}
DELETE /api/tasks/{task}
```

As listagens usam paginação por offset. A listagem de tarefas aceita os filtros `status`, `priority` e `per_page`.

### Arquitetura

O backend utiliza enums para estados do domínio, DTOs para transferência de dados, contratos de repositório para abstrair a persistência, Form Requests para validação e API Resources para definir as respostas JSON.

## Frontend

### Requisitos

- Node.js 20.19 ou superior
- npm

### Execução

```bash
cd frontend
npm ci
npm run dev
```

O frontend ficará disponível em `http://localhost:5173` e utilizará a API em `http://localhost:8000/api`.

Nenhuma configuração adicional é necessária para o ambiente local. Para alterar a URL da API, copie `frontend/.env.example` para `frontend/.env`.

### Comandos

```bash
npm run dev
npm run build
npm run test
```

### Decisões técnicas

- Vue 3 com Composition API e TypeScript
- Vue Router para lista e detalhe do projeto
- Pinia para estado compartilhado
- Composables para acesso à API e debounce
- Tailwind CSS 4 com identidade visual inspirada no Jira
- Atualização otimista ao alterar o status das tarefas
- Dropdown para mudança de status em vez de drag-and-drop
- Vitest para testes dos composables

### Fora do escopo atual

- Autenticação
- Drag-and-drop de tarefas