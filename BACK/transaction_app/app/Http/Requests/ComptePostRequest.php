<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComptePostRequest extends FormRequest
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
            "telephone" => "required|regex:/^(7[05678]{1})(\\d{7})$/"
        ];
    }
    public function messages()
    {
        return [
            "telephone.required" => "Le téléphone est obligatoire !",
            "telephone.regex" => "Le format du téléphone est incorrect !",
        ];
    }
}
