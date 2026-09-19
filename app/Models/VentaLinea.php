<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaLinea extends Model
{
    protected $table = 'venta_lineas';
    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio_unitario' => 'decimal:4',
        'descuento' => 'decimal:2',
        'iva' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
