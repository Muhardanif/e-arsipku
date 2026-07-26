<?php

namespace App\Http\Requests;

use App\Models\MenuAkses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHakAksesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'akses' => ['nullable', 'array'],
            'akses.*' => ['array'],
            'akses.*.*' => ['string', Rule::in(array_keys(MenuAkses::MENUS))],
        ];
    }
}
