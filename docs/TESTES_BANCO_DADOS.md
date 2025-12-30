# Testes e Banco de Dados - Configuração e Comportamento

## Problema Identificado

Se seus testes estão limpando o banco de dados de **desenvolvimento**, há um problema de configuração. Os testes **NÃO devem** tocar no banco de desenvolvimento.

## Comportamento Esperado

### ✅ CORRETO: RefreshDatabase nos Testes

Sim, **é esperado e correto** que todos os testes usem `RefreshDatabase`. Este trait:

1. **Limpa o banco de dados ANTES de cada teste**
2. **Roda as migrations novamente**
3. **Garante isolamento** entre testes

**MAS** isso deve acontecer apenas no **banco de testes**, não no banco de desenvolvimento!

## Configuração Atual

### phpunit.xml (Configuração Correta)

Seu `phpunit.xml` já está configurado corretamente:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    ...
</php>
```

Isso significa que os testes **devem** usar:
- ✅ SQLite em memória (`:memory:`)
- ✅ **NÃO** o banco MySQL de desenvolvimento

## Por Que o Banco de Desenvolvimento Está Sendo Limpo?

Se isso está acontecendo, possíveis causas:

### 1. Variável de Ambiente .env Sobrescrevendo

Se você tem um `.env` na raiz do projeto que está sendo carregado durante os testes, ele pode estar sobrescrevendo as configurações do `phpunit.xml`.

**Solução:** Certifique-se de que o `phpunit.xml` tem prioridade.

### 2. Comando de Teste Incorreto

**❌ ERRADO:**
```bash
php artisan test --env=local  # Usa .env local
php artisan test --env=development
```

**✅ CORRETO:**
```bash
php artisan test  # Usa phpunit.xml automaticamente
# ou
php vendor/bin/phpunit
```

### 3. Cache de Configuração

O Laravel pode estar usando configurações em cache.

**Solução:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan test
```

### 4. .env.testing Não Configurado

Se você tem um `.env.testing`, ele deve usar um banco separado.

## Solução Recomendada

### Opção 1: Usar SQLite em Memória (Já Configurado) ✅

Sua configuração atual já está correta! Os testes devem usar SQLite em memória.

**Verificar se está funcionando:**

```bash
# 1. Limpar caches
php artisan config:clear

# 2. Rodar um teste simples
php artisan test --filter ExpenseControllerTest::test_listar_expenses_requer_permissao

# 3. Verificar se o banco de desenvolvimento ainda tem dados
# (não deve ter sido afetado)
```

### Opção 2: Criar .env.testing (Alternativa)

Se preferir usar um banco MySQL dedicado para testes:

**Criar `.env.testing`:**
```env
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=appobras_test
DB_USERNAME=root
DB_PASSWORD=
```

**Atualizar `phpunit.xml`:**
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <!-- Remove DB_CONNECTION e DB_DATABASE do phpunit.xml -->
    <!-- Deixe o .env.testing definir -->
</php>
```

**Criar banco de teste:**
```sql
CREATE DATABASE appobras_test;
```

### Opção 3: Usar DatabaseTransactions (Mais Rápido)

Se quiser manter o MySQL mas usar transações (mais rápido, mas pode ter problemas com testes paralelos):

**Criar `tests/TestCase.php` customizado:**
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions; // Em vez de RefreshDatabase
    
    // ...
}
```

**E remover `RefreshDatabase` de cada teste individual.**

⚠️ **Atenção:** `DatabaseTransactions` é mais rápido, mas pode ter problemas com testes que verificam comportamento fora de transações.

## Verificação Rápida

### Como Confirmar Que Está Usando o Banco Correto

Adicione um teste temporário:

```php
public function test_verificar_banco_usado(): void
{
    $connection = \DB::connection()->getName();
    $database = \DB::connection()->getDatabaseName();
    
    dump("Connection: $connection");
    dump("Database: $database");
    
    // Deve ser 'sqlite' e ':memory:' ou 'appobras_test'
    // NÃO deve ser o nome do seu banco de desenvolvimento
    $this->assertNotEquals('seu_banco_desenvolvimento', $database);
}
```

## Resumo

| Aspecto | Status | Observação |
|---------|--------|------------|
| `RefreshDatabase` nos testes | ✅ **Correto** | É esperado e necessário |
| Limpar banco antes de cada teste | ✅ **Correto** | Garante isolamento |
| Usar banco de desenvolvimento | ❌ **ERRADO** | Nunca deve acontecer |
| SQLite em memória configurado | ✅ **Correto** | Já está no phpunit.xml |

## Ação Imediata

1. **Verificar comando usado:**
   ```bash
   # Use sempre:
   php artisan test
   
   # NUNCA use:
   php artisan test --env=local
   ```

2. **Limpar caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Verificar variáveis de ambiente:**
   ```bash
   # Durante os testes, deve mostrar:
   # DB_CONNECTION=sqlite
   # DB_DATABASE=:memory:
   php artisan tinker
   >>> config('database.default')
   >>> config('database.connections.sqlite.database')
   ```

4. **Adicionar verificação no TestCase:**
   ```php
   protected function setUp(): void
   {
       parent::setUp();
       
       // Garantir que estamos usando banco de teste
       $db = config('database.connections.' . config('database.default') . '.database');
       if ($db !== ':memory:' && !str_ends_with($db, '_test')) {
           throw new \Exception("Erro: Testes usando banco de desenvolvimento! DB: $db");
       }
   }
   ```

## Comandos Úteis

```bash
# Rodar testes específicos
php artisan test --filter ExpenseControllerTest

# Rodar com verbose
php artisan test -v

# Rodar apenas testes que falharam
php artisan test --retry

# Verificar configuração de banco durante testes
php artisan tinker
>>> config('database.connections.sqlite')
```

