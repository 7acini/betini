<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WorkshopDashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $ordersByStatus = Order::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status');

        return response()->json([
            'metrics' => [
                ['label' => 'Clientes', 'value' => Client::count(), 'trend' => '+0%'],
                ['label' => 'Veiculos', 'value' => Vehicle::count(), 'trend' => '+0%'],
                ['label' => 'Ordens abertas', 'value' => Order::where('status', 'Aberto')->count(), 'trend' => '+0%'],
                ['label' => 'Produtos', 'value' => Product::count(), 'trend' => '+0%'],
            ],
            'modules' => [
                ['name' => 'Clientes', 'count' => Client::count(), 'description' => 'Cadastro, contato e endereco dos clientes.'],
                ['name' => 'Veiculos', 'count' => Vehicle::count(), 'description' => 'Frota vinculada aos clientes da oficina.'],
                ['name' => 'Fornecedores', 'count' => Provider::count(), 'description' => 'Parceiros e origem dos produtos.'],
                ['name' => 'Produtos', 'count' => Product::count(), 'description' => 'Pecas, insumos e itens comercializados.'],
                ['name' => 'Servicos', 'count' => Service::count(), 'description' => 'Servicos executados pela equipe tecnica.'],
                ['name' => 'Pedidos', 'count' => Order::count(), 'description' => 'Ordens de servico e vendas em andamento.'],
            ],
            'ordersByStatus' => $ordersByStatus,
            'recentOrders' => Order::query()
                ->with(['client:id,name', 'service:id,name', 'services.service:id,name'])
                ->latest()
                ->limit(5)
                ->get(['id', 'client_id', 'service_id', 'status', 'total', 'created_at'])
                ->map(fn (Order $order): array => [
                    'id' => $order->id,
                    'client' => $order->client?->name,
                    'service' => $order->services->pluck('service.name')->filter()->join(', ') ?: $order->service?->name,
                    'status' => $order->status,
                    'total' => $order->total,
                    'createdAt' => $order->created_at?->toDateString(),
                ]),
            'user' => [
                'name' => request()->user()->name,
                'role' => request()->user()->role,
                'canManageRecords' => request()->user()->can('manage-records'),
            ],
        ]);
    }
}
