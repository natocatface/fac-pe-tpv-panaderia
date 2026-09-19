<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Closure;
use Illuminate\Http\Request;

class CompanyConfigured
{
    public function handle(Request $request, Closure $next)
    {
        $config = Configuracion::first();
        if (!$config && !$request->routeIs('configuracion.*')) {
            return redirect()->route('configuracion.index')
                ->with('warning', 'Por favor configura los datos de tu empresa antes de continuar.');
        }
        return $next($request);
    }
}
