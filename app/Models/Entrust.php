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

    public function entrustDetails()
    {
        return $this->hasMany(EntrustDetail::class);
    }


    protected static function booted()
    {
        static::deleting(function ($entrust) {
            Transaction::where('id', $entrust->transaction_id)->delete();
        });
    }
}
