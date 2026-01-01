# Feature: Attachments (Anexos de Tarefas)

Este documento descreve a funcionalidade de **Attachments (Anexos de Tarefas)** do sistema AppObras, incluindo arquitetura, regras de negócio, casos de uso e guias para desenvolvimento frontend.

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

**Attachments** representam anexos específicos de tarefas (fotos de progresso, documentos técnicos, etc.). Cada anexo está vinculado a uma tarefa específica e pode ser usado para documentar o progresso ou fornecer informações adicionais relacionadas à execução da tarefa.

### Objetivos

- Permitir anexar arquivos (fotos, documentos) a tarefas específicas
- Documentar o progresso visual das tarefas através de fotos
- Compartilhar documentos técnicos e informações relacionadas à execução
- Facilitar a comunicação e documentação do trabalho realizado

### Características Principais

- ✅ Anexos por tarefa
- ✅ Upload de múltiplos tipos de arquivo (PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, ZIP, RAR)
- ✅ Suporte a thumbnails (preparado para futuras implementações)
- ✅ Armazenamento flexível (local ou S3)
- ✅ Auditoria completa (created_by, updated_by)
- ✅ Soft deletes
- ✅ Rastreamento do usuário que fez upload (user_id)

---

## 🔗 Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
Company
  └── Project
      └── Phase
          └── Task
              └── Attachment (anexos da tarefa)
                  └── User (uploader)
```

### Relacionamentos

#### Attachment → Task (Obrigatório)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (muitos anexos para uma tarefa)
- **Campo**: `task_id`
- **Descrição**: Todo anexo pertence a uma tarefa específica

#### Attachment → User (Obrigatório)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1 (um anexo pertence a um usuário que fez upload)
- **Campo**: `user_id` (nullable, mas preenchido no upload)
- **Descrição**: Identifica quem fez upload do anexo

#### Attachment → User (Criação/Atualização - Audit)
- **Tipo**: `BelongsTo`
- **Cardinalidade**: N:1
- **Campos**: `created_by`, `updated_by`
- **Descrição**: Rastreamento de quem criou/atualizou o registro do anexo

### Fluxo Conceitual

```
1. Execução da Tarefa
   └── Usuário executa uma tarefa no projeto

2. Upload de Anexo
   └── Usuário anexa foto/documento para documentar o progresso
       ├── Arquivo é salvo no storage (local ou S3)
       └── Metadata é salva no banco de dados

3. Visualização/Download
   └── Usuários com acesso à tarefa podem visualizar/baixar anexos
```

---

## 📊 Modelo de Dados

### Tabela: `attachments`

| Campo | Tipo | Descrição | Obrigatório | Observações |
|-------|------|-----------|-------------|-------------|
| `id` | bigint | Identificador único | Sim | Primary key, auto-increment |
| `task_id` | bigint | ID da tarefa | Sim | FK para `tasks.id` |
| `user_id` | bigint | ID do usuário que fez upload | Não | FK para `users.id` (nullable, mas preenchido) |
| `filename` | string | Nome original do arquivo | Sim | Máximo 255 caracteres |
| `path` | string | Caminho do arquivo no storage | Sim | Path no storage (local/S3) |
| `mime_type` | string | Tipo MIME do arquivo | Não | Ex: application/pdf, image/jpeg |
| `size` | integer | Tamanho do arquivo em bytes | Sim | Tamanho em bytes |
| `thumbnail_path` | string | Caminho do thumbnail | Não | Path do thumbnail (nullable, futuro) |
| `created_by` | bigint | ID do usuário criador | Não | FK para `users.id` (nullable, audit) |
| `updated_by` | bigint | ID do usuário atualizador | Não | FK para `users.id` (nullable, audit) |
| `created_at` | timestamp | Data de criação | Sim | Automático |
| `updated_at` | timestamp | Data de atualização | Sim | Automático |
| `deleted_at` | timestamp | Data de exclusão | Não | Soft delete |

### Índices

- `task_id` - Para filtragem rápida por tarefa
- `user_id` - Para filtragem por usuário que fez upload
- `created_at` - Para ordenação cronológica

### Constraints

- `task_id` deve existir em `tasks.id` (foreign key com cascade)
- `user_id` deve existir em `users.id` (foreign key com set null)
- `size` deve ser > 0 (validação aplicada no FormRequest)
- Arquivo máximo: 10MB (validação aplicada no FormRequest)

---

## 💼 Casos de Uso

### Caso 1: Upload de Foto de Progresso

**Cenário**: Um engenheiro quer documentar o progresso de uma tarefa com uma foto.

```http
POST /api/v1/tasks/123/attachments
Content-Type: multipart/form-data
X-Company-Id: 1

