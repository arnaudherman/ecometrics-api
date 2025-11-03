<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Metric Model
 * 
 * Les metriques quotidiennes d'une app
 * Requetes, stockage, CPU = calcul automatique du CO2
 * 
 * 1 metrique par jour par app (unique sur date + application_id)
 * 
 * Laravel me donne auto a la creation:
 * - namespace App\Models
 * - use HasFactory, Model, BelongsTo
 * - class Metric extends Model { use HasFactory; }
 * Tout le reste = custom (fillable, casts, relations, calculs)
 */
class Metric extends Model
{
    use HasFactory;

    /**
     * Champs modifiables en masse
     * carbon_footprint_kg est auto-calcule mais on peut le passer en param
     */
    protected $fillable = [
        'application_id',
        'date',
        'requests_count',
        'storage_gb',
        'cpu_hours',
        'carbon_footprint_kg',
    ];

    /**
     * Conversions auto de types
     */
    protected $casts = [
        'date' => 'date',
        'requests_count' => 'integer',
        'storage_gb' => 'decimal:2',
        'cpu_hours' => 'decimal:2',
        'carbon_footprint_kg' => 'float', // en float pour les tests
    ];

    /**
     * L'app a laquelle appartient cette metrique
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Hook Laravel qui se declenche automatiquement
     * 
     * creating = avant de creer en DB
     * updating = avant de modifier en DB
     * 
     * Resultat: le CO2 est TOUJOURS calcule automatiquement
     * meme si tu le passes pas en parametre!
     */
    protected static function booted()
    {
        static::creating(function ($metric) {
            if (!isset($metric->carbon_footprint_kg)) {
                $metric->carbon_footprint_kg = $metric->calculateCarbonFootprint();
            }
        });

        static::updating(function ($metric) {
            $metric->carbon_footprint_kg = $metric->calculateCarbonFootprint();
        });
    }

    /**
     * Calcule l'empreinte carbone selon les metriques
     * 
     * Formule:
     * - Requetes : 0.0002 kg CO2 / requete
     * - Stockage : 0.05 kg CO2 / GB
     * - CPU : 0.5 kg CO2 / heure
     * 
     * Exemple: 10k requetes + 50GB + 5h CPU
     * = (10000 * 0.0002) + (50 * 0.05) + (5 * 0.5)
     * = 2 + 2.5 + 2.5 = 7 kg CO2
     */
    public function calculateCarbonFootprint(): float
    {
        $requestsCarbon = ($this->requests_count ?? 0) * 0.0002;
        $storageCarbon = ($this->storage_gb ?? 0) * 0.05;
        $cpuCarbon = ($this->cpu_hours ?? 0) * 0.5;

        return round($requestsCarbon + $storageCarbon + $cpuCarbon, 3);
    }
}