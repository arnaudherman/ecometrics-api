<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\CarbonCertificate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class CertificateController extends Controller
{
    /**
     * Creation du certif ecolo
     * calcule le c02 des 30 derniers j
     * donne le pre-badge
     */
    public function issue(Application $application)
    {
        Gate::authorize('view', $application);

        $thirtyDaysAgo = Carbon::now()->subDays(30); //calcule des 30j pour le certif
        
        $metrics = $application->metrics()
            ->where('date', '>=', $thirtyDaysAgo)
            ->get();

        if ($metrics->isEmpty()) {
            return response()->json([
                'message' => 'Not enough data to issue a certificate. Please add at least one metric.',
            ], 400);
        }

        $totalCarbonKg = $metrics->sum('carbon_footprint_kg'); //calcule en kg
        
        $daysWithData = $metrics->count();
        $monthlyCarbonKg = ($totalCarbonKg / $daysWithData) * 30;

        $badgeLevel = CarbonCertificate::determineBadgeLevel($monthlyCarbonKg);

        $certificate = $application->carbonCertificates()->create([ //creation certif
            'badge_level' => $badgeLevel,
            'issued_at' => Carbon::now(),
            'valid_until' => Carbon::now()->addYear(),
        ]);

        return response()->json([
            'message' => 'Certificate issued successfully',
            'certificate' => $certificate,
            'stats' => [
                'days_analyzed' => $daysWithData,
                'total_carbon_kg' => round($totalCarbonKg, 3),
                'monthly_average_kg' => round($monthlyCarbonKg, 3),
                'badge_level' => $badgeLevel,
            ],
        ], 201);
    }

    /**
     * Recup le last certif
     */
    public function show(Application $application)
    {
        Gate::authorize('view', $application);

        $certificate = $application->latestCertificate;

        if (!$certificate) {
            return response()->json([
                'message' => 'No certificate found for this application.',
                'certificate' => null,
            ], 404);
        }

        return response()->json([
            'certificate' => $certificate,
            'is_valid' => $certificate->isValid(),
        ]);
    }

    /**
     * Pour avoir l'historique des certif
     */
    public function index(Application $application)
    {
        Gate::authorize('view', $application);

        $certificates = $application->carbonCertificates()
            ->orderBy('issued_at', 'desc')
            ->get();

        return response()->json([
            'certificates' => $certificates,
        ]);
    }
}