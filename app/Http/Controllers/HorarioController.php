<?php

namespace App\Http\Controllers;

use App\Models\Fichaje;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        $abierto = Fichaje::where('user_id', $usuario->id)->whereNull('salida')->latest()->first();
        $hoy = Fichaje::where('user_id', $usuario->id)
            ->whereDate('entrada', today())
            ->get();
        $horasHoy = $hoy->sum('horas');
        $fichajesSemana = Fichaje::where('user_id', $usuario->id)
            ->whereBetween('entrada', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->orderByDesc('entrada')
            ->get();

        // Empleados con jornada activa
        $activos = Fichaje::with('user')->whereNull('salida')->get();

        return view('horarios.index', compact('abierto', 'hoy', 'horasHoy', 'fichajesSemana', 'activos'));
    }

    public function fichar(Request $request)
    {
        $usuario = auth()->user();
        $abierto = Fichaje::where('user_id', $usuario->id)->whereNull('salida')->latest()->first();

        if ($abierto) {
            $horas = $abierto->entrada->floatDiffInHours(now());
            $abierto->update([
                'salida' => now(),
                'horas' => round($horas, 2),
            ]);
            return back()->with('success', 'Salida registrada: ' . number_format($horas, 2) . ' horas trabajadas.');
        }

        Fichaje::create([
            'user_id' => $usuario->id,
            'entrada' => now(),
            'ip' => $request->ip(),
        ]);
        return back()->with('success', 'Entrada registrada a las ' . now()->format('H:i'));
    }

    public function historico(Request $request)
    {
        $usuarios = User::where('activo', true)->orderBy('name')->get();
        $usuarioId = $request->get('usuario');
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', Carbon::now()->format('Y-m-d'));

        $fichajes = Fichaje::with('user')
            ->when($usuarioId, fn($q) => $q->where('user_id', $usuarioId))
            ->whereBetween('entrada', [$desde, $hasta . ' 23:59:59'])
            ->orderByDesc('entrada')
            ->paginate(30);

        $totalHoras = (clone $fichajes)->getCollection()->sum('horas');

        return view('horarios.historico', compact('fichajes', 'usuarios', 'desde', 'hasta', 'totalHoras', 'usuarioId'));
    }

    public function empleado(User $usuario)
    {
        $fichajes = Fichaje::where('user_id', $usuario->id)
            ->whereMonth('entrada', now()->month)
            ->orderByDesc('entrada')
            ->get();
        $horasMes = $fichajes->sum('horas');
        return view('horarios.empleado', compact('usuario', 'fichajes', 'horasMes'));
    }
}
