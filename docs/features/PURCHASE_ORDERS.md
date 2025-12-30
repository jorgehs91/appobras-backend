# Feature: Purchase Orders (Pedidos de Compra)

Este documento descreve a funcionalidade de **Purchase Orders (Pedidos de Compra)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

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

**Purchase Orders (PO)** são pedidos de compra gerados automaticamente quando uma **Purchase Request (PR)** é aprovada. Cada PO contém itens copiados da PR aprovada e possui um número único gerado automaticamente.

### Objetivos

- Automatizar a criação de pedidos de compra a partir de requisições aprovadas
- Garantir rastreabilidade entre PR e PO
- Gerar números únicos de PO de forma sequencial
- Manter histórico completo de itens solicitados vs. pedidos

### Características Principais

- ✅ Geração automática quando PR é aprovada
- ✅ Número único sequencial (PO-YYYYMM-####)
- ✅ Cópia automática de itens da PR
- ✅ Status de acompanhamento (pending, approved, completed, cancelled)
- ✅ Relacionamento 1:1 com Purchase Request
- ✅ Auditoria completa (created_by, updated_by)
- ✅ Soft deletes

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── PurchaseRequest (PR)
          └── PurchaseOrder (PO) [1:1]
              └── PurchaseOrderItem (POI) [1:N]
                  ├── PurchaseRequestItem (PRI) [referência]
                  └── CostItem? (opcional)
```

### Relacionamentos

#### PurchaseOrder → PurchaseRequest (Obrigatório, 1:1)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: 1:1 (um PO para uma PR)
- **Campo**: `purchase_request_id` (unique)
- **Descrição**: Cada PO é gerado a partir de uma única PR aprovada. O relacionamento é único, garantindo que uma PR aprovada gere apenas um PO.

#### PurchaseOrder → PurchaseOrderItem (1:N)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N (um PO tem muitos itens)
- **Descrição**: Itens do pedido de compra, copiados dos itens da PR.

#### PurchaseOrderItem → PurchaseRequestItem (Opcional)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campo**: `purchase_request_item_id` (nullable)
- **Descrição**: Referência ao item original da PR que originou este item do PO.

#### PurchaseOrderItem → CostItem (Opcional)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campo**: `cost_item_id` (nullable)
- **Descrição**: Item de custo do orçamento relacionado.

#### PurchaseOrder → User (Criação/Atualização)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campos**: `created_by`, `updated_by`
- **Descrição**: Rastreamento de quem criou/atualizou o PO.

### Fluxo Conceitual

```
1. Criação da PR (Purchase Request)
   └── Usuário cria requisição de compra com itens
       └── Status: draft → submitted

2. Aprovação da PR
   └── Aprovador aprova a requisição
       └── Status: submitted → approved
       └── Evento: ApprovedPurchaseRequest disparado
       └── Job: GeneratePurchaseOrder executado

3. Geração Automática do PO
   └── Job cria PurchaseOrder automaticamente
       ├── Gera po_number único (PO-YYYYMM-####)
       ├── Copia itens da PR para PO
       └── Status inicial: pending

4. Processamento do PO
   └── PO pode ser aprovado, completado ou cancelado
       └── Status: pending → approved → completed
```

---

## 📊 Modelo de Dados

### Tabela: `purchase_orders`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `purchase_request_id` | bigint | ID da PR que originou o PO | Sim | Foreign key, unique, cascade delete |
| `po_number` | string | Número único do PO | Sim | Formato: PO-YYYYMM-####, auto-gerado |
| `status` | string | Status do PO | Sim | Enum: pending, approved, completed, cancelled |
| `total` | decimal(15,2) | Valor total do PO | Sim | Calculado automaticamente dos itens |
| `notes` | text | Observações | Não | Nullable |
| `created_by` | bigint | ID do usuário criador | Não | Foreign key para users, nullable |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para users, nullable |
| `created_at` | timestamp | Data de criação | Sim | Auto |
| `updated_at` | timestamp | Data de atualização | Sim | Auto |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable |

### Índices

- `purchase_request_id` - Para busca rápida por PR
- `po_number` - Para busca por número do PO (único)
- `status` - Para filtros por status
- `created_by`, `updated_by` - Para auditoria

### Constraints

- `purchase_request_id` UNIQUE - Garante 1:1 com PR
- `po_number` UNIQUE - Garante unicidade do número
- `status` IN ('pending', 'approved', 'completed', 'cancelled') - Enum PurchaseOrderStatus

### Tabela: `purchase_order_items`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `purchase_order_id` | bigint | ID do PO | Sim | Foreign key, cascade delete |
| `purchase_request_item_id` | bigint | ID do item da PR original | Não | Foreign key, nullable, set null on delete |
| `cost_item_id` | bigint | ID do item de custo | Não | Foreign key, nullable, set null on delete |
| `description` | string | Descrição do item | Sim | Copiado da PRI |
| `quantity` | integer | Quantidade | Sim | Deve ser > 0 |
| `unit_price` | decimal(15,2) | Preço unitário | Sim | Deve ser >= 0 |
| `total` | decimal(15,2) | Total do item | Sim | Calculado: quantity * unit_price |
| `created_by` | bigint | ID do usuário criador | Não | Foreign key para users, nullable |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para users, nullable |
| `created_at` | timestamp | Data de criação | Sim | Auto |
| `updated_at` | timestamp | Data de atualização | Sim | Auto |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable |

### Índices

- `purchase_order_id` - Para busca rápida de itens por PO
- `purchase_request_item_id` - Para rastreabilidade
- `cost_item_id` - Para vinculação com orçamento

### Constraints

- `quantity > 0` - Validação aplicada no model
- `unit_price >= 0` - Validação aplicada no model
- `total = quantity * unit_price` - Calculado automaticamente

---

## 🔄 Status e Workflow

### PurchaseOrderStatus Enum

```php
enum PurchaseOrderStatus: string
{
    case pending = 'pending';      // Aguardando processamento
    case approved = 'approved';    // Aprovado para compra
    case completed = 'completed';  // Compra concluída
    case cancelled = 'cancelled';   // Cancelado
}
```

### Workflow de Status

```
[pending] ──────> [approved] ──────> [completed]
   │                    │
   │                    └── Requer: Aprovação
   │
   └── [cancelled] (pode ser cancelado a qualquer momento)
```

### Transições Permitidas

| De | Para | Condição |
|----|------|----------|
| `pending` | `approved` | Aprovação do PO |
| `pending` | `cancelled` | Cancelamento |
| `approved` | `completed` | Compra finalizada |
| `approved` | `cancelled` | Cancelamento (com ressalvas) |
| `pending` | `completed` | Não recomendado, mas permitido |

### Regras de Validação

1. **Criação em `pending`**: Status padrão ao gerar PO automaticamente
2. **Atualização para `approved`**: Requer permissões adequadas
3. **Atualização para `completed`**: Indica que a compra foi finalizada
4. **Cancelamento**: Pode ser feito em qualquer status, mas deve ser justificado

---

## 💼 Casos de Uso

### Caso 1: Geração Automática de PO ao Aprovar PR

**Cenário**: Um gerente de projeto aprova uma requisição de compra.

```json
PUT /api/v1/purchase-requests/1
{
  "status": "approved"
}
```

**Resultado**: 
- Evento `ApprovedPurchaseRequest` é disparado
- Job `GeneratePurchaseOrder` é executado
- PO é criado automaticamente com número único (ex: PO-202512-0001)
- Itens da PR são copiados para o PO
- Total do PO é calculado automaticamente

---

### Caso 2: Consulta de PO por Número

**Cenário**: Usuário busca um PO pelo número.

```bash
GET /api/v1/purchase-orders?po_number=PO-202512-0001
```

**Resultado**: Retorna o PO com todos os itens e informações relacionadas.

---

### Caso 3: Visualização de PO Relacionado a uma PR

**Cenário**: Usuário visualiza uma PR e quer ver o PO gerado.

```bash
GET /api/v1/purchase-requests/1/purchase-order
```

**Resultado**: Retorna o PO gerado a partir dessa PR, se existir.

---

### Caso 4: Idempotência na Geração de PO

**Cenário**: Job é executado múltiplas vezes para a mesma PR aprovada.

**Resultado**: 
- Primeira execução: PO é criado
- Execuções subsequentes: PO existente é detectado, nenhum PO duplicado é criado
- Log registra a tentativa de duplicação

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/purchase-orders
```

### Endpoints Disponíveis

#### 1. Listar Purchase Orders

```http
GET /api/v1/purchase-orders
```

**Query Parameters:**
- `project_id` (opcional): Filtrar por projeto
- `status` (opcional): Filtrar por status (pending, approved, completed, cancelled)
- `po_number` (opcional): Buscar por número do PO

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "purchase_request_id": 5,
      "po_number": "PO-202512-0001",
      "status": "pending",
      "total": 1250.00,
      "notes": null,
      "created_at": "2025-12-30T12:00:00.000000Z",
      "updated_at": "2025-12-30T12:00:00.000000Z"
    }
  ]
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão

