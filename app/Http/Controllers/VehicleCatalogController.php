<?php

namespace App\Http\Controllers;

use App\Services\VehicleCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleCatalogController extends Controller
{
    public function brands(Request $request, VehicleCatalogService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->brands(
                query: (string) $request->query('search', ''),
            ),
        ]);
    }

    public function models(Request $request, VehicleCatalogService $service): JsonResponse
    {
        $validated = $request->validate([
            'brand_code' => ['required', 'string', 'max:20'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json([
            'data' => $service->models(
                brandCode: $validated['brand_code'],
                query: (string) ($validated['search'] ?? ''),
            ),
        ]);
    }
}
