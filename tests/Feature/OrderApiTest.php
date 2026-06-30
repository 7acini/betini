<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_orders_can_be_listed_with_relations(): void
    {
        $client = Client::create(['name' => 'Cliente OS', 'cpf' => '12345678901']);
        $service = Service::create(['name' => 'Revisao', 'base_price' => 200]);

        Order::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'status' => 'Aberto',
            'service_total' => 200,
            'items_total' => 0,
            'total' => 200,
        ]);

        $this->getJson('/api/workshop/orders')
            ->assertOk()
            ->assertJsonPath('data.0.client.name', 'Cliente OS')
            ->assertJsonPath('data.0.service.name', 'Revisao');
    }

    public function test_order_can_be_created_with_calculated_totals(): void
    {
        $client = Client::create(['name' => 'Cliente OS', 'cpf' => '12345678901']);
        $oilChange = Service::create(['name' => 'Troca de oleo', 'base_price' => 120]);
        $alignment = Service::create(['name' => 'Alinhamento', 'base_price' => 160]);
        $product = Product::create(['name' => 'Filtro', 'price' => 30]);

        $this->postJson('/api/workshop/orders', [
            'client_id' => $client->id,
            'status' => 'Aberto',
            'payment_method' => 'Pix',
            'services' => [
                ['service_id' => $oilChange->id, 'quantity' => 1],
                ['service_id' => $alignment->id, 'quantity' => 2, 'price' => 150],
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('client.name', 'Cliente OS')
            ->assertJsonPath('service.name', 'Troca de oleo')
            ->assertJsonPath('services.0.service.name', 'Troca de oleo')
            ->assertJsonPath('services.1.service.name', 'Alinhamento')
            ->assertJsonPath('items.0.product.name', 'Filtro');

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'service_id' => $oilChange->id,
            'service_total' => 420,
            'items_total' => 60,
            'total' => 480,
        ]);
        $this->assertDatabaseHas('order_services', [
            'service_id' => $oilChange->id,
            'price' => 120,
            'quantity' => 1,
            'subtotal' => 120,
        ]);
        $this->assertDatabaseHas('order_services', [
            'service_id' => $alignment->id,
            'price' => 150,
            'quantity' => 2,
            'subtotal' => 300,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'price' => 30,
            'quantity' => 2,
            'subtotal' => 60,
        ]);
    }

    public function test_order_requires_valid_status_client_and_items(): void
    {
        $this->postJson('/api/workshop/orders', [
            'client_id' => 999,
            'status' => 'Invalido',
            'services' => [
                ['service_id' => 999, 'quantity' => 0],
            ],
            'items' => [
                ['product_id' => 999, 'quantity' => 0],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['client_id', 'status', 'services.0.service_id', 'services.0.quantity', 'items.0.product_id', 'items.0.quantity']);
    }

    public function test_order_can_be_updated_and_items_are_synced(): void
    {
        $client = Client::create(['name' => 'Cliente OS', 'cpf' => '12345678901']);
        $service = Service::create(['name' => 'Diagnostico', 'base_price' => 80]);
        $product = Product::create(['name' => 'Pastilha', 'price' => 100]);
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'Aberto',
            'service_total' => 0,
            'items_total' => 0,
            'total' => 0,
        ]);

        $this->putJson("/api/workshop/orders/{$order->id}", [
            'client_id' => $client->id,
            'status' => 'Em andamento',
            'services' => [
                ['service_id' => $service->id, 'quantity' => 2, 'price' => 90],
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'price' => 110],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('status', 'Em andamento');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Em andamento',
            'service_id' => $service->id,
            'service_total' => 180,
            'items_total' => 330,
            'total' => 510,
        ]);
        $this->assertDatabaseHas('order_services', [
            'order_id' => $order->id,
            'service_id' => $service->id,
            'price' => 90,
            'quantity' => 2,
            'subtotal' => 180,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 110,
            'quantity' => 3,
            'subtotal' => 330,
        ]);
    }

    public function test_order_can_be_soft_deleted(): void
    {
        $client = Client::create(['name' => 'Cliente Remocao', 'cpf' => '12345678901']);
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'Aberto',
            'service_total' => 0,
            'items_total' => 0,
            'total' => 0,
        ]);

        $this->deleteJson("/api/workshop/orders/{$order->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }
}