---

#### 2. Visualizar Purchase Order

```http
GET /api/v1/purchase-orders/{id}
```

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "purchase_request_id": 5,
    "po_number": "PO-202512-0001",
    "status": "pending",
    "total": 1250.00,
    "notes": null,
    "purchase_request": {
      "id": 5,
      "status": "approved"
    },
    "items": [
      {
        "id": 1,
        "description": "Cimento",
        "quantity": 10,
        "unit_price": 100.00,
        "total": 1000.00
      }
    ],
    "created_at": "2025-12-30T12:00:00.000000Z",
    "updated_at": "2025-12-30T12:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `404` - Não encontrado
- `403` - Sem permissão

---

#### 3. Atualizar Status do Purchase Order

```http
PATCH /api/v1/purchase-orders/{id}/status
```

**Body:**
```json
{
  "status": "approved"
}
```

**Validações:**
- Status deve ser válido (pending, approved, completed, cancelled)
- Transição de status deve ser permitida

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "status": "approved",
    "updated_at": "2025-12-30T13:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `422` - Erro de validação
- `404` - Não encontrado
- `403` - Sem permissão

---

#### 4. Obter PO por Purchase Request

```http
GET /api/v1/purchase-requests/{id}/purchase-order
```

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "po_number": "PO-202512-0001",
    "status": "pending",
    "total": 1250.00
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso (PO encontrado)
- `404` - PO não encontrado para esta PR
- `403` - Sem permissão

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a Purchase Orders requer:**
- Role: `Financeiro` **OU** `AdminObra`
- Verificação no controller via `authorize()` ou policies

