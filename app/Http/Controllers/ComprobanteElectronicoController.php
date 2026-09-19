<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Venta;
use App\Services\Sunat\FacturacionElectronicaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Endpoints para gestionar el ciclo de vida de los comprobantes
 * electrónicos SUNAT desde la UI.
 */
class ComprobanteElectronicoController extends Controller
{
    /** Listado de comprobantes electrónicos con su estado SUNAT. */
    public function index(Request $request)
    {
        $query = Venta::with(['cliente'])
            ->whereIn('sunat_tipo_comprobante', ['01', '03', '07', '08']);

        if ($estado = $request->input('estado_sunat')) {
            $query->where('estado_sunat', $estado);
        }
        if ($tipo = $request->input('tipo')) {
            $query->where('sunat_tipo_comprobante', $tipo);
        }
        if ($desde = $request->input('desde')) {
            $query->whereDate('fecha', '>=', $desde);
        }
        if ($hasta = $request->input('hasta')) {
            $query->whereDate('fecha', '<=', $hasta);
        }

        $comprobantes = $query->latest('fecha')->paginate(25)->withQueryString();

        return view('sunat.index', compact('comprobantes'));
    }

    public function show(Venta $venta)
    {
        abort_unless($venta->esElectronico(), 404);
        $venta->load(['lineas', 'cliente', 'logsSunat']);
        return view('sunat.show', compact('venta'));
    }

    /** Emite (envía a SUNAT) un comprobante pendiente. */
    public function emitir(Venta $venta, FacturacionElectronicaService $sunat)
    {
        abort_unless($venta->esElectronico(), 404);
        $venta = $sunat->emitir($venta);

        $msg = match ($venta->estado_sunat) {
            'aceptado'  => "Comprobante {$venta->numero_sunat} ACEPTADO por SUNAT.",
            'observado' => "Comprobante {$venta->numero_sunat} aceptado con observaciones: {$venta->mensaje_sunat}",
            'rechazado' => "SUNAT rechazó el comprobante: {$venta->mensaje_sunat}",
            default     => "Comprobante en estado {$venta->estado_sunat}: {$venta->mensaje_sunat}",
        };
        $flash = $venta->estado_sunat === 'aceptado' ? 'success' : 'warning';

        return back()->with($flash, $msg);
    }

    public function reintentar(Venta $venta, FacturacionElectronicaService $sunat)
    {
        try {
            $sunat->reintentar($venta);
            return back()->with('success', "Reintento enviado: {$venta->fresh()->estado_sunat}");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Emite una nota de crédito (anulación) referenciando la venta original.
     */
    public function anular(Request $request, Venta $venta, FacturacionElectronicaService $sunat)
    {
        abort_unless(in_array($venta->sunat_tipo_comprobante, ['01', '03'], true), 422);
        abort_unless($venta->estado_sunat === 'aceptado', 422,
            'Solo se pueden anular comprobantes aceptados por SUNAT.');

        $data = $request->validate([
            'codigo_motivo_nota' => 'required|string|max:3',
            'motivo_nota'        => 'required|string|max:250',
        ]);

        $nota = Venta::create([
            'numero'                 => 'NC-TMP-' . $venta->id . '-' . time(),
            'tipo_documento'         => $venta->tipo_documento,
            'sunat_tipo_comprobante' => '07',
            'fecha'                  => now(),
            'cliente_id'             => $venta->cliente_id,
            'user_id'                => auth()->id() ?? $venta->user_id,
            'subtotal'               => $venta->subtotal,
            'descuento'              => $venta->descuento,
            'base_imponible'         => $venta->base_imponible,
            'impuestos'              => $venta->impuestos,
            'total'                  => $venta->total,
            'moneda'                 => $venta->moneda,
            'estado'                 => 'emitida',
            'datos_cliente'          => $venta->datos_cliente,
            'venta_modifica_id'      => $venta->id,
            'codigo_motivo_nota'     => $data['codigo_motivo_nota'],
            'motivo_nota'            => $data['motivo_nota'],
        ]);
        // Copiar líneas
        foreach ($venta->lineas as $l) {
            $nota->lineas()->create($l->only([
                'producto_id', 'descripcion', 'cantidad', 'precio_unitario',
                'descuento', 'iva', 'subtotal', 'total',
            ]));
        }

        $sunat->emitir($nota);

        return redirect()
            ->route('sunat.show', $nota)
            ->with('success', "Nota de crédito {$nota->fresh()->numero_sunat} emitida.");
    }

    public function descargarXml(Venta $venta)
    {
        abort_unless($venta->xml_path && Storage::disk('local')->exists($venta->xml_path), 404);
        return Storage::disk('local')->download(
            $venta->xml_path,
            $venta->numero_sunat . '.xml'
        );
    }

    public function descargarCdr(Venta $venta)
    {
        abort_unless($venta->cdr_path && Storage::disk('local')->exists($venta->cdr_path), 404);
        return Storage::disk('local')->download(
            $venta->cdr_path,
            'R-' . $venta->numero_sunat . '.zip'
        );
    }

    /**
     * Genera la representación impresa (PDF) del comprobante electrónico
     * con QR SUNAT, hash y leyenda obligatoria.
     */
    public function pdf(Venta $venta)
    {
        abort_unless($venta->esElectronico(), 404);
        $venta->load(['lineas', 'cliente']);
        $config = Configuracion::actual();

        // QR SUNAT — formato oficial:
        // RUC|TipoDoc|Serie|Correlativo|MtoIGV|MtoTotal|FechaEmision|TipoDocAdq|NumDocAdq|HashCPE
        $qr = implode('|', [
            $config->sunat_ruc ?: '',
            $venta->sunat_tipo_comprobante,
            $venta->serie,
            $venta->correlativo,
            number_format($venta->impuestos, 2, '.', ''),
            number_format($venta->total, 2, '.', ''),
            $venta->fecha?->format('Y-m-d'),
            $venta->cliente?->tipo_documento_sunat ?? ($venta->datos_cliente['tipo_documento_sunat'] ?? '0'),
            $venta->cliente?->numero_documento     ?? ($venta->datos_cliente['numero_documento']     ?? '00000000'),
            $venta->hash ?? '',
        ]);

        $qrDataUri = null;
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qr)
                ->size(180)
                ->margin(0)
                ->build();
            $qrDataUri = $result->getDataUri();
        } catch (\Throwable $e) {
            // QR opcional — si falla, el PDF se imprime sin él.
        }

        $pdf = Pdf::loadView('sunat.pdf', [
            'venta'  => $venta,
            'config' => $config,
            'qrData' => $qrDataUri,
            'qrTxt'  => $qr,
        ])->setPaper('a4');

        return $pdf->stream(($venta->numero_sunat ?: 'comprobante') . '.pdf');
    }
}
