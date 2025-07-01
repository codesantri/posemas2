<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeItem extends Model
{
    protected $guarded = [''];

    public function change()
    {
        return $this->belongsTo(Change::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
