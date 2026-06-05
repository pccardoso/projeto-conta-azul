<?php

namespace App\Http\Requests\FinancialReleases;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        return [
            'id_card_pipefy' => 'required|numeric',
            'status' => 'required|in:ABERTO,PENDENTE,PAGO,BAIXADO',
            'protocol' => 'required|string|max:255',
            'event' => 'nullable|string|max:255'
        ];
    }
}