file: [arquivo.jpg]
```

**Resultado**: Foto é salva no storage e metadata é registrada no banco. Anexo fica disponível para visualização por todos os membros do projeto.

---

### Caso 2: Listar Anexos de uma Tarefa

**Cenário**: Um membro do projeto quer ver todos os anexos de uma tarefa específica.

```http
GET /api/v1/tasks/123/attachments
X-Company-Id: 1
```

**Resultado**: Retorna lista de todos os anexos da tarefa, ordenados por data de criação (mais recentes primeiro).

---

### Caso 3: Download de Anexo

**Cenário**: Um membro do projeto precisa baixar um documento técnico anexado a uma tarefa.

```http
GET /api/v1/attachments/456/download
X-Company-Id: 1
```

**Resultado**: Arquivo é retornado para download com o tipo MIME correto.

---

### Caso 4: Remover Anexo

**Cenário**: Um usuário quer remover um anexo que foi enviado por engano.

```http
DELETE /api/v1/attachments/456
X-Company-Id: 1
```

**Resultado**: Anexo é removido (soft delete) e arquivo é excluído do storage.

---

## 🌐 API Endpoints

### Base URL

```
/api/v1/tasks/{task}/attachments
/api/v1/attachments/{attachment}
```

### Endpoints Disponíveis

#### 1. Listar Anexos de uma Tarefa

```http
GET /api/v1/tasks/{task}/attachments
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `X-Company-Id: {company_id}` (obrigatório)

**Path Parameters:**
- `task` (obrigatório): ID da tarefa

**Validações:**
- Usuário deve estar autenticado
- Usuário deve ter acesso à company
- Tarefa deve pertencer à company
- Usuário deve ser membro do projeto da tarefa

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "task_id": 123,
      "filename": "progresso.jpg",
      "mime_type": "image/jpeg",
      "size": 245678,
      "thumbnail_path": null,
      "user_id": 5,
      "user_name": "João Silva",
      "created_at": "2026-01-01T10:30:00Z",
      "updated_at": "2026-01-01T10:30:00Z"
    }
  ]
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Tarefa não encontrada

---

#### 2. Upload de Anexo

```http
POST /api/v1/tasks/{task}/attachments
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `X-Company-Id: {company_id}` (obrigatório)
- `Content-Type: multipart/form-data` (obrigatório)

**Path Parameters:**
- `task` (obrigatório): ID da tarefa

**Body (multipart/form-data):**
- `file` (obrigatório): Arquivo a ser enviado
  - Tipos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, ZIP, RAR
  - Tamanho máximo: 10MB

**Validações:**
- Arquivo é obrigatório
- Arquivo deve ser um tipo válido (mimes)
- Arquivo não pode exceder 10MB
- Usuário deve estar autenticado
- Usuário deve ter acesso à company
- Tarefa deve pertencer à company
- Usuário deve ser membro do projeto da tarefa

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "task_id": 123,
    "filename": "progresso.jpg",
    "mime_type": "image/jpeg",
    "size": 245678,
    "thumbnail_path": null,
    "user_id": 5,
    "user_name": "João Silva",
    "created_at": "2026-01-01T10:30:00Z",
    "updated_at": "2026-01-01T10:30:00Z"
  }
}
```

**Códigos HTTP:**
- `201` - Criado com sucesso
- `403` - Sem permissão
- `404` - Tarefa não encontrada
- `422` - Erro de validação

