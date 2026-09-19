<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Configuración inicial
        Configuracion::firstOrCreate(['id' => 1], [
            'nombre_empresa' => 'Panadería La Espiga Dorada',
            'razon_social' => 'La Espiga Dorada S.A.C.',
            'cif_nif' => '20512345678',
            'direccion' => 'Av. Arequipa 1234',
            'codigo_postal' => '15046',
            'ciudad' => 'Lima',
            'provincia' => 'Lima',
            'pais' => 'Perú',
            'telefono' => '+51 987 654 321',
            'email' => 'info@espigadorada.pe',
            'web' => 'www.espigadorada.pe',
            'moneda_codigo' => 'PEN',
            'moneda_simbolo' => 'S/',
            'moneda_posicion' => 'izquierda',
            'separador_miles' => ',',
            'separador_decimales' => '.',
            'decimales' => 2,
            'iva_general' => 18,
            'iva_reducido' => 18,
            'iva_superreducido' => 0,
            'precios_con_impuestos' => true,
            'pie_ticket' => "¡Gracias por su compra!\nLe esperamos pronto.\nHorario: L-S 7:00 a 21:00",
        ]);

        // Usuarios
        User::firstOrCreate(['email' => 'admin@panaderia.com'], [
            'name' => 'Administrador',
            'password' => Hash::make('panaderia123'),
            'rol' => 'admin',
            'activo' => true,
            'salario_hora' => 12.00,
        ]);
        User::firstOrCreate(['email' => 'maria@panaderia.com'], [
            'name' => 'María García',
            'password' => Hash::make('panaderia123'),
            'rol' => 'encargado',
            'activo' => true,
            'salario_hora' => 10.50,
        ]);
        User::firstOrCreate(['email' => 'jose@panaderia.com'], [
            'name' => 'José Martínez',
            'password' => Hash::make('panaderia123'),
            'rol' => 'vendedor',
            'activo' => true,
            'salario_hora' => 9.00,
        ]);

        // Caja
        Caja::firstOrCreate(['id' => 1], ['nombre' => 'Caja Principal', 'activa' => true]);

        // Categorías
        $categorias = [
            ['nombre' => 'Pan', 'color' => '#C8763D', 'icono' => 'bread-slice', 'orden' => 1],
            ['nombre' => 'Bollería', 'color' => '#D4A574', 'icono' => 'cookie', 'orden' => 2],
            ['nombre' => 'Pastelería', 'color' => '#E91E63', 'icono' => 'birthday-cake', 'orden' => 3],
            ['nombre' => 'Confitería', 'color' => '#9C27B0', 'icono' => 'candy-cane', 'orden' => 4],
            ['nombre' => 'Bebidas', 'color' => '#2196F3', 'icono' => 'coffee', 'orden' => 5],
            ['nombre' => 'Materias primas', 'color' => '#795548', 'icono' => 'wheat-awn', 'orden' => 99],
        ];
        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(['nombre' => $cat['nombre']], $cat);
        }

        // Proveedores
        $proveedores = [
            ['nombre' => 'Harinas del Norte S.L.', 'cif_nif' => 'B-87654321', 'email' => 'pedidos@harinasnorte.es', 'telefono' => '900111222', 'contacto' => 'Carlos Pérez', 'dias_entrega' => 'L, X, V', 'ciudad' => 'Burgos'],
            ['nombre' => 'Lácteos Frescos', 'cif_nif' => 'A-12121212', 'email' => 'comercial@lacteos.es', 'telefono' => '900333444', 'contacto' => 'Ana Ruiz', 'dias_entrega' => 'Diario', 'ciudad' => 'Madrid'],
            ['nombre' => 'Distribuidora Azúcar y Más', 'cif_nif' => 'B-34343434', 'email' => 'ventas@azucarymas.com', 'telefono' => '900555666', 'contacto' => 'Luis Torres', 'dias_entrega' => 'M, J', 'ciudad' => 'Sevilla'],
        ];
        foreach ($proveedores as $prov) {
            Proveedor::firstOrCreate(['nombre' => $prov['nombre']], $prov + ['activo' => true]);
        }

        // Clientes
        $clientes = [
            ['nombre' => 'Restaurante El Olivo', 'cif_nif' => '12345678A', 'email' => 'reservas@elolivo.es', 'telefono' => '915234567', 'ciudad' => 'Madrid', 'descuento' => 5],
            ['nombre' => 'Cafetería Las Flores', 'cif_nif' => '87654321B', 'email' => 'info@lasflores.es', 'telefono' => '914567890', 'ciudad' => 'Madrid', 'descuento' => 10],
            ['nombre' => 'Hotel Plaza Mayor', 'cif_nif' => 'C-11223344', 'email' => 'compras@plazamayor.com', 'telefono' => '913456789', 'ciudad' => 'Madrid', 'descuento' => 15],
            ['nombre' => 'María González', 'telefono' => '666123456', 'ciudad' => 'Madrid'],
            ['nombre' => 'Juan López', 'telefono' => '666987654', 'ciudad' => 'Madrid'],
        ];
        foreach ($clientes as $c) {
            Cliente::firstOrCreate(['nombre' => $c['nombre']], $c + ['activo' => true]);
        }

        // Productos - materias primas
        $catMat = Categoria::where('nombre', 'Materias primas')->first();
        $catPan = Categoria::where('nombre', 'Pan')->first();
        $catBolleria = Categoria::where('nombre', 'Bollería')->first();
        $catPasteleria = Categoria::where('nombre', 'Pastelería')->first();
        $catConfiteria = Categoria::where('nombre', 'Confitería')->first();
        $catBebidas = Categoria::where('nombre', 'Bebidas')->first();
        $provHarinas = Proveedor::where('nombre', 'Harinas del Norte S.L.')->first();

        $materias = [
            ['codigo' => 'MP-001', 'nombre' => 'Harina de trigo', 'precio_compra' => 0.50, 'precio_venta' => 0, 'unidad_medida' => 'kg', 'stock_actual' => 250, 'stock_minimo' => 50, 'stock_optimo' => 300, 'iva' => 4, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-002', 'nombre' => 'Levadura fresca', 'precio_compra' => 4.20, 'precio_venta' => 0, 'unidad_medida' => 'kg', 'stock_actual' => 8, 'stock_minimo' => 3, 'stock_optimo' => 10, 'iva' => 10, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-003', 'nombre' => 'Sal', 'precio_compra' => 0.30, 'precio_venta' => 0, 'unidad_medida' => 'kg', 'stock_actual' => 25, 'stock_minimo' => 5, 'stock_optimo' => 20, 'iva' => 4, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-004', 'nombre' => 'Azúcar', 'precio_compra' => 0.80, 'precio_venta' => 0, 'unidad_medida' => 'kg', 'stock_actual' => 45, 'stock_minimo' => 10, 'stock_optimo' => 50, 'iva' => 10, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-005', 'nombre' => 'Mantequilla', 'precio_compra' => 5.40, 'precio_venta' => 0, 'unidad_medida' => 'kg', 'stock_actual' => 12, 'stock_minimo' => 5, 'stock_optimo' => 15, 'iva' => 10, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-006', 'nombre' => 'Huevos', 'precio_compra' => 0.20, 'precio_venta' => 0, 'unidad_medida' => 'unidad', 'stock_actual' => 120, 'stock_minimo' => 30, 'stock_optimo' => 200, 'iva' => 4, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-007', 'nombre' => 'Leche', 'precio_compra' => 0.90, 'precio_venta' => 0, 'unidad_medida' => 'l', 'stock_actual' => 35, 'stock_minimo' => 10, 'stock_optimo' => 40, 'iva' => 4, 'tipo' => 'materia_prima'],
            ['codigo' => 'MP-008', 'nombre' => 'Chocolate cobertura', 'precio_compra' => 8.50, 'precio_venta' => 0, 'unidad_medida' => 'kg', 'stock_actual' => 6, 'stock_minimo' => 3, 'stock_optimo' => 10, 'iva' => 10, 'tipo' => 'materia_prima'],
        ];
        foreach ($materias as $m) {
            Producto::firstOrCreate(['codigo' => $m['codigo']], $m + ['categoria_id' => $catMat->id, 'proveedor_id' => $provHarinas->id, 'vende_tpv' => false, 'controla_stock' => true, 'activo' => true]);
        }

        // Productos de venta
        $productos = [
            // Pan
            ['codigo' => 'PAN-001', 'nombre' => 'Barra de pan', 'precio_compra' => 0.30, 'precio_venta' => 1.20, 'iva' => 4, 'cat' => $catPan, 'stock_actual' => 40],
            ['codigo' => 'PAN-002', 'nombre' => 'Pan rústico 500g', 'precio_compra' => 0.80, 'precio_venta' => 2.50, 'iva' => 4, 'cat' => $catPan, 'stock_actual' => 25],
            ['codigo' => 'PAN-003', 'nombre' => 'Pan integral', 'precio_compra' => 0.60, 'precio_venta' => 1.80, 'iva' => 4, 'cat' => $catPan, 'stock_actual' => 18],
            ['codigo' => 'PAN-004', 'nombre' => 'Hogaza grande 1kg', 'precio_compra' => 1.50, 'precio_venta' => 4.50, 'iva' => 4, 'cat' => $catPan, 'stock_actual' => 12],
            ['codigo' => 'PAN-005', 'nombre' => 'Chapata', 'precio_compra' => 0.50, 'precio_venta' => 1.60, 'iva' => 4, 'cat' => $catPan, 'stock_actual' => 22],

            // Bollería
            ['codigo' => 'BOL-001', 'nombre' => 'Croissant clásico', 'precio_compra' => 0.40, 'precio_venta' => 1.50, 'iva' => 10, 'cat' => $catBolleria, 'stock_actual' => 30],
            ['codigo' => 'BOL-002', 'nombre' => 'Napolitana de chocolate', 'precio_compra' => 0.55, 'precio_venta' => 1.80, 'iva' => 10, 'cat' => $catBolleria, 'stock_actual' => 25],
            ['codigo' => 'BOL-003', 'nombre' => 'Ensaimada', 'precio_compra' => 0.45, 'precio_venta' => 1.60, 'iva' => 10, 'cat' => $catBolleria, 'stock_actual' => 18],
            ['codigo' => 'BOL-004', 'nombre' => 'Magdalena casera', 'precio_compra' => 0.20, 'precio_venta' => 0.80, 'iva' => 10, 'cat' => $catBolleria, 'stock_actual' => 60],
            ['codigo' => 'BOL-005', 'nombre' => 'Donut glaseado', 'precio_compra' => 0.35, 'precio_venta' => 1.20, 'iva' => 10, 'cat' => $catBolleria, 'stock_actual' => 24],
            ['codigo' => 'BOL-006', 'nombre' => 'Palmera de chocolate', 'precio_compra' => 0.40, 'precio_venta' => 1.50, 'iva' => 10, 'cat' => $catBolleria, 'stock_actual' => 20],

            // Pastelería
            ['codigo' => 'PAS-001', 'nombre' => 'Tarta de manzana', 'precio_compra' => 4.00, 'precio_venta' => 14.00, 'iva' => 10, 'cat' => $catPasteleria, 'stock_actual' => 5],
            ['codigo' => 'PAS-002', 'nombre' => 'Tarta de chocolate', 'precio_compra' => 5.00, 'precio_venta' => 18.00, 'iva' => 10, 'cat' => $catPasteleria, 'stock_actual' => 4],
            ['codigo' => 'PAS-003', 'nombre' => 'Tarta de fresa', 'precio_compra' => 4.50, 'precio_venta' => 16.00, 'iva' => 10, 'cat' => $catPasteleria, 'stock_actual' => 3],
            ['codigo' => 'PAS-004', 'nombre' => 'Pastel individual', 'precio_compra' => 0.80, 'precio_venta' => 2.80, 'iva' => 10, 'cat' => $catPasteleria, 'stock_actual' => 15],
            ['codigo' => 'PAS-005', 'nombre' => 'Tarta de queso', 'precio_compra' => 4.20, 'precio_venta' => 15.00, 'iva' => 10, 'cat' => $catPasteleria, 'stock_actual' => 4],

            // Confitería
            ['codigo' => 'CON-001', 'nombre' => 'Bombones surtidos 250g', 'precio_compra' => 4.00, 'precio_venta' => 12.00, 'iva' => 10, 'cat' => $catConfiteria, 'stock_actual' => 8],
            ['codigo' => 'CON-002', 'nombre' => 'Trufas chocolate', 'precio_compra' => 0.25, 'precio_venta' => 0.90, 'iva' => 10, 'cat' => $catConfiteria, 'stock_actual' => 40],
            ['codigo' => 'CON-003', 'nombre' => 'Mazapán', 'precio_compra' => 0.80, 'precio_venta' => 2.50, 'iva' => 10, 'cat' => $catConfiteria, 'stock_actual' => 15],
            ['codigo' => 'CON-004', 'nombre' => 'Polvorones (caja)', 'precio_compra' => 2.50, 'precio_venta' => 7.50, 'iva' => 10, 'cat' => $catConfiteria, 'stock_actual' => 12],

            // Bebidas
            ['codigo' => 'BEB-001', 'nombre' => 'Café solo', 'precio_compra' => 0.20, 'precio_venta' => 1.30, 'iva' => 10, 'cat' => $catBebidas, 'stock_actual' => 100, 'controla_stock' => false],
            ['codigo' => 'BEB-002', 'nombre' => 'Café con leche', 'precio_compra' => 0.30, 'precio_venta' => 1.50, 'iva' => 10, 'cat' => $catBebidas, 'stock_actual' => 100, 'controla_stock' => false],
            ['codigo' => 'BEB-003', 'nombre' => 'Zumo de naranja', 'precio_compra' => 0.50, 'precio_venta' => 2.00, 'iva' => 10, 'cat' => $catBebidas, 'stock_actual' => 30],
            ['codigo' => 'BEB-004', 'nombre' => 'Agua mineral 50cl', 'precio_compra' => 0.30, 'precio_venta' => 1.00, 'iva' => 10, 'cat' => $catBebidas, 'stock_actual' => 50],
        ];
        $orden = 0;
        foreach ($productos as $p) {
            Producto::firstOrCreate(['codigo' => $p['codigo']], [
                'nombre' => $p['nombre'],
                'precio_compra' => $p['precio_compra'],
                'precio_venta' => $p['precio_venta'],
                'iva' => $p['iva'],
                'categoria_id' => $p['cat']->id,
                'tipo' => 'elaborado',
                'unidad_medida' => 'unidad',
                'stock_actual' => $p['stock_actual'],
                'stock_minimo' => 5,
                'stock_optimo' => 30,
                'vende_tpv' => true,
                'controla_stock' => $p['controla_stock'] ?? true,
                'activo' => true,
                'orden_tpv' => $orden++,
            ]);
        }

        // Generar datos de demostración (ventas, compras, mermas, fichajes)
        $this->call(DemoDataSeeder::class);

        // Configuración SUNAT (Perú) — ambiente Beta + series electrónicas
        $this->call(SunatSeeder::class);
    }
}
