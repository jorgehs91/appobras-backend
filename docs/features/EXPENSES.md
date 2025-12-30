# Feature: Expenses (Despesas)

Este documento descreve a funcionalidade de **Expenses (Despesas)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

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

**Expenses** representam despesas realizadas em um projeto. Cada despesa pode estar associada a um item de custo do orçamento (opcional) e deve conter um comprovante quando aprovada.

### Objetivos

- Registrar despesas realizadas durante a execução do projeto
- Vincular despesas a itens de custo planejados (Budget/CostItem)
- Controlar aprovação de despesas através de comprovantes
- Permitir análise de **PVxRV** (Planejado vs Realizado)

### Características Principais

- ✅ Despesas por projeto
- ✅ Associação opcional com CostItem (item de orçamento)
- ✅ Upload de comprovantes (PDF, JPG, PNG)
- ✅ Status de aprovação (draft/approved)
- ✅ Armazenamento flexível (local ou S3)
- ✅ Auditoria completa (created_by, updated_by)
- ✅ Soft deletes

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      ├── Budget
      │   └── CostItem (item planejado)
      │       └── Expense? (opcional - despesa realizada)
      └── Expense (despesa realizada no projeto)
          └── CostItem? (opcional - item planejado relacionado)
```

### Relacionamentos

#### Expense → Project (Obrigatório)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitas despesas para um projeto)
- **Campo**: `project_id`
- **Descrição**: Toda despesa pertence a um projeto

#### Expense → CostItem (Opcional)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitas despesas podem estar vinculadas a um item de custo)
- **Campo**: `cost_item_id` (nullable)
- **Descrição**: Permite vincular uma despesa realizada ao item de custo planejado no orçamento

#### Expense → User (Criação/Atualização)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campos**: `created_by`, `updated_by`
- **Descrição**: Rastreamento de quem criou/atualizou a despesa

### Fluxo Conceitual

```
1. Planejamento (Budget/CostItem)
   └── Budget criado para o projeto
       └── CostItems planejados (ex: "Cimento", "Mão de Obra")

2. Execução (Expense)
   └── Despesa realizada (ex: Compra de cimento)
       ├── Pode estar vinculada a um CostItem (opcional)
       └── Comprovante anexado

3. Análise PVxRV
   └── Comparação: CostItem.planned_amount vs Expense.amount (soma)
```

---

## 📊 Modelo de Dados

### Tabela: `expenses`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `cost_item_id` | bigint | ID do item de custo | Não | FK para `cost_items.id` (nullable) |
| `project_id` | bigint | ID do projeto | Sim | FK para `projects.id` |
| `amount` | decimal(15,2) | Valor da despesa | Sim | Deve ser > 0 |
| `date` | date | Data da despesa | Sim | Data em que a despesa ocorreu |
| `description` | text | Descrição da despesa | Não | Máximo 1000 caracteres |
| `receipt_path` | string | Caminho do comprovante | Não | Path no storage (local/S3) |
| `status` | string | Status da despesa | Sim | Enum: `draft` ou `approved` |
| `created_by` | bigint | ID do usuário criador | Não | FK para `users.id` (nullable) |
| `updated_by` | bigint | ID do usuário atualizador | Não | FK para `users.id` (nullable) |
| `created_at` | timestamp | Data de criação | Sim | Automático |
| `updated_at` | timestamp | Data de atualização | Sim | Automático |
| `deleted_at` | timestamp | Data de exclusão | Não | Soft delete |

### Índices

- `project_id` - Para filtragem rápida por projeto
- `cost_item_id` - Para filtragem por item de custo
- `status` - Para filtragem por status
- `[project_id, date]` - Composite index para queries por projeto e período

### Constraints

- `amount > 0` - Validação aplicada no FormRequest
- `status IN ('draft', 'approved')` - Enum ExpenseStatus
- Se `status = 'approved'`, então `receipt_path` é obrigatório

---

## 🔄 Status e Workflow

### ExpenseStatus Enum

```php
enum ExpenseStatus: string
{
    case draft = 'draft';      // Rascunho (sem comprovante obrigatório)
    case approved = 'approved'; // Aprovado (comprovante obrigatório)
}
```

### Workflow de Status

```
[draft] ──────> [approved]
  │                │
  │                └── Requer: receipt_path
  │
  └── Pode existir sem comprovante
