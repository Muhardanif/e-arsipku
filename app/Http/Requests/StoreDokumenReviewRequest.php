<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDokumenReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->bolehMenu('dokumen-kelola') ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal_review' => ['required', 'date', 'before_or_equal:today'],
            'hasil' => ['required', Rule::in(['sesuai', 'perlu_revisi'])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_review.before_or_equal' => 'Tanggal review tidak boleh di masa depan.',
        ];
    }
}
