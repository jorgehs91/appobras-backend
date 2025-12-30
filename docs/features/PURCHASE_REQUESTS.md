# Feature: Purchase Requests (Requisições de Compra)

Este documento descreve a funcionalidade de **Purchase Requests (Requisições de Compra)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Modelo de Dados](#modelo-de-dados)
4. [Status e Workflow](#status-e-workflow)
5. [Casos de Uso](#casos-de-uso)
6. [API Endpoints](#api-endpoints)
7. [Regras de Negócio](#regras-de-negócio)
8. [Integração Frontend](#integração-frontend)
9. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Purchase Requests (PR)** são requisições de compra criadas por usuários para solicitar materiais ou serviços para um projeto. Elas passam por um workflow de aprovação (draft → submitted → approved/rejected) e, quando aprovadas, geram automaticamente um **Purchase Order (PO)**.

### Objetivos

- Controlar solicitações de compra dentro de projetos
- Implementar workflow de aprovação para requisições
- Rastrear histórico completo de requisições e aprovações
- Automatizar geração de Purchase Orders a partir de PRs aprovadas

### Características Principais

- ✅ Workflow de estados (draft, submitted, approved, rejected)
- ✅ Múltiplos itens por requisição
- ✅ Cálculo automático de total
- ✅ Validação de transições de estado
- ✅ Geração automática de PO quando aprovada
- ✅ Auditoria completa (created_by, updated_by)
- ✅ Soft deletes
- ✅ RBAC (roles Financeiro e Admin Obra)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── PurchaseRequest (PR)
          ├── Supplier (Fornecedor) [N:1]
          ├── PurchaseRequestItem (PRI) [1:N]
          │   └── CostItem? (opcional)
          └── PurchaseOrder (PO) [1:1, quando aprovada]
```

### Relacionamentos

#### PurchaseRequest → Project (Obrigatório, N:1)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitas PRs para um projeto)
- **Campo**: `project_id`
- **Descrição**: Cada PR pertence a um único projeto.

#### PurchaseRequest → Supplier (Obrigatório, N:1)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitas PRs para um fornecedor)
- **Campo**: `supplier_id`
- **Descrição**: Cada PR está associada a um fornecedor.

#### PurchaseRequest → PurchaseRequestItem (1:N)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N (uma PR tem muitos itens)
- **Descrição**: Itens da requisição de compra.

#### PurchaseRequest → PurchaseOrder (Opcional, 1:1)
- **Tipo**: `HasOne`
- **Cardinalidade**: 1:1 (uma PR aprovada gera um PO)
- **Descrição**: PO gerado automaticamente quando PR é aprovada.

#### PurchaseRequestItem → CostItem (Opcional)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campo**: `cost_item_id` (nullable)
- **Descrição**: Item de custo do orçamento relacionado.

### Fluxo Conceitual

```
1. Criação da PR (draft)
   └── Usuário cria requisição com itens
       └── Status: draft
       └── Pode ser editada ou deletada

2. Submissão da PR
   └── Usuário submete para aprovação
       └── Status: draft → submitted
       └── Não pode mais ser editada (exceto voltar para draft)

3. Aprovação/Rejeição
   └── Aprovador aprova ou rejeita
       ├── Aprovada: submitted → approved
       │   └── Evento: ApprovedPurchaseRequest
       │   └── Job: GeneratePurchaseOrder
       │   └── PO gerado automaticamente
       └── Rejeitada: submitted → rejected
           └── Pode voltar para draft e ser reeditada
```

---

## 📊 Modelo de Dados

### Tabela: `purchase_requests`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `project_id` | bigint | ID do projeto | Sim | Foreign key, cascade delete |
| `supplier_id` | bigint | ID do fornecedor | Sim | Foreign key |
| `status` | string | Status da PR | Sim | Enum: draft, submitted, approved, rejected |
| `total` | decimal(15,2) | Valor total da PR | Sim | Calculado automaticamente dos itens |
| `notes` | text | Observações | Não | Nullable |
| `created_by` | bigint | ID do usuário criador | Não | Foreign key para users, nullable |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para users, nullable |
| `created_at` | timestamp | Data de criação | Sim | Auto |
| `updated_at` | timestamp | Data de atualização | Sim | Auto |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable |

### Índices

- `project_id` - Para busca rápida por projeto
- `supplier_id` - Para busca por fornecedor
- `status` - Para filtros por status
- `created_by`, `updated_by` - Para auditoria

### Constraints

- `status` IN ('draft', 'submitted', 'approved', 'rejected') - Enum PurchaseRequestStatus
- `total >= 0` - Validação aplicada no model

### Tabela: `purchase_request_items`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `purchase_request_id` | bigint | ID da PR | Sim | Foreign key, cascade delete |
| `cost_item_id` | bigint | ID do item de custo | Não | Foreign key, nullable |
| `description` | string | Descrição do item | Sim | Máx. 500 caracteres |
| `quantity` | integer | Quantidade | Sim | Deve ser > 0 |
| `unit_price` | decimal(15,2) | Preço unitário | Sim | Deve ser >= 0 |
| `total` | decimal(15,2) | Total do item | Sim | Calculado: quantity * unit_price |
| `created_by` | bigint | ID do usuário criador | Não | Foreign key para users, nullable |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para users, nullable |
| `created_at` | timestamp | Data de criação | Sim | Auto |
| `updated_at` | timestamp | Data de atualização | Sim | Auto |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable |

### Índices

- `purchase_request_id` - Para busca rápida de itens por PR
- `cost_item_id` - Para vinculação com orçamento

### Constraints

- `quantity > 0` - Validação aplicada no model
- `unit_price >= 0` - Validação aplicada no model
- `total = quantity * unit_price` - Calculado automaticamente

---

## 🔄 Status e Workflow

### PurchaseRequestStatus Enum

```php
enum PurchaseRequestStatus: string
{
    case draft = 'draft';        // Rascunho, pode ser editada
    case submitted = 'submitted'; // Submetida para aprovação
    case approved = 'approved';   // Aprovada, gera PO automaticamente
    case rejected = 'rejected';   // Rejeitada, pode voltar para draft
}
```

### Workflow de Status

```
[draft] ──────> [submitted] ──────> [approved] ──────> [PO gerado]
   │                 │                    │
   │                 │                    └── Não pode mais mudar
   │                 │
   │                 ├──> [rejected] ───> [draft] (pode reenviar)
   │
   └── Pode ser deletada apenas em draft
```

### Transições Permitidas

| De | Para | Condição |
|----|------|----------|
| `draft` | `submitted` | PR deve ter pelo menos um item |
| `submitted` | `approved` | Aprovação por usuário autorizado |
| `submitted` | `rejected` | Rejeição por usuário autorizado |
| `submitted` | `draft` | Voltar para rascunho (permitido) |
| `rejected` | `draft` | Editar e reenviar |
| `approved` | - | Nenhuma transição permitida |

### Regras de Validação

1. **Criação em `draft`**: Status padrão ao criar PR
2. **Submissão**: PR deve ter pelo menos um item
3. **Aprovação**: Apenas PRs em `submitted` podem ser aprovadas
4. **Edição**: Apenas PRs em `draft` ou `rejected` podem ser editadas
5. **Exclusão**: Apenas PRs em `draft` podem ser deletadas

---

## 💼 Casos de Uso

### Caso 1: Criar Requisição de Compra

**Cenário**: Um engenheiro precisa solicitar materiais para o projeto.

```json
POST /api/v1/projects/1/purchase-requests
{
  "supplier_id": 5,
  "items": [
    {
      "cost_item_id": 10,
      "description": "Cimento Portland",
      "quantity": 50,
      "unit_price": 35.00
    },
    {
      "description": "Areia média",
      "quantity": 100,
      "unit_price": 25.00
    }
  ],
  "notes": "Material para fundação"
}
```

**Resultado**: 
- PR criada com status `draft`
- Total calculado automaticamente: R$ 4.250,00
- Pode ser editada ou deletada

---

### Caso 2: Submeter Requisição para Aprovação

**Cenário**: Engenheiro finaliza a requisição e submete para aprovação.

```json
POST /api/v1/purchase-requests/1/submit
```

**Resultado**: 
- Status muda para `submitted`
- Não pode mais ser editada (exceto voltar para draft)
- Aguarda aprovação

---

### Caso 3: Aprovar Requisição

**Cenário**: Gerente financeiro aprova a requisição.

```json
POST /api/v1/purchase-requests/1/approve
```

**Resultado**: 
- Status muda para `approved`
- Evento `ApprovedPurchaseRequest` é disparado
- Job `GeneratePurchaseOrder` é executado
- Purchase Order é gerado automaticamente
- PR não pode mais ser editada

---

### Caso 4: Rejeitar Requisição com Motivo

**Cenário**: Gerente rejeita requisição por orçamento insuficiente.

```json
POST /api/v1/purchase-requests/1/reject
{
  "reason": "Orçamento insuficiente para este mês"
}
```

**Resultado**: 
- Status muda para `rejected`
- Motivo é adicionado às notas
- PR pode voltar para `draft` e ser reeditada

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/projects/{project}/purchase-requests
/api/v1/purchase-requests/{id}
```

### Endpoints Disponíveis

#### 1. Listar Requisições de Compra

```http
GET /api/v1/projects/{project}/purchase-requests
```

**Query Parameters:**
- `status` (opcional): Filtrar por status (draft, submitted, approved, rejected)

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "project_id": 1,
      "supplier_id": 5,
      "status": "submitted",
      "total": "4250.00",
      "notes": "Material para fundação",
      "items": [...],
      "supplier": {...},
      "created_at": "2025-12-30T10:00:00.000000Z"
    }
  ]
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Projeto não encontrado

---

#### 2. Exibir Requisição de Compra

```http
GET /api/v1/purchase-requests/{id}
```

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "project_id": 1,
    "supplier_id": 5,
    "status": "approved",
    "total": "4250.00",
    "items": [
      {
        "id": 1,
        "description": "Cimento Portland",
        "quantity": 50,
        "unit_price": "35.00",
        "total": "1750.00"
      }
    ],
    "supplier": {...},
    "project": {...},
    "purchase_order": {...}
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `404` - Não encontrado
- `403` - Sem permissão

---

#### 3. Criar Requisição de Compra

```http
POST /api/v1/projects/{project}/purchase-requests
```

**Body:**
```json
{
  "supplier_id": 5,
  "items": [
    {
      "cost_item_id": 10,
      "description": "Cimento Portland",
      "quantity": 50,
      "unit_price": 35.00
    }
  ],
  "notes": "Observações opcionais"
}
```

**Validações:**
- `supplier_id` deve existir
- `items` deve ter pelo menos um item
- Cada item deve ter `description`, `quantity > 0`, `unit_price >= 0`

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "status": "draft",
    "total": "1750.00",
    ...
  }
}
```

**Códigos HTTP:**
- `201` - Criado
- `422` - Erro de validação
- `403` - Sem permissão

---

#### 4. Atualizar Requisição de Compra

```http
PUT /api/v1/purchase-requests/{id}
```

**Body:**
```json
{
  "supplier_id": 5,
  "notes": "Observações atualizadas",
  "items": [
    {
      "id": 1,
      "description": "Item atualizado",
      "quantity": 60,
      "unit_price": 40.00
    },
    {
      "description": "Novo item",
      "quantity": 10,
      "unit_price": 20.00
    }
  ]
}
```

**Validações:**
- PR deve estar em `draft` ou `rejected`
- Itens existentes (com `id`) são atualizados
- Itens novos (sem `id`) são criados
- Itens não incluídos são deletados

**Códigos HTTP:**
- `200` - Sucesso
- `422` - PR não pode ser editada
- `403` - Sem permissão

---

#### 5. Deletar Requisição de Compra

```http
DELETE /api/v1/purchase-requests/{id}
```

**Validações:**
- PR deve estar em `draft`

**Códigos HTTP:**
- `204` - Deletado
- `422` - PR não pode ser deletada
- `403` - Sem permissão

---

#### 6. Submeter Requisição

```http
POST /api/v1/purchase-requests/{id}/submit
```

**Validações:**
- PR deve estar em `draft`
- PR deve ter pelo menos um item

**Códigos HTTP:**
- `200` - Submetida
- `422` - PR não pode ser submetida
- `403` - Sem permissão

---

#### 7. Aprovar Requisição

```http
POST /api/v1/purchase-requests/{id}/approve
```

**Validações:**
- PR deve estar em `submitted`
- Usuário deve ter permissão (Financeiro ou Admin Obra)

**Resposta:**
```json
{
  "data": {...},
  "message": "Requisição de compra aprovada. Purchase Order gerado automaticamente."
}
```

**Códigos HTTP:**
- `200` - Aprovada
- `403` - Sem permissão ou PR não pode ser aprovada
- `404` - Não encontrada

---

#### 8. Rejeitar Requisição

```http
POST /api/v1/purchase-requests/{id}/reject
```

**Body (opcional):**
```json
{
  "reason": "Motivo da rejeição"
}
```

**Validações:**
- PR deve estar em `submitted`
- Usuário deve ter permissão (Financeiro ou Admin Obra)

**Códigos HTTP:**
- `200` - Rejeitada
- `403` - Sem permissão ou PR não pode ser rejeitada
- `404` - Não encontrada

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a Purchase Requests requer:**
- Role: `Financeiro` **OU** `AdminObra`
- Verificação no controller via `hasBudgetAccess()`

**Outras roles:** Acesso negado (403)

### Validações

#### Status e Transições

1. **Criação**: ✅ Sempre em `draft`
2. **Submissão**: ✅ Apenas de `draft`, requer pelo menos um item
3. **Aprovação**: ✅ Apenas de `submitted`
4. **Rejeição**: ✅ Apenas de `submitted`
5. **Edição**: ✅ Apenas em `draft` ou `rejected`
6. **Exclusão**: ✅ Apenas em `draft`

#### Itens

1. **Mínimo um item**: ✅ PR deve ter pelo menos um item para ser submetida
2. **Quantidade > 0**: ✅ Validação no model
3. **Preço unitário >= 0**: ✅ Validação no model
4. **Total calculado**: ✅ quantity * unit_price (automático)

#### Total da PR

1. **Cálculo automático**: ✅ Soma dos totais dos itens
2. **Atualização automática**: ✅ Recalculado quando itens são salvos/deletados

### Lifecycle

- **Criação**: Status `draft`, pode ser editada
- **Submissão**: Status `submitted`, não pode ser editada (exceto voltar para draft)
- **Aprovação**: Status `approved`, gera PO automaticamente, não pode mais ser editada
- **Rejeição**: Status `rejected`, pode voltar para `draft` e ser reeditada
- **Delete**: Soft delete, apenas em `draft`

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/purchase-request.ts

export enum PurchaseRequestStatus {
  DRAFT = 'draft',
  SUBMITTED = 'submitted',
  APPROVED = 'approved',
  REJECTED = 'rejected',
}

export interface PurchaseRequest {
  id: number;
  project_id: number;
  supplier_id: number;
  status: PurchaseRequestStatus;
  total: string;
  notes: string | null;
  items?: PurchaseRequestItem[];
  supplier?: Supplier;
  project?: Project;
  purchase_order?: PurchaseOrder;
  created_at: string;
  updated_at: string;
}

export interface PurchaseRequestItem {
  id: number;
  purchase_request_id: number;
  cost_item_id: number | null;
  description: string;
  quantity: number;
  unit_price: string;
  total: string;
  created_at: string;
  updated_at: string;
}

export interface CreatePurchaseRequestInput {
  supplier_id: number;
  items: Array<{
    cost_item_id?: number;
    description: string;
    quantity: number;
    unit_price: number;
  }>;
  notes?: string;
}

export interface UpdatePurchaseRequestInput {
  supplier_id?: number;
  notes?: string;
  items?: Array<{
    id?: number;
    cost_item_id?: number;
    description: string;
    quantity: number;
    unit_price: number;
  }>;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/purchaseRequestService.ts

import { PurchaseRequest, CreatePurchaseRequestInput, UpdatePurchaseRequestInput } from '@/types/purchase-request';
import { api } from './api';

export const purchaseRequestService = {
  async list(projectId: number, status?: string): Promise<PurchaseRequest[]> {
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    
    const response = await api.get(`/projects/${projectId}/purchase-requests?${params.toString()}`);
    return response.data.data;
  },

  async show(id: number): Promise<PurchaseRequest> {
    const response = await api.get(`/purchase-requests/${id}`);
    return response.data.data;
  },

  async create(projectId: number, data: CreatePurchaseRequestInput): Promise<PurchaseRequest> {
    const response = await api.post(`/projects/${projectId}/purchase-requests`, data);
    return response.data.data;
  },

  async update(id: number, data: UpdatePurchaseRequestInput): Promise<PurchaseRequest> {
    const response = await api.put(`/purchase-requests/${id}`, data);
    return response.data.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/purchase-requests/${id}`);
  },

  async submit(id: number): Promise<PurchaseRequest> {
    const response = await api.post(`/purchase-requests/${id}/submit`);
    return response.data.data;
  },

  async approve(id: number): Promise<PurchaseRequest> {
    const response = await api.post(`/purchase-requests/${id}/approve`);
    return response.data.data;
  },

  async reject(id: number, reason?: string): Promise<PurchaseRequest> {
    const response = await api.post(`/purchase-requests/${id}/reject`, { reason });
    return response.data.data;
  },
};
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Criar e Submeter PR