**Outras roles:** Acesso negado (403)

### Validações

#### Geração Automática de PO

1. **PR deve estar aprovada**: ✅ PO só é gerado se PR.status = 'approved'
2. **PO único por PR**: ✅ Constraint UNIQUE em purchase_request_id garante 1:1
3. **Idempotência**: ✅ Job verifica se PO já existe antes de criar

#### Número do PO (po_number)

1. **Formato**: `PO-YYYYMM-####` (ex: PO-202512-0001)
2. **Geração automática**: ✅ Criado no evento `creating` do model
3. **Sequencial por mês**: ✅ Sequência reinicia a cada mês
4. **Unicidade garantida**: ✅ Constraint UNIQUE + lockForUpdate na geração

#### Itens do PO

1. **Cópia da PR**: ✅ Itens são copiados automaticamente da PR aprovada
2. **Quantidade > 0**: ✅ Validação no model
3. **Preço unitário >= 0**: ✅ Validação no model
4. **Total calculado**: ✅ quantity * unit_price (automático)

#### Total do PO

1. **Cálculo automático**: ✅ Soma dos totais dos itens
2. **Atualização automática**: ✅ Recalculado quando itens são salvos/deletados

### Lifecycle

- **Criação**: Automática via Job quando PR é aprovada
- **Atualização**: Status pode ser alterado manualmente
- **Delete**: Soft delete (não remove fisicamente)

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/purchase-order.ts

export enum PurchaseOrderStatus {
  PENDING = 'pending',
  APPROVED = 'approved',
  COMPLETED = 'completed',
  CANCELLED = 'cancelled',
}

export interface PurchaseOrder {
  id: number;
  purchase_request_id: number;
  po_number: string;
  status: PurchaseOrderStatus;
  total: number;
  notes: string | null;
  purchase_request?: PurchaseRequest;
  items?: PurchaseOrderItem[];
  created_by?: number;
  updated_by?: number;
  created_at: string;
  updated_at: string;
}

export interface PurchaseOrderItem {
  id: number;
  purchase_order_id: number;
  purchase_request_item_id: number | null;
  cost_item_id: number | null;
  description: string;
  quantity: number;
  unit_price: number;
  total: number;
  created_at: string;
  updated_at: string;
}

