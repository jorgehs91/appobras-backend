---
name: Cobertura Completa de Testes API
overview: Completar a cobertura de testes para todas as APIs do backend, atualizando testes existentes e criando novos arquivos de teste para endpoints sem cobertura.
todos:
  - id: complete-contractors-tests
    content: Completar testes de Contractors (update, destroy) em ContractorsTest.php
    status: pending
  - id: complete-phases-tests
    content: Completar testes de Phases (store, update, destroy) em PhasesTasksTest.php
    status: pending
  - id: complete-tasks-tests
    content: Completar testes de Tasks (index, store, update, updateStatus, destroy) em PhasesTasksTest.php
    status: pending
    dependencies:
      - complete-phases-tests
  - id: create-documents-tests
    content: Criar DocumentsTest.php com testes completos de upload e gerenciamento
    status: pending
  - id: create-progress-tests
    content: Adicionar testes de ProjectProgress em ProjectsTest.php
    status: pending
  - id: create-dashboard-tests
    content: Criar DashboardTest.php com testes de estatísticas e agregações
    status: pending
  - id: run-validate-tests
    content: Executar suite completa, validar resultados e gerar relatório final
    status: pending
    dependencies:
      - complete-contractors-tests
      - complete-phases-tests
      - complete-tasks-tests
      - create-documents-tests
      - create-progress-tests
      - create-dashboard-tests
---

# Plano de Cobertura Completa de Testes API

## Análise da Situação Atual

### APIs com Testes Completos ✓

- Auth (login, logout, refresh, forgot, reset)
- Register  
- Companies (index, store)
- Invites (create, accept)
- Me (switch-company, switch-project)
- Admin/RBAC (roles, permissions, assign, revoke)
- Projects (index, store, show, update)

### APIs com Testes Incompletos ⚠️

**1. Contractors** - [`tests/Feature/ContractorsTest.php`](tests/Feature/ContractorsTest.php)

- ✓ Existente: index, store
- ❌ Faltando: update, destroy

**2. Phases** - [`tests/Feature/PhasesTasksTest.php`](tests/Feature/PhasesTasksTest.php)

- ✓ Existente: index, cálculos de progresso
- ❌ Faltando: store, update, destroy

**3. Tasks** - [`tests/Feature/PhasesTasksTest.php`](tests/Feature/PhasesTasksTest.php)

- ✓ Existente: TaskObserver (timestamps automáticos)
- ❌ Faltando: index, store, update, updateStatus, destroy

### APIs sem Testes ❌

**4. Documents** - Precisa criar arquivo novo

- Endpoints: index, store, destroy
- Controlador: [`app/Http/Controllers/DocumentController.php`](app/Http/Controllers/DocumentController.php)

**5. ProjectProgress** - Adicionar ao arquivo existente

- Endpoint: show
- Controlador: [`app/Http/Controllers/ProjectProgressController.php`](app/Http/Controllers/ProjectProgressController.php)

**6. Dashboard** - Precisa criar arquivo novo

- Endpoint: stats
- Controlador: [`app/Http/Controllers/DashboardController.php`](app/Http/Controllers/DashboardController.php)

## Implementação

### Etapa 1: Completar Testes de Contractors

Adicionar em [`tests/Feature/ContractorsTest.php`](tests/Feature/ContractorsTest.php):

- `test_can_update_contractor`: Testa PUT /api/v1/contractors/{contractor}
- `test_can_delete_contractor`: Testa DELETE /api/v1/contractors/{contractor}
- `test_cannot_update_contractor_from_different_company`: Valida isolamento de companies
- `test_cannot_delete_contractor_from_different_company`: Valida isolamento de companies

### Etapa 2: Completar Testes de Phases

Adicionar em [`tests/Feature/PhasesTasksTest.php`](tests/Feature/PhasesTasksTest.php):

- `test_can_create_phase_in_project`: Testa POST /api/v1/projects/{project}/phases
- `test_can_update_phase`: Testa PUT /api/v1/phases/{phase}
- `test_can_delete_phase`: Testa DELETE /api/v1/phases/{phase}
- `test_cannot_create_phase_in_project_without_membership`: Valida autorização
- `test_phase_sequence_is_auto_incremented`: Valida lógica de sequência