```typescript
import { purchaseRequestService } from '@/services/purchaseRequestService';

async function createAndSubmitPR(projectId: number, supplierId: number) {
  // Criar PR
  const pr = await purchaseRequestService.create(projectId, {
    supplier_id: supplierId,
    items: [
      {
        description: 'Cimento',
        quantity: 50,
        unit_price: 35.00,
      },
    ],
  });

  // Submeter
  const submitted = await purchaseRequestService.submit(pr.id);
  console.log('PR submetida:', submitted);
}
```

### Exemplo 2: Aprovar PR e Verificar PO

```typescript
import { purchaseRequestService } from '@/services/purchaseRequestService';

async function approvePR(prId: number) {
  const approved = await purchaseRequestService.approve(prId);
  
  if (approved.purchase_order) {
    console.log('PO gerado:', approved.purchase_order.po_number);
  }
}
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: Apenas roles `Financeiro` ou `AdminObra`
- **Project Scope**: PR deve pertencer ao projeto informado
- **Policy**: `PurchaseRequestPolicy` controla ações específicas

---

## 📚 Referências

- [Documentação de Purchase Orders](./PURCHASE_ORDERS.md)
- [Documentação de Suppliers](./SUPPLIERS.md)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/PurchaseRequest.php`
- Model: `app/Models/PurchaseRequestItem.php`
- Controller: `app/Http/Controllers/PurchaseRequestController.php`
- Policy: `app/Policies/PurchaseRequestPolicy.php`
- Tests: `tests/Feature/PurchaseRequestWorkflowTest.php`

---

**Última atualização:** 2025-12-30  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

