<?php

namespace Tests\Unit;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_can_be_created_with_valid_data(): void
    {
        $supplier = Supplier::factory()->create([
            'name' => 'Fornecedor Teste',
            'cnpj' => '12345678000190',
            'contact' => 'contato@fornecedor.com',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Fornecedor Teste',
        ]);

        // CNPJ should be formatted
        $this->assertStringContainsString('.', $supplier->cnpj);
        $this->assertStringContainsString('/', $supplier->cnpj);
        $this->assertStringContainsString('-', $supplier->cnpj);
    }

    public function test_supplier_cnpj_is_formatted_on_save(): void
    {
        $supplier = Supplier::factory()->create([
            'cnpj' => '12345678000190',
        ]);

        $this->assertEquals('12.345.678/0001-90', $supplier->cnpj);
    }

    public function test_supplier_cnpj_validation_fails_with_invalid_length(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('CNPJ deve conter 14 dígitos.');

        Supplier::factory()->create([
            'cnpj' => '123456789', // Invalid length
        ]);
    }

    public function test_supplier_cnpj_must_be_unique(): void
    {
        $supplier1 = Supplier::factory()->create([
            'cnpj' => '12345678000190',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Este CNPJ já está cadastrado.');

        try {
            Supplier::factory()->create([
                'cnpj' => '12345678000190',
            ]);
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Este CNPJ já está cadastrado.', $e->getMessage());
            throw $e;
        }
    }

    public function test_supplier_has_purchase_requests_relationship(): void
    {
        $supplier = Supplier::factory()->create();
        $purchaseRequest = \App\Models\PurchaseRequest::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $this->assertTrue($supplier->purchaseRequests->contains($purchaseRequest));
    }

    public function test_supplier_uses_soft_deletes(): void
    {
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
