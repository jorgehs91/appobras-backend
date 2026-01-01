# Feature: Task Comments

Este documento descreve a funcionalidade de **Task Comments** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Modelo de Dados](#modelo-de-dados)
4. [API Endpoints](#api-endpoints)
5. [Regras de Negócio](#regras-de-negócio)
6. [Integração Frontend](#integração-frontend)
7. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Task Comments** permite que usuários adicionem comentários em tarefas, com suporte a reactions e markdown. Os comentários são organizados cronologicamente, facilitando a comunicação e colaboração entre membros da equipe durante a execução de tarefas.

### Objetivos

- Facilitar comunicação e colaboração entre membros da equipe
- Permitir discussões contextuais sobre tarefas específicas
- Organizar comentários de forma linear e cronológica
- Habilitar reactions básicas para feedback rápido

### Características Principais

- ✅ Comentários em tarefas com suporte a markdown
- ✅ Organização cronológica (sem threading)
- ✅ Reactions básicas (JSON)
- ✅ Soft deletes para preservar histórico
- ✅ Auditoria (created_by, updated_by)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── Task
          └── TaskComment
              └── User (author)
```

### Relacionamentos

#### TaskComment → Task (BelongsTo)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campo**: `task_id`
- **Descrição**: Cada comentário pertence a uma tarefa específica
- **Cascade**: Quando a tarefa é deletada, todos os comentários são deletados

#### TaskComment → User (BelongsTo)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campo**: `user_id`
- **Descrição**: Cada comentário é criado por um usuário
- **Cascade**: Quando o usuário é deletado, todos os comentários são deletados

#### Task → TaskComment[] (HasMany)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N
- **Campo**: `task_id` (foreign key)
- **Descrição**: Uma tarefa pode ter múltiplos comentários

### Fluxo Conceitual

```
1. Usuário visualiza uma tarefa
   └── Sistema exibe comentários relacionados à tarefa ordenados cronologicamente

2. Usuário cria um comentário
   └── Comentário é associado à tarefa e ao usuário
       └── Comentário é adicionado à lista ordenada por data de criação

3. Usuário adiciona reaction
   └── Sistema atualiza campo reactions (JSON)
```

---

## 📊 Modelo de Dados

### Tabela: `task_comments`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `task_id` | bigint | ID da tarefa | Sim | Foreign key para `tasks`, cascade delete |
| `user_id` | bigint | ID do usuário autor | Sim | Foreign key para `users`, cascade delete |
| `body` | text | Conteúdo do comentário | Sim | Suporta markdown |
| `reactions` | json | Reactions do comentário | Não | JSON com chave-valor (ex: {"like": 5, "love": 2}) |
| `created_by` | bigint | ID do usuário que criou | Não | Foreign key para `users`, set null on delete |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para `users`, set null on delete |
| `created_at` | timestamp | Data de criação | Sim | Auto-gerado |
| `updated_at` | timestamp | Data de atualização | Sim | Auto-gerado |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable, usado para soft deletes |

### Índices

- `task_id` - Para buscar comentários de uma tarefa rapidamente
- `created_at` - Para ordenar comentários por data de criação
- `created_by` - Para buscar comentários por autor
- `updated_by` - Para auditoria de atualizações

### Constraints

- `body` não pode ser vazio (validação no FormRequest quando endpoints forem criados)

---

## 🌐 API Endpoints

> **Nota**: Os endpoints de API ainda não foram implementados. Esta seção será atualizada quando os controllers forem criados.

### Base URL

```
/api/v1/tasks/{task_id}/comments
```

### Endpoints Planejados

#### 1. Listar Comentários de uma Tarefa

```http
GET /api/v1/tasks/{task_id}/comments
```

**Query Parameters:**
- `order_by` (opcional): Ordenar comentários por `created_at` (default: `asc`) ou `desc`

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "task_id": 10,
      "user_id": 5,
      "body": "Este é um comentário de exemplo",
      "reactions": {"like": 3},
      "user": {
        "id": 5,
        "name": "João Silva"
      },
      "created_at": "2026-01-01T10:00:00Z",
      "updated_at": "2026-01-01T10:00:00Z"
    }
  ]
}
```

#### 2. Criar Comentário

```http
POST /api/v1/tasks/{task_id}/comments
```

**Body:**
- `body` (obrigatório): Conteúdo do comentário

**Validações:**
- `body` não pode ser vazio

#### 3. Atualizar Comentário

```http
PUT /api/v1/tasks/{task_id}/comments/{comment_id}
```

**Body:**
- `body` (opcional): Novo conteúdo do comentário
- `reactions` (opcional): Novo objeto de reactions

**Validações:**
- Apenas o autor pode atualizar o comentário

#### 4. Deletar Comentário

```http
DELETE /api/v1/tasks/{task_id}/comments/{comment_id}
```

**Comportamento:**
- Soft delete (comentário não é removido fisicamente)

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a comentários requer:**
- Autenticação via Sanctum (token válido)
- Header `X-Company-Id` com company_id válido
- Usuário deve ter acesso à tarefa (ser membro do projeto)

**Permissões por ação:**
- **Criar comentário**: Qualquer membro do projeto pode comentar
- **Atualizar comentário**: Apenas o autor pode atualizar
- **Deletar comentário**: Autor ou administrador do projeto

### Validações

#### Validação de Conteúdo

1. **Body vazio**: ❌ Bloqueado - comentário deve ter conteúdo
2. **Markdown**: ✅ Permitido - body suporta markdown (renderização no frontend)

#### Validação de Reactions

1. **Formato JSON**: ✅ Reactions são armazenadas como JSON
2. **Estrutura**: Recomenda-se usar chave-valor (ex: `{"like": 5, "love": 2}`)

### Soft Deletes

- **Comportamento**: Comentários deletados não são removidos fisicamente
- **Visibilidade**: Comentários deletados não aparecem em queries normais
- **Recuperação**: Possível via `withTrashed()` (se necessário)

### Auditoria

- **created_by**: Preenchido automaticamente via `AuditTrait` quando comentário é criado
- **updated_by**: Preenchido automaticamente via `AuditTrait` quando comentário é atualizado
- **Timestamps**: `created_at` e `updated_at` são gerenciados automaticamente pelo Laravel

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/taskComment.ts

export interface TaskComment {
  id: number;
  task_id: number;
  user_id: number;
  body: string;
  reactions: Record<string, number> | null;
  user: {
    id: number;
    name: string;
    email?: string;
    avatar?: string;
  };
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

export interface CreateTaskCommentInput {
  body: string;
}

export interface UpdateTaskCommentInput {
  body?: string;
  reactions?: Record<string, number>;
}

export interface TaskCommentReaction {
  type: string;
  count: number;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/taskCommentService.ts

import { TaskComment, CreateTaskCommentInput, UpdateTaskCommentInput } from '@/types/taskComment';
import { api } from '@/utils/api';

export const taskCommentService = {
  async list(taskId: number, orderBy: 'asc' | 'desc' = 'asc'): Promise<TaskComment[]> {
    const response = await api.get(`/tasks/${taskId}/comments`, {
      params: { order_by: orderBy },
    });
    return response.data.data;
  },

  async create(taskId: number, data: CreateTaskCommentInput): Promise<TaskComment> {
    const response = await api.post(`/tasks/${taskId}/comments`, data);
    return response.data.data;
  },

  async update(taskId: number, commentId: number, data: UpdateTaskCommentInput): Promise<TaskComment> {
    const response = await api.put(`/tasks/${taskId}/comments/${commentId}`, data);
    return response.data.data;
  },

  async delete(taskId: number, commentId: number): Promise<void> {
    await api.delete(`/tasks/${taskId}/comments/${commentId}`);
  },

  async addReaction(taskId: number, commentId: number, reactionType: string): Promise<TaskComment> {
    const comment = await this.show(taskId, commentId);
    const reactions = comment.reactions || {};
    reactions[reactionType] = (reactions[reactionType] || 0) + 1;
    return this.update(taskId, commentId, { reactions });
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useTaskComments.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { taskCommentService } from '@/services/taskCommentService';
import { CreateTaskCommentInput, UpdateTaskCommentInput } from '@/types/taskComment';

export function useTaskComments(taskId: number) {
  return useQuery({
    queryKey: ['taskComments', taskId],
    queryFn: () => taskCommentService.list(taskId),
  });
}

export function useCreateTaskComment(taskId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateTaskCommentInput) => taskCommentService.create(taskId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['taskComments', taskId] });
    },
  });
}

export function useUpdateTaskComment(taskId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ commentId, data }: { commentId: number; data: UpdateTaskCommentInput }) =>
      taskCommentService.update(taskId, commentId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['taskComments', taskId] });
    },
  });
}

