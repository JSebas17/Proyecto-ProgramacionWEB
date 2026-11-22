<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

/**
 * Usuario del sistema Ayni (admin, trabajador o cliente).
 * Hereda de Authenticatable para poder iniciar sesión.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Campos que se pueden asignar masivamente. */
    protected $fillable = [
        'name', 'apellidos', 'dni', 'email', 'telefono', 'foto', 'cv',
        'password', 'rol', 'verificado', 'activo', 'distrito_id',
    ];

    /**
     * El panel del admin cachea sus cifras (10 min). Cada vez que cambian estos datos
     * se descarta ese caché, así el panel nunca muestra números viejos.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin.metricas'));
        static::deleted(fn () => Cache::forget('admin.metricas'));
    }

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

    // ---------------- Relaciones ----------------

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

    /** Calificaciones que recibió el trabajador (a través de sus servicios). */
    public function calificacionesRecibidas(): HasManyThrough
    {
        return $this->hasManyThrough(Calificacion::class, Servicio::class, 'trabajador_id', 'servicio_id');
    }

    // ---------------- Scopes ----------------

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

    /** Scope: solo usuarios con rol trabajador. */
    public function scopeTrabajadores($query)
    {
        return $query->where('rol', 'trabajador');
    }

    // ---------------- Accessors ----------------

    /** Nombre completo ("Rosa Quispe H."). Uso: $user->nombre_completo */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->name . ' ' . $this->apellidos);
    }

    /** Iniciales para el avatar sin foto ("RQ"). Uso: $user->iniciales */
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
     * URL pública de la foto de perfil, o null si no subió ninguna.
     * Usa asset() para que funcione con cualquier host (localhost:8000, XAMPP, producción).
     * Requiere el enlace simbólico: php artisan storage:link
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    /** Número del 0 al 5 para darle un color estable al avatar de iniciales. */
    public function getColorAvatarAttribute(): int
    {
        return ($this->id ?? 0) % 6;
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

    /**
     * Enlace completo a WhatsApp con el saludo ya codificado, o null si el usuario
     * no tiene un teléfono válido (evita un botón que abre "https://wa.me/" vacío).
     * Uso: $user->whatsapp_url
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        $numero = $this->whatsapp;
        if (strlen($numero) < 11) { // 51 + 9 dígitos
            return null;
        }
        return 'https://wa.me/' . $numero . '?text=' . rawurlencode('Hola ' . $this->name . ', te vi en Ayni');
    }
}