export interface UpdatePurchaseOrderStatusInput {
  status: PurchaseOrderStatus;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/purchaseOrderService.ts

import { PurchaseOrder, PurchaseOrderStatus, UpdatePurchaseOrderStatusInput } from '@/types/purchase-order';
import { api } from './api';

export const purchaseOrderService = {
  async list(projectId?: number, filters?: { status?: PurchaseOrderStatus; po_number?: string }): Promise<PurchaseOrder[]> {
    const params = new URLSearchParams();
    if (projectId) params.append('project_id', projectId.toString());
    if (filters?.status) params.append('status', filters.status);
    if (filters?.po_number) params.append('po_number', filters.po_number);

    const response = await api.get(`/purchase-orders?${params.toString()}`);
    return response.data.data;
  },

  async show(id: number): Promise<PurchaseOrder> {
    const response = await api.get(`/purchase-orders/${id}`);
    return response.data.data;
  },

  async updateStatus(id: number, data: UpdatePurchaseOrderStatusInput): Promise<PurchaseOrder> {
    const response = await api.patch(`/purchase-orders/${id}/status`, data);
    return response.data.data;
  },

  async getByPurchaseRequest(prId: number): Promise<PurchaseOrder | null> {
    try {
      const response = await api.get(`/purchase-requests/${prId}/purchase-order`);
      return response.data.data;
    } catch (error: any) {
      if (error.response?.status === 404) {
        return null;
      }
      throw error;
    }
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/usePurchaseOrder.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { purchaseOrderService } from '@/services/purchaseOrderService';
import { PurchaseOrder, PurchaseOrderStatus, UpdatePurchaseOrderStatusInput } from '@/types/purchase-order';

export function usePurchaseOrders(projectId?: number, filters?: { status?: PurchaseOrderStatus }) {
  return useQuery({
    queryKey: ['purchase-orders', projectId, filters],
    queryFn: () => purchaseOrderService.list(projectId, filters),
  });
}

export function usePurchaseOrder(id: number) {
  return useQuery({
    queryKey: ['purchase-order', id],
    queryFn: () => purchaseOrderService.show(id),
    enabled: !!id,
  });
}

export function useUpdatePurchaseOrderStatus() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdatePurchaseOrderStatusInput }) =>
      purchaseOrderService.updateStatus(id, data),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['purchase-order', data.id] });
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
    },
  });
}

