<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

/**
 * ApplicationPolicy
 * 
 * Definit QUI peut faire QUOI sur les applications
 * (la securite de l'API)
 * 
 * Utilise dans les controllers via:
 * Gate::authorize('view', $application)
 * 
 * Laravel me donne auto a la creation (php artisan make:policy):
 * - namespace App\Policies
 * - class ApplicationPolicy {}
 * 
 * Tout le reste (les 5 methodes) = custom
 * Logique: un user peut UNIQUEMENT gerer SES propres apps
 */
class ApplicationPolicy
{
    /**
     * Voir la liste de ses apps
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Voir UNE app specifique
     */
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }

    /**
     * Creer une nouvelle app
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Modifier une app existante
     */
    public function update(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }

    /**
     * Supprimer une app
     */
    public function delete(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }
}