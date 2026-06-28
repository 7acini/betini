<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cnpj',
        'phone',
        'postal_code',
        'address',
        'address_number',
        'complement',
        'city',
        'state',
        'website_url',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
