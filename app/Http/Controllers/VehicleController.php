<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));

        $vehicles = Vehicle::query()
            ->with('client:id,name,cpf')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('model', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('plate', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json($vehicles);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = Vehicle::create($this->normalizeVehicleData($request->validated()))
            ->load('client:id,name,cpf');

        return response()->json($vehicle, 201);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle->update($this->normalizeVehicleData($request->validated()));

        return response()->json($vehicle->refresh()->load('client:id,name,cpf'));
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        abort_unless(request()->user()->can('manage-records'), 403);

        $vehicle->delete();

        return response()->json(status: 204);
    }

    private function normalizeVehicleData(array $data): array
    {
        if (isset($data['plate'])) {
            $data['plate'] = mb_strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '', (string) $data['plate']));
        }

        return $data;
    }
}