---

#### 3. Obter Anexo Específico

```http
GET /api/v1/attachments/{attachment}
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `X-Company-Id: {company_id}` (obrigatório)

**Path Parameters:**
- `attachment` (obrigatório): ID do anexo

**Validações:**
- Usuário deve estar autenticado
- Usuário deve ter acesso à company
- Anexo deve pertencer a uma tarefa da company
- Usuário deve ser membro do projeto da tarefa

**Resposta:**
```json
{
  "data": {
    "id": 1,
    "task_id": 123,
    "filename": "progresso.jpg",
    "mime_type": "image/jpeg",
    "size": 245678,
    "thumbnail_path": null,
    "user_id": 5,
    "user_name": "João Silva",
    "created_at": "2026-01-01T10:30:00Z",
    "updated_at": "2026-01-01T10:30:00Z"
  }
}
```

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Anexo não encontrado

---

#### 4. Download de Anexo

```http
GET /api/v1/attachments/{attachment}/download
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `X-Company-Id: {company_id}` (obrigatório)

**Path Parameters:**
- `attachment` (obrigatório): ID do anexo

**Validações:**
- Usuário deve estar autenticado
- Usuário deve ter acesso à company
- Anexo deve pertencer a uma tarefa da company
- Usuário deve ser membro do projeto da tarefa
- Arquivo deve existir no storage

**Resposta:**
- Content-Type: baseado no `mime_type` do anexo
- Body: Arquivo binário

**Códigos HTTP:**
- `200` - Sucesso
- `403` - Sem permissão
- `404` - Anexo não encontrado ou arquivo não existe no storage

---

#### 5. Remover Anexo

```http
DELETE /api/v1/attachments/{attachment}
```

**Headers:**
- `Authorization: Bearer {token}` (obrigatório)
- `X-Company-Id: {company_id}` (obrigatório)

**Path Parameters:**
- `attachment` (obrigatório): ID do anexo

**Validações:**
- Usuário deve estar autenticado
- Usuário deve ter acesso à company
- Anexo deve pertencer a uma tarefa da company
- Usuário deve ser membro do projeto da tarefa **OU** usuário deve ser o autor do anexo

**Resposta:**
- Sem corpo (204 No Content)

**Códigos HTTP:**
- `204` - Removido com sucesso
- `403` - Sem permissão
- `404` - Anexo não encontrado

---

## 📐 Regras de Negócio

### RBAC (Permissões)

**Acesso a Attachments requer:**
- Role: Qualquer usuário autenticado que seja membro do projeto
- Verificação no controller via middleware `auth:sanctum` e validação de company/project membership

**Outras roles:** Apenas membros do projeto podem visualizar/anexar arquivos

### Validações

#### Validação de Arquivo

1. **Tipo de arquivo**: ✅ Permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, ZIP, RAR
2. **Tamanho máximo**: ✅ 10MB (10240 KB)
3. **Arquivo obrigatório**: ✅ Deve ser fornecido no upload

#### Validação de Acesso

1. **Visualização**: ✅ Usuário deve ser membro do projeto da tarefa
2. **Upload**: ✅ Usuário deve ser membro do projeto da tarefa
3. **Remoção**: ✅ Usuário deve ser membro do projeto **OU** autor do anexo

### Armazenamento

#### Configuração

O storage pode ser configurado através da variável de ambiente `TASK_ATTACHMENTS_DISK`:

- **Local (padrão)**: `TASK_ATTACHMENTS_DISK=local` ou omitir a variável
- **S3**: `TASK_ATTACHMENTS_DISK=s3`

Para usar S3, configure as credenciais AWS no `.env`:
```
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
```

#### Estrutura de Pastas

**Local:**
```
storage/app/attachments/task-{id}/nome_arquivo.ext
```

**S3:**
```
bucket-name/
  └── attachments/
      └── task-{id}/
          └── nome_arquivo.ext
```

#### Lifecycle

