<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientPostRequest extends FormRequest
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
            "nom" => "required",
            "prenom" => "required",
            "telephone" => "required|unique:clients|regex:/^(7[76508]{1})(\\d{7})$/"
        ];
    }
    public function messages()
    {
        return [
            "nom.required" => "Le nom est obligatoire",
            "prenom.required" => "Le prenom est obligatoire",
            "telephone.required" => "Le telephone est obligatoire",
            "telephone.unique" => "Le telephone est unique",
            "telephone.regex" => "Le format du telephone est incorrect",
        ];
    }
}
