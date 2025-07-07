<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrust extends Model
{
    protected $guarded = [''];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

}
