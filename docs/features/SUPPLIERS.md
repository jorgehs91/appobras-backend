# Feature: Suppliers (Fornecedores)

Este documento descreve a funcionalidade de **Suppliers (Fornecedores)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

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

**Suppliers (Fornecedores)** são empresas ou pessoas físicas que fornecem materiais ou serviços para os projetos. Cada fornecedor possui um CNPJ único e pode estar associado a múltiplas requisições de compra.

### Objetivos

- Cadastrar e gerenciar fornecedores do sistema
- Validar e formatar CNPJ automaticamente
- Garantir unicidade de CNPJ
- Rastrear histórico de compras por fornecedor

### Características Principais

- ✅ Validação e formatação automática de CNPJ
- ✅ Unicidade de CNPJ garantida
- ✅ Soft deletes
- ✅ Auditoria completa (created_by, updated_by)
- ✅ RBAC (roles Financeiro e Admin Obra)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Supplier
  └── PurchaseRequest (PR) [1:N]
      └── PurchaseOrder (PO) [quando PR aprovada]
```

### Relacionamentos

#### Supplier → PurchaseRequest (1:N)
- **Tipo**: `HasMany`
- **Cardinalidade**: 1:N (um fornecedor pode ter muitas PRs)
- **Descrição**: Requisições de compra associadas ao fornecedor.

#### Supplier → User (Criação/Atualização)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campos**: `created_by`, `updated_by`
- **Descrição**: Rastreamento de quem criou/atualizou o fornecedor.

---

## 📊 Modelo de Dados

### Tabela: `suppliers`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `name` | string | Nome do fornecedor | Sim | Máx. 255 caracteres |
| `cnpj` | string(18) | CNPJ do fornecedor | Sim | Formato: XX.XXX.XXX/XXXX-XX, unique |
| `contact` | string | Contato (telefone/email) | Não | Nullable, máx. 255 caracteres |
| `created_by` | bigint | ID do usuário criador | Não | Foreign key para users, nullable |
| `updated_by` | bigint | ID do usuário que atualizou | Não | Foreign key para users, nullable |
| `created_at` | timestamp | Data de criação | Sim | Auto |
| `updated_at` | timestamp | Data de atualização | Sim | Auto |
| `deleted_at` | timestamp | Data de exclusão (soft delete) | Não | Nullable |

### Índices

- `cnpj` - Para busca rápida e garantia de unicidade
- `created_by`, `updated_by` - Para auditoria

### Constraints

- `cnpj` UNIQUE - Garante unicidade do CNPJ
- `cnpj` formato: XX.XXX.XXX/XXXX-XX - Validação e formatação automática no model

---

## 💼 Casos de Uso

### Caso 1: Cadastrar Novo Fornecedor

**Cenário**: Usuário precisa cadastrar um novo fornecedor no sistema.

```json
POST /api/v1/suppliers
{
  "name": "Construtora ABC Ltda",
  "cnpj": "12345678000190",
  "contact": "(11) 98765-4321"
}
```

**Resultado**: 
- Fornecedor criado com CNPJ formatado: `12.345.678/0001-90`
- CNPJ validado (14 dígitos)
- Unicidade verificada

---

### Caso 2: Buscar Fornecedor por CNPJ

**Cenário**: Usuário quer verificar se um fornecedor já está cadastrado.

```bash
GET /api/v1/suppliers
# Filtrar no frontend por CNPJ
```

**Resultado**: Lista de fornecedores, pode ser filtrada no frontend.

---

### Caso 3: Atualizar Dados do Fornecedor

**Cenário**: Fornecedor mudou o contato.

```json
PUT /api/v1/suppliers/1
{
  "contact": "(11) 99999-9999"
}
```

**Resultado**: Contato atualizado, CNPJ permanece inalterado.

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/suppliers
```

### Endpoints Disponíveis

#### 1. Listar Fornecedores

```http
GET /api/v1/suppliers
```

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Construtora ABC Ltda",
      "cnpj": "12.345.678/0001-90",
      "contact": "(11) 98765-4321",
      "created_at": "2025-12-30T10:00:00.000000Z",
      "updated_at": "2025-12-30T10:00:00.000000Z"
    }
  ]
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão

---

#### 2. Exibir Fornecedor

```http
GET /api/v1/suppliers/{id}
```

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "name": "Construtora ABC Ltda",
    "cnpj": "12.345.678/0001-90",
    "contact": "(11) 98765-4321",
    "created_at": "2025-12-30T10:00:00.000000Z",
    "updated_at": "2025-12-30T10:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `404` - Não encontrado
