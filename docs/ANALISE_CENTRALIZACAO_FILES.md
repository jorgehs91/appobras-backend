# Análise: Centralização de Uploads (Documents + Attachments)

## 📊 Situação Atual

### Tabelas Existentes

**`documents`** (implementado):
- Escopo: `company_id` + `project_id`
- Campos: `name`, `file_path`, `file_url`, `mime_type`, `file_size`, `uploaded_by`
- Uso: Documentos gerais do projeto

**`attachments`** (recém criado):
- Escopo: `task_id` 
- Campos: `filename`, `path`, `mime_type`, `size`, `thumbnail_path`, `user_id`
- Uso: Anexos específicos de tarefas

### Problemas Identificados

1. **Duplicação de campos**: `file_path` vs `path`, `file_size` vs `size`, `name` vs `filename`
2. **Lógica duplicada**: Controllers e services com código similar
3. **Escalabilidade**: Futuros uploads (fases, comentários) precisarão de novas tabelas
4. **Queries complexas**: Buscar todos os arquivos de um projeto requer joins em múltiplas tabelas

---

## ✅ Proposta: Tabela Unificada `files`

### Estrutura Proposta

```php
Schema::create('files', function (Blueprint $table) {
    $table->id();
    
    // Polymorphic relationship
    $table->morphs('fileable'); // fileable_type, fileable_id
    
    // Contexto (para facilitar queries e validações)
    $table->foreignId('company_id')->constrained()->onDelete('cascade');
    $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
    
    // Metadados do arquivo
    $table->string('name'); // Nome original do arquivo
    $table->string('path'); // Caminho no storage
    $table->string('url')->nullable(); // URL pública (se aplicável)
    $table->string('mime_type')->nullable();
    $table->bigInteger('size'); // Tamanho em bytes
    $table->string('thumbnail_path')->nullable(); // Para imagens
    
    // Metadados adicionais (opcionais)
    $table->string('category')->nullable(); // 'document', 'attachment', 'receipt', etc.
    $table->text('description')->nullable();
    
    // Usuário que fez upload
    $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
    
    // Audit
    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
    
    $table->timestamps();
    $table->softDeletes();
    
    // Índices
    $table->index(['fileable_type', 'fileable_id']);
    $table->index('company_id');
    $table->index('project_id');
    $table->index('category');
    $table->index('created_at');
});
```

### Model Unificado

```php
class File extends Model
{
    use HasFactory, SoftDeletes, AuditTrait;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'company_id',
        'project_id',
        'name',
        'path',
        'url',
        'mime_type',
        'size',
        'thumbnail_path',
        'category',
        'description',
        'uploaded_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    // Polymorphic relationship
    public function fileable()
    {
        return $this->morphTo();
    }

    // Relacionamentos diretos (para queries mais eficientes)
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes úteis
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
```

### Uso nos Models

```php
// Project.php
public function files()
{
    return $this->morphMany(File::class, 'fileable');
}

public function documents()
{
    return $this->files()->where('category', 'document');
}

// Task.php
public function files()
{
    return $this->morphMany(File::class, 'fileable');
}

public function attachments()
{
    return $this->files()->where('category', 'attachment');
}

// Expense.php (futuro)
public function receipt()
{
    return $this->morphOne(File::class, 'fileable')->where('category', 'receipt');
}
```

---

## 🔄 Migração Proposta

### Opção 1: Migração Incremental (Recomendada)

1. **Criar tabela `files`** com estrutura completa
2. **Migrar dados** de `documents` e `attachments` para `files`
3. **Criar aliases** nos models antigos (deprecated)
4. **Atualizar controllers** para usar `File`
5. **Remover tabelas antigas** após validação

### Opção 2: Refatoração Direta

1. **Criar migration** para transformar `documents` em `files`
2. **Adicionar campos** faltantes (polymorphic, category)
3. **Migrar `attachments`** para `files`
4. **Atualizar código** todo de uma vez

---

## 📋 Checklist de Implementação

### Fase 1: Preparação
- [ ] Criar migration `create_files_table`
- [ ] Criar model `File` com polymorphic relationship
- [ ] Criar migration de dados: `documents` → `files` (category='document')
- [ ] Criar migration de dados: `attachments` → `files` (category='attachment')
- [ ] Adicionar relacionamento `files()` nos models `Project` e `Task`

### Fase 2: Refatoração
- [ ] Criar `FileController` unificado
- [ ] Migrar `DocumentController` para usar `File` model
- [ ] Migrar `AttachmentController` para usar `File` model
- [ ] Atualizar Resources (`FileResource`)
- [ ] Atualizar Requests (`StoreFileRequest`)
- [ ] Atualizar testes

### Fase 3: Cleanup
- [ ] Adicionar deprecated warnings nos models antigos
- [ ] Atualizar documentação
- [ ] Validar em staging
- [ ] Criar migration para remover tabelas `documents` e `attachments`

---

## ⚠️ Considerações

### Vantagens
- ✅ **Código DRY**: Lógica de upload centralizada
- ✅ **Escalabilidade**: Fácil adicionar novos tipos (fases, comentários, etc)
- ✅ **Queries eficientes**: Busca global de arquivos
- ✅ **Consistência**: Mesmo padrão para todos os uploads
- ✅ **Manutenibilidade**: Uma única fonte de verdade

### Desvantagens
- ⚠️ **Refactoring**: Requer migração de dados e código existente
- ⚠️ **Tempo**: Implementação mais complexa inicialmente
- ⚠️ **Risco**: Se documents está em produção, precisa de cuidado extra

### Recomendação Final

**SIM, é aconselhável centralizar**, mas com algumas ressalvas:

1. **Se `documents` já está em produção**: Fazer migração incremental com período de transição
2. **Se ambos são novos**: Fazer refatoração direta agora
3. **Usar `category`**: Para manter compatibilidade e facilitar queries específicas
4. **Manter aliases**: `Project::documents()` pode retornar `files()->where('category', 'document')`

---

## 🚀 Próximos Passos

1. Validar com time se faz sentido neste momento
2. Se sim, criar branch `refactor/unify-files`
3. Implementar Fase 1 (estrutura)
4. Implementar Fase 2 (refatoração)
5. Testes completos
6. Migração em staging
7. Deploy e cleanup (Fase 3)

---

**Última atualização:** 2026-01-01  
**Status:** Proposta de arquitetura