### Etapa 3: Completar Testes de Tasks

Adicionar em [`tests/Feature/PhasesTasksTest.php`](tests/Feature/PhasesTasksTest.php):

- `test_can_list_tasks_for_project`: Testa GET /api/v1/projects/{project}/tasks
- `test_can_create_task`: Testa POST /api/v1/projects/{project}/tasks
- `test_can_update_task`: Testa PUT /api/v1/tasks/{task}
- `test_can_update_task_status`: Testa PATCH /api/v1/tasks/{task}/status
- `test_can_delete_task`: Testa DELETE /api/v1/tasks/{task}
- `test_cannot_create_task_without_project_membership`: Valida autorização
- `test_task_filtering_by_phase`: Valida filtros query params

### Etapa 4: Criar Testes de Documents

Criar novo arquivo [`tests/Feature/DocumentsTest.php`](tests/Feature/DocumentsTest.php):

- `test_can_list_documents_for_project`: Testa GET /api/v1/projects/{project}/documents
- `test_can_upload_document`: Testa POST /api/v1/projects/{project}/documents (com Storage fake)
- `test_can_delete_document`: Testa DELETE /api/v1/documents/{document}
- `test_document_uploader_can_delete_own_document`: Valida que uploader pode deletar
- `test_cannot_upload_document_without_project_membership`: Valida autorização
- `test_cannot_delete_document_from_different_company`: Valida isolamento
- `test_file_is_deleted_from_storage_on_destroy`: Valida exclusão física do arquivo

### Etapa 5: Criar Testes de ProjectProgress

Adicionar em [`tests/Feature/ProjectsTest.php`](tests/Feature/ProjectsTest.php):

- `test_project_progress_shows_phases_breakdown`: Testa GET /api/v1/projects/{project}/progress
- `test_project_progress_only_shows_active_phases`: Valida filtro de status
- `test_cannot_view_progress_without_project_membership`: Valida autorização

### Etapa 6: Criar Testes de Dashboard

Criar novo arquivo [`tests/Feature/DashboardTest.php`](tests/Feature/DashboardTest.php):

- `test_dashboard_stats_returns_correct_structure`: Valida estrutura da resposta
- `test_dashboard_stats_calculates_avg_progress`: Valida cálculo de progresso médio
- `test_dashboard_stats_counts_overdue_tasks`: Valida contagem de tarefas atrasadas
- `test_dashboard_stats_counts_upcoming_deliveries`: Valida entregas próximas (7 dias)
- `test_dashboard_stats_sums_total_budget`: Valida soma de orçamentos
- `test_dashboard_stats_filters_by_project_id`: Valida filtro por projeto específico
- `test_dashboard_stats_only_shows_user_projects`: Valida isolamento por membership

### Etapa 7: Executar e Validar Todos os Testes

1. Rodar suite completa: `php artisan test`
2. Verificar cobertura: `php artisan test --coverage`
3. Corrigir quaisquer falhas encontradas
4. Validar que todos os endpoints retornam códigos HTTP corretos

## Estratégia de Dados de Teste

Para garantir testes consistentes e isolados:

- Usar `RefreshDatabase` em todos os testes
- Criar factories apropriadas quando necessário
- Seed de permissions quando testar RBAC
- Usar `Storage::fake()` para testes de upload
- Criar estruturas completas (Company → Project → Phase → Task) quando necessário
- Garantir membership adequado para validar autorizações

## Validações de Segurança Importantes

Todos os testes devem validar:

1. **Isolamento de Company**: Usuários não podem acessar recursos de companies diferentes
2. **Project Membership**: Apenas membros do projeto podem acessar seus recursos
3. **Header X-Company-Id**: Endpoints protegidos devem validar este header
4. **Autenticação**: Endpoints protegidos retornam 401 sem token
5. **Autorização**: Endpoints retornam 403 quando usuário não tem permissão

## Resultado Esperado

Após conclusão do plano:

- ✅ 100% de cobertura dos endpoints da API
- ✅ Todos os cenários de sucesso testados
- ✅ Todos os cenários de erro (401, 403, 404, 422) testados
- ✅ Validações de isolamento de dados entre companies