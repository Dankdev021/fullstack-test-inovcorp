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