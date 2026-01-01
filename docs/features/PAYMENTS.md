# Feature: Payments (Agenda de Contas a Pagar)

Este documento descreve a funcionalidade de **Payments (Agenda de Contas a Pagar)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Modelo de Dados](#modelo-de-dados)
4. [Status e Workflow](#status-e-workflow)
5. [Casos de Uso](#casos-de-uso)
6. [Regras de Negócio](#regras-de-negócio)
7. [Integração Frontend](#integração-frontend)
8. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Payments** representam a agenda de contas a pagar do sistema. Cada pagamento pode estar associado a uma **WorkOrder** (Ordem de Serviço) ou a um **Contract** (Contrato), permitindo rastrear pagamentos relacionados a prestadores de serviço e contratos.

### Objetivos

- Gerenciar agenda de pagamentos vinculados a contratos e ordens de serviço
- Controlar status de pagamentos (pendente, pago, cancelado, vencido)
- Registrar comprovantes de pagamento
- Permitir rastreamento de pagamentos por prestador/contrato

### Características Principais

- ✅ Relacionamento polimórfico com WorkOrder e Contract
- ✅ Status de pagamento (pending, paid, canceled, overdue)
- ✅ Upload de comprovantes de pagamento
- ✅ Data de vencimento e data de pagamento
- ✅ Auditoria completa (created_by, updated_by)
- ✅ Soft deletes
- ✅ Scopes úteis para consultas (pending, paid, overdue)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── Contract
          ├── WorkOrder
          │   └── Payment? (polimórfico)
          └── Payment? (polimórfico)
```

### Relacionamentos

#### Payment → WorkOrder/Contract (Polimórfico)
- **Tipo**: `MorphTo`
- **Cardinalidade**: N:1 (muitos pagamentos para uma WorkOrder OU Contract)
- **Campos**: `payable_type`, `payable_id`
- **Descrição**: Um pagamento pode estar associado a uma WorkOrder ou a um Contract (não ambos)

#### WorkOrder → Payment (Inverso)
- **Tipo**: `MorphMany`
- **Cardinalidade**: 1:N
- **Método**: `payments()`
- **Descrição**: Uma WorkOrder pode ter múltiplos pagamentos

#### Contract → Payment (Inverso)
- **Tipo**: `MorphMany`
- **Cardinalidade**: 1:N
- **Método**: `payments()`
- **Descrição**: Um Contract pode ter múltiplos pagamentos

#### Payment → User (Criação/Atualização)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campos**: `created_by`, `updated_by`
- **Descrição**: Rastreamento de quem criou/atualizou o pagamento

### Fluxo Conceitual

```
1. Contrato/Ordem de Serviço
   └── Contract ou WorkOrder criado
       └── Define valores e prazos

2. Agenda de Pagamento
   └── Payment criado vinculado ao Contract/WorkOrder
       ├── Define valor, data de vencimento
       └── Status inicial: pending

3. Execução do Pagamento
   └── Payment atualizado para paid
       ├── paid_at preenchido
       └── payment_proof_path anexado (opcional)

4. Controle de Vencimentos
   └── Payment pode ser marcado como overdue
       └── Baseado em due_date < hoje e status = pending
```

---

## 📊 Modelo de Dados

### Tabela: `payments`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `payable_type` | string | Tipo do relacionamento polimórfico | Sim | `App\Models\WorkOrder` ou `App\Models\Contract` |
| `payable_id` | bigint | ID do relacionamento polimórfico | Sim | ID da WorkOrder ou Contract |
| `amount` | decimal(15,2) | Valor do pagamento | Sim | Valor monetário com 2 casas decimais |
| `due_date` | date | Data de vencimento | Sim | Data em que o pagamento deve ser realizado |
| `status` | string | Status do pagamento | Sim | Enum: `pending`, `paid`, `canceled`, `overdue` |
| `paid_at` | timestamp | Data/hora do pagamento | Não | Preenchido quando status = `paid` |
| `payment_proof_path` | string | Caminho do comprovante | Não | Path do arquivo de comprovante (PDF, JPG, PNG) |
| `created_by` | bigint | Usuário que criou | Não | FK para `users.id`, set null on delete |
| `updated_by` | bigint | Usuário que atualizou | Não | FK para `users.id`, set null on delete |
| `created_at` | timestamp | Data de criação | Sim | Auto-preenchido |
| `updated_at` | timestamp | Data de atualização | Sim | Auto-atualizado |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Null quando não excluído |

### Índices

- `[payable_type, payable_id]` - Índice composto para relacionamento polimórfico (criado automaticamente por `morphs()`)
- `status` - Para filtros por status
- `due_date` - Para consultas de vencimentos
- `created_by` - Para auditoria
- `updated_by` - Para auditoria

### Constraints

- `amount > 0` - Validação aplicada no código/model
- `status IN ('pending', 'paid', 'canceled', 'overdue')` - Enum PaymentStatus
- `payable_type` deve ser `App\Models\WorkOrder` ou `App\Models\Contract`
- `paid_at` deve ser preenchido quando `status = 'paid'`

---

## 🔄 Status e Workflow

### PaymentStatus Enum

```php
enum PaymentStatus: string
{
    case pending = 'pending';    // Pagamento pendente
    case paid = 'paid';          // Pagamento realizado
    case canceled = 'canceled';  // Pagamento cancelado
    case overdue = 'overdue';    // Pagamento vencido
}
```

### Workflow de Status

```
[pending] ──────> [paid]
  │                   │
  │                   └── Requer: paid_at preenchido
  │
  ├──> [canceled]
  │
  └──> [overdue] (automático ou manual)
       └── Condição: due_date < hoje && status = pending
```

### Transições Permitidas

| De | Para | Condição |
|----|------|----------|
| `pending` | `paid` | Deve preencher `paid_at` |
| `pending` | `canceled` | Qualquer momento |
| `pending` | `overdue` | Automático quando `due_date < hoje` ou manual |
| `overdue` | `paid` | Deve preencher `paid_at` |
| `overdue` | `canceled` | Qualquer momento |
| `paid` | `canceled` | ⚠️ Não recomendado, mas permitido |
| `canceled` | `pending` | ⚠️ Não recomendado, mas permitido |

### Regras de Validação

1. **Criação em `pending`**: Status padrão ao criar um pagamento
2. **Transição para `paid`**: Deve preencher `paid_at` com data/hora do pagamento
3. **Transição para `overdue`**: Pode ser automática (baseado em `due_date`) ou manual
4. **Transição para `canceled`**: Pode ser feita a qualquer momento, `paid_at` permanece null

---

## 💼 Casos de Uso

### Caso 1: Criar Pagamento para WorkOrder

**Cenário**: Um pagamento precisa ser agendado para uma ordem de serviço específica.

```php
$workOrder = WorkOrder::find(1);
$payment = Payment::create([
    'payable_type' => WorkOrder::class,
    'payable_id' => $workOrder->id,
    'amount' => 5000.00,
    'due_date' => '2026-07-15',
    'status' => PaymentStatus::pending->value,
]);
```

**Resultado**: Pagamento criado e vinculado à WorkOrder, status inicial `pending`.

---

### Caso 2: Criar Pagamento para Contract

**Cenário**: Um pagamento precisa ser agendado para um contrato.

```php
$contract = Contract::find(1);
$payment = Payment::create([
    'payable_type' => Contract::class,
    'payable_id' => $contract->id,
    'amount' => 10000.00,
    'due_date' => '2026-08-01',
    'status' => PaymentStatus::pending->value,
]);
```

**Resultado**: Pagamento criado e vinculado ao Contract.

---

### Caso 3: Marcar Pagamento como Pago

**Cenário**: Um pagamento foi realizado e precisa ser marcado como pago.

```php
$payment = Payment::find(1);
$payment->update([
    'status' => PaymentStatus::paid->value,
    'paid_at' => now(),
    'payment_proof_path' => 'payments/proof_123.pdf', // Opcional
]);
```

**Resultado**: Status alterado para `paid`, `paid_at` preenchido, comprovante anexado (opcional).

---

### Caso 4: Listar Pagamentos Pendentes de uma WorkOrder

**Cenário**: Verificar quais pagamentos ainda estão pendentes para uma ordem de serviço.

```php
$workOrder = WorkOrder::find(1);
$pendingPayments = $workOrder->payments()
    ->pending()
    ->get();
```

**Resultado**: Lista de pagamentos pendentes da WorkOrder.

---

### Caso 5: Listar Pagamentos Vencidos

**Cenário**: Identificar pagamentos que já venceram e ainda não foram pagos.

```php
$overduePayments = Payment::overdue()->get();
```

**Resultado**: Lista de pagamentos com status `overdue` ou `pending` com `due_date < hoje`.

---

## 📐 Regras de Negócio

### Validações

#### Validação de Valor

1. **`amount > 0`**: ✅ Valor deve ser positivo
2. **`amount` obrigatório**: ✅ Campo obrigatório

#### Validação de Data

1. **`due_date` obrigatório**: ✅ Data de vencimento deve ser informada
2. **`due_date` formato válido**: ✅ Deve ser uma data válida

#### Validação de Status

1. **Status padrão**: ✅ Ao criar, status padrão é `pending`
2. **Transição para `paid`**: ✅ Deve preencher `paid_at`
3. **Transição para `overdue`**: ✅ Pode ser automática ou manual

#### Validação de Relacionamento Polimórfico

1. **`payable_type` válido**: ✅ Deve ser `App\Models\WorkOrder` ou `App\Models\Contract`
2. **`payable_id` existe**: ✅ O registro referenciado deve existir
3. **Apenas um relacionamento**: ✅ Payment deve estar vinculado a WorkOrder OU Contract (não ambos)

### Lifecycle

- **Criação**: Payment criado com status `pending`, `paid_at` null
- **Atualização**: Campos podem ser atualizados, transições de status validadas
- **Delete**: Soft delete, registro mantido com `deleted_at` preenchido

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/payment.ts

export enum PaymentStatus {
  PENDING = 'pending',
  PAID = 'paid',
  CANCELED = 'canceled',
  OVERDUE = 'overdue',
}

export interface Payment {
  id: number;
  payable_type: 'App\\Models\\WorkOrder' | 'App\\Models\\Contract';
  payable_id: number;
  amount: string; // Decimal como string
  due_date: string; // ISO date string
  status: PaymentStatus;
  paid_at: string | null; // ISO datetime string
  payment_proof_path: string | null;
  created_by: number | null;
  updated_by: number | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  // Relacionamentos (quando incluídos)
  payable?: WorkOrder | Contract;
  creator?: User;
  updater?: User;
}

export interface CreatePaymentInput {
  payable_type: 'App\\Models\\WorkOrder' | 'App\\Models\\Contract';
  payable_id: number;
  amount: number;
  due_date: string; // ISO date string
  status?: PaymentStatus; // Default: PENDING
}

export interface UpdatePaymentInput {
  amount?: number;
  due_date?: string;
  status?: PaymentStatus;
  paid_at?: string | null;
  payment_proof_path?: string | null;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/paymentService.ts

import { Payment, CreatePaymentInput, UpdatePaymentInput } from '@/types/payment';
import { api } from '@/utils/api';

export const paymentService = {
  async list(workOrderId?: number, contractId?: number): Promise<Payment[]> {
    const params = new URLSearchParams();
    if (workOrderId) params.append('work_order_id', workOrderId.toString());
    if (contractId) params.append('contract_id', contractId.toString());
    
    const response = await api.get(`/payments?${params}`);
    return response.data.data;
  },

  async show(id: number): Promise<Payment> {
    const response = await api.get(`/payments/${id}`);
    return response.data.data;
  },

  async create(data: CreatePaymentInput): Promise<Payment> {
    const response = await api.post('/payments', data);
    return response.data.data;
  },

  async update(id: number, data: UpdatePaymentInput): Promise<Payment> {
    const response = await api.put(`/payments/${id}`, data);
    return response.data.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/payments/${id}`);
  },

  async markAsPaid(id: number, proofPath?: string): Promise<Payment> {
    return this.update(id, {
      status: PaymentStatus.PAID,
      paid_at: new Date().toISOString(),
      payment_proof_path: proofPath || null,
    });
  },

  async cancel(id: number): Promise<Payment> {
    return this.update(id, {
      status: PaymentStatus.CANCELED,
    });
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/usePayments.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { paymentService } from '@/services/paymentService';
import { Payment, CreatePaymentInput, UpdatePaymentInput } from '@/types/payment';

export function usePayments(workOrderId?: number, contractId?: number) {
  return useQuery({
    queryKey: ['payments', workOrderId, contractId],
    queryFn: () => paymentService.list(workOrderId, contractId),
  });
}

export function usePayment(id: number) {
  return useQuery({
    queryKey: ['payments', id],
    queryFn: () => paymentService.show(id),
    enabled: !!id,
  });
}

export function useCreatePayment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreatePaymentInput) => paymentService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
    },
  });
}

