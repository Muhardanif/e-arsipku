<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('judul', 'Laporan') — E-ARSIPKU</title>
    <style>
        @page { margin: 96px 32px 64px 32px; }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.4; }

        /* ── Kop surat (fixed, tampil di setiap halaman) ─────────── */
        .kop {
            position: fixed; top: -72px; left: 0; right: 0; height: 64px;
            border-bottom: 2px solid #006B5A;
        }
        .kop td { vertical-align: middle; }
        .logo {
            width: 38px; height: 38px; background: #006B5A; color: #fff;
            border-radius: 7px; text-align: center; font-size: 15px; font-weight: bold;
            line-height: 38px; letter-spacing: .5px;
        }
        .brand { font-size: 15px; font-weight: bold; color: #006B5A; letter-spacing: .4px; }
        .tag { font-size: 8px; color: #64748b; }
        .kop .meta { text-align: right; font-size: 8px; color: #64748b; }
        .kop .meta strong { color: #1e293b; }

        /* ── Footer (fixed) ──────────────────────────────────────── */
        .footer {
            position: fixed; bottom: -44px; left: 0; right: 0; height: 32px;
            border-top: 1px solid #e2e8f0; padding-top: 6px;
            font-size: 7.5px; color: #94a3b8;
        }
        .footer td { vertical-align: middle; }
        .footer .pg { text-align: center; }
        .footer .pg:after { content: counter(page) " / " counter(pages); }
        .footer .right { text-align: right; }

        /* ── Judul laporan ───────────────────────────────────────── */
        .title-wrap { border-left: 4px solid #009177; padding-left: 10px; margin-bottom: 12px; }
        h1.judul { font-size: 14px; color: #0f172a; }
        .sub { font-size: 9px; color: #64748b; margin-top: 1px; }

        /* ── Info bar (filter & ringkasan) ───────────────────────── */
        .infobar {
            background: #f4f7fb; border: 1px solid #e6ebf2; border-radius: 6px;
            padding: 7px 10px; margin-bottom: 12px; font-size: 9px; color: #475569;
        }
        .infobar strong { color: #0f172a; }
        .infobar .total {
            float: right; background: #009177; color: #fff; font-weight: bold;
            padding: 2px 9px; border-radius: 999px; font-size: 8.5px;
        }

        /* ── Tabel data ──────────────────────────────────────────── */
        table.data { width: 100%; border-collapse: collapse; }
        table.data thead th {
            background: #006B5A; color: #fff; font-size: 8px; font-weight: bold;
            text-transform: uppercase; letter-spacing: .4px; text-align: left;
            padding: 7px 8px;
        }
        table.data thead th:first-child { border-top-left-radius: 5px; }
        table.data thead th:last-child  { border-top-right-radius: 5px; }
        table.data tbody td {
            padding: 6px 8px; border-bottom: 1px solid #eef2f7; vertical-align: top;
        }
        table.data tbody tr:nth-child(even) td { background: #f8fafc; }
        table.data tbody tr.warn td { background: #fef2f2; }

        .muted { color: #64748b; }
        .num { text-align: center; color: #94a3b8; }
        .strong { font-weight: bold; color: #0f172a; }
        .mono { font-family: 'DejaVu Sans Mono', monospace; font-size: 8.5px; }

        /* ── Pill status ─────────────────────────────────────────── */
        .pill { display: inline-block; font-size: 7.5px; font-weight: bold; padding: 2px 7px; border-radius: 999px; }
        .pill-green { background: #E6F5F2; color: #006B5A; }
        .pill-red   { background: #fee2e2; color: #b91c1c; }
        .pill-gray  { background: #e2e8f0; color: #475569; }
        .pill-amber { background: #fef3c7; color: #b45309; }
        .pill-blue  { background: #dbeafe; color: #1d4ed8; }

        .empty { text-align: center; padding: 28px; color: #94a3b8; font-size: 9.5px; }
    </style>
</head>
<body>
    {{-- Kop surat --}}
    <div class="kop">
        <table style="width:100%; height:100%;">
            <tr>
                <td style="width:46px;"><div class="logo">EA</div></td>
                <td>
                    <div class="brand">E-ARSIPKU</div>
                    <div class="tag">Sistem Pencatatan Arsip Dokumen Puskesmas</div>
                </td>
                <td class="meta">
                    Dicetak: <strong>{{ $dicetakPada->translatedFormat('d F Y, H:i') }} WIB</strong><br>
                    Oleh: <strong>{{ $dicetakOleh }}</strong>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <table style="width:100%;">
            <tr>
                <td>E-ARSIPKU · Dokumen internal puskesmas</td>
                <td class="pg"></td>
                <td class="right">Laporan dihasilkan otomatis oleh sistem</td>
            </tr>
        </table>
    </div>

    {{-- Judul --}}
    <div class="title-wrap">
        <h1 class="judul">@yield('judul', 'Laporan')</h1>
        <div class="sub">@yield('subjudul')</div>
    </div>

    @yield('konten')
</body>
</html>
