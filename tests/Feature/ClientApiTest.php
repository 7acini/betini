<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_be_listed(): void
    {
        Client::create([
            'name' => 'Maria Oficina',
            'cpf' => '12345678901',
            'phone' => '11999999999',
        ]);

        $this->getJson('/api/workshop/clients')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Maria Oficina');
    }

    public function test_client_can_be_created(): void
    {
        $this->postJson('/api/workshop/clients', [
            'name' => 'Joao Cliente',
            'cpf' => '12345678901',
            'phone' => '11999999999',
            'postal_code' => '01001000',
            'city' => 'Sao Paulo',
            'state' => 'sp',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Joao Cliente')
            ->assertJsonPath('state', 'SP');

        $this->assertDatabaseHas('clients', [
            'name' => 'Joao Cliente',
            'cpf' => '12345678901',
            'state' => 'SP',
        ]);
    }

    public function test_client_requires_valid_document(): void
    {
        $this->postJson('/api/workshop/clients', [
            'name' => 'Jo',
            'cpf' => '123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'cpf']);
    }

    public function test_client_can_be_updated(): void
    {
        $client = Client::create([
            'name' => 'Cliente Antigo',
            'cpf' => '12345678901',
        ]);

        $this->putJson("/api/workshop/clients/{$client->id}", [
            'name' => 'Cliente Atualizado',
            'cpf' => '12345678901',
            'phone' => '11888888888',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Cliente Atualizado');

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Cliente Atualizado',
            'phone' => '11888888888',
        ]);
    }

    public function test_client_can_be_soft_deleted(): void
    {
        $client = Client::create([
            'name' => 'Cliente Removido',
            'cpf' => '12345678901',
        ]);

        $this->deleteJson("/api/workshop/clients/{$client->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }
}
