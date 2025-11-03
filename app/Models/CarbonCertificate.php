<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CarbonCertificate Model
 * 
 * Les badges ecolos des apps
 * Platinum = super clean, Bronze = faut ameliorer
 * 
 * Certificat valide 1 an puis faut le renouveler
 * 
 * Laravel me donne auto:
 * - id, created_at, updated_at
 * - HasFactory pour les tests
 */
class CarbonCertificate extends Model
{
    use HasFactory;

    /**
     * Champs modifiables en masse
     */
    protected $fillable = [
        'application_id',
        'badge_level',
        'issued_at',
        'valid_until',
    ];

    /**
     * Conversions auto de types
     * (les dates deviennent des objets Carbon)
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Seuils pour les badges (en kg CO2 par mois)
     */
    const BADGE_THRESHOLDS = [
        'platinum' => 10,
        'gold' => 20,
        'silver' => 50,
        'bronze' => 100,
    ];

    /**
     * L'app qui possede ce certificat
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Determine quel badge donner selon le CO2 mensuel
     */
    public static function determineBadgeLevel(float $monthlyCarbon): string
    {
        if ($monthlyCarbon <= self::BADGE_THRESHOLDS['platinum']) {
            return 'platinum';
        }

        if ($monthlyCarbon <= self::BADGE_THRESHOLDS['gold']) {
            return 'gold';
        }

        if ($monthlyCarbon <= self::BADGE_THRESHOLDS['silver']) {
            return 'silver';
        }

        //si > 50kg = bronze (par defaut)
        return 'bronze';
    }

    /**
     * Check si le certificat est encore valide
     */
    public function isValid(): bool
    {
        return $this->valid_until >= now();
    }
}