# Feature: Exportação de Relatórios CSV

Este documento descreve a funcionalidade de **Exportação de Relatórios CSV** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura](#arquitetura)
3. [Tipos de Relatórios](#tipos-de-relatórios)
4. [API Endpoints](#api-endpoints)
5. [Regras de Negócio](#regras-de-negócio)
6. [Integração Frontend](#integração-frontend)
7. [Exemplos Práticos](#exemplos-práticos)

---

## 🎯 Visão Geral

**Exportação de Relatórios CSV** permite que usuários exportem dados do sistema em formato CSV para análise externa, auditoria e integração com outras ferramentas.

### Objetivos

- Permitir exportação de dados para análise em planilhas (Excel, Google Sheets)
- Facilitar auditoria e relatórios externos
- Suportar integração manual com sistemas externos
- Processar grandes volumes de dados de forma assíncrona

### Características Principais

- ✅ Processamento assíncrono via jobs
- ✅ Chunking para grandes datasets (1000 registros por vez)
- ✅ Encoding UTF-8 BOM para compatibilidade Excel/pt-BR
- ✅ Notificações automáticas quando o export está pronto
- ✅ Download seguro com validação de permissões
- ✅ Limpeza automática de arquivos antigos (>7 dias)
- ✅ Suporte a múltiplos filtros por tipo de relatório

---

## 🏗️ Arquitetura

### Componentes Principais

1. **BaseCsvExportJob** - Classe base abstrata para todos os jobs de exportação
2. **Jobs Específicos** - Implementações para cada tipo de relatório (TasksCsvExportJob, etc.)
3. **ReportsController** - Controller para gerenciar exports e downloads
4. **Storage** - Armazenamento de arquivos CSV em `storage/app/exports/`

### Fluxo de Exportação

```
1. Usuário solicita export via POST /api/v1/reports/{type}/export
2. Controller valida permissões e dispatches job assíncrono
3. Job processa dados em chunks e gera arquivo CSV
4. Job salva arquivo em storage e cria notificação
5. Usuário recebe notificação com link de download
6. Usuário baixa arquivo via GET /api/v1/reports/download/{filename}
```

### Processamento em Chunks

Para otimizar memória e performance, os dados são processados em chunks de 1000 registros:

```php
$query->chunk(1000, function ($rows) use ($handle) {
    foreach ($rows as $row) {
        $csvRow = $this->formatRow($row);
        fputcsv($handle, $csvRow, ';');
    }
});
```

---

## 📊 Tipos de Relatórios

### Relatórios Implementados

#### 1. Tasks (Tarefas)
- **Tipo**: `tasks`
- **Job**: `TasksCsvExportJob`
- **Filtros**: `project_id`, `phase_id`, `status`, `assignee_id`, `start_date`, `end_date`, `overdue`
- **Campos**: ID, Obra, Fase, Título, Responsável, Status, Prioridade, Datas, Atraso

### Relatórios Planejados

#### 2. Progress (Progresso)
- **Tipo**: `progress`
- **Filtros**: `project_id`, `start_date`, `end_date`
- **Campos**: Obra, Fase, Total Tarefas, Progresso Fase (%), Progresso Obra (%)

#### 3. PVxRV (Previsto vs Realizado)
- **Tipo**: `pvxrv`
- **Filtros**: `project_id`, `category`, `start_date`, `end_date`
- **Campos**: Obra, Cost Item, Previsto, Realizado, Variação, Variação (%)

#### 4. Expenses (Despesas)
- **Tipo**: `expenses`
- **Filtros**: `project_id`, `supplier_id`, `category`, `start_date`, `end_date`
- **Campos**: ID, Obra, Data, Fornecedor, Categoria, Descrição, Valor

#### 5. Purchase Requests (Requisições de Compra)
- **Tipo**: `purchase-requests`
- **Filtros**: `project_id`, `status`, `start_date`, `end_date`, `requester_id`
- **Campos**: ID, Obra, Solicitante, Status, Total Itens, Valor Estimado

#### 6. Purchase Orders (Pedidos de Compra)
- **Tipo**: `purchase-orders`
- **Filtros**: `project_id`, `supplier_id`, `status`, `start_date`, `end_date`
- **Campos**: ID, Obra, Fornecedor, Status, Total, PR Origem

#### 7. Payments (Pagamentos)
- **Tipo**: `payments`
- **Filtros**: `project_id`, `contractor_id`, `status`, `start_date`, `end_date`
- **Campos**: ID, Obra, Prestador, Referência/Contrato, Vencimento, Valor, Status

#### 8. Contractors (Prestadores)
- **Tipo**: `contractors`
- **Filtros**: `project_id`, `status`
- **Campos**: ID, Nome, CNPJ, Telefone, Email, Status

#### 9. Documents (Documentos)
- **Tipo**: `documents`
- **Filtros**: `project_id`, `category`, `start_date`, `end_date`
- **Campos**: ID, Obra, Categoria, Nome, Tamanho, Upload em

#### 10. Licenses (Licenças)
- **Tipo**: `licenses`
- **Filtros**: `project_id`, `status`, `expiring_days`
- **Campos**: ID, Obra, Arquivo, Data Vencimento, Dias até Vencimento

#### 11. Audit Logs (Logs de Auditoria)
- **Tipo**: `audit-logs`
- **Filtros**: `project_id`, `user_id`, `action`, `start_date`, `end_date`
- **Campos**: ID, Usuário, Ação, Modelo, Data, Dados

---

## 🔌 API Endpoints

### Solicitar Exportação

```http
POST /api/v1/reports/{type}/export
Authorization: Bearer {token}
X-Company-Id: {company_id}
Content-Type: application/json

{
  "project_id": 1,
  "phase_id": 2,
  "status": "in_progress",
  "start_date": "2024-01-01",
  "end_date": "2024-12-31",
  "overdue": true
}
```

**Resposta (202 Accepted):**
```json
{
  "message": "Exportação iniciada. Você receberá uma notificação quando o arquivo estiver pronto.",
  "report_type": "tasks"
}
```

### Download de Arquivo

```http
GET /api/v1/reports/download/{filename}
Authorization: Bearer {token}
X-Company-Id: {company_id}
```

**Resposta (200 OK):**
- Content-Type: `text/csv; charset=UTF-8`
- Content-Disposition: `attachment; filename="{filename}"`
- Body: Arquivo CSV com UTF-8 BOM

---

## 📐 Regras de Negócio

### Permissões

- Todos os usuários autenticados podem solicitar exports
- Usuários só podem baixar seus próprios exports (validação via notificação)
- Exports são filtrados por `company_id` automaticamente

### Validações

1. **Tipo de Relatório**: Deve ser um tipo válido definido em `ReportsController::REPORT_TYPES`
2. **Company ID**: Obrigatório via header `X-Company-Id`
3. **Filtros**: Cada tipo de relatório aceita filtros específicos
4. **Download**: Arquivo deve existir e ter notificação associada ao usuário

### Limpeza Automática

- Arquivos mais antigos que 7 dias são automaticamente removidos
- Limpeza ocorre após cada export bem-sucedido
- Logs são gerados para cada arquivo removido

### Encoding e Formato

- **Encoding**: UTF-8 com BOM (`\xEF\xBB\xBF`) para compatibilidade Excel
- **Separador**: Ponto e vírgula (`;`) para compatibilidade pt-BR
- **Formato de Data**: `dd/mm/yyyy` ou `dd/mm/yyyy HH:mm` conforme o campo
- **Headers**: Sempre em português (pt-BR)

---

## 💻 Integração Frontend

### TypeScript Types

```typescript
export type ReportType = 
  | 'tasks'
  | 'progress'
  | 'pvxrv'
  | 'expenses'
  | 'purchase-requests'
  | 'purchase-orders'
  | 'payments'
  | 'contractors'
  | 'documents'
  | 'licenses'
  | 'audit-logs';

export interface ExportFilters {
  project_id?: number;
  phase_id?: number;
  status?: string | string[];
  assignee_id?: number;
  start_date?: string;
  end_date?: string;
  overdue?: boolean;
  supplier_id?: number;
  contractor_id?: number;
  category?: string;
  // ... outros filtros específicos por tipo
}

export interface ExportRequest {
  type: ReportType;
  filters?: ExportFilters;
}

export interface ExportNotification {
  id: number;
  type: 'export.completed';
  data: {
    export_type: ReportType;
    filename: string;
    download_url: string;
    row_count: number;
    expires_at: string;
  };
  read_at: string | null;
  created_at: string;
}
```

### Service Functions

```typescript
export const reportsService = {
  /**
   * Solicita exportação de um relatório
   */
  async requestExport(
    type: ReportType,
    filters?: ExportFilters
  ): Promise<{ message: string; report_type: string }> {
    const response = await fetch(`/api/v1/reports/${type}/export`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Company-Id': getCompanyId(),
        Authorization: `Bearer ${getToken()}`,
      },
      body: JSON.stringify(filters),
    });

    if (!response.ok) {
      throw new Error('Falha ao solicitar exportação');
    }

    return response.json();
  },

  /**
   * Baixa um arquivo CSV exportado
   */
  async downloadFile(filename: string): Promise<void> {
    const response = await fetch(`/api/v1/reports/download/${filename}`, {
      headers: {
        'X-Company-Id': getCompanyId(),
        Authorization: `Bearer ${getToken()}`,
      },
    });

    if (!response.ok) {
      throw new Error('Falha ao baixar arquivo');
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  },
};
```

### React Hook

```typescript
import { useMutation } from '@tanstack/react-query';
import { reportsService } from '@/api/reports';

export function useRequestExport() {
  return useMutation({
    mutationFn: ({ type, filters }: ExportRequest) =>
      reportsService.requestExport(type, filters),
    onSuccess: () => {
      // Mostrar toast de sucesso
      toast.success('Exportação iniciada. Você receberá uma notificação quando estiver pronto.');
    },
    onError: (error) => {
      toast.error('Erro ao solicitar exportação');
    },
  });
}

export function useDownloadExport() {
  return useMutation({
    mutationFn: (filename: string) => reportsService.downloadFile(filename),
    onSuccess: () => {
      toast.success('Arquivo baixado com sucesso');
    },
    onError: () => {
      toast.error('Erro ao baixar arquivo');
    },
  });
}
```

### Componente de Exportação

```typescript
import { useState } from 'react';
import { useRequestExport } from '@/hooks/useRequestExport';
import { ReportType } from '@/types/reports';

interface ExportButtonProps {
  type: ReportType;
  filters?: ExportFilters;
}

export function ExportButton({ type, filters }: ExportButtonProps) {
  const [isExporting, setIsExporting] = useState(false);
  const requestExport = useRequestExport();

  const handleExport = async () => {
    setIsExporting(true);
    try {
      await requestExport.mutateAsync({ type, filters });
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <Button
      onPress={handleExport}
      disabled={isExporting}
      loading={isExporting}
      icon="download"
    >
      {isExporting ? 'Exportando...' : 'Exportar CSV'}
    </Button>
  );
}
```

### Tratamento de Notificações

```typescript
import { useNotifications } from '@/hooks/useNotifications';

export function ExportNotificationHandler() {
  const { notifications } = useNotifications();
  const downloadExport = useDownloadExport();

  const exportNotifications = notifications.filter(
    (n) => n.type === 'export.completed' && !n.read_at
  );

  return (
    <>
      {exportNotifications.map((notification) => (
        <NotificationCard
          key={notification.id}
          notification={notification}
          onAction={() => {
            downloadExport.mutate(notification.data.filename);
          }}
          actionLabel="Baixar CSV"
        />
      ))}
    </>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Exportar Tarefas de um Projeto

```typescript
// Solicitar export
const response = await reportsService.requestExport('tasks', {
  project_id: 1,
  status: 'in_progress',
  start_date: '2024-01-01',
  end_date: '2024-12-31',
});

// Aguardar notificação
// Quando notificação chegar, baixar arquivo
await reportsService.downloadFile('tasks_2024-01-01_12345678_abcdefgh.csv');
```

### Exemplo 2: Exportar Despesas com Filtros

```typescript
await reportsService.requestExport('expenses', {
  project_id: 1,
  supplier_id: 5,
  category: 'material',
  start_date: '2024-01-01',
  end_date: '2024-03-31',
});
```

### Exemplo 3: Exportar Apenas Tarefas Atrasadas

```typescript
await reportsService.requestExport('tasks', {
  project_id: 1,
  overdue: true,
});
```

---

## 🔒 Segurança

### Validações de Segurança

1. **Autenticação**: Todos os endpoints requerem token Sanctum
2. **Company Scope**: Exports são automaticamente filtrados por `company_id`
3. **Download**: Usuários só podem baixar seus próprios exports (validação via notificação)
4. **File Validation**: Arquivo deve existir e ter notificação associada

### Boas Práticas

- Sempre validar `company_id` no frontend antes de solicitar export
- Não armazenar URLs de download permanentemente (expirem após 7 dias)
- Limpar arquivos baixados após uso
- Validar tipo de relatório antes de solicitar export

---

## 📚 Referências

- [Laravel Jobs Documentation](https://laravel.com/docs/queues)
- [Laravel Storage Documentation](https://laravel.com/docs/filesystem)
- [CSV UTF-8 BOM Specification](https://en.wikipedia.org/wiki/Byte_order_mark)

---

## 🚀 Próximos Passos

- [ ] Implementar jobs para relatórios restantes (Progress, PVxRV, Expenses, etc.)
- [ ] Adicionar suporte a exportação em background com progress tracking
- [ ] Implementar validação de CSV antes de notificar usuário
- [ ] Adicionar suporte a exportação agendada (cron)
- [ ] Implementar compressão de arquivos grandes (ZIP)

