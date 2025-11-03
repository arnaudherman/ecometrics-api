<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * ApplicationController
 * 
 * C'est un exemple de pourquoi utiliser Laravel
 * 
 *il me donne :
 * - index()   = Lister toutes les applications
 * - store()   = Créer une nouvelle application
 * - show()    = Afficher une application spécifique
 * - update()  = Modifier une application
 * - destroy() = Supprimer une application
 * 
 * Sinon j'aurais du :
 * - Définir toutes les routes manuellement
 * - Gérer la validation des données à la main
 * - Écrire le code de sécurité (authentification, autorisations)
 * - Formater les réponses JSON proprement
 */
class ApplicationController extends Controller
{
    /**
     * Display a listing of the user's applications.
     */
    public function index(Request $request)
    {
        $applications = $request->user()
            ->applications()
            ->withCount('metrics')
            ->latest()
            ->get();

        return response()->json([
            'applications' => $applications,
        ]);
    }

    /**
     * Store a newly created application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $application = $request->user()->applications()->create($validated);

        return response()->json([
            'message' => 'Application created successfully',
            'application' => $application,
        ], 201);
    }

    /**
     * Display the specified application.
     */
    public function show(Application $application)
    {
        Gate::authorize('view', $application);

        $application->load([
            'metrics' => fn($query) => $query->latest()->limit(30),
            'latestCertificate',
        ]);

        return response()->json([
            'application' => $application,
            'average_carbon_footprint' => $application->averageCarbonFootprint,
            'total_carbon_footprint' => $application->totalCarbonFootprint,
        ]);
    }

    /**
     * Update the specified application.
     */
    public function update(Request $request, Application $application)
    {
        Gate::authorize('update', $application);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $application->update($validated);

        return response()->json([
            'message' => 'Application updated successfully',
            'application' => $application,
        ]);
    }

    /**
     * Remove the specified application.
     */
    public function destroy(Application $application)
    {
        Gate::authorize('delete', $application);

        $application->delete();

        return response()->json([
            'message' => 'Application deleted successfully',
        ]);
    }
}