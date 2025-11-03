<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Metric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * MetricController
 * 
 * C'est ici qu'on enregistre les données quotidiennes des apps :
 * - nb de requetes
 * - stockage utilisé (GB)
 * - temps CPU (heures)
 * 
 * Le calcul du CO2 se fait auto dans le model Metric
 */
class MetricController extends Controller
{
    /**
     * Liste les metriques d'une app
     * avec pagination (30 par page par defaut)
     */
    public function index(Request $request, Application $application)
    {
        Gate::authorize('view', $application);

        $perPage = $request->input('per_page', 30); //30 metriques par page
        $metrics = $application->metrics()
            ->orderBy('date', 'desc') //du plus recent au plus vieux
            ->paginate($perPage);

        return response()->json([
            'metrics' => $metrics->items(),
            'pagination' => [
                'total' => $metrics->total(),
                'per_page' => $metrics->perPage(),
                'current_page' => $metrics->currentPage(),
                'last_page' => $metrics->lastPage(),
            ],
        ]);
    }

    /**
     * Enregistre une nouvelle metrique
     * (data d'une journee : requetes, stockage, cpu)
     */
    public function store(Request $request, Application $application)
    {
        Gate::authorize('view', $application);

        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today', //pas de date future
            'requests_count' => 'required|integer|min:0',
            'storage_gb' => 'required|numeric|min:0',
            'cpu_hours' => 'required|numeric|min:0',
        ]);

        //check si une metrique existe deja pour cette date
        //1 seule metrique par jour max
        $existingMetric = $application->metrics()
            ->whereDate('date', $validated['date'])
            ->first();

        if ($existingMetric) {
            throw ValidationException::withMessages([
                'date' => ['Une métrique existe déjà pour cette date'],
            ]);
        }

        //creation + calcul auto du CO2 (fait dans le model)
        $metric = $application->metrics()->create($validated);

        return response()->json([
            'message' => 'Metric created successfully',
            'metric' => $metric,
        ], 201);
    }

    /**
     * Affiche 1 metrique precise
     */
    public function show(Application $application, Metric $metric)
    {
        Gate::authorize('view', $application);

        //verifie que la metrique appartient bien a cette app
        if ($metric->application_id !== $application->id) {
            abort(404);
        }

        return response()->json([
            'metric' => $metric,
        ]);
    }

    /**
     * Modifie une metrique existante
     */
    public function update(Request $request, Application $application, Metric $metric)
    {
        Gate::authorize('view', $application);

        if ($metric->application_id !== $application->id) {
            abort(404);
        }

        $validated = $request->validate([
            'requests_count' => 'sometimes|required|integer|min:0',
            'storage_gb' => 'sometimes|required|numeric|min:0',
            'cpu_hours' => 'sometimes|required|numeric|min:0',
        ]);

        //update + recalcul auto du CO2
        $metric->update($validated);

        return response()->json([
            'message' => 'Metric updated successfully',
            'metric' => $metric->fresh(), //fresh() recharge depuis BDD
        ]);
    }

    /**
     * Supprime une metrique
     */
    public function destroy(Application $application, Metric $metric)
    {
        Gate::authorize('view', $application);

        if ($metric->application_id !== $application->id) {
            abort(404);
        }

        $metric->delete();

        return response()->json([
            'message' => 'Metric deleted successfully',
        ]);
    }

    /**
     * Stats globales de l'app
     * (total requetes, CO2, periode, etc)
     */
    public function stats(Application $application)
    {
        Gate::authorize('view', $application);

        $metrics = $application->metrics;

        return response()->json([
            'stats' => [
                'total_metrics' => $metrics->count(), //nb de jours enregistrés
                'total_requests' => $metrics->sum('requests_count'),
                'total_storage_gb' => $metrics->sum('storage_gb'),
                'total_cpu_hours' => $metrics->sum('cpu_hours'),
                'total_carbon_footprint_kg' => $metrics->sum('carbon_footprint_kg'),
                'average_carbon_footprint_kg' => $metrics->avg('carbon_footprint_kg'),
                'date_range' => [
                    'from' => $metrics->min('date'), //premiere metrique
                    'to' => $metrics->max('date'),   //derniere metrique
                ],
            ],
        ]);
    }
}