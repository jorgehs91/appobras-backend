# Feature: Licenses (Licenças)

Este documento descreve a funcionalidade de **Licenses (Licenças)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

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

**Licenses** representam licenças e documentos com validade associados a projetos. O sistema permite gerenciar prazos de validade, alertas de vencimento e controle de documentos legais necessários para a execução de obras.

### Objetivos

- Gerenciar licenças e documentos com prazo de validade por projeto
- Controlar vencimentos e alertar sobre licenças próximas do vencimento
- Vincular documentos (Files) às licenças para rastreabilidade
- Facilitar a gestão de compliance e documentação legal

### Características Principais

- ✅ Licenças por projeto
- ✅ Controle de data de vencimento (expiry_date)
- ✅ Status opcional (active, expired, pending_renewal)
- ✅ Relacionamento com File para documentos
- ✅ Scopes para consultas (expired, expiring soon, active)
- ✅ Métodos auxiliares para verificação de status
- ✅ Integração futura com sistema de alertas
- ✅ Auditoria completa (created_by, updated_by)
- ✅ Soft deletes

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── License (licença/documento com validade)
          └── File (documento associado)
```

### Relacionamentos

#### License → File (Obrigatório)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitas licenças podem referenciar o mesmo arquivo, mas cada licença tem um arquivo)
- **Campo**: `file_id`
- **Descrição**: Toda licença está associada a um arquivo/documento

#### License → Project (Obrigatório)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitas licenças para um projeto)
- **Campo**: `project_id`
- **Descrição**: Toda licença pertence a um projeto

#### License → User (Criação/Atualização)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campos**: `created_by`, `updated_by`
- **Descrição**: Rastreamento de quem criou/atualizou a licença

#### File → License (Inverso)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N
- **Descrição**: Um arquivo pode ter múltiplas licenças associadas

#### Project → License (Inverso)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N
- **Descrição**: Um projeto pode ter múltiplas licenças

### Fluxo Conceitual

```
1. Upload de Documento
   └── File criado no sistema (ex: "Licença Ambiental.pdf")
       └── Upload via DocumentController

2. Criação de Licença
   └── License criada vinculada ao File
       ├── Data de vencimento definida (expiry_date)
       └── Status opcional (active, expired, pending_renewal)

3. Monitoramento
   └── Sistema verifica licenças próximas do vencimento
       └── AlertGenerator dispara notificações (futuro)
```

---

## 📊 Modelo de Dados

### Tabela: `licenses`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `file_id` | bigint | ID do arquivo associado | Sim | Foreign key para `files` |
| `project_id` | bigint | ID do projeto | Sim | Foreign key para `projects` |
| `expiry_date` | date | Data de vencimento da licença | Sim | Formato: YYYY-MM-DD |
| `status` | string | Status da licença | Não | Valores: active, expired, pending_renewal (nullable) |
| `notes` | text | Observações sobre a licença | Não | Texto livre (nullable) |
| `created_by` | bigint | ID do usuário que criou | Não | Foreign key para `users`, nullable |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para `users`, nullable |
| `created_at` | timestamp | Data de criação | Sim | Auto-preenchido |
| `updated_at` | timestamp | Data de atualização | Sim | Auto-atualizado |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable, para soft deletes |

### Índices

- `file_id` - Para consultas por arquivo
- `project_id` - Para consultas por projeto
- `expiry_date` - Para consultas de vencimento
- `status` - Para filtros por status
- `[project_id, expiry_date]` - Composite index para consultas combinadas

### Constraints

- `file_id` - Foreign key com `onDelete('cascade')` - Se o arquivo for deletado, a licença também será
- `project_id` - Foreign key com `onDelete('cascade')` - Se o projeto for deletado, as licenças também serão
- `expiry_date` - Obrigatório, não pode ser null
- `created_by`, `updated_by` - Foreign keys com `onDelete('set null')` - Se o usuário for deletado, os campos ficam null

---

## 💼 Casos de Uso

### Caso 1: Criar Licença para Documento de Projeto

**Cenário**: Um administrador precisa registrar uma licença ambiental que vence em 6 meses.

```http
POST /api/v1/projects/1/licenses
{
  "file_id": 5,
  "expiry_date": "2026-07-01",
  "status": "active",
  "notes": "Licença ambiental emitida pela CETESB"
}
```

**Resultado**: Licença criada com sucesso, vinculada ao arquivo e projeto. Sistema pode gerar alertas quando a data de vencimento se aproximar.

---

### Caso 2: Listar Licenças Vencendo em 30 Dias

**Cenário**: Um usuário precisa ver todas as licenças que vencem nos próximos 30 dias para planejar renovações.

```http
GET /api/v1/projects/1/licenses?expiring_soon=30
```

**Resultado**: Lista de licenças com `expiry_date` entre hoje e 30 dias no futuro, ordenadas por data de vencimento.

---

### Caso 3: Verificar Status de Licença

**Cenário**: Sistema precisa verificar se uma licença está vencida para bloquear ações.

```php
$license = License::find(1);
if ($license->isExpired()) {
    // Bloquear ação ou gerar alerta
}
```

**Resultado**: Método retorna `true` se `expiry_date < now()`, permitindo lógica condicional.

---

### Caso 4: Atualizar Data de Vencimento após Renovação

**Cenário**: Uma licença foi renovada e precisa ter sua data de vencimento atualizada.

```http
PUT /api/v1/licenses/1
{
  "expiry_date": "2027-07-01",
  "status": "active",
  "notes": "Renovada em 01/01/2026"
}
```

**Resultado**: Licença atualizada com nova data de vencimento e status atualizado.

---

### Caso 5: Filtrar Licenças por Status

**Cenário**: Visualizar apenas licenças ativas de um projeto.

```http
GET /api/v1/projects/1/licenses?status=active
```

**Resultado**: Lista filtrada contendo apenas licenças com status "active".

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/licenses
```

