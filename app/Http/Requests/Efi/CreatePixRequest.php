<?php

namespace App\Http\Requests\Efi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePixRequest extends FormRequest
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
            'calendario' => 'required|array',
            'calendario.expiracao' => 'required|integer|min:1',

            'devedor' => 'required|array',
            'devedor.cpf' => 'required_without:devedor.cnpj|digits:11',
            'devedor.cnpj' => 'required_without:devedor.cpf|digits:14',
            'devedor.nome' => 'required|string|max:255',

            'valor' => 'required|array',
            'valor.original' => ['required', 'regex:/^\d+\.\d{2}$/'],

            'chave' => 'required|string|max:77',
            'solicitacaoPagador' => 'nullable|string|max:140',
        ];
    }
}
