<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteLog extends Model
{
    protected $table = 'comprobantes_log';
    protected $guarded = ['id'];

    protected $casts = [
        'exitoso' => 'boolean',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
