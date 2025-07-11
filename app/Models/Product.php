<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [''];
    protected $casts = [
        'weight' => 'float',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function karat()
    {
        return $this->belongsTo(Karat::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function changeItems()
    {
        return $this->hasMany(ChangeItem::class);
    }

    public function entrustDetails()
    {
        return $this->hasMany(EntrustDetail::class);
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->onDelete(true);
        });
    }
}
