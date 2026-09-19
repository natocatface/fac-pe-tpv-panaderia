<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

class GenerarDatosDemo extends Command
{
    protected $signature = 'demo:generar';
    protected $description = 'Genera datos de demostración (ventas, compras, mermas, fichajes) distribuidos en los últimos 14 días.';

    public function handle(): int
    {
        $this->info('🥖 Generando datos de demostración para el dashboard...');
        (new DemoDataSeeder())->setCommand($this)->run();
        $this->info('✅ Demo lista. Refresca el dashboard.');
        return self::SUCCESS;
    }
}
