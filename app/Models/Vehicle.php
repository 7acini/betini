<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'model',
        'brand',
        'plate',
        'year',
        'current_km',
        'color',
        'fuel_type',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
