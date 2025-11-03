<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\MetricController;
use App\Http\Controllers\Api\CertificateController;
use Illuminate\Support\Facades\Route;

/**
 * Routes API - EcoMetrics
 * 
 * Architecture REST pour le tracking carbone des applications
 * 
 * Structure globale:
 * - 2 routes publiques (register, login)
 * - 17 routes protegees par Sanctum (Bearer token requis)
 * 
 * Endpoints disponibles:
 * /api/applications → CRUD des apps trackees
 * /api/applications/{id}/metrics → Metriques quotidiennes d'une app
 * /api/applications/{id}/certificates → Certificats ecologiques
 */

// ========================================
// ROUTES PUBLIQUES (pas de token requis)
// ========================================

// Inscription: cree un user + retourne un token
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Connexion: valide email/password + retourne un token
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ========================================
// ROUTES PROTEGEES (token Sanctum requis)
// Middleware: auth:sanctum
// Header: Authorization: Bearer {token}
// ========================================

Route::middleware('auth:sanctum')->group(function () {
    
    // ---------------------------
    // AUTH - Gestion du compte
    // ---------------------------
    
    // Deconnexion: supprime le token actuel
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profil: retourne les infos de l'user connecte
    Route::get('/user', [AuthController::class, 'user'])->name('user');
    
    // ---------------------------
    // APPLICATIONS - CRUD complet
    // ---------------------------
    
    // apiResource = 5 routes auto avec noms auto (applications.index, etc.)
    Route::apiResource('applications', ApplicationController::class);
    
    // ---------------------------
    // METRICS - Tracking quotidien
    // Toutes les routes commencent par /applications/{application}/
    // ---------------------------
    
    Route::prefix('applications/{application}')->group(function () {
        
        // Metriques
        Route::get('/metrics', [MetricController::class, 'index'])->name('metrics.index');
        Route::post('/metrics', [MetricController::class, 'store'])->name('metrics.store');
        Route::get('/metrics/stats', [MetricController::class, 'stats'])->name('metrics.stats');
        Route::get('/metrics/{metric}', [MetricController::class, 'show'])->name('metrics.show');
        Route::put('/metrics/{metric}', [MetricController::class, 'update'])->name('metrics.update');
        Route::delete('/metrics/{metric}', [MetricController::class, 'destroy'])->name('metrics.destroy');
        
        // ---------------------------
        // CERTIFICATES - Badges ecolos
        // ---------------------------
        
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::post('/issue-certificate', [CertificateController::class, 'issue'])->name('certificates.issue');
        Route::get('/certificate', [CertificateController::class, 'show'])->name('certificates.show');
    });
});