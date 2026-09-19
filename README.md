# TPV Panadería 🥖

**Sistema de Punto de Venta para Panaderías, Pastelerías y Confiterías**

Construido con **Laravel 11**, **MySQL** y **Bootstrap 5** + **Chart.js**.

---

## 🌟 Características

| Módulo | Descripción |
|--------|-------------|
| 🥖 **Venta rápida (TPV)** | Mostrador táctil, carrito, cobro en efectivo / tarjeta / Bizum, ticket imprimible |
| 📦 **Stock y mermas** | Existencias, movimientos, alertas, registro de mermas con motivos |
| 🧁 **Productos elaborados** | Recetas, ingredientes, formatos, materias primas |
| 🧾 **Documentos** | Tickets, facturas, presupuestos, albaranes con generación de PDF |
| 🛒 **Compras y proveedores** | Pedidos, recepción y entrada automática de stock |
| 📊 **Informes** | Ventas, productos top, márgenes, clientes, valoración stock, arqueo caja |
| ⏱️ **Control horario** | Fichaje entrada/salida, histórico, horas por empleado |
| 👥 **Clientes y proveedores** | CRUD completo con historial de compras |
| 🔐 **Usuarios y roles** | Admin, encargado, vendedor, obrador |
| ⚙️ **Configuración** | Datos empresa, logo, moneda, impuestos, separadores, formato fecha |
| 💾 **Backup y reset** | Crear/restaurar backups, resetear sistema para empresa nueva |

---

## 📋 Requisitos

- PHP **>= 8.2**
- Composer **>= 2.x**
- MySQL **>= 5.7** o MariaDB **>= 10.3**
- Extensiones PHP: `mbstring`, `xml`, `pdo_mysql`, `gd`, `fileinfo`, `bcmath`

---

## 🚀 Instalación

### 1. Clonar / copiar el proyecto

```bash
cd C:\TVP\tpv-panaderia
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Crear archivo de entorno

```bash
copy .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos en `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tpv_panaderia
DB_USERNAME=root
DB_PASSWORD=
```

Crear la base de datos en MySQL:

```sql
CREATE DATABASE tpv_panaderia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 6. Crear enlace simbólico para storage (logos, imágenes)

```bash
php artisan storage:link
```

### 7. Levantar el servidor

```bash
php artisan serve
```

Abrir en el navegador: <http://localhost:8000>

---

## 🔑 Credenciales de demostración

| Usuario | Email | Contraseña | Rol |
|---------|-------|------------|-----|
| Administrador | `admin@panaderia.com` | `panaderia123` | admin |
| Encargado | `maria@panaderia.com` | `panaderia123` | encargado |
| Vendedor | `jose@panaderia.com` | `panaderia123` | vendedor |

---

## 🗂️ Estructura del proyecto

```
tpv-panaderia/
├── app/
│   ├── Http/Controllers/    → Controladores (Tpv, Producto, Stock, ...)
│   ├── Models/              → Modelos Eloquent
│   └── Providers/
├── database/
│   ├── migrations/          → 13 migraciones con todo el esquema
│   └── seeders/             → DatabaseSeeder con datos demo
├── public/
│   ├── assets/css/app.css   → Estilos personalizados (tema panadería)
│   └── index.php
├── resources/views/
│   ├── auth/login.blade.php
│   ├── layouts/app.blade.php
│   ├── partials/{sidebar,topbar}.blade.php
│   ├── dashboard/           → Panel principal con KPIs y gráficos
│   ├── tpv/                 → Punto de venta táctil + ticket de impresión
│   ├── productos/, categorias/, stock/, clientes/, proveedores/
│   ├── documentos/, compras/, informes/, horarios/
│   ├── configuracion/       → Configuración con pestañas verticales
│   └── backup/              → Backup, restauración y reset
└── routes/web.php           → Todas las rutas
```

---

## 🎨 Diseño

- Tema **panadería**: colores cálidos (marrones, dorados, crema).
- Sidebar oscuro con navegación por secciones.
- Dashboard con tarjetas de KPIs, gráficos de líneas y donut.
- TPV optimizado para pantalla táctil de **mostrador**.
- Modal de cobro con teclado numérico y cálculo automático de cambio.

---

## 💾 Backup, restauración y reset

Accesible desde **Backup y mantenimiento** (solo admin/encargado):

- **Crear backup**: genera `.sql` completo en `storage/app/backups/`.
- **Restaurar**: sube un `.sql` y reemplaza la base de datos.
- **Resetear**: borra todos los datos y prepara el sistema para una empresa nueva.

---

## ⚙️ Configuración personalizable

Desde **Configuración**:

| Sección | Datos |
|---------|-------|
| Empresa | Nombre, razón social, NIF/CIF, dirección, teléfono, email |
| Logo | Subida del logotipo (aparece en tickets/facturas) |
| Regional | Moneda, símbolo, separadores miles/decimales, formato fecha/hora |
| Impuestos | IVA general/reducido/superreducido, precios con/sin IVA |
| Documentos | Series y siguiente número de ticket, factura, presupuesto, albarán |

---

## 🛠️ Comandos útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Resetear BD desde la consola
php artisan migrate:fresh --seed

# Crear backup manual
php artisan db:dump          # (opcional, si tienes el paquete)
```

---

## 📝 Licencia

MIT · TPV Panadería · {{ año actual }}
