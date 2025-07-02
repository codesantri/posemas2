<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    protected $guarded = [''];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    // Relasi ke new Cangeitems
    public function changeItems()
    {
        return $this->hasMany(ChangeItem::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    protected static function booted()
    {
        static::deleting(function ($change) {
            Transaction::where('invoice', $change->transaction_id)->delete();
        });
    }
}