```

### Transições Permitidas

| De | Para | Condição |
|----|------|----------|
| `draft` | `approved` | Deve ter `receipt_path` ou arquivo no upload |
| `approved` | `approved` | Manutenção do status (pode atualizar comprovante) |
| `approved` | `draft` | Não recomendado, mas permitido |

### Regras de Validação

1. **Criação em `draft`**: Comprovante não é obrigatório
2. **Criação em `approved`**: Comprovante é **obrigatório**
3. **Atualização para `approved`**: Se não tiver comprovante, deve enviar um
4. **Atualização de `approved`**: Se remover comprovante, não pode manter status `approved`

---

## 💼 Casos de Uso

### Caso 1: Registrar Despesa Simples (Draft)

**Cenário**: Engenheiro precisa registrar uma compra realizada, mas ainda não tem o comprovante em mãos.

```json
POST /api/v1/projects/1/expenses
{
  "amount": 1500.00,
  "date": "2025-12-29",
  "description": "Compra de materiais elétricos",
  "status": "draft"
}
```

**Resultado**: Despesa criada em status `draft`, sem comprovante. Pode ser aprovada posteriormente.

---

### Caso 2: Registrar Despesa com Comprovante (Approved)

**Cenário**: Financeiro registra despesa já com comprovante para aprovação imediata.

```bash
POST /api/v1/projects/1/expenses (multipart/form-data)
- amount: 2500.00
- date: 2025-12-29
- description: Pagamento fornecedor
- status: approved
- receipt: [arquivo.pdf]
```

**Resultado**: Despesa criada em status `approved` com comprovante anexado.

---

### Caso 3: Vincular Despesa a Item de Custo Planejado

**Cenário**: Despesa realizada está relacionada a um item específico do orçamento.

```
Orçamento:
  └── CostItem: "Cimento" (planned_amount: 10.000,00)
      └── Expense: 1.500,00 (realizado)
```

```json
POST /api/v1/projects/1/expenses
{
  "cost_item_id": 5,
  "amount": 1500.00,
  "date": "2025-12-29",
  "description": "Compra de cimento conforme orçamento",
  "status": "draft"
}
```

**Benefício**: Facilita análise PVxRV (Planejado vs Realizado) por item de custo.

---

### Caso 4: Aprovar Despesa em Draft

**Cenário**: Despesa foi criada em draft e agora tem o comprovante.

```json
PUT /api/v1/expenses/123
{
  "status": "approved",
  "receipt": [arquivo.pdf]  // Upload do comprovante
}
```

**Resultado**: Status alterado para `approved` e comprovante anexado.

---

### Caso 5: Filtrar Despesas por Período

**Cenário**: Relatório mensal de despesas.

```bash
GET /api/v1/projects/1/expenses?date_from=2025-12-01&date_to=2025-12-31
```

**Resultado**: Lista apenas despesas do mês de dezembro.

---

### Caso 6: Filtrar Despesas Aprovadas

**Cenário**: Ver apenas despesas já aprovadas (com comprovantes).

```bash
GET /api/v1/projects/1/expenses?status=approved
```

**Resultado**: Lista apenas despesas com status `approved`.

---

### Caso 7: Análise PVxRV (Planejado vs Realizado)

**Cenário**: Comparar orçamento planejado com despesas realizadas.

```
CostItem: "Cimento"
  ├── planned_amount: R$ 10.000,00
  └── Expenses (soma):
      ├── Expense 1: R$ 1.500,00
      ├── Expense 2: R$ 2.000,00
      └── Total realizado: R$ 3.500,00

Análise:
  - Planejado: R$ 10.000,00
  - Realizado: R$ 3.500,00
  - Restante: R$ 6.500,00
  - % Executado: 35%
```

**Implementação Futura**: Endpoint dedicado para relatório PVxRV.

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/projects/{project}/expenses
```

### Endpoints Disponíveis

#### 1. Listar Despesas

```http
GET /api/v1/projects/{project}/expenses
```

