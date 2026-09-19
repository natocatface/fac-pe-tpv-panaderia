<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';
    protected $guarded = ['id'];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function sesiones()
    {
        return $this->hasMany(SesionCaja::class);
    }
}