- **Criação**: Arquivo é salvo no storage e metadata é registrada no banco
- **Atualização**: Não permitida (anexos são imutáveis após upload)
- **Delete**: Arquivo é removido do storage e registro recebe soft delete

---

## 💻 Integração Frontend

### Estrutura de Dados TypeScript

```typescript
// types/attachment.ts

export interface Attachment {
  id: number;
  task_id: number;
  filename: string;
  mime_type: string | null;
  size: number;
  thumbnail_path: string | null;
  user_id: number | null;
  user_name?: string;
  created_at: string;
  updated_at: string;
}

export interface CreateAttachmentInput {
  file: File;
}
```

### Exemplo de Service (React/TypeScript)

```typescript
// services/attachmentService.ts

import { Attachment, CreateAttachmentInput } from '@/types/attachment';
import api from './api';

export const attachmentService = {
  async list(taskId: number): Promise<Attachment[]> {
    const response = await api.get(`/tasks/${taskId}/attachments`);
    return response.data.data;
  },

  async create(taskId: number, data: CreateAttachmentInput): Promise<Attachment> {
    const formData = new FormData();
    formData.append('file', data.file);

    const response = await api.post(`/tasks/${taskId}/attachments`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data.data;
  },

  async show(attachmentId: number): Promise<Attachment> {
    const response = await api.get(`/attachments/${attachmentId}`);
    return response.data.data;
  },

  async download(attachmentId: number): Promise<Blob> {
    const response = await api.get(`/attachments/${attachmentId}/download`, {
      responseType: 'blob',
    });
    return response.data;
  },

  async delete(attachmentId: number): Promise<void> {
    await api.delete(`/attachments/${attachmentId}`);
  },
};
```

### Exemplo de Hook (React Query)

```typescript
// hooks/useAttachments.ts

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { attachmentService } from '@/services/attachmentService';

export function useAttachments(taskId: number) {
  return useQuery({
    queryKey: ['attachments', taskId],
    queryFn: () => attachmentService.list(taskId),
  });
}

export function useCreateAttachment(taskId: number) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (file: File) => attachmentService.create(taskId, { file }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['attachments', taskId] });
    },
  });
}

export function useDeleteAttachment() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (attachmentId: number) => attachmentService.delete(attachmentId),
    onSuccess: (_, attachmentId) => {
      queryClient.invalidateQueries({ queryKey: ['attachments'] });
    },
  });
}
```

### Exemplo de Componente (React)

```typescript
// components/TaskAttachmentUpload.tsx

import { useCreateAttachment } from '@/hooks/useAttachments';
import { useState } from 'react';

interface TaskAttachmentUploadProps {
  taskId: number;
}

export function TaskAttachmentUpload({ taskId }: TaskAttachmentUploadProps) {
  const [file, setFile] = useState<File | null>(null);
  const createAttachment = useCreateAttachment(taskId);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!file) return;

    try {
      await createAttachment.mutateAsync(file);
      setFile(null);
      alert('Anexo enviado com sucesso!');
    } catch (error) {
      alert('Erro ao enviar anexo');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="file"
        onChange={(e) => setFile(e.target.files?.[0] || null)}
        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip,.rar"
      />
      <button type="submit" disabled={!file || createAttachment.isPending}>
        {createAttachment.isPending ? 'Enviando...' : 'Enviar'}
      </button>
    </form>
  );
}
```

---

## 📝 Exemplos Práticos

### Exemplo 1: Upload de Foto de Progresso

```typescript
const handleUploadProgressPhoto = async (taskId: number, photoFile: File) => {
  const formData = new FormData();
  formData.append('file', photoFile);

  const response = await api.post(`/tasks/${taskId}/attachments`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });

  console.log('Foto enviada:', response.data.data);
};
```

### Exemplo 2: Listar e Exibir Anexos

