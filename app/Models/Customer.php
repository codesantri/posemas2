<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [''];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function changes()
    {
        return $this->hasMany(Change::class);
    }

    public function entrusts()
    {
        return $this->hasMany(Entrust::class);
    }
}