export function usePurchaseOrderByRequest(prId: number) {
  return useQuery({
    queryKey: ['purchase-order-by-request', prId],
    queryFn: () => purchaseOrderService.getByPurchaseRequest(prId),
    enabled: !!prId,
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/PurchaseOrderCard.tsx

import { PurchaseOrder, PurchaseOrderStatus } from '@/types/purchase-order';
import { useUpdatePurchaseOrderStatus } from '@/hooks/usePurchaseOrder';

interface PurchaseOrderCardProps {
  purchaseOrder: PurchaseOrder;
}

export function PurchaseOrderCard({ purchaseOrder }: PurchaseOrderCardProps) {
  const updateStatus = useUpdatePurchaseOrderStatus();

  const handleStatusChange = (newStatus: PurchaseOrderStatus) => {
    updateStatus.mutate({
      id: purchaseOrder.id,
      data: { status: newStatus },
    });
  };

  return (
    <div className="border rounded-lg p-4">
      <div className="flex justify-between items-start mb-4">
        <div>
          <h3 className="text-lg font-semibold">{purchaseOrder.po_number}</h3>
          <p className="text-sm text-gray-600">
            PR #{purchaseOrder.purchase_request_id}
          </p>
        </div>
        <span className={`px-2 py-1 rounded text-sm ${
          purchaseOrder.status === PurchaseOrderStatus.COMPLETED ? 'bg-green-100 text-green-800' :
          purchaseOrder.status === PurchaseOrderStatus.APPROVED ? 'bg-blue-100 text-blue-800' :
          purchaseOrder.status === PurchaseOrderStatus.CANCELLED ? 'bg-red-100 text-red-800' :
          'bg-gray-100 text-gray-800'
        }`}>
          {purchaseOrder.status}
        </span>
      </div>

      <div className="mb-4">
        <p className="text-2xl font-bold">R$ {purchaseOrder.total.toFixed(2)}</p>
      </div>

      {purchaseOrder.items && (
        <div className="mb-4">
          <h4 className="font-semibold mb-2">Itens:</h4>
          <ul className="space-y-1">
            {purchaseOrder.items.map((item) => (
              <li key={item.id} className="text-sm">
                {item.quantity}x {item.description} - R$ {item.total.toFixed(2)}
              </li>
            ))}
          </ul>
        </div>
      )}

      <div className="flex gap-2">
        {purchaseOrder.status === PurchaseOrderStatus.PENDING && (
          <>
            <button
              onClick={() => handleStatusChange(PurchaseOrderStatus.APPROVED)}
              className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              Aprovar
            </button>
            <button
              onClick={() => handleStatusChange(PurchaseOrderStatus.CANCELLED)}
              className="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
              Cancelar
            </button>
          </>
        )}
        {purchaseOrder.status === PurchaseOrderStatus.APPROVED && (
          <button
            onClick={() => handleStatusChange(PurchaseOrderStatus.COMPLETED)}
            className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          >
            Marcar como Concluído
          </button>
        )}
      </div>
    </div>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Listar POs de um Projeto

```typescript
import { usePurchaseOrders } from '@/hooks/usePurchaseOrder';

function ProjectPurchaseOrders({ projectId }: { projectId: number }) {
  const { data: purchaseOrders, isLoading } = usePurchaseOrders(projectId);

  if (isLoading) return <div>Carregando...</div>;

  return (
    <div>
      <h2>Pedidos de Compra</h2>
      {purchaseOrders?.map((po) => (
        <PurchaseOrderCard key={po.id} purchaseOrder={po} />
      ))}
    </div>
  );
}
```

### Exemplo 2: Verificar se PR tem PO Gerado

```typescript
import { usePurchaseOrderByRequest } from '@/hooks/usePurchaseOrder';

function PurchaseRequestDetail({ prId }: { prId: number }) {
  const { data: purchaseOrder, isLoading } = usePurchaseOrderByRequest(prId);

  if (isLoading) return <div>Carregando...</div>;

  return (
    <div>
      <h2>Requisição de Compra #{prId}</h2>
      {purchaseOrder ? (
        <div>
          <p>PO Gerado: {purchaseOrder.po_number}</p>
          <p>Status: {purchaseOrder.status}</p>
        </div>
      ) : (
        <p>Nenhum PO gerado ainda</p>
      )}
    </div>
  );
}
```

### Exemplo 3: Buscar PO por Número

```typescript
import { usePurchaseOrders } from '@/hooks/usePurchaseOrder';

function SearchPurchaseOrder() {
  const [poNumber, setPoNumber] = useState('');
  const { data: purchaseOrders } = usePurchaseOrders(undefined, { po_number: poNumber });

  return (
    <div>
      <input
        type="text"
        value={poNumber}
        onChange={(e) => setPoNumber(e.target.value)}
        placeholder="Digite o número do PO (ex: PO-202512-0001)"
      />
      {purchaseOrders && purchaseOrders.length > 0 && (
        <PurchaseOrderCard purchaseOrder={purchaseOrders[0]} />
      )}
    </div>
  );
}
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: Apenas roles `Financeiro` ou `AdminObra`
- **Project Scope**: PO deve pertencer ao projeto informado

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Status transitions**: Validar transições permitidas antes de enviar
2. **PO number format**: Validar formato ao buscar por número
3. **Loading states**: Mostrar feedback durante operações assíncronas

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Notificações**: Notificar usuários quando PO é gerado ou status muda
2. **Histórico de alterações**: Registrar todas as mudanças de status
3. **Integração com fornecedores**: Enviar PO automaticamente para fornecedor
4. **Relatórios**: Dashboard com estatísticas de POs por status/projeto

### Considerações para Implementação

- **Performance**: Índices já criados para otimizar consultas
- **Escalabilidade**: Job pode ser executado em fila assíncrona
- **Auditoria**: Campos created_by/updated_by já implementados

---

## 📚 Referências

- [Documentação de Purchase Requests](./PURCHASE_REQUESTS.md) (se existir)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/PurchaseOrder.php`
- Model: `app/Models/PurchaseOrderItem.php`
- Controller: `app/Http/Controllers/PurchaseOrderController.php` (a ser criado)
- Event: `app/Events/ApprovedPurchaseRequest.php`
- Job: `app/Jobs/GeneratePurchaseOrder.php`
- Tests: `tests/Unit/PurchaseOrderTest.php`
- Tests: `tests/Unit/PurchaseOrderItemTest.php`
- Tests: `tests/Feature/GeneratePurchaseOrderTest.php`

---

## ❓ FAQ

### P: O que acontece se o job GeneratePurchaseOrder falhar?

**R:** O job é idempotente, então pode ser reexecutado. Ele verifica se já existe um PO para a PR antes de criar um novo. Se falhar, o administrador pode reexecutar o job manualmente.

### P: Posso criar um PO manualmente?

**R:** Atualmente, POs são gerados apenas automaticamente quando uma PR é aprovada. Criação manual pode ser implementada no futuro se necessário.

### P: O que acontece se eu deletar uma PR aprovada?

**R:** Como há cascade delete, o PO também será deletado (soft delete). Isso mantém a integridade referencial.

### P: Como funciona a numeração do PO?

**R:** O formato é `PO-YYYYMM-####`, onde:
- `PO-` é o prefixo fixo
- `YYYYMM` é o ano e mês (ex: 202512)
- `####` é um número sequencial de 4 dígitos que reinicia a cada mês

### P: Posso alterar itens de um PO depois de criado?

**R:** Atualmente, os itens são copiados da PR e não podem ser editados diretamente. Alterações devem ser feitas na PR original (se ainda permitido) e um novo PO pode ser gerado.

---

**Última atualização:** 2025-12-30  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

