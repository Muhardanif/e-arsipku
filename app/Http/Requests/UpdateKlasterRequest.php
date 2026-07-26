<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKlasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->bolehMenu('master-klaster') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('klaster')->id;

        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('klaster', 'kode')->ignore($id)],
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode' => $this->filled('kode') ? strtoupper(trim($this->kode)) : $this->kode,
            'urutan' => $this->filled('urutan') ? $this->urutan : 0,
        ]);
    }
}
