<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $guarded = ['id'];

    protected $casts = [
        'impuestos_activos' => 'boolean',
        'precios_con_impuestos' => 'boolean',
        'stock_negativo' => 'boolean',
        'descontar_stock_venta' => 'boolean',
        'alertas_stock' => 'boolean',
        'iva_general' => 'decimal:2',
        'iva_reducido' => 'decimal:2',
        'iva_superreducido' => 'decimal:2',

        // SUNAT (Perú) - credenciales sensibles cifradas en BD
        'sunat_activo'                => 'boolean',
        'sunat_igv_porcentaje'        => 'decimal:2',
        'sunat_sol_clave'             => 'encrypted',
        'sunat_certificado_password'  => 'encrypted',
    ];

    public static function actual(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }

    public function formatearMoneda($valor): string
    {
        $valor = (float) $valor;
        $formatted = number_format(
            $valor,
            $this->decimales,
            $this->separador_decimales,
            $this->separador_miles
        );
        return $this->moneda_posicion === 'izquierda'
            ? $this->moneda_simbolo . ' ' . $formatted
            : $formatted . ' ' . $this->moneda_simbolo;
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
