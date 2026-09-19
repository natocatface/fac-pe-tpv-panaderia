<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $guarded = ['id'];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_envio_sunat' => 'datetime',
        'datos_cliente' => 'array',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'base_imponible' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'importe_efectivo' => 'decimal:2',
        'importe_tarjeta' => 'decimal:2',
        'importe_otros' => 'decimal:2',
        'cambio' => 'decimal:2',
    ];

    public function lineas()
    {
        return $this->hasMany(VentaLinea::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sesionCaja()
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    // ----- Relaciones SUNAT -----------------------------------------------

    public function logsSunat()
    {
        return $this->hasMany(ComprobanteLog::class)->orderByDesc('id');
    }

    public function ventaModifica()
    {
        return $this->belongsTo(Venta::class, 'venta_modifica_id');
    }

    public function notasRelacionadas()
    {
        return $this->hasMany(Venta::class, 'venta_modifica_id');
    }

    /** Número formal de comprobante SUNAT: F001-00000123 */
    public function getNumeroSunatAttribute(): ?string
    {
        if (! $this->serie || ! $this->correlativo) {
            return null;
        }
        return $this->serie . '-' . str_pad((string) $this->correlativo, 8, '0', STR_PAD_LEFT);
    }

    public function esElectronico(): bool
    {
        return in_array($this->sunat_tipo_comprobante, ['01', '03', '07', '08'], true);
    }

    public function getEstadoSunatClaseAttribute(): string
    {
        return match ($this->estado_sunat) {
            'aceptado'  => 'success',
            'enviado'   => 'info',
            'pendiente' => 'warning',
            'rechazado',
            'error'     => 'danger',
            'observado' => 'warning',
            'anulado'   => 'secondary',
            default     => 'light',
        };
    }

    public function scopeTickets($q)
    {
        return $q->where('tipo_documento', 'ticket');
    }

    public function scopeFacturas($q)
    {
        return $q->where('tipo_documento', 'factura');
    }

    public function scopeBoletas($q)
    {
        return $q->where('tipo_documento', 'boleta');
    }

    /** Comprobantes fiscales (incluye facturas y boletas Perú). */
    public function scopeFiscales($q)
    {
        return $q->whereIn('tipo_documento', ['factura', 'boleta']);
    }

    public function scopeHoy($q)
    {
        return $q->whereDate('fecha', today());
    }

    public function getEstadoClaseAttribute()
    {
        return match ($this->estado) {
            'pagada', 'emitida' => 'success',
            'borrador' => 'warning',
            'anulada', 'devuelta' => 'danger',
            default => 'secondary',
        };
    }
}
