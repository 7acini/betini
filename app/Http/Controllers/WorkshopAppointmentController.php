<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Vehicle;
use App\Models\WorkshopAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkshopAppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', now()->year);

        $appointments = WorkshopAppointment::query()
            ->with(['client:id,name,cpf', 'vehicle:id,brand,model,plate', 'order:id'])
            ->whereYear('scheduled_date', $year)
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        return response()->json([
            'year' => $year,
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lead_name' => ['required', 'string', 'max:120'],
            'lead_cpf' => ['required', 'string', 'max:14'],
            'lead_phone' => ['required', 'string', 'max:30'],
            'lead_email' => ['nullable', 'email', 'max:160'],
            'vehicle_brand' => ['required', 'string', 'max:80'],
            'vehicle_model' => ['required', 'string', 'max:100'],
            'vehicle_plate' => ['required', 'string', 'max:12'],
            'vehicle_year' => ['nullable', 'digits:4'],
            'vehicle_current_km' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'desired_service' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:3000'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'date_format:H:i'],
        ]);

        $exists = WorkshopAppointment::query()
            ->whereDate('scheduled_date', $data['scheduled_date'])
            ->whereTime('scheduled_time', $data['scheduled_time'])
            ->whereIn('status', ['Novo', 'Confirmado'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Horario indisponivel para agendamento.',
                'errors' => ['scheduled_time' => ['Este horario acabou de ser reservado.']],
            ], 422);
        }

        $appointment = WorkshopAppointment::create([
            ...$data,
            'lead_cpf' => preg_replace('/\D+/', '', $data['lead_cpf']),
            'vehicle_plate' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['vehicle_plate'])),
        ]);

        return response()->json([
            'message' => 'Agendamento solicitado com sucesso.',
            'appointment' => $appointment,
        ], 201);
    }

    public function update(Request $request, WorkshopAppointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(WorkshopAppointment::STATUSES)],
        ]);

        $appointment->update($data);

        return response()->json($appointment->refresh());
    }

    public function convertToOrder(WorkshopAppointment $appointment): JsonResponse
    {
        if ($appointment->order_id) {
            return response()->json($appointment->load(['client', 'vehicle', 'order']));
        }

        $appointment = DB::transaction(function () use ($appointment): WorkshopAppointment {
            $client = Client::withTrashed()->firstOrCreate(
                ['cpf' => $appointment->lead_cpf],
                [
                    'name' => $appointment->lead_name,
                    'phone' => $appointment->lead_phone,
                ],
            );

            if ($client->trashed()) {
                $client->restore();
            }

            $client->fill([
                'name' => $appointment->lead_name,
                'phone' => $appointment->lead_phone,
            ])->save();

            $vehicle = Vehicle::withTrashed()->firstOrCreate(
                ['plate' => $appointment->vehicle_plate],
                [
                    'client_id' => $client->id,
                    'brand' => $appointment->vehicle_brand,
                    'model' => $appointment->vehicle_model,
                    'year' => $appointment->vehicle_year,
                    'current_km' => $appointment->vehicle_current_km,
                ],
            );

            if ($vehicle->trashed()) {
                $vehicle->restore();
            }

            $vehicle->fill([
                'client_id' => $client->id,
                'brand' => $appointment->vehicle_brand,
                'model' => $appointment->vehicle_model,
                'year' => $appointment->vehicle_year,
                'current_km' => $appointment->vehicle_current_km,
            ])->save();

            $order = Order::create([
                'client_id' => $client->id,
                'appointment_id' => $appointment->id,
                'status' => 'Aberto',
                'observation' => trim("Origem: agendamento do site.\nVeiculo: {$vehicle->brand} {$vehicle->model} - {$vehicle->plate}.\nServico desejado: {$appointment->desired_service}.\nMensagem: {$appointment->message}"),
                'service_total' => 0,
                'items_total' => 0,
                'total' => 0,
            ]);

            $appointment->update([
                'status' => 'Convertido em OS',
                'client_id' => $client->id,
                'vehicle_id' => $vehicle->id,
                'order_id' => $order->id,
            ]);

            return $appointment->refresh();
        });

        return response()->json($appointment->load(['client', 'vehicle', 'order']));
    }
}
