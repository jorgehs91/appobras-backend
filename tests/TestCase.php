<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     * 
     * Garante que os testes estão usando o banco de dados correto (teste, não desenvolvimento).
     * 
     * @throws \Exception Se o teste estiver usando banco de desenvolvimento
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Verificação de segurança: garantir que testes não usem banco de desenvolvimento
        // Esta verificação deve acontecer DEPOIS do parent::setUp() para garantir
        // que as configurações do phpunit.xml já foram aplicadas
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");
        $database = $dbConfig['database'] ?? null;

        // Em ambiente de teste, BLOQUEAR uso de banco que não seja de teste
        if (app()->environment('testing')) {
            // Verificar se está usando SQLite em memória (configuração esperada)
            $isMemoryDatabase = $database === ':memory:';
            
            // Verificar se está usando banco de teste (termina com _test)
            $isTestDatabase = $database && str_ends_with($database, '_test');
            
            // Se não é nem memória nem banco de teste, BLOQUEAR
            if (!$isMemoryDatabase && !$isTestDatabase && $database) {
                $message = sprintf(
                    "ERRO CRÍTICO: Testes estão usando banco de DESENVOLVIMENTO!\n" .
                    "Connection: %s\n" .
                    "Database: %s\n" .
                    "Isso pode limpar seus dados de desenvolvimento.\n" .
                    "Solução: Limpe o cache com 'php artisan config:clear' e verifique o phpunit.xml",
                    $connection,
                    $database
                );
                
                throw new \Exception($message);
            }
        }
    }
}
