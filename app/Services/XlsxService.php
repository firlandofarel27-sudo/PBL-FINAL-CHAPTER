<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Penulis berkas Excel (.xlsx) tanpa pustaka eksternal.
 *
 * Ekstensi `zip` tidak tersedia di server ini, jadi arsip ZIP disusun
 * manual memakai `gzdeflate()` (DEFLATE mentah) dan `crc32()`. Berkas yang
 * dihasilkan adalah OOXML SpreadsheetML asli sehingga bisa dibuka Excel,
 * LibreOffice, dan Google Sheets tanpa peringatan format.
 *
 * Isi sel dikirim sebagai `inlineStr`, bukan rumus, sehingga teks yang
 * diawali `=` atau `+` tetap diperlakukan sebagai teks biasa.
 */
final class XlsxService
{
    /**
     * Mengirim berkas .xlsx sebagai unduhan lalu menghentikan eksekusi.
     *
     * @param  array<int, string>                 $header  Judul kolom.
     * @param  array<int, array<int, string>>     $rows    Baris data.
     * @param  array<int, int>                    $widths  Lebar kolom dalam karakter.
     */
    public static function download(
        string $filename,
        string $sheetName,
        array $header,
        array $rows,
        array $widths = []
    ): never {
        $arsip = self::zip([
            '[Content_Types].xml'         => self::contentTypes(),
            '_rels/.rels'                 => self::rootRels(),
            'xl/workbook.xml'             => self::workbook($sheetName),
            'xl/_rels/workbook.xml.rels'  => self::workbookRels(),
            'xl/styles.xml'               => self::styles(),
            'xl/worksheets/sheet1.xml'    => self::sheet($header, $rows, $widths),
        ]);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($arsip));
        header('Cache-Control: max-age=0, must-revalidate');
        header('Pragma: public');

        echo $arsip;

        exit;
    }

    /**
     * Menyusun arsip ZIP (metode DEFLATE) dari pasangan nama → isi.
     *
     * @param  array<string, string>  $files
     */
    private static function zip(array $files): string
    {
        $lokal   = '';
        $pusat   = '';
        $offset  = 0;
        $waktu   = self::dosTime();

        foreach ($files as $nama => $isi) {
            $crc      = crc32($isi) & 0xFFFFFFFF;
            $padat    = gzdeflate($isi, 6);
            $metode   = 8;
            $muatan   = $padat;

            /* Bila hasil kompresi tidak lebih kecil, simpan apa adanya. */
            if ($padat === false || strlen($padat) >= strlen($isi)) {
                $metode = 0;
                $muatan = $isi;
            }

            $asli = strlen($isi);
            $padatPanjang = strlen($muatan);
            $namaPanjang  = strlen($nama);

            $lokal .= pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                $metode,
                $waktu,
                $waktu,
                $crc,
                $padatPanjang,
                $asli,
                $namaPanjang,
                0
            ) . $nama . $muatan;

            $pusat .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                $metode,
                $waktu,
                $waktu,
                $crc,
                $padatPanjang,
                $asli,
                $namaPanjang,
                0,
                0,
                0,
                0,
                0,
                $offset
            ) . $nama;

            $offset += 30 + $namaPanjang + $padatPanjang;
        }

        $jumlah = count($files);

        $akhir = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $jumlah,
            $jumlah,
            strlen($pusat),
            $offset,
            0
        );

        return $lokal . $pusat . $akhir;
    }

    /**
     * Waktu berkas dalam format MS-DOS (tanggal dan jam digabung).
     */
    private static function dosTime(): int
    {
        $sekarang = getdate();

        $jam  = ($sekarang['hours'] << 11) | ($sekarang['minutes'] << 5) | intdiv($sekarang['seconds'], 2);
        $hari = (($sekarang['year'] - 1980) << 9) | ($sekarang['mon'] << 5) | $sekarang['mday'];

        return ($hari << 16) | $jam;
    }

    /**
     * Lembar kerja: baris judul dibekukan, ada filter otomatis.
     *
     * @param  array<int, string>              $header
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, int>                 $widths
     */
    private static function sheet(array $header, array $rows, array $widths): string
    {
        $kolomTerakhir = self::kolom(max(1, count($header)));
        $jumlahBaris   = count($rows) + 1;

        $xml  = self::kepala();
        $xml .= '<dimension ref="A1:' . $kolomTerakhir . $jumlahBaris . '"/>';
        $xml .= '<sheetViews><sheetView tabSelected="1" workbookViewId="0">'
            . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/>';

        $xml .= '<cols>';

        foreach ($header as $index => $_) {
            $lebar = $widths[$index] ?? 18;

            $xml .= '<col min="' . ($index + 1) . '" max="' . ($index + 1) . '"'
                . ' width="' . $lebar . '" customWidth="1"/>';
        }

        $xml .= '</cols><sheetData>';

        $xml .= '<row r="1" ht="24" customHeight="1">';

        foreach ($header as $index => $judul) {
            $xml .= self::sel(self::kolom($index + 1) . '1', $judul, 1);
        }

        $xml .= '</row>';

        foreach ($rows as $barisIndex => $baris) {
            $nomor = $barisIndex + 2;
            $xml  .= '<row r="' . $nomor . '">';

            foreach (array_values($baris) as $index => $nilai) {
                $xml .= self::sel(self::kolom($index + 1) . $nomor, (string) $nilai, 0);
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData>';
        $xml .= '<autoFilter ref="A1:' . $kolomTerakhir . $jumlahBaris . '"/>';
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Satu sel bertipe teks (inlineStr), dengan gaya opsional.
     */
    private static function sel(string $referensi, string $nilai, int $gaya): string
    {
        $teks = htmlspecialchars($nilai, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
        $s    = $gaya > 0 ? ' s="' . $gaya . '"' : '';

        return '<c r="' . $referensi . '"' . $s . ' t="inlineStr"><is><t xml:space="preserve">'
            . $teks . '</t></is></c>';
    }

    /**
     * Nama kolom Excel untuk indeks 1-based (1 → A, 27 → AA).
     */
    private static function kolom(int $index): string
    {
        $nama = '';

        while ($index > 0) {
            $sisa  = ($index - 1) % 26;
            $nama  = chr(65 + $sisa) . $nama;
            $index = intdiv($index - 1, 26);
        }

        return $nama;
    }

    private static function kepala(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        $nama = htmlspecialchars(
            mb_substr($sheetName, 0, 31),
            ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE,
            'UTF-8'
        );

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $nama . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    /**
     * Gaya minimal: teks biasa, dan judul kolom (putih tebal di atas navy).
     */
    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF103E48"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1">'
            . '<alignment vertical="center"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';
    }
}
