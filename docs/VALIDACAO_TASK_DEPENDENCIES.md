# Guia de Validação - Task Dependencies

Este documento descreve como validar a funcionalidade de dependências entre tarefas (Task Dependencies).

## 📋 Índice

1. [Testes Automatizados](#testes-automatizados)
2. [Validação Manual via API](#validação-manual-via-api)
3. [Cenários de Teste](#cenários-de-teste)
4. [Exemplos Práticos](#exemplos-práticos)

---

## 🧪 Testes Automatizados

### Executar todos os testes da feature

```bash
cd appobras-backend

# Executar todos os testes
php artisan test

# Executar apenas os testes de TaskDependency
php artisan test --filter TaskDependency

# Executar apenas testes unitários do Service
php artisan test tests/Unit/TaskDependencyServiceTest.php

# Executar apenas testes de integração
php artisan test tests/Feature/TaskDependencyTest.php
```

### Ou usando composer

```bash
composer test
```

---

## 🌐 Validação Manual via API

### Pré-requisitos

1. **Servidor rodando**: Execute `php artisan serve` ou use o script `composer dev`
2. **Autenticação**: Você precisa de um token de autenticação Sanctum
3. **Dados de teste**: Crie uma empresa, projeto, fase e algumas tarefas

### Passo 1: Obter Token de Autenticação

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "seu@email.com",
    "password": "sua_senha"
  }'
```

Guarde o `token` retornado na resposta.

### Passo 2: Criar Tarefas para Teste

```bash
# Criar tarefa 1
curl -X POST http://localhost:8000/api/v1/projects/1/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "title": "Tarefa Predecessora",
    "phase_id": 1,
    "planned_start_at": "2025-01-01",
    "planned_end_at": "2025-01-05"
  }'

# Criar tarefa 2
curl -X POST http://localhost:8000/api/v1/projects/1/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "title": "Tarefa Dependente",
    "phase_id": 1,
    "planned_start_at": "2025-01-06",
    "planned_end_at": "2025-01-10"
  }'
```

Guarde os IDs das tarefas retornadas (ex: `task1_id = 1`, `task2_id = 2`).

---

## ✅ Cenários de Teste

### 1. Criar Dependência Válida

**Endpoint**: `POST /api/v1/projects/{project}/task-dependencies`

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "task_id": 2,
    "depends_on_task_id": 1
  }'
```

**Resultado esperado**: Status `201 Created` com dados da dependência criada.

---

### 2. Tentar Criar Self-Loop (deve falhar)

**Endpoint**: `POST /api/v1/projects/{project}/task-dependencies`

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "task_id": 1,
    "depends_on_task_id": 1
  }'
```

**Resultado esperado**: Status `422 Unprocessable Entity` com erro de validação.

---

### 3. Tentar Criar Ciclo (deve falhar)

Primeiro, crie uma dependência:
```bash
# Tarefa 1 depende de Tarefa 2
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "task_id": 1,
    "depends_on_task_id": 2
  }'
```

Depois, tente criar o ciclo:
```bash
# Tentar fazer Tarefa 2 depender de Tarefa 1 (criaria ciclo)
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "task_id": 2,
    "depends_on_task_id": 1
  }'
```

**Resultado esperado**: Status `422` com mensagem de erro indicando o ciclo detectado.

---

### 4. Criar Dependências em Bulk

**Endpoint**: `POST /api/v1/projects/{project}/task-dependencies/bulk`

```bash
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies/bulk \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "dependencies": [
      {
        "task_id": 2,
        "depends_on_task_id": 1
      },
      {
        "task_id": 3,
        "depends_on_task_id": 2
      }
    ]
  }'
```

**Resultado esperado**: Status `201 Created` com array de dependências criadas.

---

### 5. Validar Consistência de Datas

Crie uma tarefa predecessora:
```bash
curl -X POST http://localhost:8000/api/v1/projects/1/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "title": "Predecessora",
    "phase_id": 1,
    "planned_start_at": "2025-01-01",
    "planned_end_at": "2025-01-05"
  }'
```

Crie uma tarefa dependente com data de início ANTES do término da predecessora:
```bash
curl -X POST http://localhost:8000/api/v1/projects/1/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "title": "Dependente",
    "phase_id": 1,
    "planned_start_at": "2025-01-03",
    "planned_end_at": "2025-01-10"
  }'
```

Crie a dependência:
```bash
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "task_id": 2,
    "depends_on_task_id": 1
  }'
```

Tente atualizar a tarefa dependente para uma data inválida:
```bash
curl -X PUT http://localhost:8000/api/v1/tasks/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "planned_start_at": "2025-01-02"
  }'
