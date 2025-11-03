<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * L'utilisateur de l'API (proprietaire des apps trackees)
 * 
 * ATTENTION: Ce model est different des autres!
 * Laravel te donne PLUS de code auto ici (car c'est pour l'auth)
 * 
 * Laravel me donne auto a la creation (php artisan make:model User):
 * - extends Authenticatable (au lieu de Model)
 * - use HasFactory, Notifiable
 * - $fillable = [name, email, password]
 * - $hidden = [password, remember_token]
 * - casts() avec email_verified_at et password:hashed
 * 
 * J'ai ajoute custom:
 * - HasApiTokens (pour Sanctum)
 * - applications() relation
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Champs modifiables en masse
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Champs a cacher dans les reponses JSON
     * (securite: jamais exposer le password!)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversions auto de types
     * 
     * password:hashed = Laravel 11+ hash automatiquement
     * (plus besoin de Hash::make() dans le controller!)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Les apps que cet user possede
     * 
     * Usage: $user->applications
     * Return: Collection des apps de l'user
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}