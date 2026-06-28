<?php

namespace Tests\Feature;

use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_providers_can_be_listed(): void
    {
        Provider::create([
            'name' => 'Auto Pecas Central',
            'cnpj' => '12345678000199',
            'phone' => '11999999999',
        ]);

        $this->getJson('/api/workshop/providers')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Auto Pecas Central');
    }

    public function test_provider_can_be_created(): void
    {
        $this->postJson('/api/workshop/providers', [
            'name' => 'Distribuidora Motor Forte',
            'cnpj' => '12.345.678/0001-99',
            'phone' => '11999999999',
            'postal_code' => '01001000',
            'city' => 'Sao Paulo',
            'state' => 'sp',
            'website_url' => 'https://fornecedor.example.com',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Distribuidora Motor Forte')
            ->assertJsonPath('cnpj', '12345678000199')
            ->assertJsonPath('state', 'SP');

        $this->assertDatabaseHas('providers', [
            'name' => 'Distribuidora Motor Forte',
            'cnpj' => '12345678000199',
            'state' => 'SP',
        ]);
    }

    public function test_provider_requires_valid_document_and_url(): void
    {
        $this->postJson('/api/workshop/providers', [
            'name' => 'AB',
            'cnpj' => '123',
            'website_url' => 'site-invalido',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'cnpj', 'website_url']);
    }

    public function test_provider_can_be_updated(): void
    {
        $provider = Provider::create([
            'name' => 'Fornecedor Antigo',
            'cnpj' => '12345678000199',
        ]);

        $this->putJson("/api/workshop/providers/{$provider->id}", [
            'name' => 'Fornecedor Atualizado',
            'cnpj' => '12345678000199',
            'phone' => '11888888888',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Fornecedor Atualizado');

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'name' => 'Fornecedor Atualizado',
            'phone' => '11888888888',
        ]);
    }

    public function test_provider_can_be_soft_deleted(): void
    {
        $provider = Provider::create([
            'name' => 'Fornecedor Removido',
            'cnpj' => '12345678000199',
        ]);

        $this->deleteJson("/api/workshop/providers/{$provider->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('providers', ['id' => $provider->id]);
    }
}
