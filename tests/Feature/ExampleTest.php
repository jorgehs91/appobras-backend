<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Teste de verificação: garante que os testes estão usando o banco correto.
     * Este teste deve sempre passar e serve como verificação de segurança.
     */
    public function test_verificar_banco_de_dados_usado(): void
    {
        $connection = \DB::connection()->getName();
        $database = \DB::connection()->getDatabaseName();
        
        // Deve usar SQLite em memória durante os testes
        $this->assertEquals('sqlite', $connection, 
            "Os testes devem usar SQLite, mas estão usando: {$connection}");
        
        $this->assertEquals(':memory:', $database,
            "Os testes devem usar banco em memória (:memory:), mas estão usando: {$database}");
    }
}
