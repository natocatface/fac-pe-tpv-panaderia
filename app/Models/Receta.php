<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table = 'recetas';
    protected $guarded = ['id'];

    protected $casts = [
        'cantidad' => 'decimal:4',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function ingrediente()
    {
        return $this->belongsTo(Producto::class, 'ingrediente_id');
    }
}
