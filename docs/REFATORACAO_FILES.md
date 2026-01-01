# Refatoração: Centralização de Uploads em Tabela `files`

**Data:** 2026-01-01  
**Status:** ✅ Concluído

## 📋 Resumo

Refatoração completa para centralizar todos os uploads (documents e attachments) em uma única tabela `files` usando polymorphic relationships do Laravel.

---

## 🎯 Objetivos Alcançados

- ✅ Centralização de uploads em tabela única `files`
- ✅ Uso de polymorphic relationships (já padrão no projeto)
- ✅ Migração de dados preservada
- ✅ APIs mantêm compatibilidade (mesmos endpoints)
- ✅ Código DRY (menos duplicação)
- ✅ Facilita adicionar novos tipos de uploads no futuro

---

## 📊 Mudanças Implementadas

### 1. Nova Tabela `files`

**Migration:** `2026_01_01_121917_create_files_table.php`

Campos principais:
- `fileable_type` / `fileable_id` (polymorphic)
- `company_id`, `project_id` (contexto direto)
- `name`, `path`, `url`, `mime_type`, `size`, `thumbnail_path`
- `category` ('document', 'attachment', etc)
- `uploaded_by`, `created_by`, `updated_by`
- `timestamps`, `soft_deletes`

### 2. Migrações de Dados

- `2026_01_01_121938_migrate_documents_to_files_table.php`
- `2026_01_01_121940_migrate_attachments_to_files_table.php`
- `2026_01_01_122301_drop_documents_and_attachments_tables.php`

### 3. Model `File`

**Arquivo:** `app/Models/File.php`

- Polymorphic relationship `fileable()`
- Relacionamentos diretos: `company()`, `project()`, `uploader()`
- Scopes: `forProject()`, `category()`

### 4. Controllers Refatorados

#### DocumentController
- Agora usa `File` model
- Filtra por `category = 'document'` e `fileable_type = Project::class`
- Método `getFilesDisk()` unificado

#### AttachmentController
- Agora usa `File` model
- Filtra por `category = 'attachment'` e `fileable_type = Task::class`
- Método `getFilesDisk()` unificado

### 5. Models Atualizados

#### Project
- `files()` - morphMany relationship
- `documents()` - alias deprecated (mantido para compatibilidade)

#### Task
- `files()` - morphMany relationship
- `attachments()` - alias deprecated (mantido para compatibilidade)

### 6. Resources Atualizados

- `DocumentResource` - usa campos do `File` model
- `AttachmentResource` - usa campos do `File` model

### 7. Factory Unificada

- `FileFactory` com states:
  - `document()` - para criar documentos
  - `attachment()` - para criar anexos

### 8. Testes Atualizados

- `DocumentsTest` - usa `File` model
- `AttachmentControllerTest` - usa `File` model
- `AuditingTest` - atualizado para usar `File`

### 9. Configuração

**Arquivo:** `config/filesystems.php`

Nova configuração unificada:
```php
'files_disk' => env('FILES_DISK', 'local'),
```

---

## 🔄 Compatibilidade

### APIs Mantidas

Todos os endpoints continuam funcionando:

- `GET /api/v1/projects/{project}/documents`
- `POST /api/v1/projects/{project}/documents`
- `GET /api/v1/documents/{document}`
- `GET /api/v1/documents/{document}/download`
- `DELETE /api/v1/documents/{document}`

- `GET /api/v1/tasks/{task}/attachments`
- `POST /api/v1/tasks/{task}/attachments`
- `GET /api/v1/attachments/{attachment}`
- `GET /api/v1/attachments/{attachment}/download`
- `DELETE /api/v1/attachments/{attachment}`

### Relacionamentos

Relacionamentos antigos mantidos como aliases (deprecated):

```php
// Project
$project->documents(); // Retorna files()->where('category', 'document')

// Task
$task->attachments(); // Retorna files()->where('category', 'attachment')
```

---

## 📝 Como Usar

### Criar Documento

```php
$file = File::create([
    'fileable_type' => Project::class,
    'fileable_id' => $project->id,
    'company_id' => $project->company_id,
    'project_id' => $project->id,
    'name' => 'documento.pdf',
    'path' => 'documents/project-1/documento.pdf',
    'category' => 'document',
    // ...
]);
```

### Criar Anexo

```php
$file = File::create([
    'fileable_type' => Task::class,
    'fileable_id' => $task->id,
    'company_id' => $task->company_id,
    'project_id' => $task->project_id,
    'name' => 'anexo.jpg',
    'path' => 'attachments/task-1/anexo.jpg',
    'category' => 'attachment',
    // ...
]);
```

### Queries

```php
// Todos os arquivos de um projeto
File::where('project_id', $projectId)->get();

// Apenas documentos
File::where('project_id', $projectId)
    ->where('category', 'document')
    ->get();

// Apenas anexos de uma tarefa
File::where('fileable_type', Task::class)
    ->where('fileable_id', $taskId)
    ->where('category', 'attachment')
    ->get();
```

---

## 🚀 Próximos Passos

### Futuro

1. **Novos tipos de upload:**
   - `category = 'receipt'` para comprovantes de despesas
   - `category = 'license'` para licenças
   - etc.

2. **Funcionalidades comuns:**
   - Busca global de arquivos
   - Compartilhamento de arquivos
   - Versionamento unificado

3. **Limpeza (opcional):**
   - Remover aliases deprecated após migração completa do frontend
   - Consolidar lógica de storage em service class

---

## ⚠️ Notas Importantes

1. **Route Model Binding:** Laravel resolve automaticamente mesmo com nomes diferentes de parâmetro (`{document}`, `{attachment}`) desde que o type hint seja `File`.

2. **Migração de Dados:** As migrations de dados são idempotentes (verificam se tabelas existem antes de migrar).

3. **Storage:** Configuração unificada via `FILES_DISK` (substitui `TASK_ATTACHMENTS_DISK`).

4. **Factories:** Use `FileFactory` com states `document()` ou `attachment()`.

---

## 📚 Referências

- Model: `app/Models/File.php`
- Controllers: `app/Http/Controllers/DocumentController.php`, `app/Http/Controllers/AttachmentController.php`
- Migrations: `database/migrations/2026_01_01_121917_create_files_table.php`
- Tests: `tests/Feature/DocumentsTest.php`, `tests/Feature/AttachmentControllerTest.php`

---

**Última atualização:** 2026-01-01  
**Versão:** 1.0

