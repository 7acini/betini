<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);

        $providers = Provider::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('cnpj', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($providers);
    }

    public function store(StoreProviderRequest $request): JsonResponse
    {
        $provider = Provider::create($this->normalizeProviderData($request->validated()));

        return response()->json($provider, 201);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): JsonResponse
    {
        $provider->update($this->normalizeProviderData($request->validated()));

        return response()->json($provider->refresh());
    }

    public function destroy(Provider $provider): JsonResponse
    {
        abort_unless(request()->user()->can('manage-records'), 403);

        $provider->delete();

        return response()->json(status: 204);
    }

    private function normalizeProviderData(array $data): array
    {
        foreach (['cnpj', 'phone', 'postal_code'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', (string) $data[$field]);
            }
        }

        if (isset($data['state'])) {
            $data['state'] = mb_strtoupper($data['state']);
        }

        return $data;
    }
}