- `403` - Sem permissão

---

#### 3. Criar Fornecedor

```http
POST /api/v1/suppliers
```

**Body:**
```json
{
  "name": "Construtora ABC Ltda",
  "cnpj": "12345678000190",
  "contact": "(11) 98765-4321"
}
```

**Validações:**
- `name` obrigatório, máx. 255 caracteres
- `cnpj` obrigatório, deve ter 14 dígitos (aceita formatado ou não)
- `cnpj` deve ser único
- `contact` opcional, máx. 255 caracteres

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "name": "Construtora ABC Ltda",
    "cnpj": "12.345.678/0001-90",
    "contact": "(11) 98765-4321",
    "created_at": "2025-12-30T10:00:00.000000Z",
    "updated_at": "2025-12-30T10:00:00.000000Z"
  }
}
```

**Códigos HTTP:**
- `201` - Criado
- `422` - Erro de validação (CNPJ duplicado, formato inválido)
- `403` - Sem permissão

---

#### 4. Atualizar Fornecedor

```http
PUT /api/v1/suppliers/{id}
```

**Body:**
```json
{
  "name": "Construtora ABC Ltda - Filial SP",
  "contact": "(11) 99999-9999"
}
```

**Validações:**
- `cnpj` pode ser atualizado, mas deve permanecer único
- Outros campos seguem mesmas validações da criação

**Códigos HTTP:**
- `200` - Atualizado
- `422` - Erro de validação
- `404` - Não encontrado
- `403` - Sem permissão

---

#### 5. Deletar Fornecedor

```http
DELETE /api/v1/suppliers/{id}
```

**Validações:**
- Soft delete (não remove fisicamente)
- Fornecedor com PRs associadas ainda pode ser deletado (soft delete)

**Códigos HTTP:**
- `204` - Deletado
- `404` - Não encontrado
- `403` - Sem permissão

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a Suppliers requer:**
- Role: `Financeiro` **OU** `AdminObra`
- Verificação no controller via `hasBudgetAccess()`

**Outras roles:** Acesso negado (403)

### Validações

#### CNPJ

1. **Formato aceito**: ✅ Aceita 14 dígitos ou formato XX.XXX.XXX/XXXX-XX
2. **Formatação automática**: ✅ Sempre formatado para XX.XXX.XXX/XXXX-XX
3. **Validação de dígitos**: ✅ Deve ter exatamente 14 dígitos numéricos
4. **Unicidade**: ✅ CNPJ deve ser único no sistema
5. **Validação no model**: ✅ Validação ocorre no evento `saving` do model

#### Nome

1. **Obrigatório**: ✅ Campo obrigatório
2. **Tamanho máximo**: ✅ 255 caracteres

#### Contato

1. **Opcional**: ✅ Campo opcional
2. **Tamanho máximo**: ✅ 255 caracteres

### Lifecycle

- **Criação**: Valida CNPJ, formata automaticamente, verifica unicidade
- **Atualização**: Valida CNPJ se alterado, mantém formatação
- **Delete**: Soft delete (não remove fisicamente do banco)

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/supplier.ts

export interface Supplier {
  id: number;
  name: string;
  cnpj: string; // Formato: XX.XXX.XXX/XXXX-XX
  contact: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateSupplierInput {
  name: string;
  cnpj: string; // Aceita formatado ou não
  contact?: string;
}

export interface UpdateSupplierInput {
  name?: string;
  cnpj?: string; // Aceita formatado ou não
  contact?: string;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/supplierService.ts

import { Supplier, CreateSupplierInput, UpdateSupplierInput } from '@/types/supplier';
import { api } from './api';

export const supplierService = {
  async list(): Promise<Supplier[]> {
    const response = await api.get('/suppliers');
    return response.data.data;
  },

  async show(id: number): Promise<Supplier> {
    const response = await api.get(`/suppliers/${id}`);
    return response.data.data;
  },

  async create(data: CreateSupplierInput): Promise<Supplier> {
    const response = await api.post('/suppliers', data);
    return response.data.data;
  },

  async update(id: number, data: UpdateSupplierInput): Promise<Supplier> {
    const response = await api.put(`/suppliers/${id}`, data);
    return response.data.data;
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/suppliers/${id}`);
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useSupplier.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { supplierService } from '@/services/supplierService';
import { Supplier, CreateSupplierInput, UpdateSupplierInput } from '@/types/supplier';

export function useSuppliers() {
  return useQuery({
    queryKey: ['suppliers'],
    queryFn: () => supplierService.list(),
  });
}

