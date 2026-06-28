<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_products_can_be_listed_with_provider(): void
    {
        $provider = Provider::create([
            'name' => 'Auto Pecas Central',
            'cnpj' => '12345678000199',
        ]);

        Product::create([
            'provider_id' => $provider->id,
            'category' => 'Filtros',
            'name' => 'Filtro de oleo',
            'price' => 39.9,
            'barcode' => '7891234567890',
        ]);

        $this->getJson('/api/workshop/products')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Filtro de oleo')
            ->assertJsonPath('data.0.provider.name', 'Auto Pecas Central');
    }

    public function test_product_can_be_created(): void
    {
        $provider = Provider::create([
            'name' => 'Distribuidora Motor Forte',
            'cnpj' => '12345678000199',
        ]);

        $this->postJson('/api/workshop/products', [
            'provider_id' => $provider->id,
            'category' => 'Lubrificantes',
            'name' => 'Oleo 5W30',
            'price' => 59.9,
            'description' => 'Oleo sintetico para motor flex.',
            'barcode' => '789 123 456 7890',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Oleo 5W30')
            ->assertJsonPath('barcode', '7891234567890')
            ->assertJsonPath('provider.name', 'Distribuidora Motor Forte');

        $this->assertDatabaseHas('products', [
            'provider_id' => $provider->id,
            'name' => 'Oleo 5W30',
            'barcode' => '7891234567890',
        ]);
    }

    public function test_product_requires_valid_name_price_and_provider(): void
    {
        $this->postJson('/api/workshop/products', [
            'provider_id' => 999,
            'name' => 'A',
            'price' => -1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['provider_id', 'name', 'price']);
    }

    public function test_product_can_be_updated(): void
    {
        $provider = Provider::create([
            'name' => 'Fornecedor Produto',
            'cnpj' => '12345678000199',
        ]);

        $product = Product::create([
            'provider_id' => $provider->id,
            'category' => 'Pecas',
            'name' => 'Pastilha antiga',
            'price' => 80,
            'barcode' => '7891234567890',
        ]);

        $this->putJson("/api/workshop/products/{$product->id}", [
            'provider_id' => $provider->id,
            'category' => 'Freios',
            'name' => 'Pastilha de freio',
            'price' => 99.9,
            'barcode' => '7891234567890',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Pastilha de freio')
            ->assertJsonPath('category', 'Freios');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Pastilha de freio',
            'category' => 'Freios',
        ]);
    }

    public function test_product_can_be_soft_deleted(): void
    {
        $product = Product::create([
            'category' => 'Pecas',
            'name' => 'Produto removido',
            'price' => 10,
        ]);

        $this->deleteJson("/api/workshop/products/{$product->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
