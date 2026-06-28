<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicles_can_be_listed_with_client(): void
    {
        $client = Client::create([
            'name' => 'Maria Oficina',
            'cpf' => '12345678901',
        ]);

        Vehicle::create([
            'client_id' => $client->id,
            'model' => 'Gol',
            'brand' => 'Volkswagen',
            'plate' => 'ABC1D23',
        ]);

        $this->getJson('/api/workshop/vehicles')
            ->assertOk()
            ->assertJsonPath('data.0.model', 'Gol')
            ->assertJsonPath('data.0.client.name', 'Maria Oficina');
    }

    public function test_vehicle_can_be_created(): void
    {
        $client = Client::create([
            'name' => 'Joao Cliente',
            'cpf' => '12345678901',
        ]);

        $this->postJson('/api/workshop/vehicles', [
            'client_id' => $client->id,
            'model' => 'Civic',
            'brand' => 'Honda',
            'plate' => 'abc1d23',
            'year' => '2020',
            'current_km' => 45000,
            'color' => 'Prata',
            'fuel_type' => 'Flex',
        ])
            ->assertCreated()
            ->assertJsonPath('model', 'Civic')
            ->assertJsonPath('plate', 'ABC1D23')
            ->assertJsonPath('client.name', 'Joao Cliente');

        $this->assertDatabaseHas('vehicles', [
            'client_id' => $client->id,
            'plate' => 'ABC1D23',
        ]);
    }

    public function test_vehicle_requires_valid_client_and_plate(): void
    {
        $this->postJson('/api/workshop/vehicles', [
            'client_id' => 999,
            'model' => '',
            'brand' => '',
            'plate' => '123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['client_id', 'model', 'brand', 'plate']);
    }

    public function test_vehicle_can_be_updated(): void
    {
        $client = Client::create([
            'name' => 'Cliente Veiculo',
            'cpf' => '12345678901',
        ]);

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'model' => 'Uno',
            'brand' => 'Fiat',
            'plate' => 'ABC1D23',
        ]);

        $this->putJson("/api/workshop/vehicles/{$vehicle->id}", [
            'client_id' => $client->id,
            'model' => 'Argo',
            'brand' => 'Fiat',
            'plate' => 'abc1d23',
            'year' => '2022',
            'current_km' => 20000,
        ])
            ->assertOk()
            ->assertJsonPath('model', 'Argo')
            ->assertJsonPath('plate', 'ABC1D23');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'model' => 'Argo',
            'current_km' => 20000,
        ]);
    }

    public function test_vehicle_can_be_soft_deleted(): void
    {
        $client = Client::create([
            'name' => 'Cliente Remocao',
            'cpf' => '12345678901',
        ]);

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'model' => 'Onix',
            'brand' => 'Chevrolet',
            'plate' => 'ABC1D23',
        ]);

        $this->deleteJson("/api/workshop/vehicles/{$vehicle->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
    }
}
