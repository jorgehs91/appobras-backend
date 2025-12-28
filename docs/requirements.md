
Quero criar uma aplicação que funcione tanto na web quanto no mobile. Tema da aplicação: App de gestão de obras com as seguintes funcionalidades. Abaixo está a descrição geral do sistema.

1. Gestão de Projetos e Obras

- Cadastro de obras e projetos
- Definição de fases e cronogramas
- Acompanhamento do progresso (Gantt, Kanban, etc.)
- Alertas para prazos e entregas

2. Gestão Financeira

- Orçamentos e custos previstos x realizados
- Controle de compras e pedidos
- Emissão de relatórios financeiros

3. Gestão de Equipes contratadas

- Cadastro do prestador de serviço
- Pagamentos dos prestadores

4. Documentação e Compliance

- Armazenamento de contratos, plantas, normas técnicas
- Gestão de permissões e aprovações
- Controle de licenças e autorizações

5. Comunicação e Relatórios

- Painel de indicadores e dashboards
- Relatórios detalhados (técnicos, financeiros, operacionais)
- Integração com WhatsApp, e-mail e SMS

Fim da descrição geral.

Aqui abaixo está uma descrição mais rica, a partir de onde vamos seguir todas as implementações.

Arquitetura proposta

- Backend: Laravel 12 (PHP 8.3), arquitetura modular (DDD/hexagonal light), REST + OpenAPI, Laravel Sanctum (SPA + tokens mobile), Spatie Permission (RBAC), Queues (Redis), Events/Listeners para notificações, Jobs para integrações, Cache (Redis), Storage S3 (ou MinIO).
- Banco de dados: MySQL 8 (InnoDB), migrações, seeds, índices, views materializadas para dashboards (quando necessário).
- Frontend web: React 18 + Vite, TypeScript, React Router, TanStack Query, Zustand (ou Redux Toolkit), MUI/Ant Design como design system, Recharts/ECharts para gráficos, biblioteca de Gantt (e.g., dhtmlxGantt via wrapper, ou Syncfusion/AnyGantt).
- Mobile: React Native + Expo, TypeScript, Expo Router, React Query, UI (NativeBase/React Native Paper), suporte a push notifications (Expo), câmera e upload de documentos/fotos (fase 2).
- Observabilidade: Laravel Telescope (dev), Laravel Horizon (queues), logs estruturados (JSON) em CloudWatch/ELK, Sentry (web/mobile/backend).
- Infra/DevOps: Docker + docker-compose, Nginx, CI/CD (GitHub Actions), ambientes dev/homolog/produção, variáveis via .env, backups MySQL, migrations automatizadas, versionamento de API (v1).
- Integrações: E-mail (SES/SendGrid), WhatsApp (Meta Cloud API) e SMS (Twilio/Zenvia) — WhatsApp/SMS preferencialmente pós-MVP devido à homologação/volume.
- Segurança: OWASP ASVS, rate limiting, CORS, CSRF (SPA), validações, encriptação, LGPD (termos/consentimento), backup/restore, controle de auditoria (logs de ações).

Módulos e demandas organizadas (com foco em MVP) Legenda: [MVP] essencial para MVP | [Pós] pós-MVP

1. Autenticação, Usuários e Permissões [MVP] Objetivo: controle de acesso, multi-empresa (se aplicável), perfis.

- Backend
    
    - [MVP] Endpoints: login/logout, refresh, registro/convite, recuperação de senha.
    - [MVP] RBAC com Spatie Permission (roles: Admin da Obra, Engenheiro, Financeiro, Compras, Prestador, Leitor).
    - [MVP] Tenancy simples por “empresa/cliente” (campo company_id nas entidades) ou Workspace.
    - [MVP] Auditoria (model events) e trilhas básicas (created_by/updated_by).
    - [Pós] MFA (TOTP), SSO (OAuth/SAML), políticas avançadas.
- Web
    
    - [MVP] Páginas: Login, Esqueci Senha, Convite/Ativação, Gestão de Usuários, Perfis e Permissões.
    - [MVP] Alternância de workspace/empresa, perfil do usuário, troca de senha.
    - [Pós] Gestão avançada de políticas e auditoria navegável.
- Mobile
    
    - [MVP] Tela de login e persistência de sessão, perfil do usuário.
    - [Pós] MFA, troca de workspace.

2. Obras e Projetos [MVP] Objetivo: cadastro de obras/projetos, equipes, meta-dados.

