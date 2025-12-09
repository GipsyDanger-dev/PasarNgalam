<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Tambahkan 'category' dan 'addons' disini
    protected $fillable = [
        'merchant_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'category', // <--- Pastikan ini ada
        'addons'    // <--- Pastikan ini ada
    ];

    // Pastikan addons dibaca sebagai array/json otomatis
    protected $casts = [
        'addons' => 'array',
        'is_available' => 'boolean',
    ];

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }
}