@php $isEdit = (bool) ($klaster ?? null); @endphp

<form method="POST"
      action="{{ $isEdit ? route('master.klaster.update', $klaster) : route('master.klaster.store') }}"
      data-modal-form
      data-confirm="{{ $isEdit ? 'Simpan perubahan klaster ini?' : 'Simpan klaster baru ini?' }}"
      data-success="{{ $isEdit ? 'Klaster berhasil diperbarui.' : 'Klaster berhasil ditambahkan.' }}">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="px-6 py-5">
        @include('master.klaster._form', ['klaster' => $klaster ?? null])
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">
            <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Klaster' }}
        </x-button>
    </div>
</form>
