@extends('laporan.pdf.layout')

@section('judul', 'Laporan Daftar Dokumen')
@section('subjudul', 'Rekapitulasi dokumen arsip puskesmas')

@section('konten')
    @php
        $pills = ['berlaku' => 'pill-green', 'kadaluarsa' => 'pill-red', 'dicabut' => 'pill-gray'];
        $namaKategori = $kategoriList->firstWhere('id', $filters['kategori_id'] ?? null)?->nama;
        $ket = [];
        if ($namaKategori) $ket[] = 'Kategori: '.$namaKategori;
        if (! empty($filters['status'])) $ket[] = 'Status: '.ucfirst($filters['status']);
        if (! empty($filters['dari'])) $ket[] = 'Dari: '.\Illuminate\Support\Carbon::parse($filters['dari'])->translatedFormat('d M Y');
        if (! empty($filters['sampai'])) $ket[] = 'Sampai: '.\Illuminate\Support\Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
    @endphp

    <div class="infobar">
        <span class="total">{{ $dokumen->count() }} dokumen</span>
        <strong>Filter:</strong> {{ $ket ? implode(' · ', $ket) : 'Semua dokumen' }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 18%;">Nomor Dokumen</th>
                <th style="width: 30%;">Judul</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 12%;">Tgl Dokumen</th>
                <th style="width: 16%;">Pengesah</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dokumen as $i => $dok)
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td>{{ $dok->nomor_dokumen }}</td>
                    <td class="strong">{{ $dok->judul }}</td>
                    <td class="muted">{{ $dok->kategori?->kode }}</td>
                    <td>{{ $dok->tanggal_dokumen?->translatedFormat('d M Y') }}</td>
                    <td class="muted">{{ $dok->pengesah ?: '—' }}</td>
                    <td><span class="pill {{ $pills[$dok->status] ?? 'pill-gray' }}">{{ ucfirst($dok->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada dokumen sesuai filter.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
