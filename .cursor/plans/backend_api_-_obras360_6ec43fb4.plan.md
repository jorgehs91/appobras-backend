# Backend API Requirements - Obras360

## Contexto

O frontend Expo/React Native Web precisa de endpoints REST para gerenciar Fases (Phases), Tarefas (Tasks), Empreiteiros (Contractors) e Documentos. O cálculo de progresso deve ser feito on-the-fly (sem coluna de % persistida).**Padrões:**

- Laravel API Resources para normalizar responses
- Validação via Form Requests
- Policies para autorização (RBAC)
- Filtrar sempre por `company_id` (via middleware ou policy)
- Timestamps: `created_at`, `updated_at`, `deleted_at` (soft deletes)

---

## 1. Database Migrations

### Migration: `create_phases_table`

```php
Schema::create('phases', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->onDelete('cascade');
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('status', ['draft', 'active', 'archived'])->default('active');
    $table->integer('sequence')->default(0); // ordem no cronograma
    $table->string('color', 7)->nullable(); // hex color, ex: #3B82F6
    $table->date('planned_start_at')->nullable();
    $table->date('planned_end_at')->nullable();
    $table->date('actual_start_at')->nullable();
    $table->date('actual_end_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['project_id', 'status', 'sequence']);
    $table->index(['company_id', 'project_id']);
});
```

**Validações:**

- `name`: required, string, max:255
- `status`: in:draft,active,archived
- `planned_end_at` >= `planned_start_at` (se ambos informados)
- `sequence`: integer, >= 0

---

