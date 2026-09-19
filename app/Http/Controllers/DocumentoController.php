<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaLinea;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index()
    {
        $tickets = Venta::tickets()->count();
        $facturas = Venta::facturas()->count();
        $presupuestos = Venta::where('tipo_documento', 'presupuesto')->count();
        $albaranes = Venta::where('tipo_documento', 'albaran')->count();
        return view('documentos.index', compact('tickets', 'facturas', 'presupuestos', 'albaranes'));
    }

    public function tickets(Request $request)
    {
        return $this->lista($request, 'ticket', 'Tickets');
    }

    public function facturas(Request $request)
    {
        return $this->lista($request, 'factura', 'Facturas');
    }

    public function presupuestos(Request $request)
    {
        return $this->lista($request, 'presupuesto', 'Presupuestos');
    }

    public function albaranes(Request $request)
    {
        return $this->lista($request, 'albaran', 'Albaranes');
    }

    protected function lista(Request $request, $tipo, $titulo)
    {
        $documentos = Venta::with(['cliente', 'user'])
            ->where('tipo_documento', $tipo)
            ->when($request->q, fn($q) => $q->where('numero', 'like', "%{$request->q}%"))
            ->when($request->desde, fn($q) => $q->whereDate('fecha', '>=', $request->desde))
            ->when($request->hasta, fn($q) => $q->whereDate('fecha', '<=', $request->hasta))
            ->when($request->cliente, fn($q) => $q->where('cliente_id', $request->cliente))
            ->orderByDesc('fecha')
            ->paginate(25);
        $clientes = Cliente::orderBy('nombre')->get();
        return view('documentos.lista', compact('documentos', 'tipo', 'titulo', 'clientes'));
    }

    public function crear($tipo)
    {
        $clientes = Cliente::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::vendibles()->orderBy('nombre')->get();
        $config = Configuracion::actual();
        return view('documentos.crear', compact('tipo', 'clientes', 'productos', 'config'));
    }

    public function store(Request $request, $tipo)
    {
        return app(TpvController::class)->storeVenta($request->merge(['tipo_documento' => $tipo]));
    }

    public function show($tipo, $id)
    {
        $documento = Venta::with(['lineas.producto', 'cliente', 'user'])->findOrFail($id);
        $config = Configuracion::actual();
        return view('documentos.show', compact('documento', 'config'));
    }

    public function pdf($tipo, $id)
    {
        $documento = Venta::with(['lineas.producto', 'cliente', 'user'])->findOrFail($id);
        $config = Configuracion::actual();
        $pdf = Pdf::loadView('documentos.pdf', compact('documento', 'config'));
        return $pdf->stream($documento->numero . '.pdf');
    }
}