**Query Parameters:**
- `status` (opcional): `draft` ou `approved`
- `date_from` (opcional): Data inicial (formato: YYYY-MM-DD)
- `date_to` (opcional): Data final (formato: YYYY-MM-DD)

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "cost_item_id": 5,
      "project_id": 1,
      "amount": 1500.00,
      "date": "2025-12-29",
      "description": "Compra de materiais",
      "receipt_path": "expenses/project-1/abc123.pdf",
      "status": "approved",
      "cost_item": { /* CostItemResource */ },
      "project": { /* ProjectResource */ },
      "created_at": "2025-12-29T10:00:00Z",
      "updated_at": "2025-12-29T10:00:00Z"
    }
  ]
}
```

---

#### 2. Criar Despesa

```http
POST /api/v1/projects/{project}/expenses
Content-Type: multipart/form-data
```

**Body (Form Data):**
- `cost_item_id` (opcional): ID do item de custo
- `amount` (obrigatório): Valor da despesa
- `date` (obrigatório): Data da despesa (YYYY-MM-DD)
- `description` (opcional): Descrição
- `receipt` (opcional): Arquivo do comprovante (PDF, JPG, PNG - máx. 10MB)
- `status` (obrigatório): `draft` ou `approved`

**Validações:**
- Se `status = approved`, então `receipt` é obrigatório
- `amount` deve ser > 0
- `date` deve ser uma data válida

**Resposta:** `201 Created` com o ExpenseResource

---

#### 3. Visualizar Despesa

```http
GET /api/v1/expenses/{expense}
```

**Resposta:** ExpenseResource completo com relacionamentos carregados

---

#### 4. Atualizar Despesa

```http
PUT /api/v1/expenses/{expense}
PATCH /api/v1/expenses/{expense}
Content-Type: multipart/form-data
```

**Body (Form Data):**
- Campos opcionais para atualização parcial
- Se atualizar `status` para `approved`, deve enviar `receipt` se não tiver

**Resposta:** ExpenseResource atualizado

---

#### 5. Deletar Despesa

```http
DELETE /api/v1/expenses/{expense}
```

**Comportamento:**
- Soft delete (marca `deleted_at`)
- Remove arquivo do comprovante do storage (se existir)

**Resposta:** `204 No Content`

---

#### 6. Download do Comprovante

```http
GET /api/v1/expenses/{expense}/receipt
```

**Resposta:** Stream do arquivo do comprovante

**Headers:**
- `Content-Type`: Tipo do arquivo (application/pdf, image/jpeg, etc.)

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a Expenses requer:**
- Role: `Financeiro` **OU** `Admin Obra`
- Verificação no controller via `hasBudgetAccess()`

**Outras roles:** Não têm permissão para acessar expenses (retorna 403).

### Validações

#### Validação de Status

1. **Criação `approved` sem comprovante**: ❌ Bloqueado (422)
2. **Atualização para `approved` sem comprovante**: ❌ Bloqueado (422)
3. **Manter `approved` sem comprovante**: ⚠️ Verificado na validação

#### Validação de Valor

- `amount` deve ser > 0
- `amount` máximo: 9.999.999.999.999,99 (decimal 15,2)

#### Validação de Arquivo

- Tipos permitidos: PDF, JPG, JPEG, PNG
- Tamanho máximo: 10MB
- Armazenamento: Local (padrão) ou S3 (configurável via `EXPENSE_RECEIPTS_DISK`)

#### Validação de Relacionamentos

- `project_id`: Obrigatório (vem da URL)
- `cost_item_id`: Opcional, mas deve existir se fornecido
- `cost_item` deve pertencer ao mesmo projeto (validação futura recomendada)

### Armazenamento de Arquivos

#### Configuração

Por padrão, os comprovantes são armazenados **localmente** no servidor:

```env
# .env
EXPENSE_RECEIPTS_DISK=local  # padrão
```

Para usar S3:

```env
EXPENSE_RECEIPTS_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...
```

#### Estrutura de Pastas

**Local:**
```
storage/app/private/expenses/project-{id}/nome_arquivo.pdf
```

**S3:**
```
expenses/project-{id}/nome_arquivo.pdf
```

#### Lifecycle

- **Criação**: Arquivo é salvo no storage
- **Atualização**: Arquivo antigo é deletado, novo é salvo
- **Delete**: Arquivo é removido do storage

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/expense.ts

export enum ExpenseStatus {
  DRAFT = 'draft',
  APPROVED = 'approved',
}

export interface Expense {
  id: number;
  cost_item_id: number | null;
  project_id: number;
  amount: number;
  date: string; // YYYY-MM-DD
  description: string | null;
  receipt_path: string | null;
  status: ExpenseStatus;
  created_by: number | null;
  updated_by: number | null;
  cost_item?: CostItem; // Relacionamento carregado
  project?: Project; // Relacionamento carregado
  created_at: string; // ISO 8601
  updated_at: string; // ISO 8601
  deleted_at: string | null; // ISO 8601
}

export interface CreateExpenseInput {
  cost_item_id?: number;
  amount: number;
  date: string;
  description?: string;
  receipt?: File;
  status: ExpenseStatus;
}

export interface UpdateExpenseInput {
  cost_item_id?: number;
  amount?: number;
  date?: string;
  description?: string;
  receipt?: File;
  status?: ExpenseStatus;
}

export interface ExpenseFilters {
  status?: ExpenseStatus;
  date_from?: string;
  date_to?: string;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/expenseService.ts

import { Expense, CreateExpenseInput, UpdateExpenseInput, ExpenseFilters } from '@/types/expense';

export const expenseService = {
  /**
   * Lista despesas de um projeto
   */
  async list(projectId: number, filters?: ExpenseFilters): Promise<Expense[]> {
    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.date_from) params.append('date_from', filters.date_from);
    if (filters?.date_to) params.append('date_to', filters.date_to);

    const response = await api.get(`/projects/${projectId}/expenses?${params}`);
    return response.data.data;
  },

  /**
   * Cria uma nova despesa
   */
  async create(projectId: number, data: CreateExpenseInput): Promise<Expense> {
    const formData = new FormData();
    
    formData.append('amount', data.amount.toString());
    formData.append('date', data.date);
    formData.append('status', data.status);
    
    if (data.cost_item_id) formData.append('cost_item_id', data.cost_item_id.toString());
    if (data.description) formData.append('description', data.description);
    if (data.receipt) formData.append('receipt', data.receipt);

    const response = await api.post(`/projects/${projectId}/expenses`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    
    return response.data.data;
  },

  /**
   * Visualiza uma despesa específica
   */
  async show(expenseId: number): Promise<Expense> {
    const response = await api.get(`/expenses/${expenseId}`);
    return response.data.data;
  },

  /**
   * Atualiza uma despesa
   */
  async update(expenseId: number, data: UpdateExpenseInput): Promise<Expense> {
    const formData = new FormData();
    
    if (data.amount !== undefined) formData.append('amount', data.amount.toString());
    if (data.date) formData.append('date', data.date);
    if (data.status) formData.append('status', data.status);
    if (data.cost_item_id !== undefined) {
      formData.append('cost_item_id', data.cost_item_id?.toString() || '');
    }
    if (data.description !== undefined) formData.append('description', data.description || '');
    if (data.receipt) formData.append('receipt', data.receipt);

    const response = await api.put(`/expenses/${expenseId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    
    return response.data.data;
  },

  /**
   * Deleta uma despesa
   */
  async delete(expenseId: number): Promise<void> {
    await api.delete(`/expenses/${expenseId}`);
  },

  /**
   * Baixa o comprovante de uma despesa
   */
  async downloadReceipt(expenseId: number): Promise<Blob> {
    const response = await api.get(`/expenses/${expenseId}/receipt`, {
      responseType: 'blob',
    });
    return response.data;
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useExpenses.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { expenseService } from '@/services/expenseService';
import { Expense, CreateExpenseInput, UpdateExpenseInput, ExpenseFilters } from '@/types/expense';

export function useExpenses(projectId: number, filters?: ExpenseFilters) {
  return useQuery({
    queryKey: ['expenses', projectId, filters],
    queryFn: () => expenseService.list(projectId, filters),
  });
}

export function useExpense(expenseId: number) {
  return useQuery({
    queryKey: ['expense', expenseId],
    queryFn: () => expenseService.show(expenseId),
    enabled: !!expenseId,
  });
}

export function useCreateExpense(projectId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateExpenseInput) => expenseService.create(projectId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['expenses', projectId] });
    },
  });
}