- Backend
    
    - [MVP] Entidades: Project(Obra), Site/Local, Stakeholders, Team, vinculação de usuários/contratados.
    - [MVP] Endpoints CRUD, filtros (status, data, responsável), paginação, policies.
    - [MVP] Webhooks internos/domínio (ProjectCreated → notificação).
    - [Pós] Templates de obras, clonagem, importação CSV.
- Web
    
    - [MVP] Lista/Detalhe de Obras, filtros, criação/edição, anexos principais (logo/planta).
    - [MVP] Gestão de equipe por obra (membros e papéis).
    - [Pós] Mapa de obras (Mapbox/Google Maps).
- Mobile
    
    - [MVP] Lista e detalhes da obra, membros.
    - [Pós] Seleção de obra “ativa” para check-ins, geolocalização.

3. Fases, Cronogramas e WBS (Gantt/Kanban) [MVP] Objetivo: modelar fases, tarefas, dependências, status e visualizações.

- Backend
    
    - [MVP] Entidades: Phase, Task, Dependencies, Assignees, Checklist.
    - [MVP] Endpoints CRUD, bulk updates (drag & drop), cálculo de progresso (percentual).
    - [MVP] Validações de dependências e datas, eventos (atraso/adoiantamento).
    - [Pós] Linha de base (baseline), versão de cronograma.
- Web
    
    - [MVP] Gantt básico (criar/editar tarefas, datas, dependências), Kanban por status.
    - [MVP] Formulários de tarefa (responsável, prazo, anexos, comentários).
    - [Pós] Linha de base, comparação visual, impressão/export PDF.
- Mobile
    
    - [MVP] Lista/Kanban simplificado, atualizar status, comentar, anexar foto.
    - [Pós] Gantt de leitura, trabalho offline/sincronização.

4. Alertas e Notificações [MVP] Objetivo: alertas para prazos e entregas, eventos críticos.

- Backend
    
    - [MVP] Jobs agendados (Scheduler) para verificar SLAs/prazos.
    - [MVP] Canal e-mail; preferências por usuário/obra.
    - [Pós] SMS/WhatsApp push, templates multi-idioma, digest diário/semanal.
- Web
    
    - [MVP] Centro de notificações, assinatura de eventos por usuário.
    - [Pós] Configuração de canais (SMS/WhatsApp), silenciar/pausar.
- Mobile
    
    - [MVP] Push notifications (Expo) e inbox.
    - [Pós] Configuração avançada no app.

5. Gestão Financeira [MVP] Objetivo: orçamento, custos previstos x realizados, compras/pedidos, relatórios.

- Backend
    
    - [MVP] Entidades: Budget, CostItem (WBS/códigos), PurchaseRequest, PurchaseOrder, Expense/Invoice, Supplier.
    - [MVP] Regras: estados (rascunho, aprovado, cancelado), aprovações por papel.
    - [MVP] Cálculo PVxRV (previsto vs realizado), centros de custo por obra.
    - [Pós] Integração ERP (export contábil), impostos/retenções, multi-moeda.
- Web
    
    - [MVP] Cadastro de orçamento por obra, itens, revisões simples.
    - [MVP] Fluxo de compras: requisição → pedido → recebimento → lançamento.
    - [MVP] Relatórios PVxRV, curva S simples, filtros e export CSV.
    - [Pós] Aprovações multi-nível com matriz, dashboards financeiros avançados.
- Mobile
    
    - [MVP] Registro rápido de despesa com foto do comprovante.
    - [Pós] Aprovação de requisições/pedidos pelo celular, OCR de notas.

6. Prestadores de Serviço e Pagamentos [MVP] Objetivo: cadastro de contratados, contratos, pagamentos.

- Backend
    
    - [MVP] Entidades: Contractor, Contract, Payment (AP/Contas a Pagar), WorkOrder.
    - [MVP] Fluxos: cadastro/qualificação, vínculo a obra/tarefa, pagamentos programados.
    - [Pós] Integração bancária (CNAB/Pix), retenções (INSS/ISS).
- Web
    
    - [MVP] CRUD de prestadores, contratos e agenda de pagamentos.
    - [MVP] Relatório de pagamentos por obra/contratado.
    - [Pós] Portal do prestador (autoatendimento).
- Mobile
    
    - [MVP] Consulta de ordens/contratos e status de pagamento.
    - [Pós] Upload de documentos, aceite de OS.

7. Documentação e Compliance [MVP] Objetivo: armazenamento de contratos, plantas, normas; permissões e aprovações; licenças.

