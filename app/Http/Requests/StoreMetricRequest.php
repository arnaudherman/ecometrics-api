<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreMetricRequest - Validation pour creer une metrique
 * 
 * Au lieu de valider direct dans le controller,
 * je centralise toutes les regles ici
 * Plus propre et reutilisable
 */
class StoreMetricRequest extends FormRequest
{
    /**
     * Check si le user a le droit
     * (pour l'instant true = tout le monde)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regles de validation
     */
    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                'before_or_equal:today', //pas de date future
                Rule::unique('metrics')->where(function ($query) {
                    //1 seule metrique par jour par app
                    return $query->where('application_id', $this->route('application')->id);
                }),
            ],
            'requests_count' => 'required|integer|min:0', //nb requetes
            'storage_gb' => 'required|numeric|min:0',     //stockage GB
            'cpu_hours' => 'required|numeric|min:0',      //temps CPU h
        ];
    }

    /**
     * Messages d'erreur custom
     */
    public function messages(): array
    {
        return [
            'date.unique' => 'A metric for this date already exists for this application.',
            'date.before_or_equal' => 'The date cannot be in the future.',
        ];
    }
}