export function useUpdateExpense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ expenseId, data }: { expenseId: number; data: UpdateExpenseInput }) =>
      expenseService.update(expenseId, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['expense', variables.expenseId] });
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
    },
  });
}

export function useDeleteExpense(projectId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (expenseId: number) => expenseService.delete(expenseId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['expenses', projectId] });
    },
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/ExpenseForm.tsx

import { useForm } from 'react-hook-form';
import { ExpenseStatus, CreateExpenseInput } from '@/types/expense';
import { useCreateExpense } from '@/hooks/useExpenses';

interface ExpenseFormProps {
  projectId: number;
  costItems?: Array<{ id: number; name: string }>;
  onSuccess?: () => void;
}

export function ExpenseForm({ projectId, costItems, onSuccess }: ExpenseFormProps) {
  const { register, handleSubmit, watch, formState: { errors } } = useForm<CreateExpenseInput>();
  const createExpense = useCreateExpense(projectId);
  const status = watch('status');

  const onSubmit = async (data: CreateExpenseInput) => {
    try {
      await createExpense.mutateAsync(data);
      onSuccess?.();
    } catch (error) {
      // Tratar erro
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} encType="multipart/form-data">
      {/* Cost Item Select */}
      {costItems && (
        <select {...register('cost_item_id')}>
          <option value="">Nenhum item de custo</option>
          {costItems.map(item => (
            <option key={item.id} value={item.id}>{item.name}</option>
          ))}
        </select>
      )}

      {/* Amount */}
      <input
        type="number"
        step="0.01"
        {...register('amount', { required: true, min: 0.01 })}
      />
      {errors.amount && <span>Valor é obrigatório e deve ser maior que zero</span>}

      {/* Date */}
      <input
        type="date"
        {...register('date', { required: true })}
      />
      {errors.date && <span>Data é obrigatória</span>}

      {/* Description */}
      <textarea {...register('description')} />

      {/* Status */}
      <select {...register('status', { required: true })}>
        <option value={ExpenseStatus.DRAFT}>Rascunho</option>
        <option value={ExpenseStatus.APPROVED}>Aprovado</option>
      </select>

      {/* Receipt - obrigatório se approved */}
      {(status === ExpenseStatus.APPROVED || !status) && (
        <input
          type="file"
          accept=".pdf,.jpg,.jpeg,.png"
          {...register('receipt', {
            required: status === ExpenseStatus.APPROVED,
          })}
        />
        {errors.receipt && <span>Comprovante é obrigatório para despesas aprovadas</span>}
      )}

      <button type="submit" disabled={createExpense.isPending}>
        {createExpense.isPending ? 'Salvando...' : 'Salvar Despesa'}
      </button>
    </form>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Fluxo Completo de Criação e Aprovação

```typescript
// 1. Criar despesa em draft
const draftExpense = await expenseService.create(projectId, {
  amount: 1500.00,
  date: '2025-12-29',
  description: 'Compra de materiais',
  status: ExpenseStatus.DRAFT,
});

// 2. Usuário faz upload do comprovante posteriormente
const approvedExpense = await expenseService.update(draftExpense.id, {
  status: ExpenseStatus.APPROVED,
  receipt: receiptFile, // File object
});
```

### Exemplo 2: Filtrar Despesas do Mês

```typescript
const startOfMonth = '2025-12-01';
const endOfMonth = '2025-12-31';

const expenses = await expenseService.list(projectId, {
  date_from: startOfMonth,
  date_to: endOfMonth,
});
```

### Exemplo 3: Relatório de Despesas por Item de Custo

```typescript
// Listar todas as despesas do projeto
const allExpenses = await expenseService.list(projectId);

// Agrupar por cost_item_id
const expensesByCostItem = allExpenses.reduce((acc, expense) => {
  const key = expense.cost_item_id || 'sem-item';
  if (!acc[key]) acc[key] = [];
  acc[key].push(expense);
  return acc;
}, {} as Record<number | string, Expense[]>);

// Calcular total por item
Object.entries(expensesByCostItem).forEach(([costItemId, expenses]) => {
  const total = expenses.reduce((sum, e) => sum + e.amount, 0);
  console.log(`Item ${costItemId}: R$ ${total.toFixed(2)}`);
});
```

### Exemplo 4: Download de Comprovante

```typescript
async function handleDownloadReceipt(expenseId: number, expenseDescription: string) {
  try {
    const blob = await expenseService.downloadReceipt(expenseId);
    
    // Criar URL temporária e fazer download
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `comprovante-${expenseDescription}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erro ao baixar comprovante:', error);
  }
}
```

---

## 🔍 Queries Úteis para Frontend

### Agrupar Despesas por Status

```typescript
const expensesByStatus = expenses.reduce((acc, expense) => {
  if (!acc[expense.status]) acc[expense.status] = [];
  acc[expense.status].push(expense);
  return acc;
}, {} as Record<ExpenseStatus, Expense[]>);
```

### Calcular Total de Despesas

```typescript
const totalExpenses = expenses.reduce((sum, expense) => sum + expense.amount, 0);
```

### Despesas do Mês Atual

```typescript
const currentMonth = new Date().toISOString().slice(0, 7); // YYYY-MM
const currentMonthExpenses = expenses.filter(
  expense => expense.date.startsWith(currentMonth)
);
```

### Despesas Pendentes de Aprovação

```typescript
const pendingExpenses = expenses.filter(
  expense => expense.status === ExpenseStatus.DRAFT
);
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: `hasBudgetAccess()` - apenas roles `Financeiro` ou `Admin Obra`
- **Project Scope**: Expense deve pertencer ao projeto informado

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Status Approved sem comprovante**: Mostrar erro antes de enviar
2. **Valor zero ou negativo**: Validar input numérico
3. **Tamanho de arquivo**: Validar antes do upload (max 10MB)
4. **Tipo de arquivo**: Validar extensão (PDF, JPG, PNG)

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Relatório PVxRV**: Endpoint dedicado para análise Planejado vs Realizado
2. **Categorização**: Adicionar campo `category` para classificação adicional
3. **Fornecedor**: Campo `supplier_id` para vincular a fornecedores
4. **Nota Fiscal**: Campos adicionais para informações fiscais
5. **Aprovação em Lote**: Endpoint para aprovar múltiplas despesas
6. **Exportação**: Exportar despesas para CSV/PDF

