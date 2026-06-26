<?php

namespace App\Http\Requests;

use App\Models\KategoriDokumen;
use App\Support\PenomoranDokumen;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || $user->isPetugas());
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('mode_nomor')) {
            $this->merge(['mode_nomor' => 'manual']);
        }

        // Tanpa berkas, dokumen disimpan sebagai draf (nomor dipesan dulu,
        // berkas final diunggah belakangan lewat "Terbitkan").
        if (! $this->hasFile('file')) {
            $this->merge(['status' => 'draf']);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Mode otomatis hanya valid bila kategori punya template penomoran.
            if ($this->input('mode_nomor') === 'otomatis') {
                $kategori = KategoriDokumen::find($this->input('kategori_id'));

                if (! $kategori || ! $kategori->punyaFormatNomor()) {
                    $validator->errors()->add('nomor_dokumen',
                        'Kategori ini belum memiliki format penomoran otomatis. Gunakan mode manual atau atur format di Master Kategori.');

                    return;
                }

                // Kategori berklaster wajib memilih klaster.
                if (PenomoranDokumen::pakaiKlaster($kategori->format_nomor) && ! $this->filled('klaster_id')) {
                    $validator->errors()->add('klaster_id', 'Silakan pilih klaster untuk kategori ini.');
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'mode_nomor' => ['required', Rule::in(['otomatis', 'manual'])],
            'nomor_dokumen' => ['nullable', 'required_if:mode_nomor,manual', 'string', 'max:100', Rule::unique('dokumen', 'nomor_dokumen')],
            'klaster_id' => ['nullable', Rule::exists('klaster', 'id')],
            'judul' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', Rule::exists('kategori_dokumen', 'id')],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_dokumen' => ['required', 'date'],
            'tanggal_berlaku' => ['nullable', 'date'],
            'tanggal_berakhir' => ['nullable', 'date', 'after_or_equal:tanggal_berlaku'],
            'pengesah' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draf', 'berlaku', 'kadaluarsa', 'dicabut'])],
            'catatan_revisi' => ['nullable', 'string', 'max:500'],
            // Berkas wajib hanya bila bukan draf; draf boleh tanpa berkas.
            'file' => ['nullable', 'required_unless:status,draf', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
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
            'catatan_revisi' => 'catatan revisi',
            'file' => 'berkas dokumen',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
            'file.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal berlaku.',
        ];
    }
}
