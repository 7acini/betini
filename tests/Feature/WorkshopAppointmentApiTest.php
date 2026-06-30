<?php

namespace Tests\Feature;

use App\Models\WorkshopAppointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopAppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lead_can_request_appointment(): void
    {
        $this->postJson('/api/landing/appointments', [
            'lead_name' => 'Lead Betini',
            'lead_cpf' => '123.456.789-01',
            'lead_phone' => '(11) 99999-0000',
            'vehicle_brand' => 'Audi',
            'vehicle_model' => 'A3',
            'vehicle_plate' => 'ABC1D23',
            'desired_service' => 'Revisao preventiva',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Agendamento solicitado com sucesso.');

        $this->assertDatabaseHas('workshop_appointments', [
            'lead_name' => 'Lead Betini',
            'lead_cpf' => '12345678901',
            'vehicle_plate' => 'ABC1D23',
            'status' => 'Novo',
        ]);
    }

    public function test_authenticated_user_can_convert_appointment_to_order(): void
    {
        $this->authenticate();

        $appointment = WorkshopAppointment::create([
            'lead_name' => 'Lead OS',
            'lead_cpf' => '12345678901',
            'lead_phone' => '11999990000',
            'vehicle_brand' => 'Volkswagen',
            'vehicle_model' => 'Golf',
            'vehicle_plate' => 'BET1N11',
            'desired_service' => 'Diagnostico',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '10:00',
        ]);

        $this->postJson("/api/workshop/appointments/{$appointment->id}/convert-to-order")
            ->assertOk()
            ->assertJsonPath('status', 'Convertido em OS');

        $this->assertDatabaseHas('clients', [
            'cpf' => '12345678901',
            'name' => 'Lead OS',
        ]);
        $this->assertDatabaseHas('vehicles', [
            'plate' => 'BET1N11',
            'brand' => 'Volkswagen',
        ]);
        $this->assertDatabaseHas('orders', [
            'appointment_id' => $appointment->id,
            'status' => 'Aberto',
        ]);
    }
}