### Considerações para Implementação

- **Categorização**: Pode usar categorias do CostItem ou criar enum próprio
- **Fornecedor**: Aguardar implementação do módulo de Suppliers
- **Nota Fiscal**: Considerar integração com sistema fiscal (futuro)

---

## 📚 Referências

- [Documentação de Teste Manual](../../docs/TESTE_MANUAL_EXPENSES_API.md)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/Expense.php`
- Controller: `app/Http/Controllers/ExpenseController.php`
- Tests: `tests/Feature/ExpenseControllerTest.php`

---

## ❓ FAQ

### P: Posso criar uma despesa sem vincular a um CostItem?

**R:** Sim! O `cost_item_id` é opcional. Despesas podem ser registradas independentemente do orçamento planejado.

### P: O que acontece se deletar um CostItem que tem Expenses vinculadas?

**R:** As Expenses permanecem, mas `cost_item_id` fica `null` (onDelete: set null). Os valores e dados são preservados.

### P: Posso mudar o `project_id` de uma Expense?

**R:** Não diretamente via API atual. Uma Expense está sempre vinculada ao projeto da URL. Para mover, seria necessário deletar e recriar (ou implementar endpoint específico).

### P: Como funciona o armazenamento de arquivos em produção?

**R:** Configure `EXPENSE_RECEIPTS_DISK=s3` no `.env` de produção e configure as credenciais AWS. Os arquivos serão armazenados no S3 automaticamente.

### P: Há limite de despesas por projeto?

**R:** Não há limite técnico. O limite prático é o espaço de armazenamento e performance das queries.

---

**Última atualização:** 2025-12-29  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

