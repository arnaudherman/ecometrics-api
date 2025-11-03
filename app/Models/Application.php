<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Application Model
 * 
 * Represente une app/site qu'on veut tracker ecologiquement
 * Ex: mon e-commerce, mon blog, mon app mobile, etc
 * 
 * Relations:
 * - 1 app = 1 user (proprietaire)
 * - 1 app = plusieurs metriques (donnees journalieres)
 * - 1 app = plusieurs certificats (historique badges)
 * 
 * Laravel me donne auto:
 * - id, created_at, updated_at (timestamps)
 * - HasFactory trait pour les tests/seeders
 */
class Application extends Model
{
    use HasFactory;

    /**
     * Champs qu'on peut remplir en masse
     * (protection contre les injections)
     */
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'description',
    ];

    /**
     * L'user qui possede cette app
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Toutes les metriques de l'app
     * (1 metrique = data d'une journee)
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    /**
     * Tous les certificats de l'app
     * (historique complet)
     */
    public function carbonCertificates(): HasMany
    {
        return $this->hasMany(CarbonCertificate::class);
    }

    /**
     * Le dernier certificat emis
     * (le plus recent seulement)
     */
    public function latestCertificate(): HasOne
    {
        return $this->hasOne(CarbonCertificate::class)->latestOfMany('issued_at');
    }

    /**
     * Calcule la moyenne de CO2
     * Usage: $app->averageCarbonFootprint
     */
    public function getAverageCarbonFootprintAttribute(): float
    {
        return (float) $this->metrics()->avg('carbon_footprint_kg') ?? 0;
    }

    /**
     * Calcule le total de CO2
     * Usage: $app->totalCarbonFootprint
     */
    public function getTotalCarbonFootprintAttribute(): float
    {
        return (float) $this->metrics()->sum('carbon_footprint_kg') ?? 0;
    }
}