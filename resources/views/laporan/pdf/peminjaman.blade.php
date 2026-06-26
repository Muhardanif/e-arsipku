@extends('laporan.pdf.layout')

@section('judul', 'Laporan Peminjaman Dokumen')
@section('subjudul', 'Riwayat peminjaman dokumen fisik')

@section('konten')
    @php
        $labelStatus = ['semua' => 'Semua', 'dipinjam' => 'Sedang Dipinjam', 'terlambat' => 'Terlambat', 'dikembalikan' => 'Dikembalikan'];
    @endphp

    <div class="infobar">
        <span class="total">{{ $peminjaman->count() }} data</span>
        <strong>Kategori:</strong> {{ $labelStatus[$status] ?? 'Semua' }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 26%;">Dokumen</th>
                <th style="width: 16%;">Peminjam</th>
                <th style="width: 18%;">Tujuan</th>
                <th style="width: 11%;">Pinjam</th>
                <th style="width: 11%;">Kembali</th>
                <th style="width: 14%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peminjaman as $i => $p)
                @php $terlambat = $p->isTerlambat(); @endphp
                <tr class="{{ $terlambat ? 'warn' : '' }}">
                    <td class="num">{{ $i + 1 }}</td>
                    <td>
                        <span class="strong">{{ $p->dokumen?->judul ?? '—' }}</span><br>
                        <span class="muted">{{ $p->dokumen?->nomor_dokumen }}</span>
                    </td>
                    <td>{{ $p->peminjam_nama ?? $p->peminjam?->nama ?? '—' }}</td>
                    <td class="muted">{{ $p->tujuan }}</td>
                    <td>{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                    <td>{{ $p->tanggal_kembali_aktual?->translatedFormat('d M Y') ?? $p->tanggal_kembali_rencana?->translatedFormat('d M Y') }}</td>
                    <td>
                        @if ($terlambat)
                            <span class="pill pill-red">Terlambat</span>
                        @elseif ($p->status === 'dikembalikan')
                            <span class="pill pill-green">Dikembalikan</span>
                        @else
                            <span class="pill pill-amber">Dipinjam</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada data peminjaman.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
