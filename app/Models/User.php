<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'telefono', 'dni',
        'avatar', 'salario_hora', 'activo', 'rol',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'salario_hora' => 'decimal:2',
        ];
    }

    public function fichajes()
    {
        return $this->hasMany(Fichaje::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esEncargado(): bool
    {
        return in_array($this->rol, ['admin', 'encargado']);
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $iniciales = collect(explode(' ', $this->name))
            ->map(fn($n) => mb_substr($n, 0, 1))
            ->take(2)
            ->implode('');
        return 'https://ui-avatars.com/api/?name=' . urlencode($iniciales) . '&background=C8763D&color=fff';
    }
}
