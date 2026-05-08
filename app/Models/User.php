<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Tabla real en la base de datos.
     */
    protected $table = 'erabiltzaileak';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        // Campos "virtuales" que usa el starter kit
        'name',
        'email',
        'password',

        // Campos del esquema
        'izena',
        'abizena',
        'telefonoa',
        'pasahitza',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'pasahitza',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // La contrasena real en DB es "pasahitza".
            'pasahitza' => 'hashed',
        ];
    }

    /**
     * Compatibilidad con Laravel Auth: password real.
     */
    public function getAuthPassword(): string
    {
        return (string) ($this->pasahitza ?? '');
    }

    /**
     * Atributo "name" compatible (usa izena + abizena).
     */
    public function getNameAttribute(): string
    {
        return trim((string) ($this->izena ?? '').' '.(string) ($this->abizena ?? ''));
    }

    /**
     * Mutador para "password" (lo redirige a "pasahitza").
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['pasahitza'] = Hash::make($value);
    }

    /**
     * Mutador para "name" (divide en nombre/apellido de forma simple).
     */
    public function setNameAttribute(string $value): void
    {
        $value = trim($value);
        if ($value === '') {
            $this->attributes['izena'] = '';
            $this->attributes['abizena'] = null;

            return;
        }

        $parts = preg_split('/\s+/', $value) ?: [$value];
        $this->attributes['izena'] = array_shift($parts) ?? $value;
        $this->attributes['abizena'] = $parts ? implode(' ', $parts) : null;
    }

    /**
     * Permite leer $user->password (no es una columna real).
     */
    public function getPasswordAttribute(): ?string
    {
        return $this->pasahitza;
    }

    public function erreserbak()
    {
        return $this->hasMany(\App\Models\Erreserba::class, 'telefonoa', 'telefonoa');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\PasahitzaBerrezarri($token));
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
