<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fichaje extends Model
{
    protected $table = 'fichajes';
    protected $guarded = ['id'];

    protected $casts = [
        'entrada' => 'datetime',
        'salida' => 'datetime',
        'horas' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calcularHoras(): float
    {
        if (!$this->salida) return 0;
        return round($this->entrada->floatDiffInHours($this->salida), 2);
    }
}
