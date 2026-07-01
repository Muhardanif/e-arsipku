<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', Password::min(6), 'different:current_password', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ];
    }
}
