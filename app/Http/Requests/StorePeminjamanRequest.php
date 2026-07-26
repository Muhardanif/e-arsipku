<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->bolehMenu('peminjaman') ?? false;
    }

    public function rules(): array
    {
        return [
            'dokumen_id' => ['required', Rule::exists('dokumen', 'id')->whereNull('deleted_at')],
            'peminjam_id' => ['nullable', Rule::exists('users', 'id')],
            'peminjam_nama' => ['nullable', 'string', 'max:255', 'required_without:peminjam_id'],
            'tujuan' => ['required', 'string', 'max:255'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'dokumen_id' => 'dokumen',
            'peminjam_id' => 'peminjam',
            'peminjam_nama' => 'nama peminjam',
            'tanggal_pinjam' => 'tanggal pinjam',
            'tanggal_kembali_rencana' => 'tanggal rencana kembali',
        ];
    }

    public function messages(): array
    {
        return [
            'peminjam_nama.required_without' => 'Pilih peminjam dari daftar pengguna atau isi nama peminjam secara manual.',
            'tanggal_kembali_rencana.after_or_equal' => 'Tanggal rencana kembali tidak boleh sebelum tanggal pinjam.',
        ];
    }
}
