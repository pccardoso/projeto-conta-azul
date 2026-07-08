<?php

namespace App\Http\Requests\Efi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLinkCreditCardRequest extends FormRequest
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
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.value' => 'required|integer|min:1',
            'items.*.amount' => 'required|integer|min:1',

            'metadata' => 'required|array',
            'metadata.custom_id' => 'required|string|max:255',
            'metadata.notification_url' => 'required|url',

            'customer' => 'required|array',
            'customer.email' => 'required|email',

            'settings' => 'required|array',
            'settings.message' => 'nullable|string|max:80',
            'settings.payment_method' => ['required', Rule::in(['banking_billet', 'credit_card', 'all'])],
            'settings.expire_at' => 'required|date_format:Y-m-d|after_or_equal:today',
            'settings.request_delivery_address' => 'required|boolean',
        ];
    }
}
