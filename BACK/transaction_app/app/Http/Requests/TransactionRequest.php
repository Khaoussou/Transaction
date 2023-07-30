<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "expediteur" => "required",
            "destinataire" => "required",
            "type" => "required",
            "montant" => "required",
            "fournisseur" => "required"
        ];
    }
    public function messages()
    {
        return [
            "expediteur.required" => "Veuillez remplir les champs !",
            "destinataire.required" => "Veuillez remplir les champs !",
            "type.required" => "Veuillez remplir les champs !",
            "montant.required" => "Veuillez remplir les champs !",
            "fournisseur.required" => "Veuillez remplir les champs !"
        ];
    }
}