export function useUpdatePayment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdatePaymentInput }) =>
      paymentService.update(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
      queryClient.invalidateQueries({ queryKey: ['payments', variables.id] });
    },
  });
}

export function useDeletePayment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => paymentService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
    },
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/PaymentForm.tsx

import { useState } from 'react';
import { useCreatePayment } from '@/hooks/usePayments';
import { PaymentStatus, CreatePaymentInput } from '@/types/payment';

interface PaymentFormProps {
  payableType: 'App\\Models\\WorkOrder' | 'App\\Models\\Contract';
  payableId: number;
  onSuccess?: () => void;
}

export function PaymentForm({ payableType, payableId, onSuccess }: PaymentFormProps) {
  const [amount, setAmount] = useState('');
  const [dueDate, setDueDate] = useState('');
  const createPayment = useCreatePayment();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    const data: CreatePaymentInput = {
      payable_type: payableType,
      payable_id: payableId,
      amount: parseFloat(amount),
      due_date: dueDate,
      status: PaymentStatus.PENDING,
    };

    try {
      await createPayment.mutateAsync(data);
      onSuccess?.();
    } catch (error) {
      console.error('Erro ao criar pagamento:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>Valor</label>
        <input
          type="number"
          step="0.01"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          required
        />
      </div>
      <div>
        <label>Data de Vencimento</label>
        <input
          type="date"
          value={dueDate}
          onChange={(e) => setDueDate(e.target.value)}
          required
        />
      </div>
      <button type="submit" disabled={createPayment.isPending}>
        {createPayment.isPending ? 'Criando...' : 'Criar Pagamento'}
      </button>
    </form>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Criar Pagamento para WorkOrder

```php
use App\Models\Payment;
use App\Models\WorkOrder;
use App\Enums\PaymentStatus;

$workOrder = WorkOrder::find(1);

$payment = Payment::create([
    'payable_type' => WorkOrder::class,
    'payable_id' => $workOrder->id,
    'amount' => 5000.00,
    'due_date' => '2026-07-15',
    'status' => PaymentStatus::pending,
]);

// Acessar relacionamento
$payable = $payment->payable; // Retorna a WorkOrder
```

### Exemplo 2: Listar Pagamentos Pendentes de um Contract

```php
use App\Models\Contract;
use App\Enums\PaymentStatus;

$contract = Contract::find(1);

$pendingPayments = $contract->payments()
    ->byStatus(PaymentStatus::pending)
    ->orderBy('due_date')
    ->get();

foreach ($pendingPayments as $payment) {
    echo "Pagamento: R$ {$payment->amount} - Vencimento: {$payment->due_date->format('d/m/Y')}\n";
}
```

### Exemplo 3: Marcar Pagamento como Pago

```php
use App\Models\Payment;
use App\Enums\PaymentStatus;

$payment = Payment::find(1);

$payment->update([
    'status' => PaymentStatus::paid,
    'paid_at' => now(),
    'payment_proof_path' => 'payments/proof_' . $payment->id . '.pdf',
]);
```

### Exemplo 4: Identificar Pagamentos Vencidos

```php
use App\Models\Payment;

// Usando scope
$overduePayments = Payment::overdue()->get();

// Ou manualmente
$overduePayments = Payment::where('status', PaymentStatus::overdue)
    ->orWhere(function ($query) {
        $query->where('status', PaymentStatus::pending)
            ->where('due_date', '<', now());
    })
    ->get();
```

### Exemplo 5: Calcular Total de Pagamentos Pendentes

```php
use App\Models\Contract;
use App\Enums\PaymentStatus;

$contract = Contract::find(1);

$totalPending = $contract->payments()
    ->byStatus(PaymentStatus::pending)
    ->sum('amount');

echo "Total pendente: R$ " . number_format($totalPending, 2, ',', '.');
```

---

## 🔍 Queries Úteis para Frontend

### Filtrar Pagamentos por Status

```typescript
const payments = usePayments(workOrderId);
const pendingPayments = payments.data?.filter(p => p.status === PaymentStatus.PENDING);
const paidPayments = payments.data?.filter(p => p.status === PaymentStatus.PAID);
```

### Calcular Total Pendente

```typescript
const totalPending = payments.data
  ?.filter(p => p.status === PaymentStatus.PENDING)
  .reduce((sum, p) => sum + parseFloat(p.amount), 0) || 0;
```

### Identificar Pagamentos Vencidos

```typescript
const overduePayments = payments.data?.filter(p => {
  const dueDate = new Date(p.due_date);
  const today = new Date();
  return (
    (p.status === PaymentStatus.PENDING || p.status === PaymentStatus.OVERDUE) &&
    dueDate < today
  );
});
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: A definir quando endpoints forem criados
- **Project Scope**: Payment deve pertencer a um projeto (via WorkOrder ou Contract)

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Valor positivo**: Validar que `amount > 0`
2. **Data de vencimento**: Validar que `due_date` é uma data válida e futura (ou passada se permitido)
3. **Status ao marcar como pago**: Validar que `paid_at` é preenchido quando `status = paid`

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Endpoints REST**: Criar endpoints completos para CRUD de pagamentos
2. **Notificações**: Alertar sobre pagamentos próximos ao vencimento
3. **Relatórios**: Relatórios de fluxo de caixa baseados em pagamentos
4. **Recorrência**: Suporte a pagamentos recorrentes
5. **Integração Bancária**: Integração com APIs bancárias para confirmação automática

### Considerações para Implementação

- **Endpoints**: Considerar criar endpoints aninhados (`/work-orders/{id}/payments`, `/contracts/{id}/payments`)
- **Validações**: Implementar validações de transição de status no backend
- **Jobs**: Criar job para marcar automaticamente pagamentos como `overdue` baseado em `due_date`

---

## 📚 Referências

- [Documentação de Contracts e WorkOrders](./CONTRACTS_AND_WORK_ORDERS.md)
- Model: `app/Models/Payment.php`
- Enum: `app/Enums/PaymentStatus.php`
- Factory: `database/factories/PaymentFactory.php`
- Tests: `tests/Unit/PaymentTest.php`
- Migration: `database/migrations/2026_01_01_150321_create_payments_table.php`

---

## ❓ FAQ

### P: Um Payment pode estar vinculado a uma WorkOrder E um Contract ao mesmo tempo?

**R:** Não. Um Payment está vinculado a **OU** uma WorkOrder **OU** um Contract através do relacionamento polimórfico. Os campos `payable_type` e `payable_id` definem qual entidade está relacionada.

### P: Como identificar se um Payment está vencido?

**R:** Use o scope `overdue()` ou verifique manualmente: `status = 'overdue'` OU (`status = 'pending'` AND `due_date < hoje`).

### P: Posso cancelar um pagamento que já foi pago?

**R:** Tecnicamente sim (não há constraint), mas não é recomendado. Considere criar uma nova entrada de pagamento reverso se necessário.

### P: O campo `paid_at` é obrigatório quando status é `paid`?

**R:** Sim, é uma boa prática preencher `paid_at` quando o status muda para `paid`. Considere implementar validação no backend para garantir isso.

### P: Como listar todos os pagamentos de um projeto?

**R:** Como Payment está vinculado a WorkOrder ou Contract (que pertencem a um Project), você precisaria fazer uma query através desses relacionamentos:

```php
$project = Project::find(1);
$payments = Payment::whereHasMorph('payable', [WorkOrder::class, Contract::class], function ($query) use ($project) {
    $query->where('project_id', $project->id);
})->get();
```

---

**Última atualização:** 2026-01-01  
**Versão da API:** v1  
**Status:** ✅ Model e Relacionamentos Implementados e Testados