export function useDeleteTaskComment(taskId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (commentId: number) => taskCommentService.delete(taskId, commentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['taskComments', taskId] });
    },
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/TaskCommentList.tsx

import { useTaskComments, useCreateTaskComment } from '@/hooks/useTaskComments';
import { TaskComment } from '@/types/taskComment';

interface TaskCommentListProps {
  taskId: number;
}

export function TaskCommentList({ taskId }: TaskCommentListProps) {
  const { data: comments, isLoading } = useTaskComments(taskId);
  const createComment = useCreateTaskComment(taskId);

  if (isLoading) return <div>Carregando comentários...</div>;

  return (
    <div className="task-comments">
      <h3>Comentários ({comments?.length || 0})</h3>
      
      {comments?.map((comment) => (
        <TaskCommentItem key={comment.id} comment={comment} taskId={taskId} />
      ))}
    </div>
  );
}

function TaskCommentItem({ comment, taskId }: { comment: TaskComment; taskId: number }) {
  return (
    <div className="comment">
      <div className="comment-header">
        <span className="author">{comment.user.name}</span>
        <span className="date">{new Date(comment.created_at).toLocaleDateString()}</span>
      </div>
      <div className="comment-body">{comment.body}</div>
    </div>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Criar Comentário

```typescript
const { mutate: createComment } = useCreateTaskComment(taskId);

createComment({
  body: 'Este é um comentário sobre a tarefa.',
});
```

### Exemplo 2: Adicionar Reaction

```typescript
const { mutate: updateComment } = useUpdateTaskComment(taskId);

updateComment({
  commentId: comment.id,
  data: {
    reactions: {
      ...comment.reactions,
      like: (comment.reactions?.like || 0) + 1,
    },
  },
});
```

### Exemplo 3: Renderizar Markdown

```typescript
import ReactMarkdown from 'react-markdown';

function CommentBody({ body }: { body: string }) {
  return <ReactMarkdown>{body}</ReactMarkdown>;
}
```

### Exemplo 4: Ordenar Comentários Cronologicamente

```typescript
function sortCommentsByDate(comments: TaskComment[], order: 'asc' | 'desc' = 'asc'): TaskComment[] {
  return [...comments].sort((a, b) => {
    const dateA = new Date(a.created_at).getTime();
    const dateB = new Date(b.created_at).getTime();
    return order === 'asc' ? dateA - dateB : dateB - dateA;
  });
}
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Project Scope**: Comentário deve pertencer à tarefa do projeto informado
- **Permissão de Atualização**: Apenas o autor pode atualizar seu próprio comentário

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Body vazio**: Mostrar erro antes de enviar requisição
2. **Markdown preview**: Permitir preview do markdown antes de postar
3. **Permissions**: Desabilitar botões de edição/deleção se usuário não tiver permissão
4. **Ordenação**: Permitir alternar entre ordenação cronológica ascendente/descendente

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Mentions**: Permitir mencionar usuários em comentários (@username)
2. **Notificações**: Notificar usuários quando mencionados ou quando há resposta
3. **Edição com histórico**: Manter histórico de edições de comentários
4. **Anexos**: Permitir anexar arquivos/imagens aos comentários
5. **Busca**: Buscar comentários por conteúdo ou autor
6. **Filtros**: Filtrar comentários por data, autor, reactions
7. **Markdown avançado**: Suporte a tabelas, código com syntax highlighting

### Considerações para Implementação

- **Performance**: Para tarefas com muitos comentários, considerar paginação
- **Real-time**: Considerar WebSockets para atualizações em tempo real
- **Cache**: Cachear comentários frequentemente acessados
- **Indexação**: Índices em `task_id` e `created_at` já estão implementados

---

## 📚 Referências

- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation) (quando endpoints forem implementados)
- Model: `app/Models/TaskComment.php`
- Factory: `database/factories/TaskCommentFactory.php`
- Tests: `tests/Unit/TaskCommentTest.php`
- Migration: `database/migrations/2026_01_01_111842_create_task_comments_table.php`

---

## ❓ FAQ

### P: Como os comentários são organizados?

**R:** Os comentários são organizados cronologicamente por data de criação (`created_at`). Não há threading ou respostas diretas - todos os comentários são lineares e ordenados por tempo.

### P: O que acontece quando um comentário é deletado?

**R:** O comentário é marcado como deletado (soft delete) e não aparece mais em queries normais.

### P: Posso editar um comentário após postá-lo?

**R:** Sim, mas apenas o autor pode editar seu próprio comentário.

### P: Como funcionam as reactions?

**R:** Reactions são armazenadas como JSON no campo `reactions`. A estrutura é um objeto chave-valor onde a chave é o tipo de reaction (ex: "like", "love") e o valor é a contagem. Exemplo: `{"like": 5, "love": 2}`.

### P: Posso recuperar um comentário deletado?

**R:** Tecnicamente sim, usando `TaskComment::withTrashed()->find($id)`, mas isso não está exposto na API. Comentários deletados são considerados permanentemente removidos do ponto de vista do usuário.

### P: Os comentários suportam markdown?

**R:** Sim, o campo `body` suporta markdown. A renderização deve ser feita no frontend usando uma biblioteca como `react-markdown` ou similar.

---

**Última atualização:** 2026-01-01  
**Versão da API:** v1 (endpoints ainda não implementados)  
**Status:** ✅ Model, Migration, Factory e Testes Implementados

