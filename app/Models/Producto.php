<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $guarded = ['id'];

    protected $casts = [
        'controla_stock' => 'boolean',
        'vende_tpv' => 'boolean',
        'activo' => 'boolean',
        'precio_compra' => 'decimal:4',
        'precio_venta' => 'decimal:4',
        'precio_coste' => 'decimal:4',
        'stock_actual' => 'decimal:3',
        'stock_minimo' => 'decimal:3',
        'stock_optimo' => 'decimal:3',
        'iva' => 'decimal:2',
        'margen' => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function ingredientes()
    {
        return $this->hasMany(Receta::class);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function mermas()
    {
        return $this->hasMany(Merma::class);
    }

    public function imagenUrl(): string
    {
        if ($this->imagen) {
            return asset('storage/' . $this->imagen);
        }
        $emoji = match (true) {
            str_contains(strtolower($this->nombre), 'pan') => 'PAN',
            str_contains(strtolower($this->nombre), 'croissant') => 'CRO',
            str_contains(strtolower($this->nombre), 'pastel') => 'PAS',
            str_contains(strtolower($this->nombre), 'tarta') => 'TAR',
            default => mb_substr($this->nombre, 0, 3),
        };
        $color = $this->color_tpv ?: ($this->categoria?->color ?? 'C8763D');
        $color = ltrim($color, '#');
        return "https://placehold.co/200x200/{$color}/fff?text=" . urlencode($emoji);
    }

    public function tieneStockBajo(): bool
    {
        return $this->controla_stock && $this->stock_actual <= $this->stock_minimo;
    }

    public function scopeVendibles($query)
    {
        return $query->where('activo', true)->where('vende_tpv', true);
    }

    public function scopeStockBajo($query)
    {
        return $query->where('controla_stock', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo');
    }
}
