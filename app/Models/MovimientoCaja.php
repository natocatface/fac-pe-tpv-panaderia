<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';
    protected $guarded = ['id'];

    protected $casts = [
        'fecha' => 'datetime',
        'importe' => 'decimal:2',
    ];

    public function sesion()
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }
}
