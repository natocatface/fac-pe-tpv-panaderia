# Guía rápida de instalación · TPV Panadería

## En Windows (XAMPP / Laragon)

### Paso 1: Preparar entorno

1. Instala **XAMPP** o **Laragon** (incluye PHP 8.2+ y MySQL).
2. Instala **Composer**: <https://getcomposer.org/download/>.
3. Verifica en CMD:
   ```cmd
   php -v
   composer -V
   mysql --version
   ```

### Paso 2: Colocar el proyecto

Copia toda la carpeta `tpv-panaderia` dentro de `C:\xampp\htdocs\` o `C:\laragon\www\`.

### Paso 3: Instalar dependencias

```cmd
cd C:\xampp\htdocs\tpv-panaderia
composer install
```

### Paso 4: Configurar entorno

```cmd
copy .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales de MySQL.

### Paso 5: Crear la base de datos

Abre **phpMyAdmin** (<http://localhost/phpmyadmin>) y crea una base de datos llamada `tpv_panaderia` con cotejamiento `utf8mb4_unicode_ci`.

### Paso 6: Migrar y poblar

```cmd
php artisan migrate --seed
php artisan storage:link
```

### Paso 7: Arrancar el servidor

```cmd
php artisan serve
```

Abre <http://localhost:8000> y entra con:

- **Email:** `admin@panaderia.com`
- **Contraseña:** `panaderia123`

---

## Solución de problemas comunes

| Problema | Solución |
|----------|----------|
| `Class "Redis" not found` | En `.env`, asegúrate de que `CACHE_STORE=database` y `SESSION_DRIVER=file` |
| Logos no se muestran | Ejecuta `php artisan storage:link` |
| `PDOException no such table` | Ejecuta `php artisan migrate:fresh --seed` |
| Permisos denegados en Linux | `chmod -R 775 storage bootstrap/cache` |
| Error SQL al hacer backup | Aumenta `max_allowed_packet` en my.ini de MySQL |

---

## Tras instalar — Configuración inicial

1. Entra como **admin**.
2. Ve a **Configuración → Empresa** y completa tus datos.
3. **Configuración → Logo**: sube el logotipo (aparece en tickets/facturas).
4. **Configuración → Regional**: ajusta moneda, separadores y formato de fecha.
5. **Configuración → Impuestos**: define los IVAs según tu país.
6. **Categorías**: revisa y personaliza las categorías de productos.
7. **Productos**: ajusta precios, stock y márgenes a tu inventario real.
8. Crea tus **usuarios** del equipo en el módulo correspondiente.
9. ¡Empieza a vender desde el **TPV**!

---

## Hacer backups regulares

Te recomendamos:
- **Diario**: crear un backup desde **Backup y mantenimiento**.
- **Antes de cualquier actualización del sistema**.
- **Antes de resetear** para una empresa nueva.

Los backups quedan en `storage/app/backups/` y se pueden descargar al ordenador.
