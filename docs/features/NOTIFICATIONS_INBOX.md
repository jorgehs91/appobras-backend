# Feature: Inbox de Notificações

Este documento descreve a funcionalidade de **Inbox de Notificações** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Modelo de Dados](#modelo-de-dados)
4. [Casos de Uso](#casos-de-uso)
5. [API Endpoints](#api-endpoints)
6. [Regras de Negócio](#regras-de-negócio)
7. [Integração Frontend](#integração-frontend)
8. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Inbox de Notificações** permite que usuários visualizem e gerenciem suas notificações recebidas no sistema. O sistema fornece endpoints para listar notificações com paginação, filtrar por status de leitura e tipo, e marcar notificações como lidas.

### Objetivos

- Permitir que usuários visualizem todas as suas notificações de forma organizada
- Fornecer contador de notificações não lidas para badges e indicadores
- Permitir que usuários marquem notificações como lidas
- Suportar filtros e paginação para melhorar a experiência do usuário

### Características Principais

- ✅ Listagem paginada de notificações
- ✅ Contador de notificações não lidas
- ✅ Filtros por status de leitura (lidas/não lidas)
- ✅ Filtros por tipo de notificação
- ✅ Marcação de notificações como lidas
- ✅ Isolamento por usuário (usuário só vê suas próprias notificações)
- ✅ Ordenação por data de criação (mais recentes primeiro)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
User
  └── userNotifications() (HasMany)
      └── Notification
          └── notifiable() (MorphTo)
              └── Task, Project, etc.
```

### Relacionamentos

#### User → Notification (HasMany)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N
- **Campo**: `user_id` na tabela `notifications`
- **Descrição**: Um usuário pode ter múltiplas notificações

#### Notification → Notifiable (MorphTo)
- **Tipo**: `MorphTo` (polimórfico)
- **Cardinalidade**: N:1
- **Campos**: `notifiable_id`, `notifiable_type`
- **Descrição**: Uma notificação pode estar relacionada a diferentes entidades (Task, Project, etc.)

### Fluxo Conceitual

```
1. Usuário acessa o inbox
   └── GET /api/v1/notifications
       └── Sistema retorna notificações do usuário
           └── Inclui contador de não lidas no meta

2. Usuário marca notificação como lida
   └── PATCH /api/v1/notifications/{id}/read
       └── Sistema atualiza read_at
           └── Notificação fica marcada como lida

3. Usuário filtra notificações
   └── GET /api/v1/notifications?read=false&type=task.overdue
       └── Sistema retorna apenas não lidas do tipo especificado
```

---

## 📊 Modelo de Dados

### Tabela: `notifications`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `user_id` | bigint | ID do usuário destinatário | Sim | Foreign key para users |
| `notifiable_id` | bigint | ID da entidade relacionada | Sim | Parte da relação polimórfica |
| `notifiable_type` | string | Tipo da entidade relacionada | Sim | Parte da relação polimórfica |
| `type` | string | Tipo da notificação | Sim | Ex: 'task.overdue', 'task.near_due' |
| `data` | json | Dados adicionais da notificação | Sim | Estrutura varia por tipo |
| `read_at` | timestamp | Data/hora de leitura | Não | Null quando não lida |
| `channels` | json | Canais de envio | Sim | Array: ['database', 'expo', 'email'] |
| `created_at` | timestamp | Data de criação | Sim | Auto-preenchido |
| `updated_at` | timestamp | Data de atualização | Sim | Auto-atualizado |

### Índices

- `user_id` - Para consultas rápidas por usuário
- `[user_id, read_at]` - Para consultas de notificações não lidas
- `[user_id, type]` - Para filtros por tipo
- `created_at` - Para ordenação

### Constraints

- `user_id` deve existir na tabela `users`
- `read_at` é nullable (null = não lida)
- `data` e `channels` são arrays JSON

---

## 💼 Casos de Uso

### Caso 1: Visualizar Inbox de Notificações

**Cenário**: Usuário acessa a tela de notificações para ver todas as suas notificações.

```http
GET /api/v1/notifications
Authorization: Bearer {token}
```

**Resultado**: Retorna lista paginada de notificações com contador de não lidas.

```json
{
  "data": [
    {
      "id": 1,
      "type": "task.overdue",
      "data": {
        "task_id": 123,
        "task_title": "Instalação elétrica"
      },
      "is_read": false,
      "read_at": null,
      "created_at": "2025-12-30T10:00:00Z"
    }
  ],
  "meta": {
    "unread_count": 5,
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

---

### Caso 2: Filtrar Notificações Não Lidas

**Cenário**: Usuário quer ver apenas notificações que ainda não foram lidas.

```http
GET /api/v1/notifications?read=false
Authorization: Bearer {token}
```

**Resultado**: Retorna apenas notificações não lidas (read_at = null).

---

### Caso 3: Filtrar por Tipo de Notificação

**Cenário**: Usuário quer ver apenas notificações de tarefas atrasadas.

```http
GET /api/v1/notifications?type=task.overdue
Authorization: Bearer {token}
```

**Resultado**: Retorna apenas notificações do tipo 'task.overdue'.

---

### Caso 4: Marcar Notificação como Lida

**Cenário**: Usuário lê uma notificação e quer marcá-la como lida.

```http
PATCH /api/v1/notifications/1/read
Authorization: Bearer {token}
```

**Resultado**: Notificação é marcada como lida (read_at é atualizado), retorna 204 No Content.

---

### Caso 5: Paginação de Notificações

**Cenário**: Usuário tem muitas notificações e quer navegar entre páginas.

```http
GET /api/v1/notifications?per_page=10&page=2
Authorization: Bearer {token}
```

**Resultado**: Retorna segunda página com 10 itens por página.

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/notifications
```

### Endpoints Disponíveis

#### 1. Listar Notificações

```http
GET /api/v1/notifications
```

**Query Parameters:**
- `read` (opcional, boolean): Filtrar por status de leitura (true = lidas, false = não lidas)
- `type` (opcional, string): Filtrar por tipo de notificação
- `per_page` (opcional, integer, default: 15, max: 100): Número de itens por página
- `page` (opcional, integer, default: 1): Número da página

**Validações:**
- Usuário deve estar autenticado
- `per_page` máximo é 100
- `read` aceita valores booleanos (true/false, 1/0, "true"/"false")

**Resposta (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "type": "task.overdue",
      "data": {
        "task_id": 123,
        "task_title": "Instalação elétrica"
      },
      "is_read": false,
      "read_at": null,
      "channels": ["database", "expo"],
      "notifiable_type": "App\\Models\\Task",
      "notifiable_id": 123,
      "created_at": "2025-12-30T10:00:00Z",
      "updated_at": "2025-12-30T10:00:00Z"
    }
  ],
  "meta": {
    "unread_count": 5,
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `401` - Não autenticado

---

#### 2. Marcar Notificação como Lida

```http
PATCH /api/v1/notifications/{id}/read
```

**Path Parameters:**
- `id` (obrigatório, integer): ID da notificação

**Validações:**
- Usuário deve estar autenticado
- Notificação deve existir
- Notificação deve pertencer ao usuário autenticado

**Resposta (204 No Content):**
- Sem corpo de resposta

**Códigos HTTP:**
- `204` - Notificação marcada como lida com sucesso
- `401` - Não autenticado
- `404` - Notificação não encontrada ou não pertence ao usuário

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso aos endpoints requer:**
- Autenticação via Sanctum (`auth:sanctum`)
- Usuário pode visualizar e gerenciar apenas suas próprias notificações

**Isolamento de Dados:**
- Todas as consultas são automaticamente filtradas por `user_id` do usuário autenticado
- Tentativa de acessar notificação de outro usuário retorna 404

### Validações

#### Validação de Propriedade

1. **Notificação pertence ao usuário**: ✅ Permite acesso
2. **Notificação pertence a outro usuário**: ❌ Retorna 404
3. **Notificação não existe**: ❌ Retorna 404

#### Validação de Paginação

1. **per_page <= 100**: ✅ Aceito
2. **per_page > 100**: ✅ Limitado automaticamente a 100
3. **page < 1**: ✅ Retorna primeira página

#### Validação de Filtros

1. **read=true**: ✅ Retorna apenas notificações lidas
2. **read=false**: ✅ Retorna apenas notificações não lidas
3. **read não informado**: ✅ Retorna todas as notificações
4. **type informado**: ✅ Filtra por tipo exato

### Comportamento de Marcação

#### Marcar como Lida

- **Notificação não lida**: ✅ `read_at` é atualizado para timestamp atual
- **Notificação já lida**: ✅ Não gera erro, mas `read_at` não é alterado
- **Método `markAsRead()`**: Retorna `false` se já estava lida, `true` se foi marcada

### Ordenação

- Notificações são sempre ordenadas por `created_at DESC` (mais recentes primeiro)
- Ordenação é aplicada antes da paginação

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/notification.ts

export interface Notification {
  id: number;
  type: string;
  data: Record<string, any>;
  is_read: boolean;
  read_at: string | null;
  channels: string[];
  notifiable_type: string;
  notifiable_id: number;
  notifiable?: any; // Quando carregado via eager loading
  created_at: string;
  updated_at: string;
}

export interface NotificationListResponse {
  data: Notification[];
  meta: {
    unread_count: number;
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/notificationService.ts

import { Notification, NotificationListResponse } from '@/types/notification';
import { api } from './api';

export const notificationService = {
  async list(params?: {
    read?: boolean;
    type?: string;
    per_page?: number;
    page?: number;
  }): Promise<NotificationListResponse> {
    const response = await api.get<NotificationListResponse>('/notifications', {
      params,
    });
    return response.data;
  },

  async markAsRead(id: number): Promise<void> {
    await api.patch(`/notifications/${id}/read`);
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useNotifications.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { notificationService } from '@/services/notificationService';

export function useNotifications(params?: {
  read?: boolean;
  type?: string;
  per_page?: number;
  page?: number;
}) {
  return useQuery({
    queryKey: ['notifications', params],
    queryFn: () => notificationService.list(params),
  });
}

export function useMarkNotificationAsRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => notificationService.markAsRead(id),
    onSuccess: () => {
      // Invalidar queries para atualizar contadores
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });
}

export function useUnreadCount() {
  const { data } = useNotifications({ per_page: 1 });
  return data?.meta.unread_count ?? 0;
}
```

### Exemplo de Componente (React)

```typescript
// components/NotificationInbox.tsx

import { useNotifications, useMarkNotificationAsRead } from '@/hooks/useNotifications';
import { useState } from 'react';

export function NotificationInbox() {
  const [page, setPage] = useState(1);
  const [filterRead, setFilterRead] = useState<boolean | undefined>(undefined);
  
  const { data, isLoading } = useNotifications({
    read: filterRead,
    per_page: 15,
    page,
  });
  
  const markAsRead = useMarkNotificationAsRead();

  if (isLoading) return <div>Carregando...</div>;

  return (
    <div>
      <div>
        <button onClick={() => setFilterRead(undefined)}>Todas</button>
        <button onClick={() => setFilterRead(false)}>Não lidas</button>
        <button onClick={() => setFilterRead(true)}>Lidas</button>
      </div>

      <div>
        <p>Não lidas: {data?.meta.unread_count ?? 0}</p>
      </div>

      <ul>
        {data?.data.map((notification) => (
          <li key={notification.id}>
            <div>
              <h3>{notification.data.title || notification.type}</h3>
              <p>{notification.data.message}</p>
              {!notification.is_read && (
                <button onClick={() => markAsRead.mutate(notification.id)}>
                  Marcar como lida
                </button>
              )}
            </div>
          </li>
        ))}
      </ul>

      {/* Paginação */}
      {data && data.meta.last_page > 1 && (
        <div>
          <button
            disabled={page === 1}
            onClick={() => setPage(page - 1)}
          >
            Anterior
          </button>
          <span>
            Página {data.meta.current_page} de {data.meta.last_page}
          </span>
          <button
            disabled={page === data.meta.last_page}
            onClick={() => setPage(page + 1)}
          >
            Próxima
          </button>
        </div>
      )}
    </div>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Obter Contador de Não Lidas

```typescript
const { data } = useNotifications({ per_page: 1 });
const unreadCount = data?.meta.unread_count ?? 0;

// Usar em badge
<Badge count={unreadCount}>Notificações</Badge>
```

### Exemplo 2: Marcar Todas como Lidas

```typescript
const { data } = useNotifications({ read: false });
const markAsRead = useMarkNotificationAsRead();

const markAllAsRead = async () => {
  if (data?.data) {
    await Promise.all(
      data.data.map((notification) =>
        markAsRead.mutateAsync(notification.id)
      )
    );
  }
};
```

### Exemplo 3: Polling para Atualizações em Tempo Real

```typescript
const { data, refetch } = useNotifications({
  queryKey: ['notifications'],
  refetchInterval: 30000, // Atualizar a cada 30 segundos
});
```

### Exemplo 4: Filtrar por Tipo Específico

```typescript
// Apenas notificações de tarefas atrasadas
const { data } = useNotifications({
  type: 'task.overdue',
  read: false,
});
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Isolamento**: Todas as consultas são automaticamente filtradas por `user_id`
- **Validação de Propriedade**: Notificações de outros usuários retornam 404

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Verificar autenticação**: Não fazer requisições se usuário não estiver autenticado
2. **Tratar 404**: Mostrar mensagem apropriada se notificação não for encontrada
3. **Tratar 401**: Redirecionar para login se token expirar

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Marcar todas como lidas**: Endpoint para marcar todas as notificações de uma vez
2. **Deletar notificações**: Permitir que usuários deletem notificações antigas
3. **Notificações agrupadas**: Agrupar notificações similares (ex: múltiplas tarefas atrasadas)
4. **Filtros avançados**: Filtros por data, entidade relacionada, etc.
5. **WebSockets**: Atualizações em tempo real via WebSockets

### Considerações para Implementação

- **Marcar todas como lidas**: Implementar endpoint `PATCH /api/v1/notifications/read-all`
- **Deletar notificações**: Considerar soft delete ou arquivamento
- **WebSockets**: Integrar com Laravel Echo ou similar

---

## 📚 Referências

- [Documentação Expo Push Notifications](./EXPO_PUSH_NOTIFICATIONS.md)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/Notification.php`
- Controller: `app/Http/Controllers/NotificationController.php`
- Resource: `app/Http/Resources/NotificationResource.php`
- Tests: `tests/Feature/NotificationControllerTest.php`

---

## ❓ FAQ

### P: Como atualizar o contador de não lidas em tempo real?

**R:** Use polling com `refetchInterval` no React Query ou implemente WebSockets para atualizações em tempo real. O contador é sempre retornado no `meta.unread_count` da resposta.

### P: Posso marcar uma notificação como não lida novamente?

**R:** Atualmente não há endpoint para isso. A notificação só pode ser marcada como lida. Se necessário, pode ser implementado um endpoint `PATCH /api/v1/notifications/{id}/unread`.

### P: Como filtrar notificações por data?

**R:** Atualmente não há filtro por data. Pode ser implementado adicionando parâmetros `date_from` e `date_to` no endpoint de listagem.

### P: As notificações são deletadas automaticamente?

**R:** Não. As notificações permanecem no banco indefinidamente. Considere implementar uma limpeza periódica de notificações antigas (ex: > 90 dias).

### P: Posso ver notificações de outros usuários?

**R:** Não. O sistema garante isolamento total - usuários só podem ver e gerenciar suas próprias notificações. Tentativas de acessar notificações de outros usuários retornam 404.

---

**Última atualização:** 2025-12-30  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

