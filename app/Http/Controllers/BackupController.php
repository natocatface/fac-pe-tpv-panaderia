<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $directorio = storage_path('app/backups');
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $archivos = collect(glob($directorio . '/*.sql'))
            ->map(fn($file) => [
                'nombre' => basename($file),
                'tamano' => filesize($file),
                'fecha' => date('d/m/Y H:i:s', filemtime($file)),
                'fecha_iso' => filemtime($file),
            ])
            ->sortByDesc('fecha_iso')
            ->values();

        return view('backup.index', compact('archivos'));
    }

    public function crear()
    {
        try {
            $directorio = storage_path('app/backups');
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            $nombre = 'backup_' . date('Y_m_d_His') . '.sql';
            $ruta = $directorio . '/' . $nombre;

            $tablas = DB::select('SHOW TABLES');
            $clave = 'Tables_in_' . config('database.connections.mysql.database');
            $sql = "-- Backup TPV Panadería\n-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tablas as $tabla) {
                $nombreTabla = $tabla->$clave;
                $sql .= "-- Tabla: $nombreTabla\n";
                $sql .= "DROP TABLE IF EXISTS `$nombreTabla`;\n";
                $create = DB::select("SHOW CREATE TABLE `$nombreTabla`");
                $sql .= $create[0]->{'Create Table'} . ";\n\n";

                $filas = DB::table($nombreTabla)->get();
                if ($filas->isNotEmpty()) {
                    foreach ($filas as $fila) {
                        $valores = array_map(function ($v) {
                            if (is_null($v)) return 'NULL';
                            return "'" . str_replace("'", "''", $v) . "'";
                        }, (array) $fila);
                        $sql .= "INSERT INTO `$nombreTabla` VALUES (" . implode(',', $valores) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($ruta, $sql);

            return back()->with('success', 'Backup creado: ' . $nombre . ' (' . round(filesize($ruta) / 1024, 2) . ' KB)');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al crear backup: ' . $e->getMessage());
        }
    }

    public function descargar($archivo)
    {
        $archivo = basename($archivo);
        $ruta = storage_path('app/backups/' . $archivo);
        if (!file_exists($ruta)) {
            return back()->with('error', 'Archivo no encontrado.');
        }
        return response()->download($ruta);
    }

    public function eliminar($archivo)
    {
        $archivo = basename($archivo);
        $ruta = storage_path('app/backups/' . $archivo);
        if (file_exists($ruta)) {
            unlink($ruta);
            return back()->with('success', 'Backup eliminado.');
        }
        return back()->with('error', 'Archivo no encontrado.');
    }

    public function restaurar(Request $request)
    {
        $request->validate(['backup' => 'required|file|mimetypes:application/sql,text/plain,application/octet-stream']);
        try {
            $sql = file_get_contents($request->file('backup')->getRealPath());
            DB::unprepared($sql);
            return back()->with('success', 'Backup restaurado correctamente. Cierra sesión y vuelve a entrar.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function reset(Request $request)
    {
        $request->validate([
            'confirmacion' => 'required|in:RESETEAR',
        ]);
        try {
            // Crear backup automático antes de resetear
            $directorio = storage_path('app/backups');
            $nombre = 'pre_reset_' . date('Y_m_d_His') . '.sql';

            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

            return redirect()->route('login')->with('success', 'Sistema reseteado para nueva empresa. Inicia sesión con las credenciales por defecto.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al resetear: ' . $e->getMessage());
        }
    }
}
