<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $guarded = ['id'];

    protected $casts = [
        'activo' => 'boolean',
        'descuento' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function getTotalComprasAttribute()
    {
        return $this->ventas()->sum('total');
    }
}
