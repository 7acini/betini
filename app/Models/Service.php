<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'base_price',
    ];

    protected function casts(): array
    {
        return ['base_price' => 'decimal:2'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderServices(): HasMany
    {
        return $this->hasMany(OrderService::class);
    }
}
