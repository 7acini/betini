<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->authenticate();
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_workshop_dashboard_returns_summary_payload(): void
    {
        $this->authenticate();

        $response = $this->getJson('/api/workshop/dashboard');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'metrics' => [
                    '*' => ['label', 'value', 'trend'],
                ],
                'modules' => [
                    '*' => ['name', 'count', 'description'],
                ],
                'ordersByStatus',
                'recentOrders',
                'user' => ['name', 'role', 'canManageRecords'],
            ]);
    }
}