### Endpoints Disponíveis

> **Nota**: ✅ Endpoints CRUD completos implementados na tarefa 34.

#### 1. Listar Licenças

```http
GET /api/v1/licenses
```

**Query Parameters:**
- `project_id` (opcional): Filtrar por projeto específico
- `status` (opcional): Filtrar por status (active, expired, pending_renewal)
- `expiring_soon` (opcional, boolean): Filtrar licenças próximas do vencimento (usa threshold configurado)

**Validações:**
- Usuário deve ter acesso ao projeto
- Permissão: Admin Obra ou Financeiro

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "file_id": 5,
      "project_id": 1,
      "expiry_date": "2026-07-01",
      "status": "active",
      "notes": "Licença ambiental",
      "file": {
        "id": 5,
        "name": "licenca_ambiental.pdf",
        "url": "https://..."
      },
      "project": {
        "id": 1,
        "name": "Projeto Exemplo"
      },
      "created_at": "2026-01-01T10:00:00.000000Z",
      "updated_at": "2026-01-01T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Projeto não encontrado

---

#### 2. Criar Licença

```http
POST /api/v1/licenses
```

**Body:**
- `file_id` (obrigatório): ID do arquivo associado
- `expiry_date` (obrigatório): Data de vencimento (YYYY-MM-DD)
- `status` (opcional): Status da licença
- `notes` (opcional): Observações

**Validações:**
- `file_id` deve existir e pertencer ao projeto
- `expiry_date` deve ser uma data válida no futuro
- Usuário deve ter acesso ao projeto
- Permissão: Admin Obra ou Financeiro

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "file_id": 5,
    "project_id": 1,
    "expiry_date": "2026-07-01",
    "status": "active",
    "notes": "Licença ambiental",
    "created_at": "2026-01-01T10:00:00.000000Z",
    "updated_at": "2026-01-01T10:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `201` - Criado com sucesso
- `403` - Sem permissão
- `404` - Projeto ou arquivo não encontrado
- `422` - Erro de validação

---

#### 3. Visualizar Licença

```http
GET /api/v1/licenses/{id}
```

**Validações:**
- Licença deve existir
- Usuário deve ter acesso ao projeto da licença

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "file_id": 5,
    "project_id": 1,
    "expiry_date": "2026-07-01",
    "status": "active",
    "notes": "Licença ambiental",
    "is_expired": false,
    "is_expiring_soon": false,
    "days_until_expiration": 182,
    "file": { ... },
    "project": { ... },
    "created_at": "2026-01-01T10:00:00.000000Z",
    "updated_at": "2026-01-01T10:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Licença não encontrada

---

#### 4. Atualizar Licença

```http
PUT /api/v1/licenses/{id}
```

**Body:**
- `expiry_date` (opcional): Nova data de vencimento
- `status` (opcional): Novo status
- `notes` (opcional): Observações atualizadas

**Validações:**
- Licença deve existir
- Usuário deve ter acesso ao projeto
- Permissão: Admin Obra ou Financeiro
- `expiry_date` deve ser uma data válida

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "file_id": 5,
    "project_id": 1,
    "expiry_date": "2027-07-01",
    "status": "active",
    "notes": "Renovada em 01/01/2026",
    "updated_at": "2026-01-01T11:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `200` - Atualizado com sucesso
