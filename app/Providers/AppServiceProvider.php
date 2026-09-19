<?php

namespace App\Providers;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Compartir configuración con todas las vistas
        View::composer('*', function ($view) {
            $config = null;
            try {
                if (Schema::hasTable('configuracion')) {
                    $config = Configuracion::first();
                }
            } catch (\Throwable $e) {
                $config = null;
            }
            $view->with('appConfig', $config);
        });
    }
}
