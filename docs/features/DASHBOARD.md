# Feature: Dashboard Stats

Este documento descreve a funcionalidade de **Dashboard Stats** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Widgets Disponíveis](#widgets-disponíveis)
4. [API Endpoints](#api-endpoints)
5. [Regras de Negócio](#regras-de-negócio)
6. [Cache e Performance](#cache-e-performance)
7. [Integração Frontend](#integração-frontend)
8. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Dashboard Stats** fornece estatísticas agregadas e widgets avançados para visualização rápida do status dos projetos, permitindo tomada de decisão baseada em dados.

### Objetivos

- Fornecer visão consolidada do progresso e status dos projetos
- Agregar métricas financeiras (PVxRV - Planejado vs Realizado)
- Alertar sobre tarefas atrasadas e entregas próximas
- Monitorar vencimentos de licenças (quando implementado)

### Características Principais

- ✅ Estatísticas agregadas de múltiplos projetos
- ✅ Widget de progresso médio
- ✅ Widget de tarefas atrasadas
- ✅ Widget de entregas próximas
- ✅ Widget de orçamento total
- ✅ Widget PVxRV summary (Planejado vs Realizado)
- ✅ Widget de licenças vencendo (placeholder)
- ✅ Cache inteligente com invalidação automática
- ✅ Filtro por projeto específico

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      ├── Phase (active)
      │   └── Task
      ├── Budget
      │   └── CostItem (planned_amount)
      └── Expense (approved - realized)
```

### Relacionamentos

O Dashboard agrega dados de:
- **Projects**: Progresso, orçamento planejado
- **Tasks**: Tarefas atrasadas, entregas próximas
- **CostItems**: Valores planejados (PVxRV)
- **Expenses**: Valores realizados (PVxRV)

### Fluxo Conceitual

```
1. Usuário acessa dashboard
   └── Sistema busca projetos acessíveis pelo usuário
       └── Agrega dados de múltiplas fontes

2. Cálculo de Estatísticas
   ├── Progresso médio: média de progress_percent dos projetos
   ├── Tarefas atrasadas: tasks com planned_end_at < hoje e status != done
   ├── Entregas próximas: tasks com due_at nos próximos 7 dias
   ├── Orçamento total: soma de planned_budget_amount
   └── PVxRV: agregação de CostItems (planned) vs Expenses (realized)

3. Cache e Invalidação
   └── Cache de 10 minutos
       └── Invalidação automática quando dados mudam
```

---

## 📊 Widgets Disponíveis

### 1. Progresso Médio (`avg_progress`)

- **Tipo**: `integer` (0-100)
- **Descrição**: Média aritmética do progresso percentual de todos os projetos acessíveis
- **Cálculo**: `SUM(progress_percent) / COUNT(projects)`
- **Fonte**: `Project.progress_percent` (calculado a partir de fases ativas)

### 2. Tarefas Atrasadas (`overdue_tasks_count`)

- **Tipo**: `integer`
- **Descrição**: Contagem de tarefas com data de término planejada no passado e status diferente de "done"
- **Cálculo**: `COUNT(tasks WHERE planned_end_at < TODAY AND status != 'done')`
- **Fonte**: `Task` model

### 3. Entregas Próximas (`upcoming_deliveries_count`)

- **Tipo**: `integer`
- **Descrição**: Contagem de tarefas com data de vencimento nos próximos 7 dias e status diferente de "done"
- **Cálculo**: `COUNT(tasks WHERE due_at BETWEEN TODAY AND TODAY+7 DAYS AND status != 'done')`
- **Fonte**: `Task` model

### 4. Orçamento Total (`total_budget`)

- **Tipo**: `number` (float)
- **Descrição**: Soma dos orçamentos planejados de todos os projetos acessíveis
- **Cálculo**: `SUM(projects.planned_budget_amount)`
- **Fonte**: `Project.planned_budget_amount`

### 5. PVxRV Summary (`pvxr_summary`)

- **Tipo**: `object`
- **Descrição**: Resumo agregado de Planejado vs Realizado de todos os projetos
- **Estrutura**:
  ```json
  {
    "total_planned": 50000.00,
    "total_realized": 35000.00,
    "variance": 15000.00,
    "variance_percentage": 30.0
  }
  ```
- **Cálculo**:
  - `total_planned`: Soma de `CostItem.planned_amount` de todos os budgets dos projetos
  - `total_realized`: Soma de `Expense.amount` onde `status = 'approved'`
  - `variance`: `total_planned - total_realized`
  - `variance_percentage`: `(variance / total_planned) * 100`

### 6. Licenças Vencendo (`expiring_licenses`)

- **Tipo**: `object` (placeholder)
- **Descrição**: Contagem de licenças próximas do vencimento (model License ainda não implementado)
- **Estrutura**:
  ```json
  {
    "expiring_count": 0,
    "expiring_soon_count": 0,
    "days_threshold": 30
  }
  ```
- **Status**: Placeholder retornando valores zero até implementação do model License

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/dashboard
```

### Endpoints Disponíveis

#### 1. Obter Estatísticas do Dashboard

```http
GET /api/v1/dashboard/stats
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `X-Company-Id: {company_id}` (obrigatório)
- `Accept: application/json`

**Query Parameters:**
- `project_id` (opcional): Filtrar estatísticas para um projeto específico

**Validações:**
- Usuário deve estar autenticado
- Usuário deve ter acesso à company informada
- Se `project_id` fornecido, usuário deve ter acesso ao projeto

**Resposta:**
```json
{
  "avg_progress": 75,
  "overdue_tasks_count": 5,
  "upcoming_deliveries_count": 12,
  "total_budget": 500000.00,
  "pvxr_summary": {
    "total_planned": 50000.00,
    "total_realized": 35000.00,
    "variance": 15000.00,
    "variance_percentage": 30.0
  },
  "expiring_licenses": {
    "expiring_count": 0,
    "expiring_soon_count": 0,
    "days_threshold": 30
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `401` - Não autenticado
- `403` - Sem permissão (company ou project)
- `422` - Erro de validação

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso ao Dashboard requer:**
- Autenticação via Sanctum (token válido)
- Header `X-Company-Id` com company_id válido
- Usuário deve ser membro da company informada
- Se `project_id` fornecido, usuário deve ter acesso ao projeto

**Escopo de Dados:**
- Dashboard mostra apenas projetos onde o usuário é membro
- Dados são agregados apenas dos projetos acessíveis
- Filtro por `project_id` limita ainda mais o escopo

### Validações

#### Validação de Company

1. **Company existe**: ✅ Verificado via `whereKey($companyId)->exists()`
2. **Usuário é membro**: ✅ Verificado via `user->companies()->whereKey($companyId)->exists()`
3. **Company não existe ou usuário não é membro**: ❌ Retorna 403

#### Validação de Project (quando fornecido)

1. **Project existe**: ✅ Verificado implicitamente na query
2. **Usuário tem acesso**: ✅ Verificado via `whereHas('users')`
3. **Project não existe ou sem acesso**: ❌ Não aparece nos resultados (vazio)

### Cache

#### Configuração

- **TTL**: 10 minutos (600 segundos)
- **Chave**: `dashboard.stats:user:{userId}:company:{companyId}:project:{projectId?}`
- **Driver**: Configurado em `config/cache.php` (Redis recomendado)

#### Invalidação Automática

Cache é invalidado automaticamente quando:
- **Expense** é criado/atualizado/deletado (via `ExpenseObserver`)
- **CostItem** é criado/atualizado/deletado (via `CostItemObserver`)
- **Task** é criado/atualizado/deletado (via `TaskObserver`)

#### Invalidação Manual

```php
// Limpar cache para um projeto específico
DashboardController::clearCacheForProject($projectId);

// Limpar cache para usuário/company específico
DashboardController::clearCache($userId, $companyId, $projectId);
```

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/dashboard.ts

export interface DashboardStats {
  avg_progress: number;
  overdue_tasks_count: number;
  upcoming_deliveries_count: number;
  total_budget: number;
  pvxr_summary: {
    total_planned: number;
    total_realized: number;
    variance: number;
    variance_percentage: number;
  };
  expiring_licenses: {
    expiring_count: number;
    expiring_soon_count: number;
    days_threshold: number;
  };
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/dashboardService.ts

import { DashboardStats } from '@/types/dashboard';
import { api } from '@/utils/api';

export const dashboardService = {
  async getStats(companyId: number, projectId?: number): Promise<DashboardStats> {
    const params = projectId ? { project_id: projectId } : {};
    const response = await api.get('/dashboard/stats', {
      params,
      headers: {
        'X-Company-Id': companyId,
      },
    });
    return response.data;
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useDashboardStats.ts

import { useQuery } from '@tanstack/react-query';
import { dashboardService } from '@/services/dashboardService';

export function useDashboardStats(companyId: number, projectId?: number) {
  return useQuery({
    queryKey: ['dashboard', 'stats', companyId, projectId],
    queryFn: () => dashboardService.getStats(companyId, projectId),
    staleTime: 5 * 60 * 1000, // 5 minutos (cache do frontend)
    cacheTime: 10 * 60 * 1000, // 10 minutos
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/DashboardStats.tsx

import { useDashboardStats } from '@/hooks/useDashboardStats';
import { useCompany } from '@/hooks/useCompany';

export function DashboardStats() {
  const { company } = useCompany();
  const { data: stats, isLoading, error } = useDashboardStats(company?.id);

  if (isLoading) return <div>Carregando...</div>;
  if (error) return <div>Erro ao carregar estatísticas</div>;
  if (!stats) return null;

  return (
    <div className="dashboard-stats">
      <div className="stat-card">
        <h3>Progresso Médio</h3>
        <p>{stats.avg_progress}%</p>
      </div>
      
      <div className="stat-card">
        <h3>Tarefas Atrasadas</h3>
        <p>{stats.overdue_tasks_count}</p>
      </div>
      
      <div className="stat-card">
        <h3>Entregas Próximas</h3>
        <p>{stats.upcoming_deliveries_count}</p>
      </div>
      
      <div className="stat-card">
        <h3>Orçamento Total</h3>
        <p>R$ {stats.total_budget.toLocaleString('pt-BR')}</p>
      </div>
      
      <div className="stat-card">
        <h3>PVxRV</h3>
        <p>Planejado: R$ {stats.pvxr_summary.total_planned.toLocaleString('pt-BR')}</p>
        <p>Realizado: R$ {stats.pvxr_summary.total_realized.toLocaleString('pt-BR')}</p>
        <p>Variação: {stats.pvxr_summary.variance_percentage.toFixed(2)}%</p>
      </div>
    </div>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Obter Estatísticas Gerais

```typescript
const stats = await dashboardService.getStats(companyId);
console.log(`Progresso médio: ${stats.avg_progress}%`);
console.log(`Tarefas atrasadas: ${stats.overdue_tasks_count}`);
```

### Exemplo 2: Filtrar por Projeto Específico

```typescript
const stats = await dashboardService.getStats(companyId, projectId);
// Retorna estatísticas apenas do projeto especificado
```

### Exemplo 3: Calcular Variação Percentual PVxRV

```typescript
const stats = await dashboardService.getStats(companyId);
const { pvxr_summary } = stats;

if (pvxr_summary.variance_percentage > 0) {
  console.log(`Projeto está ${pvxr_summary.variance_percentage}% abaixo do planejado`);
} else if (pvxr_summary.variance_percentage < 0) {
  console.log(`Projeto está ${Math.abs(pvxr_summary.variance_percentage)}% acima do planejado`);
} else {
  console.log('Projeto está exatamente no planejado');
}
```

### Exemplo 4: Monitorar Tarefas Atrasadas

```typescript
const stats = await dashboardService.getStats(companyId);

if (stats.overdue_tasks_count > 10) {
  // Enviar alerta para gestores
  sendAlert('Muitas tarefas atrasadas', stats.overdue_tasks_count);
}
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Project Scope**: Filtro opcional por `project_id` na query
- **Permissão**: Usuário deve ser membro da company e ter acesso aos projetos

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Company ID**: Verificar se está presente antes de fazer requisição
2. **Loading States**: Mostrar indicadores de carregamento durante requisições
3. **Error Handling**: Tratar erros 403 (sem permissão) e 401 (não autenticado)
4. **Cache**: Usar React Query para cache no frontend (5-10 minutos)

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Widget de Licenças**: Implementar quando model License estiver disponível
2. **Gráficos**: Adicionar endpoints para dados de gráficos (progresso ao longo do tempo, curva S)
3. **Filtros Avançados**: Permitir filtrar por período, status de projeto, etc.
4. **Exportação**: Permitir exportar estatísticas em CSV/PDF
5. **Widgets Customizáveis**: Permitir usuários escolherem quais widgets ver

### Considerações para Implementação

- **Performance**: Cache atual de 10 minutos pode ser ajustado conforme necessidade
- **Escalabilidade**: Considerar cache distribuído (Redis) para múltiplos servidores
- **Real-time**: Considerar WebSockets para atualizações em tempo real (opcional)

---

## 📚 Referências

- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Controller: `app/Http/Controllers/DashboardController.php`
- Tests: `tests/Feature/DashboardTest.php`
- [Documentação de Expenses](./EXPENSES.md) - Para detalhes sobre PVxRV

---

## ❓ FAQ

### P: O cache é invalidado automaticamente?

**R:** Sim, o cache é invalidado automaticamente quando há mudanças em Expenses, CostItems ou Tasks através dos observers do Laravel.

### P: Posso filtrar por múltiplos projetos?

**R:** Atualmente, apenas um `project_id` pode ser fornecido por vez. Para múltiplos projetos, faça requisições separadas ou use o endpoint sem filtro para ver todos os projetos.

### P: Como funciona o cálculo de progresso médio?

**R:** O progresso médio é calculado como a média aritmética do `progress_percent` de todos os projetos acessíveis. O `progress_percent` de cada projeto é calculado a partir das fases ativas.

### P: O widget de licenças sempre retorna zero?

**R:** Sim, atualmente é um placeholder. Quando o model License for implementado, o widget será atualizado para retornar dados reais.

### P: O cache funciona com qualquer driver de cache?

**R:** Sim, o cache funciona com qualquer driver configurado no Laravel (file, redis, memcached, etc.). Redis é recomendado para produção.

---

**Última atualização:** 2025-12-30  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