```typescript
const { data: attachments, isLoading } = useAttachments(taskId);

if (isLoading) return <div>Carregando anexos...</div>;

return (
  <div>
    <h3>Anexos ({attachments?.length || 0})</h3>
    {attachments?.map((attachment) => (
      <div key={attachment.id}>
        <span>{attachment.filename}</span>
        <span>{(attachment.size / 1024).toFixed(2)} KB</span>
        <a href={`/api/v1/attachments/${attachment.id}/download`} target="_blank">
          Download
        </a>
      </div>
    ))}
  </div>
);
```

### Exemplo 3: Download de Anexo

```typescript
const handleDownload = async (attachmentId: number, filename: string) => {
  const blob = await attachmentService.download(attachmentId);
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  window.URL.revokeObjectURL(url);
  document.body.removeChild(a);
};
```

### Exemplo 4: Remover Anexo com Confirmação

```typescript
const deleteAttachment = useDeleteAttachment();

const handleDelete = async (attachmentId: number) => {
  if (!confirm('Tem certeza que deseja remover este anexo?')) return;

  try {
    await deleteAttachment.mutateAsync(attachmentId);
    alert('Anexo removido com sucesso!');
  } catch (error) {
    alert('Erro ao remover anexo');
  }
};
```

---

## 🔐 Segurança e Permissões

### Middleware e Policies

- **Autenticação**: `auth:sanctum` (obrigatório)
- **Company Scope**: Header `X-Company-Id` (obrigatório)
- **Permissão**: Apenas membros do projeto podem acessar anexos
- **Task Scope**: Attachment deve pertencer a uma tarefa do projeto informado

### Validações no Frontend

Embora validações sejam feitas no backend, é recomendado validar no frontend para melhor UX:

1. **Tamanho do arquivo**: Verificar `file.size <= 10 * 1024 * 1024` antes do upload
2. **Tipo de arquivo**: Verificar extensão permitida antes do upload
3. **Feedback visual**: Mostrar progresso durante upload de arquivos grandes

---

## 🚀 Melhorias Futuras

### Planejadas

1. **Geração de Thumbnails**: Implementar geração automática de thumbnails para imagens
2. **Presigned URLs**: Implementar presigned URLs para upload direto ao S3 (reduz carga no servidor)
3. **Preview de Imagens**: Adicionar preview inline de imagens na listagem
4. **Compressão de Imagens**: Compressão automática de imagens antes do upload

### Considerações para Implementação

- **Thumbnails**: Usar biblioteca como Intervention Image para gerar thumbnails
- **Presigned URLs**: Implementar endpoint separado para gerar URL presigned antes do upload
- **Preview**: Usar componente de galeria de imagens no frontend

---

## 📚 Referências

- [Swagger/OpenAPI Documentation](http://localhost:8000/api/documentation)
- Model: `app/Models/Attachment.php`
- Controller: `app/Http/Controllers/AttachmentController.php`
- Tests: `tests/Feature/AttachmentControllerTest.php`
- Resource: `app/Http/Resources/AttachmentResource.php`

---

## ❓ FAQ

### P: Qual o tamanho máximo de arquivo permitido?

**R:** O tamanho máximo é 10MB (10240 KB). Arquivos maiores serão rejeitados com erro de validação.

### P: Quais tipos de arquivo são permitidos?

**R:** São permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, ZIP e RAR.

### P: Como configurar S3 para armazenamento?

**R:** Configure `TASK_ATTACHMENTS_DISK=s3` no `.env` e configure as credenciais AWS (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET).

### P: Posso atualizar um anexo após o upload?

**R:** Não, anexos são imutáveis após o upload. Para alterar, é necessário deletar o anexo antigo e fazer upload de um novo.

### P: Quem pode deletar um anexo?

**R:** Qualquer membro do projeto da tarefa ou o próprio autor do anexo pode deletá-lo.

### P: Os arquivos são deletados permanentemente?

**R:** Não, os registros recebem soft delete (deleted_at é preenchido), mas o arquivo físico é removido do storage. Para recuperar, seria necessário restaurar o registro e fazer upload novamente.

---

**Última atualização:** 2026-01-01  
**Versão da API:** v1  
**Status:** ✅ Implementado e Testado

