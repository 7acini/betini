<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));

        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);

        $clients = Client::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($this->normalizeClientData($request->validated()));

        return response()->json($client, 201);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($this->normalizeClientData($request->validated()));

        return response()->json($client->refresh());
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(status: 204);
    }

    private function normalizeClientData(array $data): array
    {
        foreach (['cpf', 'phone', 'postal_code'] as $field) {
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
