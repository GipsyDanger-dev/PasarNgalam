<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'merchant_id', 'driver_id', 'delivery_address',
        'total_price', 'delivery_fee', 'status',
        // Payment
        'payment_status', 'payment_method', 'payment_code',
        // coordinates saved at checkout
        'dest_latitude', 'dest_longitude',
        // timestamps
        'picked_at',
        // TAMBAHAN BARU: items (Menyimpan data keranjang dalam 1 kolom)
        'items' 
    ];

    // Casting agar otomatis berubah jadi Array saat diambil dari DB
    protected $casts = [
        'items' => 'array', 
        'picked_at' => 'datetime',
    ];

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}