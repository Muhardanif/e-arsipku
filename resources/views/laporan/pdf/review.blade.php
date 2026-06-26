@extends('laporan.pdf.layout')

@section('judul', 'Laporan Dokumen Perlu Review')
@section('subjudul', 'Dokumen yang jatuh tempo tinjauan berkala')

@section('konten')
    <div class="infobar">
        <span class="total">{{ $dokumen->count() }} dokumen</span>
        <strong>Rentang:</strong> {{ $hari }} hari ke depan (termasuk yang sudah melewati jatuh tempo review)
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 19%;">Nomor Dokumen</th>
                <th style="width: 31%;">Judul</th>
                <th style="width: 11%;">Kategori</th>
                <th style="width: 9%;">Periode</th>
                <th style="width: 14%;">Jatuh Tempo</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dokumen as $i => $dok)
                @php
                    $sisa = $dok->sisaHariReview();
                    $lewat = $sisa < 0;
                @endphp
                <tr class="{{ $lewat ? 'warn' : '' }}">
                    <td class="num">{{ $i + 1 }}</td>
                    <td>{{ $dok->nomor_dokumen }}</td>
                    <td class="strong">{{ $dok->judul }}</td>
                    <td class="muted">{{ $dok->kategori?->kode }}</td>
                    <td class="muted">{{ $dok->kategori?->periode_review_tahun }} th</td>
                    <td>{{ $dok->jatuhTempoReview()?->translatedFormat('d M Y') }}</td>
                    <td>
                        @if ($lewat)
                            <span class="pill pill-red">Lewat {{ abs($sisa) }} hari</span>
                        @else
                            <span class="pill pill-amber">{{ $sisa }} hari lagi</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada dokumen yang perlu ditinjau dalam rentang ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