- Backend
    
    - [MVP] Entidades: Document, Folder, Tag, Version, Approval, License.
    - [MVP] Upload S3, versionamento, trilha de aprovações, permissões por pasta/obra.
    - [Pós] Assinatura digital (ICP-Brasil/Adobe Sign), OCR e busca semântica.
- Web
    
    - [MVP] Biblioteca de documentos (lista/árvore), upload/versão, permissões simples.
    - [MVP] Fluxo de aprovação básico e controle de vencimento de licenças.
    - [Pós] Visualizador CAD/PDF avançado, comparador de versões.
- Mobile
    
    - [MVP] Consulta/download, upload de foto/documentos ligados a tarefa.
    - [Pós] Scanner com recorte e OCR.

8. Painéis, Relatórios e KPIs [MVP] Objetivo: indicadores operacionais, técnicos e financeiros.

- Backend
    
    - [MVP] Endpoints agregados: progresso por obra, tarefas atrasadas, PVxRV, próximos vencimentos.
    - [Pós] ETL leve para Data Mart, pré-cálculo noturno.
- Web
    
    - [MVP] Dashboard por obra e global, widgets configuráveis, export CSV/PDF simples.
    - [Pós] Construtor de relatórios, agendamento por e-mail.
- Mobile
    
    - [MVP] Dashboard compacto da obra ativa.
    - [Pós] KPIs customizáveis.

9. Comunicação (E-mail, WhatsApp, SMS) [MVP parcial] Objetivo: centralizar comunicação operacional.

- Backend
    
    - [MVP] E-mail transactional (SendGrid/SES), templates blade/markdown.
    - [Pós] WhatsApp Business (Meta Cloud API), SMS (Twilio/Zenvia), webhooks de entrega.
- Web
    
    - [MVP] Configuração de remetente e templates.
    - [Pós] Caixa de saída, histórico por entidade (tarefa/pedido).
- Mobile
    
    - [Pós] Deep links para WhatsApp, envio de mensagens pré-formatadas.

10. Configurações, Auditoria e Administração [MVP]

- Backend
    
    - [MVP] Configurações por obra/empresa (fuso horário, moeda, numeração).
    - [MVP] Audit log (model changes, key actions).
    - [Pós] Feature flags, parâmetros de aprovação.
- Web
    
    - [MVP] Tela de configurações básicas, consulta de auditoria.
    - [Pós] Gestão de parâmetros avançados.
- Mobile
    
    - [Pós] Preferências do app e sincronização.

Requisitos não-funcionais e transversais

- Qualidade/QA
    - Testes: unitários (PHPUnit), feature (Laravel), contrato de API (OpenAPI + Dredd/Prism), E2E Web (Playwright/Cypress), Mobile (Detox/maestro).
    - Code style: PHP-CS-Fixer, ESLint/Prettier, TypeScript strict, Husky pre-commit.
    - Cobertura mínima: 70% MVP.
- Segurança
    - Sanitização/validação robusta, RBAC nas policies, rate limiting, secrets rotacionados, backups, LGPD (consentimento e exclusão).
- DevOps
    - CI/CD com pipelines: build, testes, análise estática, migrations, deploy blue/green.
    - Infra como código (Terraform opcional), monitoramento (Uptime, Sentry), backups automáticos.
- UX/UI
    - Design System compartilhado (Tokens de design, Figma), componentes unificados entre web e mobile quando possível.
    - Acessibilidade (WCAG AA), i18n preparado.
- Documentação
    - OpenAPI 3.1 sempre atualizado, Storybook para web/mobile, ADRs (Architecture Decision Records), Guia de contribuições.

Mapa de dados (visão rápida de entidades-chave)

- Users, Roles, Permissions, Companies/Workspaces
- Projects(Obras), Sites
- Phases, Tasks, Dependencies, Checklists, Comments, Attachments
- Budgets, CostItems, PurchaseRequests, PurchaseOrders, Expenses/Invoices, Payments, CostCenters
- Contractors, Contracts, WorkOrders
- Documents, Folders, Versions, Approvals, Licenses
- Notifications, Templates, AuditLogs

Prioridade para MVP (escopo mínimo recomendável)

- Autenticação e RBAC básico.
- Obras/Projetos com equipe.
- Fases/Tarefas com Gantt/Kanban, status e prazos.
- Alertas e notificações por e-mail + push mobile.
- Financeiro: orçamento, PVxRV básico, compras (requisição → pedido) e despesas.
- Prestadores/contratos e agenda de pagamentos.
- Documentos com upload e versionamento simples + aprovações básicas.
- Dashboards essenciais (progresso, atrasos, PVxRV, vencimentos).