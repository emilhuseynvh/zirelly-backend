<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()-]{7,20}$/'],
            'subject' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}