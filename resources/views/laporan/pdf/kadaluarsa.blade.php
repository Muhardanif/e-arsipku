@extends('laporan.pdf.layout')

@section('judul', 'Laporan Dokumen Akan Kadaluarsa')
@section('subjudul', 'Dokumen dengan masa berlaku akan / sudah berakhir')

@section('konten')
    <div class="infobar">
        <span class="total">{{ $dokumen->count() }} dokumen</span>
        <strong>Rentang:</strong> {{ $hari }} hari ke depan (termasuk yang sudah melewati tanggal berakhir)
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 20%;">Nomor Dokumen</th>
                <th style="width: 34%;">Judul</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 15%;">Tgl Berakhir</th>
                <th style="width: 15%;">Sisa Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dokumen as $i => $dok)
                @php
                    $sisa = (int) now()->startOfDay()->diffInDays($dok->tanggal_berakhir, false);
                    $lewat = $sisa < 0;
                @endphp
                <tr class="{{ $lewat ? 'warn' : '' }}">
                    <td class="num">{{ $i + 1 }}</td>
                    <td>{{ $dok->nomor_dokumen }}</td>
                    <td class="strong">{{ $dok->judul }}</td>
                    <td class="muted">{{ $dok->kategori?->kode }}</td>
                    <td>{{ $dok->tanggal_berakhir?->translatedFormat('d M Y') }}</td>
                    <td>
                        @if ($lewat)
                            <span class="pill pill-red">Lewat {{ abs($sisa) }} hari</span>
                        @else
                            <span class="pill pill-amber">{{ $sisa }} hari lagi</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Tidak ada dokumen yang akan kadaluarsa dalam rentang ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
