@extends('layouts.app')

@section('title', 'Hak Akses Menu')
@section('header', 'Master Data — Hak Akses Menu')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Hak Akses Menu']]" />
@endsection

@section('content')
@php use App\Models\MenuAkses; @endphp
<div class="space-y-5">

    <div class="flex items-start gap-3 rounded-xl border border-accent/20 bg-accent-soft/60 px-4 py-3 text-sm text-slate-600">
        @svg('heroicon-o-information-circle', 'mt-0.5 h-5 w-5 shrink-0 text-accent')
        <p>Centang menu yang boleh diakses tiap role, lalu simpan. Perubahan langsung berlaku pada
            sidebar dan seluruh halaman terkait. <span class="font-medium text-foreground">Admin selalu
            memiliki akses penuh</span> dan tidak dapat diubah.</p>
    </div>

    <form method="POST" action="{{ route('master.hak-akses.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Menu</th>
                            <th class="px-5 py-3 text-center">Admin</th>
                            @foreach (MenuAkses::ROLES as $roleLabel)
                                <th class="px-5 py-3 text-center">{{ $roleLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach (MenuAkses::MENUS as $menu => [$label, $keterangan])
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-foreground">{{ $label }}</p>
                                    <p class="text-xs text-slate-500">{{ $keterangan }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-md bg-accent-soft px-2 py-1 text-xs font-semibold text-accent ring-1 ring-inset ring-accent/15"
                                          title="Admin selalu memiliki akses penuh">Penuh</span>
                                </td>
                                @foreach (MenuAkses::ROLES as $role => $roleLabel)
                                    <td class="px-5 py-3.5 text-center">
                                        <label class="inline-flex cursor-pointer items-center justify-center p-2"
                                               title="{{ $roleLabel }} — {{ $label }}">
                                            <input type="checkbox" name="akses[{{ $role }}][]" value="{{ $menu }}"
                                                @checked(in_array($menu, old("akses.$role", $akses[$role] ?? []), true))
                                                class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent/40">
                                            <span class="sr-only">{{ $roleLabel }} boleh mengakses {{ $label }}</span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @error('akses.*.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <div class="flex justify-end">
            <x-button type="submit" variant="primary">
                <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
                Simpan Perubahan
            </x-button>
        </div>
    </form>
</div>
@endsection
