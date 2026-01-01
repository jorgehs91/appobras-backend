<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            
            // Metadados adicionais
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
            // Note: morphs('fileable') já cria automaticamente o índice ['fileable_type', 'fileable_id']
            $table->index('company_id');
            $table->index('project_id');
            $table->index('category');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
