<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function pawnings()
    {
        return $this->hasMany(Pawning::class);
    }

    public function orderservices()
    {
        return $this->hasMany(OrderService::class);
    }

    public function changes()
    {
        return $this->hasMany(Change::class);
    }
}
