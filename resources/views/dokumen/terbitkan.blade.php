@extends('layouts.app')

@section('title', 'Terbitkan Dokumen')
@section('header', 'Terbitkan Dokumen')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dokumen', 'url' => route('dokumen.index')],
        ['label' => $dokumen->nomor_dokumen, 'url' => route('dokumen.show', $dokumen)],
        ['label' => 'Terbitkan'],
    ]" />
@endsection

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="card overflow-hidden">
            @include('dokumen._terbitkan', ['dokumen' => $dokumen])
        </div>
    </div>
@endsection
