<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VehicleCatalogService
{
    public function brands(string $query = '', string $type = 'carros'): array
    {
        $brands = Cache::remember("vehicle_catalog.{$type}.brands", now()->addDay(), function () use ($type): array {
            return $this->get("/{$type}/marcas");
        });

        return $this->filterByName($brands, $query);
    }

    public function models(string $brandCode, string $query = '', string $type = 'carros'): array
    {
        $brandCode = trim($brandCode);

        if ($brandCode === '') {
            return [];
        }

        $payload = Cache::remember("vehicle_catalog.{$type}.brands.{$brandCode}.models", now()->addDay(), function () use ($type, $brandCode): array {
            return $this->get("/{$type}/marcas/{$brandCode}/modelos");
        });

        return $this->filterByName($payload['modelos'] ?? [], $query);
    }

    private function get(string $path): array
    {
        $baseUrl = rtrim((string) config('services.fipe.base_url'), '/');

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get($baseUrl.$path);

            if (! $response->ok()) {
                Log::warning('vehicle_catalog.request_failed', [
                    'path' => $path,
                    'status' => $response->status(),
                ]);

                return [];
            }

            return $response->json() ?? [];
        } catch (\Throwable $exception) {
            Log::warning('vehicle_catalog.request_exception', [
                'path' => $path,
                'exception' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    private function normalizeName(string $name): string
    {
        $parts = preg_split('/\s+-\s+/', trim($name));

        return trim((string) end($parts));
    }

    private function filterByName(array $items, string $query): array
    {
        $query = Str::of($query)->trim()->lower()->ascii()->toString();

        return collect($items)
            ->map(fn (array $item): array => [
                'code' => (string) ($item['codigo'] ?? $item['code'] ?? ''),
                'name' => $this->normalizeName((string) ($item['nome'] ?? $item['name'] ?? '')),
            ])
            ->filter(fn (array $item): bool => $item['code'] !== '' && $item['name'] !== '')
            ->when($query !== '', function ($collection) use ($query) {
                return $collection->filter(function (array $item) use ($query): bool {
                    return Str::of($item['name'])->lower()->ascii()->contains($query);
                });
            })
            ->sortBy('name')
            ->take(12)
            ->values()
            ->all();
    }
}
