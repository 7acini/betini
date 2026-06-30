<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopAppointment extends Model
{
    use HasFactory;

    public const STATUSES = ['Novo', 'Confirmado', 'Convertido em OS', 'Cancelado'];

    protected $fillable = [
        'lead_name',
        'lead_cpf',
        'lead_phone',
        'lead_email',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_plate',
        'vehicle_year',
        'vehicle_current_km',
        'desired_service',
        'message',
        'scheduled_date',
        'scheduled_time',
        'status',
        'client_id',
        'vehicle_id',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'vehicle_current_km' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
