<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = Configuracion::actual();
        return view('configuracion.index', compact('config'));
    }

    public function actualizarEmpresa(Request $request)
    {
        $data = $request->validate([
            'nombre_empresa' => 'required|max:255',
            'razon_social' => 'nullable|max:255',
            'cif_nif' => 'nullable|max:30',
            'direccion' => 'nullable|max:255',
            'codigo_postal' => 'nullable|max:10',
            'ciudad' => 'nullable|max:100',
            'provincia' => 'nullable|max:100',
            'pais' => 'nullable|max:100',
            'telefono' => 'nullable|max:30',
            'email' => 'nullable|email|max:255',
            'web' => 'nullable|max:255',
        ]);
        Configuracion::actual()->update($data);
        return back()->with('success', 'Datos de empresa actualizados.');
    }

    public function actualizarRegional(Request $request)
    {
        $data = $request->validate([
            'moneda_codigo' => 'required|max:5',
            'moneda_simbolo' => 'required|max:5',
            'moneda_posicion' => 'required|in:izquierda,derecha',
            'separador_miles' => 'required|max:1',
            'separador_decimales' => 'required|max:1',
            'decimales' => 'required|integer|min:0|max:4',
            'formato_fecha' => 'required|max:20',
            'formato_hora' => 'required|max:20',
            'zona_horaria' => 'required|max:50',
            'idioma' => 'required|max:10',
        ]);
        Configuracion::actual()->update($data);
        return back()->with('success', 'Configuración regional actualizada.');
    }

    public function actualizarImpuestos(Request $request)
    {
        $data = $request->validate([
            'impuestos_activos' => 'nullable|boolean',
            'precios_con_impuestos' => 'nullable|boolean',
            'iva_general' => 'required|numeric|min:0|max:100',
            'iva_reducido' => 'required|numeric|min:0|max:100',
            'iva_superreducido' => 'required|numeric|min:0|max:100',
        ]);
        $data['impuestos_activos'] = $request->has('impuestos_activos');
        $data['precios_con_impuestos'] = $request->has('precios_con_impuestos');
        Configuracion::actual()->update($data);
        return back()->with('success', 'Impuestos actualizados.');
    }

    public function actualizarDocumentos(Request $request)
    {
        $data = $request->validate([
            'serie_ticket' => 'required|max:10',
            'serie_factura' => 'required|max:10',
            'serie_presupuesto' => 'required|max:10',
            'serie_albaran' => 'required|max:10',
            'siguiente_ticket' => 'required|integer|min:1',
            'siguiente_factura' => 'required|integer|min:1',
            'siguiente_presupuesto' => 'required|integer|min:1',
            'siguiente_albaran' => 'required|integer|min:1',
            'pie_ticket' => 'nullable',
            'pie_factura' => 'nullable',
        ]);
        Configuracion::actual()->update($data);
        return back()->with('success', 'Configuración de documentos actualizada.');
    }

    public function subirLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);
        $config = Configuracion::actual();
        if ($config->logo) {
            Storage::disk('public')->delete($config->logo);
        }
        $path = $request->file('logo')->store('empresa', 'public');
        $config->update(['logo' => $path]);
        return back()->with('success', 'Logo actualizado correctamente.');
    }

    /**
     * Actualiza los datos SUNAT del emisor: RUC, dirección fiscal, ubigeo,
     * credenciales SOL, modo (beta/producción), series y porcentaje IGV.
     * El certificado se sube por endpoint separado (multipart).
     */
    public function actualizarSunat(Request $request)
    {
        $data = $request->validate([
            'sunat_ruc'                          => 'nullable|digits:11',
            'sunat_razon_social'                 => 'nullable|max:255',
            'sunat_nombre_comercial'             => 'nullable|max:255',
            'sunat_ubigeo'                       => 'nullable|digits:6',
            'sunat_departamento'                 => 'nullable|max:60',
            'sunat_provincia'                    => 'nullable|max:60',
            'sunat_distrito'                     => 'nullable|max:60',
            'sunat_urbanizacion'                 => 'nullable|max:100',
            'sunat_direccion_fiscal'             => 'nullable|max:255',
            'sunat_codigo_pais'                  => 'nullable|size:2',
            'sunat_sol_usuario'                  => 'nullable|max:60',
            'sunat_sol_clave'                    => 'nullable|max:60',
            'sunat_modo'                         => 'required|in:beta,produccion',
            'sunat_endpoint'                     => 'nullable|max:255',
            'sunat_igv_porcentaje'               => 'required|numeric|min:0|max:100',
            'sunat_serie_factura'                => 'required|alpha_num|size:4',
            'sunat_serie_boleta'                 => 'required|alpha_num|size:4',
            'sunat_serie_nota_credito_factura'   => 'required|alpha_num|size:4',
            'sunat_serie_nota_credito_boleta'    => 'required|alpha_num|size:4',
            'sunat_serie_nota_debito_factura'    => 'required|alpha_num|size:4',
            'sunat_serie_nota_debito_boleta'     => 'required|alpha_num|size:4',
        ]);
        $data['sunat_activo'] = $request->boolean('sunat_activo');

        Configuracion::actual()->update($data);
        return back()->with('success', 'Configuración SUNAT actualizada.');
    }

    /**
     * Sube el certificado digital (.pfx o .pem) y opcionalmente su contraseña.
     * Lo guarda fuera del disco `public` por seguridad.
     */
    public function subirCertificado(Request $request)
    {
        $request->validate([
            'certificado'                  => 'required|file|max:4096|mimes:pfx,p12,pem,crt,key,txt',
            'sunat_certificado_password'   => 'nullable|max:120',
        ]);
        $config = Configuracion::actual();
        if ($config->sunat_certificado_path && file_exists($config->sunat_certificado_path)) {
            @unlink($config->sunat_certificado_path);
        }

        $dir = storage_path('app/sunat');
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $extension = $request->file('certificado')->getClientOriginalExtension();
        $dest = $dir . DIRECTORY_SEPARATOR . 'certificate.' . strtolower($extension);
        $request->file('certificado')->move($dir, basename($dest));

        $config->update([
            'sunat_certificado_path'     => $dest,
            'sunat_certificado_password' => $request->input('sunat_certificado_password'),
        ]);
        return back()->with('success', 'Certificado SUNAT cargado correctamente.');
    }
}
