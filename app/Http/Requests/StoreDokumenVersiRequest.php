<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDokumenVersiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || $user->isPetugas());
    }

    public function rules(): array
    {
        return [
            'tanggal_revisi' => ['required', 'date', 'before_or_equal:today'],
            'catatan_revisi' => ['required', 'string', 'max:500'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal_revisi' => 'tanggal revisi',
            'catatan_revisi' => 'catatan revisi',
            'file' => 'berkas revisi',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_revisi.required' => 'Tanggal revisi wajib diisi.',
            'tanggal_revisi.before_or_equal' => 'Tanggal revisi tidak boleh melebihi hari ini.',
            'catatan_revisi.required' => 'Catatan revisi wajib diisi untuk menjelaskan perubahan.',
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
            'file.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
        ];
    }
}
