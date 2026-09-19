<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merma extends Model
{
    protected $table = 'mermas';
    protected $guarded = ['id'];

    protected $casts = [
        'fecha' => 'datetime',
        'cantidad' => 'decimal:3',
        'coste' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
