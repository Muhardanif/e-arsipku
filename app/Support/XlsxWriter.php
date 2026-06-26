<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Penulis berkas .xlsx minimalis tanpa dependency eksternal.
 *
 * XLSX pada dasarnya adalah arsip ZIP berisi beberapa berkas XML. Kelas ini
 * membangun struktur paling ringkas yang dikenali Excel/LibreOffice: satu
 * worksheet dengan baris header tebal, sisanya data tabular. Cocok untuk
 * lingkungan offline (XAMPP) yang tidak bisa menarik paket Composer besar.
 */
class XlsxWriter
{
    /**
     * Bangun berkas xlsx lalu kembalikan sebagai unduhan.
     *
     * @param  array<int,string>            $headers  Judul kolom (baris pertama, tebal).
     * @param  array<int,array<int,mixed>>  $rows     Baris data.
     */
    public static function download(string $namaBerkas, string $judulSheet, array $headers, array $rows): StreamedResponse
    {
        $isi = self::build($judulSheet, $headers, $rows);

        return response()->streamDownload(function () use ($isi) {
            echo $isi;
        }, $namaBerkas, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  array<int,string>            $headers
     * @param  array<int,array<int,mixed>>  $rows
     */
    public static function build(string $judulSheet, array $headers, array $rows): string
    {
        // ZipArchive membutuhkan path berkas, jadi pakai berkas sementara.
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook($judulSheet));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($headers, $rows));

        $zip->close();
        $isi = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $isi;
    }

    /** @param array<int,array<int,mixed>> $rows */
    private static function sheet(array $headers, array $rows): string
    {
        $baris = 1;
        $xml = self::rowXml($baris++, $headers, true);

        foreach ($rows as $row) {
            $xml .= self::rowXml($baris++, array_values($row), false);
        }

        $kolomTerakhir = self::colLetter(max(count($headers), 1) - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<sheetData>'.$xml.'</sheetData>'
            .'<autoFilter ref="A1:'.$kolomTerakhir.($baris - 1).'"/>'
            .'</worksheet>';
    }

    /** @param array<int,mixed> $cells */
    private static function rowXml(int $r, array $cells, bool $tebal): string
    {
        $xml = '<row r="'.$r.'">';
        $i = 0;
        foreach ($cells as $val) {
            $ref = self::colLetter($i++).$r;
            $teks = self::esc((string) ($val ?? ''));
            $style = $tebal ? ' s="1"' : '';
            $xml .= '<c r="'.$ref.'"'.$style.' t="inlineStr"><is><t xml:space="preserve">'.$teks.'</t></is></c>';
        }

        return $xml.'</row>';
    }

    /** Indeks 0 → A, 25 → Z, 26 → AA, dst. */
    private static function colLetter(int $i): string
    {
        $s = '';
        $i++;
        while ($i > 0) {
            $sisa = ($i - 1) % 26;
            $s = chr(65 + $sisa).$s;
            $i = intdiv($i - 1, 26);
        }

        return $s;
    }

    private static function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbook(string $judulSheet): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.self::esc(self::namaSheet($judulSheet)).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    /** Nama sheet Excel maks 31 karakter & tanpa karakter terlarang. */
    private static function namaSheet(string $nama): string
    {
        $nama = preg_replace('/[:\\\\\/?*\[\]]/', ' ', $nama) ?? 'Laporan';

        return mb_substr(trim($nama), 0, 31) ?: 'Laporan';
    }
}