### Migration: `create_tasks_table`

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->onDelete('cascade');
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->foreignId('phase_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('status', ['backlog', 'in_progress', 'in_review', 'done', 'canceled'])->default('backlog');
    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
    $table->integer('order_in_phase')->default(0);
    $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('contractor_id')->nullable()->constrained()->onDelete('set null');
    $table->boolean('is_blocked')->default(false);
    $table->string('blocked_reason')->nullable();
    $table->date('planned_start_at')->nullable();
    $table->date('planned_end_at')->nullable();
    $table->date('due_at')->nullable(); // alias de planned_end_at para UX
    $table->timestamp('started_at')->nullable(); // quando foi para in_progress
    $table->timestamp('completed_at')->nullable(); // quando foi para done
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['project_id', 'phase_id', 'status']);
    $table->index(['assignee_id']);
    $table->index(['contractor_id']);
    $table->index(['planned_end_at']);
});
```

**Validações:**

- `title`: required, string, max:255
- `phase_id`: required, exists:phases,id (e pertence ao mesmo project_id)
- `status`: in:backlog,in_progress,in_review,done,canceled
- `priority`: nullable, in:low,medium,high,urgent
- `planned_end_at` >= `planned_start_at`
- `assignee_id`: nullable, exists:users,id
- `contractor_id`: nullable, exists:contractors,id

**Observers/Events:**

- Ao mudar status para `in_progress`: setar `started_at = now()`
- Ao mudar status para `done`: setar `completed_at = now()`
- Ao reabrir (done → outro status): limpar `completed_at`

---

### Migration: `create_contractors_table`

```php
Schema::create('contractors', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('contact')->nullable(); // phone, email, etc
    $table->text('specialties')->nullable(); // pode ser JSON ou texto simples
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('company_id');
});
```

**Validações:**

- `name`: required, string, max:255

---

### Migration: `create_documents_table`

```php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->onDelete('cascade');
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('file_path'); // path no storage
    $table->string('file_url')->nullable(); // URL pública (se usar S3, por ex)
    $table->string('mime_type')->nullable();
    $table->bigInteger('file_size')->nullable(); // bytes
    $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['project_id', 'created_at']);
});
```

**Validações:**

- `name`: required, string, max:255
- Upload: max 10MB (ou conforme necessidade), tipos permitidos: pdf, jpg, png, docx, xlsx

---

### Atualização: `projects` table

Adicionar campo computado (accessor) para `progress_percent`:

```php
// Model Project
public function getProgressPercentAttribute(): int
{
    $activePhases = $this->phases()->where('status', 'active')->get();
    if ($activePhases->isEmpty()) return 0;
    
    $sum = $activePhases->sum(fn($phase) => $phase->progress_percent);
    return (int) round($sum / $activePhases->count());
}
```

---

## 2. API Endpoints - Phases

### `GET /api/v1/projects/{project}/phases`

**Descrição:** Listar fases de um projeto.**Headers:**

- `Authorization: Bearer {token}`
- `X-Company-Id: {company_id}`

**Query params:**

- `status` (opcional): draft|active|archived (filtrar por status)

**Response 200:**

```json
[
  {
    "id": 1,
    "project_id": 5,
    "name": "Fundação",
    "description": "Preparação e execução das fundações",
    "status": "active",
    "sequence": 1,
    "color": "#3B82F6",
    "planned_start_at": "2024-01-15",
    "planned_end_at": "2024-02-15",
    "actual_start_at": null,
    "actual_end_at": null,
    "progress_percent": 75,
    "tasks_counts": {
      "total": 4,
      "backlog": 0,
      "in_progress": 1,
      "in_review": 0,
      "done": 3,
      "canceled": 0
    },
    "schedule_status": "no_prazo",
    "created_at": "2024-01-01T10:00:00Z",
    "updated_at": "2024-01-10T15:30:00Z"
  }
]
```

**Lógica:**

- `progress_percent`: média simples do `progress_percent` das tasks da fase com `status != 'canceled'`. Se não houver tasks, retornar 0.
- `tasks_counts`: contar tasks por status
- `schedule_status`: comparar progresso com tempo esperado (opcional; pode retornar sempre "sem_datas" no MVP)

---

### `POST /api/v1/projects/{project}/phases`

**Descrição:** Criar nova fase.**Body:**

```json
{
  "name": "Estrutura",
  "description": "Concretagem de pilares e lajes",
  "status": "active",
  "sequence": 2,
  "color": "#10B981",
  "planned_start_at": "2024-02-16",
  "planned_end_at": "2024-04-30"
}
```

**Response 201:**

```json
{
  "id": 2,
  "project_id": 5,
  "name": "Estrutura",
  ...
}
```

---

### `PUT /api/v1/phases/{phase}`

**Descrição:** Atualizar fase.**Body:** mesmos campos do POST (parcial permitido).**Response 200:** objeto atualizado.---

### `DELETE /api/v1/phases/{phase}`

**Descrição:** Soft delete de fase.**Response 204:** No Content.**Regra de negócio:** Não permitir deletar fase com tasks (ou deletar em cascata com confirmação).---

## 3. API Endpoints - Tasks

### `GET /api/v1/projects/{project}/tasks`

**Descrição:** Listar tasks de um projeto (todas as fases).**Query params:**

- `phase_id` (opcional): filtrar por fase
- `status` (opcional): filtrar por status
- `assignee_id` (opcional): filtrar por responsável

**Response 200:**

```json
[
  {
    "id": 10,
    "project_id": 5,
    "phase_id": 1,
    "phase_name": "Fundação",
    "title": "Concretar sapatas",
    "description": "Executar concretagem das sapatas do bloco A",
    "status": "done",
    "priority": "high",
    "order_in_phase": 1,
    "assignee_id": 3,
    "assignee_name": "João Silva",
    "contractor_id": 2,
    "contractor_name": "Construtora XYZ",
    "is_blocked": false,
    "blocked_reason": null,
    "planned_start_at": "2024-01-15",
    "planned_end_at": "2024-01-20",
    "due_at": "2024-01-20",
    "started_at": "2024-01-15T08:00:00Z",
    "completed_at": "2024-01-19T17:30:00Z",
    "progress_percent": 100,
    "schedule_status": "no_prazo",
    "created_at": "2024-01-10T10:00:00Z",
    "updated_at": "2024-01-19T17:30:00Z"
  }
]
```

**Lógica:**

- `progress_percent`: mapeado por status:
- `backlog` = 0
- `in_progress` = 50
- `in_review` = 90
- `done` = 100
- `canceled` = excluir do cálculo (não retornar em médias)
- Incluir `phase_name`, `assignee_name`, `contractor_name` via eager loading

---

### `POST /api/v1/projects/{project}/tasks`

**Descrição:** Criar nova task.**Body:**

```json
{
  "phase_id": 1,
  "title": "Instalar armaduras",
  "description": "Montagem das armaduras de ferro",
  "status": "backlog",
  "priority": "medium",
  "assignee_id": 3,
  "contractor_id": null,
  "planned_start_at": "2024-01-21",
  "planned_end_at": "2024-01-25",
  "due_at": "2024-01-25"
}
```

**Response 201:** objeto criado.---

### `PUT /api/v1/tasks/{task}`

**Descrição:** Atualizar task.**Body:** campos parciais permitidos.**Response 200:** objeto atualizado.**Regras:**

- Se mudar `status` para `in_progress` e `started_at` for null, setar `started_at = now()`
- Se mudar `status` para `done`, setar `completed_at = now()`
- Se mudar de `done` para outro status, limpar `completed_at`

---

### `PATCH /api/v1/tasks/{task}/status`

**Descrição:** Atalho para mudar apenas o status (útil para drag-and-drop no Kanban).**Body:**

```json
{
  "status": "in_progress"
}
```

**Response 200:** objeto atualizado.---

### `DELETE /api/v1/tasks/{task}`

**Descrição:** Soft delete de task.**Response 204:** No Content.---

## 4. API Endpoints - Contractors

### `GET /api/v1/contractors`

**Descrição:** Listar empreiteiros da empresa.**Response 200:**

```json
[
  {
    "id": 1,
    "name": "Construtora ABC",
    "contact": "(11) 98765-4321",
    "specialties": "Fundação, Estrutura",
    "created_at": "2024-01-01T10:00:00Z"
  }
]
```

---

### `POST /api/v1/contractors`

**Descrição:** Criar novo empreiteiro.**Body:**

```json
{
  "name": "Construtora XYZ",
  "contact": "contato@xyz.com",
  "specialties": "Alvenaria, Acabamentos"
}
```

**Response 201:** objeto criado.---

### `PUT /api/v1/contractors/{contractor}`

**Descrição:** Atualizar empreiteiro.**Response 200:** objeto atualizado.---

### `DELETE /api/v1/contractors/{contractor}`

**Descrição:** Soft delete.**Response 204:** No Content.---

## 5. API Endpoints - Documents

### `GET /api/v1/projects/{project}/documents`

**Descrição:** Listar documentos de um projeto.**Response 200:**

```json
[
  {
    "id": 1,
    "project_id": 5,
    "name": "Projeto Arquitetônico.pdf",
    "file_url": "https://storage.exemplo.com/documents/abc123.pdf",
    "mime_type": "application/pdf",
    "file_size": 2048576,
    "uploaded_by": 3,
    "uploaded_by_name": "João Silva",
    "created_at": "2024-01-10T10:00:00Z"
  }
]
```

---

### `POST /api/v1/projects/{project}/documents`

**Descrição:** Upload de documento.**Body:** `multipart/form-data`

- `file`: arquivo (max 10MB)
- `name`: nome do documento (opcional; usar filename se não informado)

**Response 201:** objeto criado.**Armazenamento:**

- Usar `Storage::disk('local')` ou S3 (conforme ambiente)
- Gerar `file_url` público (signed URL se necessário)

---

### `DELETE /api/v1/documents/{document}`

**Descrição:** Deletar documento (soft delete + remover arquivo do storage).**Response 204:** No Content.---

## 6. API Endpoints - Agregações e Estatísticas

### `GET /api/v1/projects/{project}/progress`

**Descrição:** Retornar progresso detalhado do projeto.**Response 200:**

```json
{
  "project_id": 5,
  "project_progress_percent": 68,
  "phases": [
    {
      "id": 1,
      "name": "Fundação",
      "status": "active",
      "progress_percent": 100,
      "tasks_count": 4
    },
    {
      "id": 2,
      "name": "Estrutura",
      "status": "active",
      "progress_percent": 60,
      "tasks_count": 5
    }
  ]
}
```

**Lógica:**

- `project_progress_percent`: média das fases com `status = 'active'`
- Retornar apenas fases ativas

---

### `GET /api/v1/dashboard/stats`

**Descrição:** Estatísticas gerais para o dashboard.**Query params:**

- `project_id` (opcional): filtrar por projeto específico

**Response 200:**

```json
{
  "avg_progress": 68,
  "overdue_tasks_count": 12,
  "upcoming_deliveries_count": 5,
  "total_budget": 2400000,
  "total_spent": 2040000,
  "budget_percent": 85
}
```

**Lógica:**

- `avg_progress`: média do `progress_percent` de todos os projetos ativos da company
- `overdue_tasks_count`: tasks com `planned_end_at < hoje` e `status != 'done'`
- `upcoming_deliveries_count`: tasks com `due_at` nos próximos 7 dias

---

## 7. Regras de Negócio e Validações

### Progresso (Progress Calculation)

**Task:**

```php
public function getProgressPercentAttribute(): int
{
    return match($this->status) {
        'backlog' => 0,
        'in_progress' => 50,
        'in_review' => 90,
        'done' => 100,
        'canceled' => 0, // não entra em médias
        default => 0,
    };
}
```

**Phase:**

```php
public function getProgressPercentAttribute(): int
{
    $tasks = $this->tasks()->whereNot('status', 'canceled')->get();
    if ($tasks->isEmpty()) return 0;
    
    $sum = $tasks->sum(fn($task) => $task->progress_percent);
    return (int) round($sum / $tasks->count());
}
```

**Project:**

```php
public function getProgressPercentAttribute(): int
{
    $activePhases = $this->phases()->where('status', 'active')->get();
    if ($activePhases->isEmpty()) return 0;
    
    $sum = $activePhases->sum(fn($phase) => $phase->progress_percent);
    return (int) round($sum / $activePhases->count());
}
```

---

### Autorização (Policies)

**PhasePolicy:**

- `viewAny`: user pertence à company do project
- `create`: user pode editar o project
- `update`, `delete`: idem

**TaskPolicy:**

- `viewAny`: user pertence à company do project
- `create`, `update`: user pode editar o project ou é assignee
- `delete`: user pode editar o project

**Middleware:**

- Sempre filtrar por `company_id` do usuário autenticado (usar global scope no modelo ou middleware)

---

### Eventos (Opcional para MVP)

- `TaskStatusChanged`: disparar ao mudar status (para notificações futuras)
- `PhaseCompleted`: quando todas as tasks de uma fase ficarem `done`
- `ProjectProgressUpdated`: após task/phase mudar (para cache)

---

## 8. Seeds e Factories

### Phase Templates

Criar seeder `PhaseTemplatesSeeder` com templates do CONTEXT.md:**Template Essencial (9 fases):**

1. Planejamento e Projeto
2. Preparação do Canteiro
3. Fundação
4. Estrutura
5. Vedações e Cobertura
6. Instalações
7. Esquadrias e Fachada
8. Acabamentos Internos
9. Comissionamento e Entrega

**Armazenar como JSON ou tabela `phase_templates`** (opcional):

```php
Schema::create('phase_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // "Essencial", "Avançado"
    $table->string('project_type')->nullable(); // "residencial", "comercial"
    $table->json('phases'); // [{ name, description }]
    $table->timestamps();
});
```

---

## 9. Testes (Recomendado)

### Feature Tests

- `PhaseControllerTest`:
- Criar, listar, atualizar, deletar fase
- Calcular `progress_percent` corretamente
- Filtrar por status
- `TaskControllerTest`:
- CRUD completo
- Atualizar status e verificar `started_at`, `completed_at`
- Calcular `progress_percent` por status
- Filtrar por fase, assignee, status
- `ProjectProgressTest`:
- Verificar cálculo cascata: task → phase → project
- Fases inativas não afetam progresso do projeto

---

## 10. Documentação OpenAPI

**Atualizar Swagger/OpenAPI** com:

- Schemas de Phase, Task, Contractor, Document
- Endpoints listados acima
- Exemplos de request/response
- Códigos de erro (401, 403, 404, 422, 500)

---

## Prioridades de Implementação

### Alta (MVP):

1. Migrations: phases, tasks
2. Endpoints CRUD: phases, tasks
3. Cálculo de `progress_percent` (accessors)
4. Endpoint `/projects/{project}/progress`

### Média (pós-MVP imediato):

5. Contractors CRUD
6. Documents upload/listagem
7. Dashboard stats endpoint

### Baixa (futuro):

8. Phase templates (seeder ou tabela)
9. Eventos e notificações
10. Testes automatizados

---

## Checklist de Validação

Após implementar, testar com frontend:

- [ ] Criar projeto → adicionar fases (manual) → progresso = 0%
- [ ] Criar tasks em fase → mover status → progresso da fase atualiza
- [ ] Progresso do projeto = média das fases ativas
- [ ] Listar tasks por fase no Kanban
- [ ] Atribuir contractor a task
- [ ] Upload documento → listar no frontend
- [ ] Dashboard stats retornar dados corretos

---

## Notas Técnicas

- **Performance:** Usar eager loading (`with()`) para evitar N+1 em lists