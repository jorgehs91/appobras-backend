# Migra

ção para Laravel Octane

## Análise de Viabilidade

### ✅ Pontos Positivos

- **API REST stateless**: Aplicação é puramente API, ideal para Octane
- **Laravel 12 + PHP 8.2**: Versões compatíveis com Octane
- **Redis já configurado**: Necessário para cache/sessões no Octane
- **Horizon em uso**: Compatível com Octane
- **Sem estado global aparente**: Não há uso problemático de variáveis estáticas
- **Cache usando Facade**: Fácil migração para driver Redis/Octane

### ⚠️ Pontos de Atenção

- **Cache atual**: Usa driver `database` - precisa migrar para `redis` ou `octane`
- **Sessões**: Usa driver `database` - precisa migrar para `redis`
- **Observers**: Precisam ser verificados para garantir stateless
- **Middleware customizado**: `EnsureCompanyContext` precisa revisão
- **Deployment**: Requer ajustes no processo de deploy

## Ganhos Esperados

### Performance

- **2-10x mais requisições/segundo**: Aplicação mantém estado em memória entre requisições
- **Redução de latência**: Elimina bootstrap do Laravel em cada requisição
- **Menor uso de CPU**: Menos overhead de inicialização
- **Melhor concorrência**: Swoole/RoadRunner processam múltiplas requisições simultaneamente

### Recursos

- **Cache em memória**: Driver `octane` permite cache compartilhado entre workers
- **Conexões persistentes**: DB e Redis mantêm conexões abertas
- **Menor uso de memória por requisição**: Estado compartilhado reduz alocação

## Passos de Migração

### 1. Instalação e Configuração Inicial

#### 1.1 Instalar Laravel Octane

```bash
composer require laravel/octane
php artisan octane:install
```

Escolher entre:

- **Swoole** (recomendado): Mais features, melhor performance, requer extensão PHP
- **RoadRunner**: Go-based, sem extensões PHP, mais simples

#### 1.2 Configurar Octane

Arquivo: `config/octane.php`

- Workers: 4-8 para produção (baseado em CPU cores)
- Max requests: 500-1000 antes de restart (prevenir memory leaks)
- Task workers: 2-4 para tasks assíncronas

### 2. Migração de Cache

#### 2.1 Alterar driver de cache

Arquivo: `config/cache.php` ou `.env`

```php
'default' => env('CACHE_STORE', 'redis'), // Era 'database'
```

#### 2.2 Verificar uso de cache

Arquivos que usam cache:

- `app/Http/Controllers/DashboardController.php` - Cache de stats
- `app/Http/Controllers/ExpenseController.php` - Cache de PVxR
- `app/Observers/CostItemObserver.php` - Invalidação de cache
- `app/Observers/ExpenseObserver.php` - Invalidação de cache
- `app/Jobs/RecalculatePvxrJob.php` - Cache de dados calculados

**Ação**: Todos já usam `Cache::remember()` e `Cache::forget()` - compatível, apenas mudar driver.

#### 2.3 Opcional: Usar cache Octane

Para cache compartilhado entre workers:

```php
'stores' => [
    'octane' => [
        'driver' => 'octane',
    ],
],
```

### 3. Migração de Sessões

#### 3.1 Alterar driver de sessão

Arquivo: `config/session.php` ou `.env`

```php
'driver' => env('SESSION_DRIVER', 'redis'), // Era 'database'
```

**Nota**: Como é API REST com Sanctum, sessões podem não ser críticas, mas se houver uso, migrar.

### 4. Revisão de Código para Compatibilidade

#### 4.1 Verificar Observers

Arquivos: `app/Observers/*.php`

- ✅ `AuditLogObserver` - Stateless, OK
- ✅ `CostItemObserver` - Usa Cache facade, OK
- ✅ `ExpenseObserver` - Usa Cache facade, OK
- ✅ `TaskObserver` - Stateless, OK

