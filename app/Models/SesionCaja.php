<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesionCaja extends Model
{
    protected $table = 'sesiones_caja';
    protected $guarded = ['id'];

    protected $casts = [
        'apertura' => 'datetime',
        'cierre' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'saldo_calculado' => 'decimal:2',
        'saldo_contado' => 'decimal:2',
        'descuadre' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sesion_caja_id');
    }
}