export function useSupplier(id: number) {
  return useQuery({
    queryKey: ['supplier', id],
    queryFn: () => supplierService.show(id),
    enabled: !!id,
  });
}

export function useCreateSupplier() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateSupplierInput) => supplierService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['suppliers'] });
    },
  });
}

export function useUpdateSupplier() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateSupplierInput }) =>
      supplierService.update(id, data),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['supplier', data.id] });
      queryClient.invalidateQueries({ queryKey: ['suppliers'] });
    },
  });
}

export function useDeleteSupplier() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => supplierService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['suppliers'] });
    },
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/SupplierForm.tsx

import { useForm } from 'react-hook-form';
import { useCreateSupplier, useUpdateSupplier } from '@/hooks/useSupplier';
import { Supplier, CreateSupplierInput } from '@/types/supplier';

interface SupplierFormProps {
  supplier?: Supplier;
  onSuccess?: () => void;
}

export function SupplierForm({ supplier, onSuccess }: SupplierFormProps) {
  const create = useCreateSupplier();
  const update = useUpdateSupplier();
  const { register, handleSubmit, formState: { errors } } = useForm<CreateSupplierInput>({
    defaultValues: supplier ? {
      name: supplier.name,
      cnpj: supplier.cnpj,
      contact: supplier.contact || '',
    } : undefined,
  });

  const onSubmit = async (data: CreateSupplierInput) => {
    if (supplier) {
      await update.mutateAsync({ id: supplier.id, data });
    } else {
      await create.mutateAsync(data);
    }
    onSuccess?.();
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <div>
        <label>Nome *</label>
        <input {...register('name', { required: 'Nome é obrigatório' })} />
        {errors.name && <span>{errors.name.message}</span>}
      </div>

      <div>
        <label>CNPJ *</label>
        <input 
          {...register('cnpj', { required: 'CNPJ é obrigatório' })}
          placeholder="12345678000190 ou 12.345.678/0001-90"
        />
        {errors.cnpj && <span>{errors.cnpj.message}</span>}
      </div>

      <div>
        <label>Contato</label>
        <input {...register('contact')} />
      </div>

      <button type="submit" disabled={create.isPending || update.isPending}>
        {supplier ? 'Atualizar' : 'Criar'}
      </button>
    </form>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Listar e Filtrar Fornecedores

```typescript
import { useSuppliers } from '@/hooks/useSupplier';
import { useState, useMemo } from 'react';

function SupplierList() {
  const { data: suppliers, isLoading } = useSuppliers();
  const [search, setSearch] = useState('');

  const filtered = useMemo(() => {
    if (!suppliers) return [];
    if (!search) return suppliers;
    
    return suppliers.filter(s => 
      s.name.toLowerCase().includes(search.toLowerCase()) ||
      s.cnpj.includes(search)
    );
  }, [suppliers, search]);

  if (isLoading) return <div>Carregando...</div>;

  return (
    <div>
      <input
        type="text"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        placeholder="Buscar por nome ou CNPJ"
      />
      {filtered.map(supplier => (
        <div key={supplier.id}>
          <h3>{supplier.name}</h3>
          <p>CNPJ: {supplier.cnpj}</p>
          {supplier.contact && <p>Contato: {supplier.contact}</p>}
        </div>
      ))}
    </div>
  );
}
```

### Exemplo 2: Validar CNPJ no Frontend

```typescript
// utils/cnpj.ts

export function formatCNPJ(cnpj: string): string {
  // Remove caracteres não numéricos
  const numbers = cnpj.replace(/\D/g, '');
  
  if (numbers.length !== 14) return cnpj;
  
  // Formata: XX.XXX.XXX/XXXX-XX
  return numbers.replace(
    /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/,
    '$1.$2.$3/$4-$5'
  );
}

export function validateCNPJ(cnpj: string): boolean {
  const numbers = cnpj.replace(/\D/g, '');
  return numbers.length === 14;
}
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: Apenas roles `Financeiro` ou `AdminObra`
- **Validação**: CNPJ validado e formatado no backend

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **CNPJ format**: Validar formato antes de enviar
2. **CNPJ length**: Verificar se tem 14 dígitos
3. **Name required**: Validar campo obrigatório

---

## 📚 Referências

- [Documentação de Purchase Requests](./PURCHASE_REQUESTS.md)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/Supplier.php`
- Controller: `app/Http/Controllers/SupplierController.php`
- Tests: `tests/Feature/SuppliersTest.php`

---

**Última atualização:** 2025-12-30  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