**Ação**: Garantir que não armazenam estado em propriedades de classe.

#### 4.2 Revisar Middleware

Arquivo: `app/Http/Middleware/EnsureCompanyContext.php`

```php
app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);
```

**Atenção**: `setPermissionsTeamId()` pode manter estado. Verificar se Spatie Permission suporta Octane ou usar alternativa stateless.

#### 4.3 Verificar Service Providers

Arquivo: `app/Providers/AppServiceProvider.php`

- ✅ Rate limiters - OK (usam cache)
- ✅ Observers - OK (stateless)
- ✅ ResetPassword URL - OK (stateless)

#### 4.4 Verificar uso de Singleton/Instance

Busca realizada: Não há uso problemático de singletons ou instâncias globais.

### 5. Configuração de Ambiente

#### 5.1 Variáveis de ambiente

Adicionar ao `.env`:

```env
OCTANE_SERVER=swoole
OCTANE_WORKERS=4
OCTANE_MAX_REQUESTS=500
OCTANE_TASK_WORKERS=2
OCTANE_WATCH=false
```

#### 5.2 Configuração de produção

- Workers: 2x número de CPU cores
- Max requests: 500-1000 (monitorar memory leaks)
- Task workers: 2-4

### 6. Ajustes no Deployment

#### 6.1 Process Manager

Usar Supervisor ou systemd para gerenciar Octane:

```ini
[program:octane]
command=php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
autostart=true
autorestart=true
```

#### 6.2 Nginx/Apache

Configurar reverse proxy para Octane (porta 8000) ao invés de PHP-FPM.

#### 6.3 Hot Reload (desenvolvimento)

```bash
php artisan octane:start --watch
```

### 7. Testes e Validação

#### 7.1 Testes funcionais

- ✅ Executar suite de testes Pest existente
- ✅ Verificar endpoints críticos
- ✅ Validar autenticação Sanctum
- ✅ Testar jobs/queues com Horizon

#### 7.2 Testes de performance

- Comparar requisições/segundo antes/depois
- Monitorar uso de memória
- Verificar latência p95/p99

#### 7.3 Monitoramento

- Configurar logs do Octane
- Monitorar workers (restarts, memory)
- Alertas para memory leaks

### 8. Documentação e Rollout

#### 8.1 Atualizar README

- Instruções de instalação
- Comandos de desenvolvimento (`octane:start --watch`)
- Configuração de produção

#### 8.2 Rollout gradual

1. Deploy em staging
2. Testes de carga
3. Deploy em produção com rollback plan
4. Monitoramento intensivo nas primeiras 24h

## Arquivos que Serão Modificados

1. `composer.json` - Adicionar dependência
2. `config/octane.php` - Nova configuração
3. `config/cache.php` - Mudar default para redis
4. `config/session.php` - Mudar default para redis (se necessário)
5. `.env.example` - Adicionar variáveis Octane
6. `README.md` - Documentação
7. Scripts de deployment (supervisor/systemd)

## Arquivos que Precisam Revisão (sem mudanças esperadas)

1. `app/Http/Middleware/EnsureCompanyContext.php` - Verificar compatibilidade Spatie Permission
2. `app/Providers/AppServiceProvider.php` - Já compatível
3. Todos os Observers - Verificar stateless

## Riscos e Mitigações

### Riscos

1. **Memory leaks**: Código que mantém estado entre requisições
2. **Incompatibilidade de extensões**: Swoole requer extensão PHP
3. **Debugging mais complexo**: Estado compartilhado dificulta debug

### Mitigações

1. Configurar `max_requests` para restart periódico
2. Usar RoadRunner se Swoole não disponível
3. Manter ambiente tradicional para debug quando necessário

## Conclusão

**Recomendação: ✅ SIM, migrar para Octane**A aplicação é ideal para Octane:

- API stateless
- Já usa Redis
- Sem estado global problemático
- Ganhos de performance significativos esperados