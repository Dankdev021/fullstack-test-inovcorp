# Task Manager

Aplicação full stack para gerenciamento de projetos e tarefas. Cada projeto possui um quadro Kanban com filtros, criação de tarefas e movimentação entre os estados `A fazer`, `Em andamento`, `Em testes` e `Concluído`.

O projeto está organizado como um monorepositório:

```text
.
├── backend/       Laravel 12
├── frontend/      Vue 3 e Tailwind CSS 4
├── docker/        Configuração do Nginx
└── compose.yaml   Ambiente do backend
```

## Funcionalidades

- Listagem paginada e criação de projetos
- Contagem de tarefas por projeto
- Quadro Kanban responsivo com quatro colunas
- Criação, movimentação e exclusão de tarefas
- Drag-and-drop entre colunas
- Menu de ações pelo botão de opções ou pelo botão direito
- Filtros por status e prioridade com debounce
- Indicação visual de tarefas em atraso
- Atualização otimista com rollback em caso de falha
- Feedback de loading, sucesso, erro e estados vazios
- Paginação, rate limiting e soft delete na API

## Tecnologias

### Backend

- Laravel 12
- PHP 8.3 FPM
- MySQL 8.4
- Nginx 1.27
- Docker Compose
- PHPUnit

### Frontend

- Vue 3 com Composition API e TypeScript
- Vue Router
- Pinia
- Tailwind CSS 4
- Vite
- Vitest

## Requisitos

- Docker
- Docker Compose
- Node.js 20.19 ou superior
- npm

Não é necessário instalar PHP, Composer ou MySQL no host.

As portas `8000`, `3306` e `5173` precisam estar disponíveis.

## Instalação e execução

### 1. Backend

Na raiz do projeto, construa e inicie os containers:

```bash
docker compose up -d --build
```

O container da aplicação cria o arquivo de ambiente, gera a chave do Laravel e executa as migrações automaticamente.

Popule o banco com os dados de demonstração:

```bash
docker compose exec app php artisan migrate --seed
```

A API ficará disponível em:

```text
http://localhost:8000
```

Para verificar os containers:

```bash
docker compose ps
```

### 2. Frontend

Em outro terminal:

```bash
cd frontend
npm ci
npm run dev
```

A aplicação ficará disponível em:

```text
http://localhost:5173
```

O frontend utiliza `http://localhost:8000/api` por padrão, portanto nenhuma configuração adicional é necessária.

Para usar outra URL de API:

```bash
cp .env.example .env
```

Depois, altere `VITE_API_URL` no arquivo `frontend/.env`.

## Testes e build

### Backend

```bash
docker compose exec app php artisan test
```

### Frontend

```bash
cd frontend
npm run test
npm run build
```

O build executa a validação do TypeScript antes de gerar os arquivos de produção.

## Comandos úteis

```bash
docker compose logs -f
docker compose down
docker compose up -d
```

Os dados do MySQL são mantidos no volume `database_data` após reiniciar os containers.

Para recriar completamente o banco com os dados de demonstração:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## API

```text
GET    /api/projects
POST   /api/projects
GET    /api/projects/{project}/tasks
POST   /api/projects/{project}/tasks
PATCH  /api/tasks/{task}
DELETE /api/tasks/{task}
```

### Filtros e paginação

Projetos:

```text
GET /api/projects?page=1&per_page=15
```

Tarefas:

```text
GET /api/projects/1/tasks?status=todo&priority=high&per_page=15
```

As listagens utilizam paginação por offset. Projetos aceitam `per_page` entre 1 e 100 e tarefas entre 1 e 200.

Cada projeto aceita no máximo 200 tarefas, distribuídas livremente entre as colunas do Kanban.

Valores aceitos:

```text
Projeto:   active | archived
Status:    todo | in_progress | in_testing | done
Prioridade: low | medium | high
```

## Decisões técnicas

### Monorepositório com frontend fora do Docker

O backend foi dockerizado para garantir versões consistentes de PHP, Nginx e MySQL. O frontend permanece fora do Docker para que o avaliador precise executar apenas `npm ci` e `npm run dev`, mantendo o feedback do Vite rápido durante o desenvolvimento.

### Organização do backend

O backend utiliza:

- Enums para valores do domínio
- DTOs para transportar dados validados
- Form Requests para validação dos endpoints de escrita
- Contratos e repositórios para separar persistência dos controllers
- API Resources para manter respostas JSON consistentes
- Scope `overdue` para centralizar a consulta de tarefas atrasadas

O fluxo principal é:

```text
Rota → Form Request → Controller → DTO → Repository → Model → API Resource
```

Essa estrutura mantém os controllers pequenos e permite substituir ou testar a camada de persistência sem espalhar consultas pela aplicação.

### Quadro Kanban

Foi adicionado o estado `in_testing` para representar uma etapa comum do fluxo de desenvolvimento e formar quatro colunas. A API e o frontend compartilham os mesmos valores de status.

O drag-and-drop utiliza a API nativa do navegador para evitar uma dependência adicional. O menu do card oferece uma alternativa acessível para mover tarefas.

As colunas têm altura limitada e rolagem vertical independente. Em telas menores, o quadro utiliza rolagem horizontal sem aumentar a altura total da página.

### Estado e comunicação com a API

Pinia mantém o estado compartilhado de projetos, tarefas e notificações. Os composables `useProjects` e `useTask` encapsulam chamadas HTTP e regras de atualização.

A mudança de status é otimista para produzir resposta imediata. Se a API falhar, o estado anterior é restaurado e o usuário recebe uma notificação.

As respostas antigas dos filtros são ignoradas para evitar condições de corrida durante alterações rápidas. O Kanban carrega as 200 tarefas permitidas por projeto para manter todas as colunas sincronizadas sem paginação visual.

### Testes

Os testes de integração do backend cobrem paginação, validação, filtros, criação, atualização, soft delete e tarefas atrasadas.

No frontend, os testes com Vitest cobrem o rollback da atualização otimista e o limite de tarefas solicitado pelo Kanban.

## O que ficou por implementar

### Autenticação e gestão de usuários

Não foram implementadas porque o teste solicita uma aplicação simplificada e não exige autenticação. Adicionar usuários também exigiria definir permissões, propriedade de projetos, recuperação de senha e proteção das rotas, aumentando o escopo sem contribuir diretamente para os requisitos avaliados.

### Edição completa de projetos e tarefas

A API permite atualizar status e prioridade da tarefa, conforme solicitado. Edição de título, descrição, prazo e dados do projeto não foi adicionada porque esses endpoints não fazem parte do contrato exigido.

### Paginação visual das tarefas

A API possui paginação, mas o Kanban carrega todas as tarefas do projeto, respeitando o limite de 200. Paginar cada coluna de forma independente adicionaria complexidade ao drag-and-drop e poderia ocultar tarefas durante filtros ou movimentações. Para um gerenciador simplificado, o limite atual mantém o comportamento previsível.

### CI/CD e publicação

O projeto possui comandos locais de teste e build, mas não inclui pipeline de publicação. A prioridade foi entregar uma instalação reproduzível e as funcionalidades solicitadas sem vincular a solução a uma plataforma específica.