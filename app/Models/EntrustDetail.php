<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntrustDetail extends Model
{
    protected $guarded = [''];

    public function entrust()
    {
        return $this->belongsTo(Entrust::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
