<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Numerador atómico de series SUNAT.
 *
 * `siguienteCorrelativo()` obtiene el próximo número con lock pesimista,
 * de modo que dos ventas concurrentes nunca obtengan el mismo correlativo.
 * Si la serie no existe se crea bajo demanda en `0`.
 */
class SerieCorrelativo extends Model
{
    protected $table = 'series_correlativos';
    protected $guarded = ['id'];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_correlativo' => 'integer',
    ];

    /**
     * Reserva el siguiente correlativo para (tipo, serie).
     * Devuelve un entero estrictamente creciente. Debe usarse dentro
     * de una transacción del controlador.
     */
    public static function siguienteCorrelativo(string $tipo, string $serie): int
    {
        // Aseguramos que la fila exista ANTES de entrar al lock pesimista
        // para evitar la condición de carrera SELECT-NULL → INSERT colisión.
        // `firstOrCreate` es atómico contra el índice único (tipo, serie).
        try {
            static::firstOrCreate(
                ['tipo_comprobante' => $tipo, 'serie' => $serie],
                ['ultimo_correlativo' => 0, 'activo' => true]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Ignoramos 23000 (duplicate key) — otro proceso la creó.
            if ((string) $e->getCode() !== '23000') throw $e;
        }

        return DB::transaction(function () use ($tipo, $serie) {
            $row = static::where('tipo_comprobante', $tipo)
                ->where('serie', $serie)
                ->lockForUpdate()
                ->firstOrFail();
            $row->ultimo_correlativo += 1;
            $row->save();
            return (int) $row->ultimo_correlativo;
        });
    }
}