- `403` - Sem permissão
- `404` - Licença não encontrada
- `422` - Erro de validação

---

#### 5. Excluir Licença

```http
DELETE /api/v1/licenses/{id}
```

**Validações:**
- Licença deve existir
- Usuário deve ter acesso ao projeto
- Permissão: Admin Obra ou Financeiro

**Resposta:**
```json
{
  "message": "Licença excluída com sucesso"
}
```

**Códigos HTTP:**
- `200` - Excluído com sucesso (soft delete)
- `403` - Sem permissão
- `404` - Licença não encontrada

---

#### 6. Listar Licenças Vencendo

```http
GET /api/v1/licenses/expiring
```

**Query Parameters:**
- `days` (opcional): Número de dias para considerar "vencendo em breve" (padrão: 30)

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "expiry_date": "2026-01-15",
      "days_until_expiration": 14,
      "status": "active",
      "file": { ... }
    }
  ]
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Projeto não encontrado

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a Licenses requer:**
- Role: `Admin Obra` **OU** `Financeiro`
- Verificação no controller via `hasBudgetAccess()` ou similar

**Outras roles:** Não têm acesso para criar/editar/excluir licenças, apenas visualizar (se implementado).

### Validações

#### Validação de Data de Vencimento

1. **Data obrigatória**: ✅ `expiry_date` é obrigatório
2. **Data futura recomendada**: ⚠️ Sistema permite datas passadas (para licenças já vencidas), mas recomenda-se validar no frontend
3. **Formato**: Deve ser `YYYY-MM-DD`

#### Validação de File

1. **File deve existir**: ✅ `file_id` deve referenciar um File válido
2. **File deve pertencer ao projeto**: ✅ Validação no controller (futuro)

#### Validação de Status

- Valores permitidos: `active`, `expired`, `pending_renewal` (ou null)
- Status não é obrigatório, mas recomendado para organização

### Scopes e Métodos Auxiliares

#### Scopes Disponíveis

- `byProject($projectId)`: Filtra licenças por projeto
- `byStatus($status)`: Filtra licenças por status
- `expiringSoon($days = 30)`: Licenças vencendo nos próximos N dias
- `expired()`: Licenças já vencidas
- `active()`: Licenças ainda válidas (não vencidas)

#### Métodos Auxiliares

- `isExpired()`: Retorna `true` se a licença está vencida
- `isExpiringSoon($days = 30)`: Retorna `true` se vence nos próximos N dias
- `daysUntilExpiration()`: Retorna número de dias até o vencimento (0 se vencida)

### Integração com AlertGenerator

**✅ Implementado (Tarefa 34):**
- `AlertGenerator` inclui query de licenças vencendo usando o scope `expiringSoon()`
- Query: `License::expiringSoon($licenseAlertDays)->with(['project.users', 'file'])->get()`
- Licenças são agrupadas por projeto e notificações são disparadas para todos os membros do projeto
- Tipo de notificação: `license.expiring`
- Notificações incluem: `license_id`, `file_name`, `expiry_date`, `days_until_expiration`, `project_id`, `project_name`

### Lifecycle

- **Criação**: Licença criada com `expiry_date` e opcionalmente `status`. `created_by` preenchido automaticamente via `AuditTrait`.
- **Atualização**: `expiry_date` e `status` podem ser atualizados. `updated_by` preenchido automaticamente.
- **Delete**: Soft delete - licença não é removida fisicamente, apenas marcada como deletada.

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/license.ts

export type LicenseStatus = 'active' | 'expired' | 'pending_renewal' | null;

export interface License {
  id: number;
  file_id: number;
  project_id: number;
  expiry_date: string; // ISO date string
  status: LicenseStatus;
  notes: string | null;
  file?: {
    id: number;
    name: string;
    url: string;
    mime_type: string;
  };
  project?: {
    id: number;
    name: string;
  };
  is_expired?: boolean; // Calculated field
  is_expiring_soon?: boolean; // Calculated field
  days_until_expiration?: number; // Calculated field
  created_at: string;
  updated_at: string;
}

export interface CreateLicenseInput {
  file_id: number;
  expiry_date: string; // YYYY-MM-DD
  status?: LicenseStatus;
  notes?: string;
}

