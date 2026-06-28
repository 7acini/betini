<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_can_be_listed(): void
    {
        Service::create([
            'name' => 'Troca de oleo',
            'description' => 'Substituicao de oleo e filtro.',
            'base_price' => 120,
        ]);

        $this->getJson('/api/workshop/services')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Troca de oleo');
    }

    public function test_service_can_be_created(): void
    {
        $this->postJson('/api/workshop/services', [
            'name' => 'Alinhamento',
            'description' => 'Alinhamento dianteiro e traseiro.',
            'base_price' => 150.5,
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Alinhamento')
            ->assertJsonPath('description', 'Alinhamento dianteiro e traseiro.');

        $this->assertDatabaseHas('services', [
            'name' => 'Alinhamento',
            'base_price' => 150.5,
        ]);
    }

    public function test_service_requires_valid_name_and_price(): void
    {
        $this->postJson('/api/workshop/services', [
            'name' => 'A',
            'base_price' => -1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'base_price']);
    }

    public function test_service_can_be_updated(): void
    {
        $service = Service::create([
            'name' => 'Servico antigo',
            'base_price' => 50,
        ]);

        $this->putJson("/api/workshop/services/{$service->id}", [
            'name' => 'Balanceamento',
            'description' => 'Balanceamento das quatro rodas.',
            'base_price' => 90,
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Balanceamento');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Balanceamento',
            'base_price' => 90,
        ]);
    }

    public function test_service_can_be_soft_deleted(): void
    {
        $service = Service::create([
            'name' => 'Servico removido',
            'base_price' => 10,
        ]);

        $this->deleteJson("/api/workshop/services/{$service->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }
}
