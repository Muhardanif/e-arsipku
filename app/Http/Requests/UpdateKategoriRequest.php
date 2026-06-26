<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('kategori')->id;

        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('kategori_dokumen', 'kode')->ignore($id)],
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'format_nomor' => ['nullable', 'string', 'max:255', 'regex:/\{NO\}/'],
            'digit_nomor' => ['required', 'integer', 'min:1', 'max:6'],
            'periode_review_tahun' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'format_nomor.regex' => 'Format harus memuat token {NO} sebagai posisi nomor urut.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode' => $this->filled('kode') ? strtoupper(trim($this->kode)) : $this->kode,
            'format_nomor' => $this->filled('format_nomor') ? trim($this->format_nomor) : null,
            'digit_nomor' => $this->filled('digit_nomor') ? $this->digit_nomor : 3,
            'periode_review_tahun' => $this->filled('periode_review_tahun') ? $this->periode_review_tahun : null,
        ]);
    }
}
