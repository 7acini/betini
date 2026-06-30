<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VehicleCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_vehicle_catalog_requires_authentication(): void
    {
        $this->getJson('/api/workshop/vehicle-catalog/brands')
            ->assertUnauthorized();
    }

    public function test_brands_can_be_filtered_by_partial_name(): void
    {
        $this->authenticate();

        Http::fake([
            'parallelum.com.br/fipe/api/v1/carros/marcas' => Http::response([
                ['codigo' => '21', 'nome' => 'Fiat'],
                ['codigo' => '23', 'nome' => 'GM - Chevrolet'],
                ['codigo' => '59', 'nome' => 'Volkswagen'],
            ]),
        ]);

        $this->getJson('/api/workshop/vehicle-catalog/brands?search=Chev')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '23')
            ->assertJsonPath('data.0.name', 'Chevrolet');
    }

    public function test_public_landing_can_filter_vehicle_brands(): void
    {
        Http::fake([
            'parallelum.com.br/fipe/api/v1/carros/marcas' => Http::response([
                ['codigo' => '21', 'nome' => 'Fiat'],
                ['codigo' => '23', 'nome' => 'GM - Chevrolet'],
                ['codigo' => '59', 'nome' => 'Volkswagen'],
            ]),
        ]);

        $this->getJson('/api/landing/vehicle-catalog/brands?search=volks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '59')
            ->assertJsonPath('data.0.name', 'Volkswagen');
    }

    public function test_models_can_be_filtered_by_brand_and_partial_name(): void
    {
        $this->authenticate();

        Http::fake([
            'parallelum.com.br/fipe/api/v1/carros/marcas/23/modelos' => Http::response([
                'modelos' => [
                    ['codigo' => '4828', 'nome' => 'ONIX HATCH LT 1.0 8V FlexPower 5p Mec.'],
                    ['codigo' => '4695', 'nome' => 'CRUZE LT 1.8 16V FlexPower 4p Aut.'],
                    ['codigo' => '9999', 'nome' => 'S10 Pick-Up LT 2.8 TDI 4x4 CD Diesel'],
                ],
            ]),
        ]);

        $this->getJson('/api/workshop/vehicle-catalog/models?brand_code=23&search=onix')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '4828')
            ->assertJsonPath('data.0.name', 'ONIX HATCH LT 1.0 8V FlexPower 5p Mec.');
    }

    public function test_models_require_brand_code(): void
    {
        $this->authenticate();

        $this->getJson('/api/workshop/vehicle-catalog/models')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand_code']);
    }
}