export interface UpdateLicenseInput {
  expiry_date?: string;
  status?: LicenseStatus;
  notes?: string;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/licenseService.ts

import { License, CreateLicenseInput, UpdateLicenseInput } from '@/types/license';
import { api } from './api';

export const licenseService = {
  async list(projectId: number, params?: {
    status?: string;
    expiring_soon?: number;
    page?: number;
    per_page?: number;
  }): Promise<{ data: License[]; meta: any }> {
    const response = await api.get(`/projects/${projectId}/licenses`, { params });
    return response.data;
  },

  async show(id: number): Promise<License> {
    const response = await api.get(`/licenses/${id}`);
    return response.data.data;
  },

  async create(projectId: number, data: CreateLicenseInput): Promise<License> {
    const response = await api.post(`/projects/${projectId}/licenses`, data);
    return response.data.data;
  },

  async update(id: number, data: UpdateLicenseInput): Promise<License> {
    const response = await api.put(`/licenses/${id}`, data);
    return response.data.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/licenses/${id}`);
  },

  async expiring(projectId: number, days: number = 30): Promise<License[]> {
    const response = await api.get(`/projects/${projectId}/licenses/expiring`, {
      params: { days },
    });
    return response.data.data;
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useLicenses.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { licenseService } from '@/services/licenseService';
import { License, CreateLicenseInput, UpdateLicenseInput } from '@/types/license';

export function useLicenses(projectId: number, filters?: {
  status?: string;
  expiring_soon?: number;
}) {
  return useQuery({
    queryKey: ['licenses', projectId, filters],
    queryFn: () => licenseService.list(projectId, filters),
    enabled: !!projectId,
  });
}

export function useLicense(id: number) {
  return useQuery({
    queryKey: ['license', id],
    queryFn: () => licenseService.show(id),
    enabled: !!id,
  });
}

export function useCreateLicense(projectId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateLicenseInput) => licenseService.create(projectId, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['licenses', projectId] });
    },
  });
}

export function useUpdateLicense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateLicenseInput }) =>
      licenseService.update(id, data),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['license', variables.id] });
      queryClient.invalidateQueries({ queryKey: ['licenses'] });
    },
  });
}

export function useDeleteLicense() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => licenseService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['licenses'] });
    },
  });
}

export function useExpiringLicenses(projectId: number, days: number = 30) {
  return useQuery({
    queryKey: ['licenses', projectId, 'expiring', days],
    queryFn: () => licenseService.expiring(projectId, days),
    enabled: !!projectId,
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/LicenseForm.tsx

import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useCreateLicense, useUpdateLicense } from '@/hooks/useLicenses';
import { CreateLicenseInput, UpdateLicenseInput } from '@/types/license';

const licenseSchema = z.object({
  file_id: z.number().min(1, 'Arquivo é obrigatório'),
  expiry_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/, 'Data inválida'),
  status: z.enum(['active', 'expired', 'pending_renewal']).optional().nullable(),
  notes: z.string().optional().nullable(),
});

type LicenseFormData = z.infer<typeof licenseSchema>;

interface LicenseFormProps {
  projectId: number;
  fileId?: number;
  onSuccess?: () => void;
}

export function LicenseForm({ projectId, fileId, onSuccess }: LicenseFormProps) {
  const createLicense = useCreateLicense(projectId);
  const { register, handleSubmit, formState: { errors } } = useForm<LicenseFormData>({
    resolver: zodResolver(licenseSchema),
    defaultValues: {
      file_id: fileId,
      expiry_date: '',
      status: 'active',
      notes: '',
    },
  });

  const onSubmit = async (data: LicenseFormData) => {
    try {
      await createLicense.mutateAsync(data);
      onSuccess?.();
    } catch (error) {
      console.error('Erro ao criar licença:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <div>
        <label>Arquivo</label>
        <input
          type="number"
          {...register('file_id', { valueAsNumber: true })}
          disabled={!!fileId}
        />
        {errors.file_id && <span>{errors.file_id.message}</span>}
      </div>

      <div>
        <label>Data de Vencimento</label>
        <input type="date" {...register('expiry_date')} />
        {errors.expiry_date && <span>{errors.expiry_date.message}</span>}
      </div>

      <div>
        <label>Status</label>
        <select {...register('status')}>
          <option value="active">Ativa</option>
          <option value="expired">Vencida</option>
          <option value="pending_renewal">Renovação Pendente</option>
        </select>
      </div>

      <div>
        <label>Observações</label>
        <textarea {...register('notes')} />
      </div>

      <button type="submit" disabled={createLicense.isPending}>
        {createLicense.isPending ? 'Salvando...' : 'Salvar'}
      </button>
    </form>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Listar Licenças Vencendo em 30 Dias

```typescript
import { useExpiringLicenses } from '@/hooks/useLicenses';

function ExpiringLicensesList({ projectId }: { projectId: number }) {
  const { data: licenses, isLoading } = useExpiringLicenses(projectId, 30);

  if (isLoading) return <div>Carregando...</div>;

  return (
    <div>
      <h2>Licenças Vencendo em 30 Dias</h2>
      {licenses?.map((license) => (
        <div key={license.id}>
          <p>{license.file?.name}</p>
          <p>Vence em: {license.days_until_expiration} dias</p>
          <p>Data: {new Date(license.expiry_date).toLocaleDateString('pt-BR')}</p>
        </div>
      ))}
    </div>
  );
}
```

### Exemplo 2: Badge de Status Visual

```typescript
function LicenseStatusBadge({ license }: { license: License }) {
  const getStatusColor = () => {
    if (license.is_expired) return 'red';
    if (license.is_expiring_soon) return 'orange';
    return 'green';
  };

  const getStatusText = () => {
    if (license.is_expired) return 'Vencida';
    if (license.is_expiring_soon) return `Vence em ${license.days_until_expiration} dias`;
    return 'Válida';
  };

  return (
    <span style={{ color: getStatusColor() }}>
      {getStatusText()}
    </span>
  );
}
```

### Exemplo 3: Filtrar Licenças por Status

```typescript
function LicenseFilters({ projectId }: { projectId: number }) {
  const [statusFilter, setStatusFilter] = useState<string>('');
  const { data } = useLicenses(projectId, { status: statusFilter || undefined });

  return (
    <div>
      <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
        <option value="">Todos</option>
        <option value="active">Ativas</option>
        <option value="expired">Vencidas</option>
        <option value="pending_renewal">Renovação Pendente</option>
      </select>

      {data?.data.map((license) => (
        <LicenseCard key={license.id} license={license} />
      ))}
    </div>
  );
}
```

### Exemplo 4: Calcular Dias até Vencimento

```typescript
function calculateDaysUntilExpiration(expiryDate: string): number {
  const today = new Date();
  const expiry = new Date(expiryDate);
  const diffTime = expiry.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return Math.max(0, diffDays);
}

// Uso
const days = calculateDaysUntilExpiration('2026-07-01');
console.log(`Vence em ${days} dias`);
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: `hasBudgetAccess()` - apenas roles `Admin Obra` ou `Financeiro`
- **Project Scope**: License deve pertencer ao projeto informado

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Data de vencimento**: Validar formato YYYY-MM-DD e garantir que não é muito antiga
2. **File ID**: Verificar se o arquivo existe e pertence ao projeto antes de enviar
3. **Status**: Validar que o status é um dos valores permitidos

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Integração com AlertGenerator**: Notificações automáticas para licenças vencendo
2. **Renovação Automática**: Workflow para solicitar renovação de licenças
3. **Histórico de Renovações**: Rastreamento de renovações anteriores
4. **Alertas por Email**: Envio de emails quando licenças estão próximas do vencimento
5. **Dashboard Widget**: Widget no dashboard mostrando licenças vencendo

### Considerações para Implementação

- **Performance**: Índices já criados para consultas por `expiry_date` e `project_id`
- **Escalabilidade**: Scopes otimizados para grandes volumes de dados
- **Integração**: Pronto para integração com sistema de alertas existente

---

## 📚 Referências

- [Documentação de Files](../STORAGE_LOCAL_S3.md)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/License.php`
- Migration: `database/migrations/2026_01_01_214827_create_licenses_table.php`
- Factory: `database/factories/LicenseFactory.php`
- Tests: `tests/Unit/LicenseTest.php`

---

## ❓ FAQ

### P: Posso criar uma licença sem status?

**R:** Sim, o campo `status` é opcional. O sistema calcula automaticamente se a licença está vencida através do `expiry_date`.

### P: O que acontece se eu deletar um File que tem licenças associadas?

**R:** As licenças serão deletadas em cascata (cascade delete) devido à foreign key constraint. Use soft delete no File se quiser manter histórico.

### P: Como o sistema determina se uma licença está "vencendo em breve"?

**R:** O scope `expiringSoon($days)` verifica se `expiry_date` está entre hoje e N dias no futuro. O padrão é 30 dias, mas pode ser configurado.

### P: Posso ter múltiplas licenças para o mesmo arquivo?

**R:** Sim, tecnicamente é possível, mas não é recomendado. Cada licença deve representar um documento único com sua própria data de vencimento.

### P: Como integrar com o sistema de alertas?

**R:** Na tarefa 34, o `AlertGenerator` será expandido para incluir query de licenças vencendo usando o scope `expiringSoon()`. As notificações serão disparadas automaticamente.

---

**Última atualização:** 2026-01-01  
**Versão da API:** v1  
**Status:** ✅ Model e Endpoints Implementados e Testados

