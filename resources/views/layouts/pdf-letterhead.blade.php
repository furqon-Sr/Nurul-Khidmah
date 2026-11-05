@php
    \Carbon\Carbon::setLocale('id');
    $infoCollection = $siteInfo ?? $contents ?? collect();
    $kopRecord = is_array($infoCollection) ? ($infoCollection['kop_surat'] ?? null) : $infoCollection->get('kop_surat');
    $kopImagePath = is_object($kopRecord) ? ($kopRecord->image_path ?? null) : ($kopRecord['image_path'] ?? null);

    $isPdfDoc = !empty($isPdf);
    $pageOrient = $pageOrientation ?? 'portrait';

    $diskPath = $kopImagePath ? storage_path('app/public/' . $kopImagePath) : public_path('images/kop surat ppnk.png');
    $kopSuratBase64 = null;
    if (file_exists($diskPath)) {
        $ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        $kopSuratBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($diskPath));
    }

    $kopSuratUrl = $kopImagePath
        ? asset('storage/' . $kopImagePath)
        : asset('images/kop surat ppnk.png');
    $kopSuratUrl = str_replace(' ', '%20', $kopSuratUrl);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dokumen Resmi SIAKAD - Ponpes Nurul Khidmah')</title>
    <style>
        @page {
            size: A4 {{ $pageOrient }};
            margin: {{ $isPdfDoc ? '8mm 12mm 12mm 12mm' : '0' }};
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.35;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @if($isPdfDoc)
        body {
            background-color: #ffffff;
            padding: 0;
            display: block;
        }
        .preview-navbar {
            display: none !important;
        }
        .screen-paper {
            width: 100% !important;
            box-shadow: none !important;
            min-height: auto !important;
            position: static !important;
            overflow: visible !important;
        }
        .letterhead-bg {
            display: none !important;
        }
        table.page-frame {
            width: 100% !important;
        }
        table.page-frame > thead > tr > td.header-spacer {
            height: 0 !important;
            padding: 0 !important;
        }
        table.page-frame > tfoot > tr > td.footer-spacer {
            height: 0 !important;
            padding: 0 !important;
        }
        .pdf-kop-header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #111827;
            padding-bottom: 6px;
        }
        .pdf-kop-header img {
            max-width: 100%;
            max-height: 85px;
            height: auto;
        }
        @else
        body {
            background-color: #e5e7eb;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            min-height: 100dvh;
            padding-top: 64px;
            padding-bottom: 32px;
        }

        .pdf-kop-header {
            display: none;
        }

        .preview-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            min-height: 54px;
            background-color: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .preview-title {
            font-weight: 700;
            color: #34d399;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            overflow: hidden;
        }

        .preview-title span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-print {
            background-color: #059669;
            color: #ffffff;
            border: none;
            min-height: 40px;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-print:hover {
            background-color: #047857;
        }

        .btn-download-pdf {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            min-height: 40px;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-download-pdf:hover {
            background-color: #1d4ed8;
        }

        .btn-close {
            background-color: #334155;
            color: #cbd5e1;
            border: none;
            min-height: 40px;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-close:hover {
            background-color: #475569;
            color: #ffffff;
        }

        @media screen and (max-width: 840px) {
            body {
                align-items: flex-start;
                overflow-x: auto;
                padding: 66px 12px 24px;
            }
            .preview-navbar {
                padding: 6px 12px;
            }
            .preview-title {
                font-size: 12px;
            }
            .btn-print,
            .btn-download-pdf,
            .btn-close {
                font-size: 12px;
                padding: 8px 10px;
            }
        }

        .screen-paper {
            background-color: #ffffff;
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
            position: relative;
            overflow: hidden;
        }

        .letterhead-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
            object-fit: fill;
            pointer-events: none;
        }

        table.page-frame {
            width: 210mm;
            border-collapse: collapse;
            position: relative;
            z-index: 2;
        }

        table.page-frame > thead > tr > td.header-spacer {
            height: 36mm;
            border: none;
            padding: 0;
        }

        table.page-frame > tfoot > tr > td.footer-spacer {
            height: 25mm;
            border: none;
            padding: 0;
        }
        @endif

        table.page-frame > tbody > tr > td.body-cell {
            padding: 0 10mm;
            vertical-align: top;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title h3 {
            margin: 0 0 3px 0;
            font-size: 15px;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .doc-title p {
            margin: 0;
            font-size: 11.5px;
            color: #374151;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .table-data th, .table-data td {
            border: 1px solid #111827;
            padding: 4.5px 6px;
            font-size: 11px;
        }

        .table-data th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 10px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .signature-section {
            width: 100%;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 11.5px;
        }

        .signature-table td {
            vertical-align: top;
            padding: 4px 6px;
        }

        .signature-space {
            height: 52px;
        }

        @media print {
            html, body {
                background-color: #ffffff;
                padding: 0;
                display: block;
            }
            .no-print {
                display: none !important;
            }
            .screen-paper {
                box-shadow: none;
                width: 210mm;
                min-height: auto;
                overflow: visible;
            }
            .letterhead-bg {
                position: fixed;
                top: 0;
                left: 0;
                width: 210mm;
                height: 297mm;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(! $isPdfDoc)
    <div class="preview-navbar no-print">
        <div class="preview-title">
            <span>@yield('toolbar_title', 'Pratinjau Dokumen Resmi SIAKAD')</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ request()->fullUrlWithQuery(['download' => 'pdf']) }}" class="btn-download-pdf">
                Download PDF
            </a>
            <button onclick="window.print()" class="btn-print">Cetak</button>
            <button onclick="window.close()" class="btn-close">Tutup</button>
        </div>
    </div>
    @endif

    <div class="screen-paper">
        @if($isPdfDoc)
            <div class="pdf-kop-header">
                @if($kopSuratBase64)
                    <img src="{{ $kopSuratBase64 }}" alt="Kop Surat">
                @else
                    <div style="font-size: 15px; font-weight: bold; text-transform: uppercase;">YAYASAN AL-MACHMUDI BANTANI</div>
                    <div style="font-size: 17px; font-weight: bold; text-transform: uppercase;">PONDOK PESANTREN NURUL KHIDMAH</div>
                    <div style="font-size: 10.5px;">Dusun Wringinanom, Desa Lopang, Kec. Kembangbahu, Kab. Lamongan, Jawa Timur</div>
                @endif
            </div>
        @else
            <img src="{{ $kopSuratUrl }}" class="letterhead-bg" alt="Kop Surat Yayasan Al-Machmudi Bantani - Ponpes Nurul Khidmah">
        @endif

        <table class="page-frame">
            <thead>
                <tr><td class="header-spacer"></td></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="body-cell">
                        @yield('content')
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr><td class="footer-spacer"></td></tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
