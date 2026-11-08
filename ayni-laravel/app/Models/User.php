<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Usuario del sistema Ayni (admin, trabajador o cliente).
 * Hereda de Authenticatable para poder iniciar sesión.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Campos que se pueden asignar masivamente. */
    protected $fillable = [
        'name', 'apellidos', 'dni', 'email', 'telefono',
        'password', 'rol', 'verificado', 'activo', 'distrito_id',
    ];

    /** Campos ocultos al serializar el modelo. */
    protected $hidden = ['password', 'remember_token'];

    /** Conversión automática de tipos y hash de contraseña. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verificado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /** Distrito donde vive o trabaja el usuario. */
    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    /** Perfil de trabajador (solo si rol = trabajador). */
    public function perfilTrabajador(): HasOne
    {
        return $this->hasOne(PerfilTrabajador::class, 'user_id');
    }

    /** Perfil de cliente (solo si rol = cliente). */
    public function perfilCliente(): HasOne
    {
        return $this->hasOne(PerfilCliente::class, 'user_id');
    }

    /** Servicios publicados por este trabajador. */
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class, 'trabajador_id');
    }

    /** Scope: solo usuarios con cuenta activa. */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /** Scope: solo identidades verificadas por el administrador. */
    public function scopeVerificados($query)
    {
        return $query->where('verificado', true);
    }

    public function calificacionesRecibidas(): HasManyThrough
{
    return $this->hasManyThrough(Calificacion::class, Servicio::class, 'trabajador_id', 'servicio_id');
}

/** Scope: solo usuarios con rol trabajador. */
public function scopeTrabajadores($query)
{
    return $query->where('rol', 'trabajador');
}

/** Accessor: nombre completo ("Rosa Quispe H."). Uso: $user->nombre_completo */
public function getNombreCompletoAttribute(): string
{
    return trim($this->name . ' ' . $this->apellidos);
}

/** Accessor: iniciales para el avatar ("RQ"). Uso: $user->iniciales */
public function getInicialesAttribute(): string
{
    $partes = preg_split('/\s+/', $this->nombre_completo);
    $ini = mb_substr($partes[0] ?? '', 0, 1);
    if (count($partes) > 1) {
        $ini .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($ini);
}
/**
 * Número listo para wa.me: antepone el código de Perú (51)
 * si el teléfono es de 9 dígitos. Uso: $user->whatsapp
 */
public function getWhatsappAttribute(): string
{
    $tel = preg_replace('/\D/', '', (string) $this->telefono);
    return strlen($tel) === 9 ? '51' . $tel : $tel;
}
}