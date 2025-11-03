<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthController
 * 
 * Gère le user:
 * - register() = inscription (création compte + premier token)
 * - login()    = connexion (vérification + nouveau token)
 * - logout()   = déconnexion (sup du token actuel)
 * - user()     = profil (infos de l'utilisateur connecté)
 * 
 * j'use Laravel Sanctum pour les tokens API (Bearer tokens).
 * Les mots de passe sont hashés avec bcrypt (Hash::make).
 * bcrypt c'est un algo que j'ai recup
 * 
 * Sécurité :
 * - passwords toujours Hash
 * - Tokens révocables quand je veux
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), //C'est comme ça que le mdp est confi
        ]);

        $token = $user->createToken('auth_token')->plainTextToken; //creation token le truc que j'ai quand je rentre un new_user

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) { //check mdp avec verif user + mdp
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete(); //efface anciens token c'est un choix de secu mais pas obli

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user info.
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}