<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->bolehMenu('dokumen-kelola') ?? false;
    }

    public function rules(): array
    {
        $dokumenId = $this->route('dokumen')->id;

        return [
            'nomor_dokumen' => ['required', 'string', 'max:100', Rule::unique('dokumen', 'nomor_dokumen')->ignore($dokumenId)],
            'judul' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', Rule::exists('kategori_dokumen', 'id')],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_dokumen' => ['required', 'date'],
            'tanggal_berlaku' => ['nullable', 'date'],
            'tanggal_berakhir' => ['nullable', 'date', 'after_or_equal:tanggal_berlaku'],
            'pengesah' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draf', 'berlaku', 'kadaluarsa', 'dicabut'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'nomor_dokumen' => 'nomor dokumen',
            'kategori_id' => 'kategori',
            'tanggal_dokumen' => 'tanggal dokumen',
            'tanggal_berlaku' => 'tanggal berlaku',
            'tanggal_berakhir' => 'tanggal berakhir',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal berlaku.',
        ];
    }
}
