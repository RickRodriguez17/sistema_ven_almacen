<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROL_ADMIN = 'admin';

    public const ROL_CAJERO = 'cajero';

    public const ROL_ALMACEN = 'almacen';

    public const ROLES = [
        self::ROL_ADMIN => 'Administrador',
        self::ROL_CAJERO => 'Cajero',
        self::ROL_ALMACEN => 'Almacén',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esCajero(): bool
    {
        return $this->rol === self::ROL_CAJERO;
    }

    public function esAlmacen(): bool
    {
        return $this->rol === self::ROL_ALMACEN;
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function puedeImprimirReportes(): bool
    {
        return $this->tieneRol(self::ROL_ADMIN, self::ROL_ALMACEN);
    }
}
