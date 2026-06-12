# Sistema de Ventas y Almacén

Sistema de punto de venta (POS) e inventario construido con **Laravel 12** y **MySQL**.

## Requisitos del hosting

- PHP **8.2** o superior con las extensiones: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `gd`, `intl`, `tokenizer`, `fileinfo`, `openssl`.
- MySQL **5.7+** o MariaDB **10.3+**.
- Apache con `mod_rewrite` habilitado (o equivalente en otro servidor web).
- Composer (sólo para el despliegue; no se requiere en el servidor si subes la carpeta `vendor/` ya generada).

## Despliegue paso a paso

### 1. Base de datos

Crea una base de datos MySQL vacía desde el panel del hosting (cPanel, Plesk, etc.) y un usuario con todos los permisos sobre ella. Toma nota de los datos:

- Host (normalmente `localhost`)
- Nombre de la base de datos
- Usuario
- Contraseña

### 2. Subir el proyecto

Sube **todo el contenido del repositorio** a la raíz del dominio (típicamente `public_html/`).

> El archivo `.htaccess` de la raíz reescribe automáticamente las peticiones hacia `public/index.php`, por lo que no necesitas mover archivos si tu hosting no permite cambiar el _document root_. Si tu hosting sí permite cambiarlo, apúntalo directamente a la carpeta `public/` para mayor seguridad.

### 3. Configurar `.env`

Copia `.env.example` a `.env` y completa los siguientes valores:

```env
APP_NAME="Sistema de Ventas y Almacén"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_la_bd
DB_USERNAME=usuario_de_la_bd
DB_PASSWORD=contraseña_de_la_bd
```

### 4. Instalar dependencias y preparar la aplicación

Desde la raíz del proyecto:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Si el hosting no permite SSH, sube la carpeta `vendor/` ya generada desde tu máquina local y ejecuta los comandos `artisan` mediante una herramienta como _Terminal_ de cPanel, _Cron Jobs_ o un script PHP temporal que invoque `Artisan::call()`.

### 5. Permisos de carpetas

Las siguientes carpetas deben ser escribibles por el servidor web:

```
storage/
bootstrap/cache/
```

En cPanel los permisos `755` (carpetas) / `644` (archivos) suelen ser suficientes. Si hay errores de escritura puedes elevar a `775`.

### 6. Acceso inicial

El sistema crea un usuario administrador por defecto:

- **Email:** `admin@admin.com`
- **Password:** `admin123`

> **Importante:** cambia esta contraseña inmediatamente después del primer ingreso.

## Estructura

- `app/` — Controladores, modelos y lógica de negocio.
- `config/` — Configuración de Laravel (DB sólo soporta MySQL/MariaDB).
- `database/migrations/` — Migraciones de todas las tablas.
- `database/seeders/` — Sólo `EmpresaSeeder` (datos del negocio) y `DatabaseSeeder` (usuario admin).
- `public/` — Front controller (`index.php`) y assets estáticos (plantilla NiceAdmin).
- `resources/views/` — Plantillas Blade.
- `routes/web.php` — Rutas HTTP.

## Comandos útiles en producción

```bash
# Limpiar y regenerar cachés tras un despliegue:
php artisan optimize:clear
php artisan optimize

# Ver el estado de las migraciones:
php artisan migrate:status

# Modo mantenimiento:
php artisan down
php artisan up
```
