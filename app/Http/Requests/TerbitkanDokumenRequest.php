<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TerbitkanDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || $user->isPetugas());
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'catatan_revisi' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'berkas final',
            'catatan_revisi' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Unggah berkas final dokumen untuk menerbitkan.',
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
            'file.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
        ];
    }
}
