<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [''];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function entrust()
    {
        return $this->hasOne(Entrust::class);
    }

    public function exchange()
    {
        return $this->hasOne(Change::class);
    }

    public function getRouteKeyName()
    {
        return 'invoice';
    }

    public function details()
    {
        return $this->hasMany(Detail::class);
    }



    protected static function booted()
    {
        static::creating(function ($transaction) {
            $prefixes = [
                'sale' => 'LMJE-',
                'purchase' => 'LMPBE-',
                'entrust' => 'LMTE-',
                'change' => 'LMTKR-',
            ];
            $prefix = $prefixes[$transaction->transaction_type] ?? 'TRX';
            if (!$transaction->invoice) {
                $today = now()->format('dmy'); // e.g., 280625
                $countToday = static::where('transaction_type', $transaction->transaction_type)
                    ->whereDate('created_at', now()->toDateString())
                    ->count() + 1;

                $formattedCount = str_pad($countToday, 2, '0', STR_PAD_LEFT);

                $transaction->invoice = $prefix . $today . $formattedCount;
            }
            if (Auth::check()) {
                $transaction->user_id = Auth::id();
            }
        });
    }
}