```

**Resultado esperado**: Status `422` com erro de validação indicando conflito de datas.

---

### 6. Atualizar Dependência

**Endpoint**: `PUT /api/v1/task-dependencies/{taskDependency}`

```bash
curl -X PUT http://localhost:8000/api/v1/task-dependencies/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "depends_on_task_id": 3
  }'
```

**Resultado esperado**: Status `200 OK` com dados atualizados.

---

### 7. Deletar Dependência (Soft Delete)

**Endpoint**: `DELETE /api/v1/task-dependencies/{taskDependency}`

```bash
curl -X DELETE http://localhost:8000/api/v1/task-dependencies/1 \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1"
```

**Resultado esperado**: Status `204 No Content`.

Para verificar que foi soft-deleted, consulte o banco de dados:
```sql
SELECT * FROM task_dependencies WHERE id = 1;
-- deleted_at não deve ser NULL
```

---

### 8. Ciclo Multi-Node (3+ tarefas)

Crie 3 tarefas:
```bash
# Tarefa 1
curl -X POST http://localhost:8000/api/v1/projects/1/tasks ... -d '{"title": "Tarefa A", ...}'
# Tarefa 2  
curl -X POST http://localhost:8000/api/v1/projects/1/tasks ... -d '{"title": "Tarefa B", ...}'
# Tarefa 3
curl -X POST http://localhost:8000/api/v1/projects/1/tasks ... -d '{"title": "Tarefa C", ...}'
```

Crie dependências: A -> B -> C
```bash
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies ... -d '{"task_id": 1, "depends_on_task_id": 2}'
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies ... -d '{"task_id": 2, "depends_on_task_id": 3}'
```

Tente criar ciclo: C -> A
```bash
curl -X POST http://localhost:8000/api/v1/projects/1/task-dependencies \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Company-Id: 1" \
  -d '{
    "task_id": 3,
    "depends_on_task_id": 1
  }'
```

**Resultado esperado**: Status `422` com caminho do ciclo: `3 -> 1 -> 2 -> 3`

---

## 📊 Checklist de Validação

- [ ] ✅ Criar dependência válida funciona
- [ ] ✅ Self-loop é rejeitado (422)
- [ ] ✅ Ciclo simples (2 nodes) é detectado e rejeitado
- [ ] ✅ Ciclo multi-node (3+ nodes) é detectado e rejeitado
- [ ] ✅ Cadeias acíclicas são permitidas
- [ ] ✅ Criação bulk funciona
- [ ] ✅ Criação bulk com ciclo reverte toda a transação
- [ ] ✅ Atualização de dependência funciona
- [ ] ✅ Soft delete funciona (deleted_at preenchido)
- [ ] ✅ Soft-deleted dependencies são ignoradas em ciclo detection
- [ ] ✅ Validação de datas previne conflitos
- [ ] ✅ Soft-deleted dependencies são ignoradas na validação de datas
- [ ] ✅ Cross-project dependencies são rejeitadas

---

## 🔍 Validação no Banco de Dados

Para verificar diretamente no banco:

```sql
-- Ver todas as dependências
SELECT * FROM task_dependencies;

-- Ver apenas dependências ativas (não soft-deleted)
SELECT * FROM task_dependencies WHERE deleted_at IS NULL;

-- Ver relacionamentos com tasks
SELECT 
  td.id,
  td.task_id,
  t1.title as task_title,
  td.depends_on_task_id,
  t2.title as depends_on_title,
  td.created_at
FROM task_dependencies td
JOIN tasks t1 ON td.task_id = t1.id
JOIN tasks t2 ON td.depends_on_task_id = t2.id
WHERE td.deleted_at IS NULL;
```

---

## 🐛 Debugging

### Verificar logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Procurar por validações de ciclo
grep -i "cycle" storage/logs/laravel.log
```

### Testar Service diretamente via Tinker

```bash
php artisan tinker

# No tinker:
$service = new App\Services\TaskDependencyService();
$service->canAddDependency(1, 2); // true/false
$service->detectCycleOnAdd(1, 2); // null ou array com ciclo
```

---

## 📝 Notas Importantes

1. **Autenticação obrigatória**: Todos os endpoints requerem autenticação Sanctum
2. **Company ID obrigatório**: Header `X-Company-Id` é necessário
3. **Project membership**: Usuário deve ser membro do projeto
4. **Soft deletes**: Dependências deletadas não aparecem em queries padrão
5. **Validação de datas**: Acontece automaticamente via Observer quando tasks são salvas

---

## 🚀 Próximos Passos

Após validar, você pode:

1. Integrar com o frontend
2. Adicionar interface visual para gerenciar dependências
3. Visualizar grafos de dependências
4. Gerar relatórios de caminho crítico

