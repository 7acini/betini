<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);

        $orders = Order::query()
            ->with(['client:id,name,cpf', 'service:id,name', 'services.service:id,name', 'items.product:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('status', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('service', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('services.service', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = DB::transaction(fn () => $this->persistOrder(new Order(), $request->validated()));

        return response()->json($order, 201);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $order = DB::transaction(fn () => $this->persistOrder($order, $request->validated()));

        return response()->json($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        abort_unless(request()->user()->can('manage-records'), 403);

        $order->delete();

        return response()->json(status: 204);
    }

    private function persistOrder(Order $order, array $data): Order
    {
        $items = $data['items'] ?? [];
        $services = $data['services'] ?? $this->legacyServicePayload($data);
        unset($data['items'], $data['services']);

        $servicesPayload = $this->buildServicesPayload($services);
        $serviceTotal = array_sum(array_column($servicesPayload, 'subtotal'));
        $itemsPayload = $this->buildItemsPayload($items);
        $itemsTotal = array_sum(array_column($itemsPayload, 'subtotal'));
        $firstServiceId = $servicesPayload[0]['service_id'] ?? ($data['service_id'] ?? null);

        $order->fill([
            ...$data,
            'service_id' => $firstServiceId,
            'service_total' => $serviceTotal,
            'items_total' => $itemsTotal,
            'total' => $serviceTotal + $itemsTotal,
        ]);
        $order->save();

        $order->services()->delete();
        $order->services()->createMany($servicesPayload);

        $order->items()->delete();
        $order->items()->createMany($itemsPayload);

        return $order->refresh()->load(['client:id,name,cpf', 'service:id,name', 'services.service:id,name', 'items.product:id,name']);
    }

    private function legacyServicePayload(array $data): array
    {
        if (empty($data['service_id'])) {
            return [];
        }

        return [[
            'service_id' => $data['service_id'],
            'quantity' => 1,
            'price' => $data['service_total'] ?? null,
        ]];
    }

    private function buildServicesPayload(array $services): array
    {
        $serviceIds = collect($services)->pluck('service_id')->filter()->unique();
        $availableServices = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

        return collect($services)
            ->map(function (array $item) use ($availableServices): array {
                $service = $availableServices->get($item['service_id']);
                $price = isset($item['price']) ? (float) $item['price'] : (float) ($service?->base_price ?? 0);
                $quantity = (int) $item['quantity'];

                return [
                    'service_id' => $item['service_id'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            })
            ->values()
            ->all();
    }

    private function buildItemsPayload(array $items): array
    {
        $productIds = collect($items)->pluck('product_id')->filter()->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return collect($items)
            ->map(function (array $item) use ($products): array {
                $product = $products->get($item['product_id']);
                $price = isset($item['price']) ? (float) $item['price'] : (float) ($product?->price ?? 0);
                $quantity = (int) $item['quantity'];

                return [
                    'product_id' => $item['product_id'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            })
            ->values()
            ->all();
    }
}
