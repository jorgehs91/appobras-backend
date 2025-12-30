# Template: Documentação de Features

Este documento serve como **template e guia de referência** para criar documentações de features no AppObras. Use este template como base para garantir consistência e completude nas documentações.

> **Referência:** Esta documentação segue o padrão estabelecido em [EXPENSES.md](./EXPENSES.md)

---

## 📋 Estrutura Obrigatória

Toda documentação de feature DEVE seguir esta estrutura mínima:

1. [Título e Introdução](#título-e-introdução)
2. [Índice](#índice)
3. [Visão Geral](#visão-geral)
4. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
5. [Modelo de Dados](#modelo-de-dados)
6. [API Endpoints](#api-endpoints)
7. [Regras de Negócio](#regras-de-negócio)
8. [Integração Frontend](#integração-frontend)
9. [Exemplos Práticos](#exemplos-práticos)

---

## 📝 Template Completo

### 1. Título e Introdução

```markdown
# Feature: [Nome da Feature]

Este documento descreve a funcionalidade de **[Nome da Feature]** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.
```

**Diretrizes:**
- Use o nome oficial da feature (mesmo do model/controller)
- Descrição deve ser clara e direta
- Mencione que inclui guias para frontend

---

### 2. Índice

```markdown
## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Entidades e Relacionamentos](#entidades-e-relacionamentos)
3. [Modelo de Dados](#modelo-de-dados)
4. [Status e Workflow](#status-e-workflow) <!-- Opcional: apenas se houver status/enum -->
5. [Casos de Uso](#casos-de-uso)
6. [API Endpoints](#api-endpoints)
7. [Regras de Negócio](#regras-de-negócio)
8. [Integração Frontend](#integração-frontend)
9. [Exemplos Práticos](#exemplos-práticos)

---
```

**Diretrizes:**
- Use emojis para facilitar navegação visual
- Links devem usar IDs de seção (lowercase, hífens)
- Inclua separador `---` após o índice

---

### 3. Visão Geral

```markdown
## 🎯 Visão Geral

**[Nome da Feature]** representa [descrição concisa do que a feature faz no sistema].

### Objetivos

- Objetivo 1 (benefício claro)
- Objetivo 2 (benefício claro)
- Objetivo 3 (benefício claro)

### Características Principais

- ✅ Característica 1
- ✅ Característica 2
- ✅ Característica 3
- ✅ Característica 4

---
```

**Diretrizes:**
- **Objetivos**: Foque em **por que** a feature existe, não **como** funciona
- **Características**: Liste features técnicas importantes (RBAC, soft deletes, etc.)
- Use checkmarks (✅) para características implementadas

---

### 4. Entidades e Relacionamentos

```markdown
## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── [Feature]
          └── [Relacionamento 1]?
          └── [Relacionamento 2]
```

### Relacionamentos

#### [Feature] → [Entidade] ([Tipo])
- **Tipo**: `BelongsTo` / `HasMany` / `HasOne` / `BelongsToMany`
- **Cardinalidade**: N:1, 1:N, 1:1, N:M
- **Campo**: `campo_id` ou tabela pivot
- **Descrição**: Explicação clara do relacionamento

#### [Feature] → [Outra Entidade] ([Obrigatório/Opcional])
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campo**: `campo_id`
- **Descrição**: Descrição do relacionamento

### Fluxo Conceitual

```
1. [Etapa 1]
   └── [Descrição]

2. [Etapa 2]
   └── [Descrição]

3. [Etapa 3]
   └── [Descrição]
```

---
```

**Diretrizes:**
- **Diagrama**: Use ASCII art simples, mostrando hierarquia
- **Relacionamentos**: Liste TODOS os relacionamentos do model
- **Fluxo Conceitual**: Explique como a feature se encaixa no processo de negócio
- Use `?` para relacionamentos opcionais

---

### 5. Modelo de Dados

```markdown
## 📊 Modelo de Dados

### Tabela: `nome_da_tabela`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `campo1` | tipo | Descrição | Sim/Não | Observações importantes |
| `campo2` | tipo | Descrição | Sim/Não | Observações importantes |

### Índices

- `campo1` - Para [motivo do índice]
- `[campo1, campo2]` - Composite index para [motivo]

### Constraints

- `campo1 > 0` - Validação aplicada no FormRequest
- `campo2 IN ('valor1', 'valor2')` - Enum [NomeEnum]
- [Constraint adicional] - [Explicação]

---
```

**Diretrizes:**
- **Tabela**: Liste TODOS os campos da migration
- **Índices**: Explique o propósito de cada índice
- **Constraints**: Liste validações importantes (DB e código)

---

### 6. Status e Workflow (Opcional)

**Incluir apenas se a feature tiver enum de status ou workflow complexo:**

```markdown
## 🔄 Status e Workflow

### [NomeEnum] Enum

```php
enum [NomeEnum]: string
{
    case valor1 = 'valor1';      // Descrição
    case valor2 = 'valor2'; // Descrição
}
```

### Workflow de Status

```
[status1] ──────> [status2]
  │                   │
  │                   └── Requer: [condição]
  │
  └── [condição para existir]
```

### Transições Permitidas

| De | Para | Condição |
|----|------|----------|
| `status1` | `status2` | Deve ter [condição] |
| `status2` | `status1` | Não recomendado, mas permitido |

### Regras de Validação

1. **Criação em `status1`**: [Regra]
2. **Criação em `status2`**: [Regra]
3. **Atualização para `status2`**: [Regra]

---
```

**Diretrizes:**
- Use diagramas ASCII para workflow
- Explique todas as transições permitidas
- Liste regras de validação por status

---

### 7. Casos de Uso

```markdown
## 💼 Casos de Uso

### Caso 1: [Nome do Caso]

**Cenário**: [Contexto do usuário e situação]

```json
[POST/PUT/GET] /api/v1/[endpoint]
{
  "campo": "valor"
}
```

**Resultado**: [O que acontece após a ação]

---

### Caso 2: [Nome do Caso]

**Cenário**: [Contexto]

```bash
[Comando ou exemplo de código]
```

**Resultado**: [Resultado esperado]

---
```

**Diretrizes:**
- Mínimo de 3-5 casos de uso
- Inclua casos mais comuns primeiro
- Use exemplos reais (não genéricos)
- Mostre tanto requisições quanto resultados

---

### 8. API Endpoints

```markdown
## 🌐 API Endpoints

### Base URL

```
/api/v1/[base-path]
```

### Endpoints Disponíveis

#### 1. [Nome da Ação]

```http
[GET/POST/PUT/PATCH/DELETE] /api/v1/[endpoint]
```

**Query Parameters:** <!-- Se aplicável -->
- `param1` (opcional): Descrição

**Body:** <!-- Se aplicável -->
- `campo1` (obrigatório): Descrição
- `campo2` (opcional): Descrição

**Validações:**
- [Validação 1]
- [Validação 2]

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "campo": "valor"
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `201` - Criado
- `403` - Sem permissão
- `404` - Não encontrado
- `422` - Erro de validação

---

#### 2. [Próximo Endpoint]

[Formato similar]

---
```

**Diretrizes:**
- Liste TODOS os endpoints do controller
- Inclua todos os códigos HTTP possíveis
- Mostre exemplos de request e response
- Documente validações importantes

---

### 9. Regras de Negócio

```markdown
## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a [Feature] requer:**
- Role: `[Role1]` **OU** `[Role2]`
- Verificação no controller via `[método]()`

**Outras roles:** [Comportamento]

### Validações

#### Validação de [Tipo]

1. **[Situação 1]**: ✅/❌ [Comportamento]
2. **[Situação 2]**: ✅/❌ [Comportamento]

#### Validação de [Outro Tipo]

- [Regra 1]
- [Regra 2]

### [Tópico Específico] <!-- Se aplicável -->

#### Configuração

[Como configurar]

#### Estrutura

[Estrutura de dados/pastas/arquivos]

#### Lifecycle

- **Criação**: [O que acontece]
- **Atualização**: [O que acontece]
- **Delete**: [O que acontece]

---
```

**Diretrizes:**
- **RBAC**: Sempre documente permissões
- **Validações**: Agrupe por tipo (Status, Valor, Arquivo, etc.)
- Use ✅/❌ para indicar permitido/bloqueado
- Documente configurações importantes (env vars, etc.)

---

### 10. Integração Frontend

```markdown
## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/[feature].ts

export enum [NomeEnum] {
  VALOR1 = 'valor1',
  VALOR2 = 'valor2',
}

export interface [Feature] {
  id: number;
  campo1: string;
  campo2: number | null;
  created_at: string;
  updated_at: string;
}

export interface Create[Feature]Input {
  campo1: string;
  campo2?: number;
}

export interface Update[Feature]Input {
  campo1?: string;
  campo2?: number;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/[feature]Service.ts

import { [Feature], Create[Feature]Input } from '@/types/[feature]';

export const [feature]Service = {
  async list(projectId: number): Promise<[Feature][]> {
    const response = await api.get(`/projects/${projectId}/[feature]`);
    return response.data.data;
  },

  async create(projectId: number, data: Create[Feature]Input): Promise<[Feature]> {
    const response = await api.post(`/projects/${projectId}/[feature]`, data);
    return response.data.data;
  },

  // ... outros métodos
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/use[Feature].ts

import { useQuery, useMutation } from '@tanstack/react-query';
import { [feature]Service } from '@/services/[feature]Service';

export function use[Feature](projectId: number) {
  return useQuery({
    queryKey: ['[feature]', projectId],
    queryFn: () => [feature]Service.list(projectId),
  });
}

// ... outros hooks
```

### Exemplo de Componente (React)

```typescript
// components/[Feature]Form.tsx

// Exemplo completo de componente funcional
```

---
```

**Diretrizes:**
- **TypeScript**: Inclua enums, interfaces, tipos de input/output
- **Service**: Mostre métodos principais (list, create, update, delete, show)
- **Hooks**: Use React Query padrão
- **Componente**: Um exemplo completo e funcional

---

### 11. Exemplos Práticos

```markdown
## 📝 Exemplos Práticos

### Exemplo 1: [Título do Exemplo]

```typescript
// Código de exemplo completo e funcional
const exemplo = await service.metodo();
```

### Exemplo 2: [Título do Exemplo]

```typescript
// Outro exemplo prático
```

### Exemplo 3: [Título do Exemplo]

```typescript
// Mais um exemplo
```

---
```

**Diretrizes:**
- Mínimo de 3-4 exemplos
- Foque em casos reais de uso
- Mostre código completo e executável
- Inclua exemplos de queries/transformações úteis

---

### 12. Queries Úteis para Frontend (Opcional)

```markdown
## 🔍 Queries Úteis para Frontend

### [Operação Comum]

```typescript
const resultado = dados.reduce((acc, item) => {
  // Transformação útil
}, {});
```

### [Outra Operação]

```typescript
// Código útil
```

---
```

**Diretrizes:**
- Inclua apenas se houver transformações complexas comuns
- Foque em operações que frontend fará frequentemente

---

### 13. Segurança e Permissões

```markdown
## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: `[método]()` - apenas roles `[Role1]` ou `[Role2]`
- **Project Scope**: [Feature] deve pertencer ao projeto informado

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **[Validação 1]**: [Como fazer]
2. **[Validação 2]**: [Como fazer]

---
```

**Diretrizes:**
- Documente TODOS os middlewares aplicados
- Liste validações frontend recomendadas
- Explique scoping (company, project)

---

### 14. Melhorias Futuras

```markdown
## 🚀 Melhorias Futuras

### Planejadas

1. **[Feature futura 1]**: [Descrição breve]
2. **[Feature futura 2]**: [Descrição breve]
3. **[Feature futura 3]**: [Descrição breve]

### Considerações para Implementação

- **[Tópico 1]**: [Consideração]
- **[Tópico 2]**: [Consideração]

---
```

**Diretrizes:**
- Liste apenas melhorias realmente planejadas
- Inclua considerações técnicas relevantes

---

### 15. Referências

```markdown
## 📚 Referências

- [Documentação relacionada](../OUTRO_DOC.md)
- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/[Feature].php`
- Controller: `app/Http/Controllers/[Feature]Controller.php`
- Tests: `tests/Feature/[Feature]ControllerTest.php`

---
```

**Diretrizes:**
- Links para documentação relacionada
- Links para código fonte relevante
- Links para testes

---

### 16. FAQ

```markdown
## ❓ FAQ

### P: [Pergunta frequente 1]?

**R:** [Resposta clara e direta]

### P: [Pergunta frequente 2]?

**R:** [Resposta clara e direta]

---
```

**Diretrizes:**
- Mínimo de 3-5 perguntas frequentes
- Foque em dúvidas reais que desenvolvedores podem ter
- Respostas devem ser práticas e diretas

---

### 17. Rodapé

```markdown
**Última atualização:** YYYY-MM-DD  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado
```

**Diretrizes:**
- Sempre inclua data de atualização
- Versão da API atual
- Status da feature (Implementado, Em Desenvolvimento, etc.)

---

## 🎨 Diretrizes de Formatação

### Emojis para Seções

Use estes emojis consistentemente:

- 📋 Índice
- 🎯 Visão Geral
- 🔗 Relacionamentos
- 📊 Modelo de Dados
- 🔄 Status/Workflow
- 💼 Casos de Uso
- 🌐 API Endpoints
- 📐 Regras de Negócio
- 💻 Frontend/Integração
- 📝 Exemplos
- 🔍 Queries
- 🔐 Segurança
- 🚀 Melhorias
- 📚 Referências
- ❓ FAQ

### Código

- **PHP**: Use blocos de código com `php`
- **TypeScript**: Use blocos de código com `typescript`
- **JSON**: Use blocos de código com `json`
- **HTTP**: Use blocos de código com `http`
- **Bash**: Use blocos de código com `bash`
- **Markdown**: Use blocos de código com `markdown`

### Tabelas

- Use tabelas Markdown para estruturas de dados
- Alinhe colunas quando possível
- Use `|` para separar colunas

### Separadores

- Use `---` para separar seções principais
- Use `---` após índice
- Use `---` antes de referências/FAQ

### Destaques

- **Negrito**: Para termos importantes, nomes de features, métodos
- `Código inline`: Para nomes de arquivos, variáveis, campos
- ✅: Para características implementadas, permitido
- ❌: Para bloqueado, não permitido
- ⚠️: Para avisos, não recomendado

---

## ✅ Checklist de Qualidade

Antes de finalizar uma documentação, verifique:

- [ ] Todas as seções obrigatórias estão presentes
- [ ] Índice está atualizado e com links funcionais
- [ ] Diagrama de relacionamentos está correto
- [ ] TODOS os campos da tabela estão documentados
- [ ] TODOS os endpoints estão documentados
- [ ] Exemplos de código são funcionais e testáveis
- [ ] TypeScript types estão completos
- [ ] RBAC e permissões estão claramente documentados
- [ ] FAQ cobre perguntas comuns
- [ ] Referências estão corretas e acessíveis
- [ ] Data de atualização está correta
- [ ] Emojis estão consistentes
- [ ] Código está formatado corretamente
- [ ] Sem erros de ortografia/gramática

---

## 📖 Exemplo de Referência

Para ver este template em prática, consulte:
- **[EXPENSES.md](./EXPENSES.md)** - Implementação completa seguindo este template

---

**Última atualização:** 2025-12-29  
**Versão do Template:** 1.0  
**Status:** ✅ Ativo

