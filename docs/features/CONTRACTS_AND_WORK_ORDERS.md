# Feature: Contracts and Work Orders

Este documento descreve a funcionalidade de **Contratos e Ordens de Serviço** do sistema AppObras, incluindo arquitetura, regras de negócio, relacionamentos e estrutura de dados.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Estrutura de Dados](#estrutura-de-dados)
4. [Enum: ContractStatus](#enum-contractstatus)
5. [Regras de Negócio](#regras-de-negócio)
6. [Auditoria](#auditoria)
7. [Relacionamentos Eloquent](#relacionamentos-eloquent)
8. [Testes](#testes)

---

## 🎯 Visão Geral

**Contracts (Contratos)** e **Work Orders (Ordens de Serviço)** são entidades que expandem a funcionalidade de gestão de prestadores de serviço (Contractors) no sistema, permitindo o controle formal de contratos e suas respectivas ordens de serviço.

### Objetivos

- Gerenciar contratos formais entre projetos e prestadores de serviço
- Controlar ordens de serviço vinculadas a contratos
- Rastrear valores, prazos e status de contratos
- Manter histórico de auditoria de criação e atualização

### Características Principais

- ✅ Model Contract com relacionamentos para Contractor e Project
- ✅ Model WorkOrder vinculado a Contract
- ✅ Enum ContractStatus para controle de estados
- ✅ Soft Deletes para preservação de histórico
- ✅ Audit Trail automático (created_by, updated_by)
- ✅ Casts apropriados para valores decimais e datas

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Contractor
      └── Contract (contractor_id)
          └── WorkOrder (contract_id)

Project
  └── Contract (project_id)
      └── WorkOrder
```

### Relacionamentos Detalhados

#### Contract
- **belongsTo**: Contractor, Project
- **hasMany**: WorkOrder
- **belongsTo**: User (creator, updater via audit fields)

#### WorkOrder
- **belongsTo**: Contract
- **belongsTo**: User (creator, updater via audit fields)

#### Contractor
- **hasMany**: Contract

#### Project
- **hasMany**: Contract

---

## 📊 Estrutura de Dados

### Tabela: `contracts`

| Campo | Tipo | Descrição | Observações |
|-------|------|-----------|-------------|
| `id` | bigint | Primary key | Auto increment |
| `contractor_id` | bigint | FK para contractors | NOT NULL, CASCADE DELETE |
| `project_id` | bigint | FK para projects | NOT NULL, CASCADE DELETE |
| `value` | decimal(15,2) | Valor do contrato | NOT NULL |
| `start_date` | date | Data de início | NOT NULL |
| `end_date` | date | Data de término | NULLABLE |
| `status` | string | Status do contrato | NOT NULL, default: 'draft', enum: ContractStatus |
| `created_by` | bigint | FK para users (criador) | NULLABLE, SET NULL on delete |
| `updated_by` | bigint | FK para users (atualizador) | NULLABLE, SET NULL on delete |
| `created_at` | timestamp | Data de criação | Auto |
| `updated_at` | timestamp | Data de atualização | Auto |
| `deleted_at` | timestamp | Soft delete | NULLABLE |

**Índices:**
- `contractor_id`
- `project_id`
- `status`
- `created_by`
- `updated_by`

### Tabela: `work_orders`

| Campo | Tipo | Descrição | Observações |
|-------|------|-----------|-------------|
| `id` | bigint | Primary key | Auto increment |
| `contract_id` | bigint | FK para contracts | NOT NULL, CASCADE DELETE |
| `description` | text | Descrição da ordem de serviço | NOT NULL |
| `value` | decimal(15,2) | Valor da ordem de serviço | NOT NULL |
| `due_date` | date | Data de vencimento | NULLABLE |
| `created_by` | bigint | FK para users (criador) | NULLABLE, SET NULL on delete |
| `updated_by` | bigint | FK para users (atualizador) | NULLABLE, SET NULL on delete |
| `created_at` | timestamp | Data de criação | Auto |
| `updated_at` | timestamp | Data de atualização | Auto |
| `deleted_at` | timestamp | Soft delete | NULLABLE |

**Índices:**
- `contract_id`
- `created_by`
- `updated_by`

---

## 🔖 Enum: ContractStatus

O enum `ContractStatus` define os estados possíveis de um contrato:

```php
enum ContractStatus: string
{
    case draft = 'draft';      // Rascunho
    case active = 'active';    // Ativo
    case completed = 'completed'; // Concluído
    case canceled = 'canceled';   // Cancelado
}
```

### Estados

- **draft**: Contrato em rascunho, ainda não finalizado
- **active**: Contrato ativo e em execução
- **completed**: Contrato concluído com sucesso
- **canceled**: Contrato cancelado

---

## 📝 Regras de Negócio

### Contract

1. **Obrigatoriedade de Campos**:
   - `contractor_id`, `project_id`, `value`, `start_date` e `status` são obrigatórios
   - `end_date` é opcional

2. **Valores**:
   - `value` deve ser um valor decimal positivo
   - Cast para `decimal:2` garante precisão de centavos

3. **Datas**:
   - `start_date` é obrigatória
   - `end_date` é opcional (contratos podem não ter data de término definida)
   - Cast para `date` garante formatação correta

4. **Status**:
   - Status padrão: `draft`
   - Status deve ser um dos valores do enum `ContractStatus`

5. **Cascade Delete**:
   - Ao deletar um Contractor, todos os seus Contracts são deletados (cascade)
   - Ao deletar um Project, todos os seus Contracts são deletados (cascade)

6. **Soft Delete**:
   - Contracts utilizam soft delete para preservar histórico

### WorkOrder

1. **Obrigatoriedade de Campos**:
   - `contract_id`, `description` e `value` são obrigatórios
   - `due_date` é opcional

2. **Valores**:
   - `value` deve ser um valor decimal positivo
   - Cast para `decimal:2` garante precisão de centavos

3. **Datas**:
   - `due_date` é opcional (ordens de serviço podem não ter data de vencimento)

4. **Cascade Delete**:
   - Ao deletar um Contract, todas as suas WorkOrders são deletadas (cascade)
   - Utiliza soft delete para preservar histórico

---

## 🔍 Auditoria

Ambos os models (Contract e WorkOrder) utilizam o `AuditTrait` para rastreamento automático de criação e atualização.

### Campos de Auditoria

- **created_by**: ID do usuário que criou o registro
- **updated_by**: ID do usuário que atualizou o registro pela última vez

### Funcionamento

O `AuditTrait` preenche automaticamente:
- `created_by` no evento `creating` (quando o modelo é criado)
- `updated_by` no evento `updating` (quando o modelo é atualizado)

**Requisito**: O usuário deve estar autenticado (`auth()->check()`) para que os campos sejam preenchidos.

---

## 🔄 Relacionamentos Eloquent

### Contract Model

```php
// Relacionamentos
public function contractor(): BelongsTo
public function project(): BelongsTo
public function workOrders(): HasMany
public function creator(): BelongsTo
public function updater(): BelongsTo
```

### WorkOrder Model

```php
// Relacionamentos
public function contract(): BelongsTo
public function creator(): BelongsTo
public function updater(): BelongsTo
```

### Contractor Model

```php
// Novo relacionamento adicionado
public function contracts(): HasMany
```

### Project Model

```php
// Novo relacionamento adicionado
public function contracts(): HasMany
```

### Exemplos de Uso

```php
// Obter todos os contratos de um prestador
$contractor = Contractor::find(1);
$contracts = $contractor->contracts;

// Obter todas as ordens de serviço de um contrato
$contract = Contract::find(1);
$workOrders = $contract->workOrders;

// Obter todos os contratos de um projeto
$project = Project::find(1);
$contracts = $project->contracts;

// Criar uma ordem de serviço vinculada a um contrato
$workOrder = $contract->workOrders()->create([
    'description' => 'Executar serviço X',
    'value' => 5000.00,
    'due_date' => '2026-12-31',
]);
```

---

## 🧪 Testes

### Testes Unitários

Os testes estão localizados em:
- `tests/Unit/ContractTest.php`
- `tests/Unit/WorkOrderTest.php`

### Cobertura de Testes

#### ContractTest

✅ Criação de contract com dados válidos
✅ Relacionamento com Contractor
✅ Relacionamento com Project
✅ Contractor tem relacionamento hasMany com Contracts
✅ Project tem relacionamento hasMany com Contracts
✅ Soft deletes funcionando
✅ Relacionamento hasMany com WorkOrders
✅ Status é castado para enum
✅ Value é castado para decimal
✅ Datas são castadas corretamente
✅ Campos de auditoria (created_by, updated_by)

#### WorkOrderTest

✅ Criação de work order com dados válidos
✅ Relacionamento com Contract
✅ Contract tem relacionamento hasMany com WorkOrders
✅ Soft deletes funcionando
✅ Value é castado para decimal
✅ Due date é castado para date
✅ Due date pode ser null
✅ Campos de auditoria (created_by, updated_by)

### Factories

Factories disponíveis para testes:
- `ContractFactory`: Cria contracts com dados faker
- `WorkOrderFactory`: Cria work orders com dados faker

#### Estados Disponíveis no ContractFactory

- `draft()`: Cria contract em status draft
- `active()`: Cria contract em status active
- `completed()`: Cria contract em status completed
- `canceled()`: Cria contract em status canceled

---

## 🔮 Próximos Passos (Futuras Implementações)

Esta feature implementa apenas a estrutura de dados (models, migrations, relacionamentos). As seguintes funcionalidades ainda não foram implementadas:

1. **Controllers e Endpoints API**:
   - CRUD de Contracts
   - CRUD de WorkOrders
   - Endpoints para listar contracts por contractor ou project
   - Endpoints para listar work orders por contract

2. **Validações e Regras de Negócio Avançadas**:
   - Validação de datas (end_date deve ser após start_date)
   - Validação de valores (não permitir valores negativos)
   - Regras de transição de status

3. **Políticas de Acesso (Policies)**:
   - ContractPolicy
   - WorkOrderPolicy
   - Verificação de acesso baseado em company/project

4. **Documentação Swagger**:
   - Documentação dos endpoints quando forem criados

5. **Resources e Transformers**:
   - ContractResource
   - WorkOrderResource
   - Formatação adequada para API responses

6. **Funcionalidades Avançadas**:
   - Cálculo automático de totais
   - Relatórios de contratos
   - Notificações de vencimento de ordens de serviço

---

## 📚 Referências

- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Laravel Enums](https://laravel.com/docs/collections#method-enum)
- [Laravel Soft Deletes](https://laravel.com/docs/eloquent#soft-deleting)
- AuditTrait: `app/Traits/AuditTrait.php`

---

**Última atualização**: 2026-01-01
**Versão**: 1.0.0

