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
        static::creating(function ($change) {
            if (!$change->invoice) {
                $prefix = 'CHG';
                $today = now()->format('dmy'); // 060725
                $countToday = static::whereDate('created_at', now()->toDateString())->count() + 1;
                $formattedCount = str_pad($countToday, 2, '0', STR_PAD_LEFT); // 2 digit urut

                $change->invoice = $prefix . $today . $formattedCount;
            }
        });

        static::deleting(function ($change) {
            Transaction::where('invoice', $change->invoice)->delete();
        });
    }
}
