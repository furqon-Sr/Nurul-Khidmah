@php
    \Carbon\Carbon::setLocale('id');
    $kopSuratUrl = isset($contents['kop_surat']) && $contents['kop_surat']?->image_path
        ? asset('storage/' . $contents['kop_surat']->image_path)
        : asset('images/kop surat ppnk.png');
    // Bersihkan spasi menjadi %20 agar terbaca dengan baik di browser saat cetak
    $kopSuratUrl = str_replace(' ', '%20', $kopSuratUrl);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti_Pendaftaran_{{ $registration->registration_number }}</title>
    @if (empty($isPdf))
        @include('partials.site-favicon')
    @endif
    <style>
        /* Gaya dasar halaman A4 */
        @page {
            size: A4 portrait;
            margin: 0;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: 'Times New Roman', Times, serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Tampilan layar monitor (dengan bayangan kertas) */
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            min-height: 100dvh;
            padding-top: 64px;
            padding-bottom: 24px;
            box-sizing: border-box;
        }

        @media screen and (max-width: 840px) {
            body {
                align-items: flex-start;
                overflow-x: auto;
                padding: 66px 12px 24px;
            }
            .preview-navbar {
                padding: 6px 12px !important;
                gap: 8px;
            }
            .preview-title {
                font-size: 12px !important;
                min-width: 0;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
        }

        .print-page {
            background-color: #ffffff;
            width: 210mm;
            height: 297mm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Kontainer Utama Pengatur Margins Konten */
        .content-container {
            width: 210mm;
            height: 297mm;
            box-sizing: border-box;
            padding-top: 45mm; /* Mengosongkan area Kop atas pesantren (sekitar 4.5 cm) */
            padding-bottom: 35mm; /* Jarak aman dari footer alamat bawah (sekitar 3.5 cm) */
            padding-left: 24mm; /* Margin kiri surat resmi */
            padding-right: 20mm; /* Margin kanan surat resmi */
            position: relative;
            z-index: 10;
        }

        /* Gambar Kop Surat sebagai Background Absolute */
        .letterhead-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 1;
            object-fit: fill;
            pointer-events: none;
        }

        /* Navbar Menu Pratinjau (Hanya Layar Monitor) */
        .preview-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 55px;
            background-color: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            font-family: ui-sans-serif, system-ui, sans-serif;
        }

        .preview-title {
            font-weight: 700;
            color: #10b981;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background-color: #0d6b3a;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s;
        }

        .btn-print:hover {
            background-color: #09502b;
        }

        .btn-close {
            background-color: #334155;
            color: #cbd5e1;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-close:hover {
            background-color: #475569;
            color: #ffffff;
        }

        /* Aturan Cetak Printer */
        @media print {
            html, body {
                background-color: #ffffff;
            }
            body {
                padding-top: 0;
            }
            .print-page {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navbar Pratinjau (Tersembunyi saat dicetak) -->
    <div class="preview-navbar no-print">
        <div class="preview-title">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Pratinjau Lembar Bukti Pendaftaran
            <span style="color: #94a3b8; font-size: 12px; font-weight: normal;">({{ $registration->registration_number }})</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button onclick="window.print()" class="btn-print">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn-close">Tutup</button>
        </div>
    </div>

    <!-- Halaman Utama Surat A4 -->
    <div class="print-page">
        <!-- Gambar Latar Belakang Kop Surat Pesantren -->
        <img src="{{ $kopSuratUrl }}" class="letterhead-bg" alt="Kop Surat Pesantren">

        <div class="content-container">
            <!-- Judul Bukti Pendaftaran -->
            <div style="text-align: center; margin-bottom: 22px;">
                <h2 style="font-size: 18px; font-weight: bold; text-decoration: underline; margin: 0; text-transform: uppercase; color: #000; letter-spacing: 0.5px;">BUKTI PENDAFTARAN SANTRI BARU</h2>
                <p style="font-size: 13px; margin: 5px 0 0 0; color: #000; font-weight: bold; letter-spacing: 0.5px;">Nomor Registrasi: {{ $registration->registration_number }}</p>
            </div>

            <!-- Pernyataan Pembuka -->
            <p style="margin: 0 0 16px 0; text-indent: 40px; text-align: justify; font-size: 13px; line-height: 1.5; color: #000;">
                Dengan ini Panitia Penerimaan Santri Baru Pondok Pesantren Nurul Khidmah Lamongan menyatakan bahwa calon santri yang tercantum di bawah ini telah melakukan pengisian formulir pendaftaran daring secara lengkap dan resmi melalui sistem PSB online:
            </p>

            <!-- Tabel Informasi Pendaftar -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; color: #000;">
                <tr>
                    <td style="width: 32%; padding: 4px 6px; font-weight: bold; vertical-align: top;">Nama Lengkap Santri</td>
                    <td style="width: 3%; padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="width: 65%; padding: 4px 6px; vertical-align: top; text-transform: uppercase; font-weight: bold;">{{ $registration->student_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">NISN</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; font-family: monospace; font-weight: bold;">{{ $registration->nisn ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Jenis Kelamin</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top;">{{ $registration->gender == 'L' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Tempat &amp; Tanggal Lahir</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top;">
                        {{ $registration->birth_place ? $registration->birth_place . ', ' : '' }}{{ \Carbon\Carbon::parse($registration->birth_date)->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Asal Sekolah (SD/MI)</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top;">{{ $registration->previous_school ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Status Asrama</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; font-weight: bold;">{{ $registration->boarding_status ?? 'Mukim' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Program / Kelas Tujuan</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; font-weight: bold;">{{ $registration->target_class }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Tahun Ajaran</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; font-weight: bold;">{{ $registration->academic_year }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Alamat Domisili</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; text-align: justify; line-height: 1.4;">{{ $registration->residential_address }}</td>
                </tr>
                
                <!-- Pembatas Sub-Section -->
                <tr>
                    <td colspan="3" style="padding: 12px 6px 4px 6px; font-weight: bold; border-bottom: 1.5px double #000; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                        Data Orang Tua / Wali & Kontak Pendaftar
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 6px 6px 4px 6px; font-weight: bold; vertical-align: top;">Nama Orang Tua (Ayah &amp; Ibu)</td>
                    <td style="padding: 6px 0 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 6px 6px 4px 6px; vertical-align: top;">{{ $registration->guardian_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">No. WhatsApp Wali</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; font-weight: bold;">{{ $registration->guardian_phone }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Email Pendaftar</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top;">{{ $registration->guardian_email ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Waktu Pendaftaran</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top;">{{ $registration->created_at->translatedFormat('d F Y - H:i') }} WIB</td>
                </tr>
                <tr>
                    <td style="padding: 4px 6px; font-weight: bold; vertical-align: top;">Status Verifikasi</td>
                    <td style="padding: 4px 0; vertical-align: top; text-align: center;">:</td>
                    <td style="padding: 4px 6px; vertical-align: top; font-weight: bold;">
                        @if($registration->status == 'accepted')
                            <span style="color: #0d6b3a;">DITERIMA (APPROVED)</span>
                        @elseif($registration->status == 'rejected')
                            <span style="color: #b91c1c;">DITOLAK (REJECTED)</span>
                        @else
                            <span style="color: #d97706;">MENUNGGU VERIFIKASI (PENDING)</span>
                        @endif
                    </td>
                </tr>
            </table>

            @if($registration->status == 'accepted' && $registration->santri)
            <!-- Kotak Informasi Akun Portal Wali Santri (Daftar Ulang) -->
            <div style="border: 1.5px solid #0d6b3a; background-color: #f0fdf4; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 12px; color: #000;">
                <div style="font-weight: bold; color: #065f46; text-transform: uppercase; margin-bottom: 4px; font-size: 11.5px;">
                    Informasi NIS &amp; Akun Login Portal Pemantauan Wali Santri
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <tr>
                        <td style="width: 28%; padding: 2px 0; font-weight: bold;">Nomor Induk Santri (NIS)</td>
                        <td style="width: 3%;">:</td>
                        <td style="width: 25%; font-weight: bold;">{{ $registration->santri->nis }}</td>
                        <td style="width: 18%; padding: 2px 0; font-weight: bold;">Kata Sandi</td>
                        <td style="width: 3%;">:</td>
                        <td style="width: 23%; font-size: 11px;">Diberikan oleh Panitia PSB / Staf TU</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0; font-weight: bold;">Portal Wali Santri</td>
                        <td>:</td>
                        <td colspan="4" style="font-family: monospace;">{{ url('/portal-wali') }} &nbsp;(Login cukup pakai NIS <strong>{{ $registration->santri->nis }}</strong>)</td>
                    </tr>
                </table>
            </div>
            @endif

            <!-- Pernyataan Penutup -->
            <p style="margin: 0 0 18px 0; text-indent: 40px; text-align: justify; font-size: 12.5px; line-height: 1.45; color: #000;">
                Demikian bukti pendaftaran ini diterbitkan secara sah oleh sistem untuk dipergunakan sebagai dokumen kelengkapan administrasi. Calon santri wajib membawa cetakan lembar bukti ini beserta berkas persyaratan penunjang lainnya saat melakukan pendaftaran ulang di Pondok Pesantren Nurul Khidmah Lamongan.
            </p>

            <!-- Blok Penandatangan & Pas Foto -->
            <div style="width: 100%; display: flex; justify-content: space-between; align-items: flex-start;">
                
                <!-- Sisi Kiri: Pas Foto Santri -->
                <div style="width: 40%; padding-top: 10px;">
                    @if($registration->student_photo)
                        <div style="width: 3cm; height: 4cm; border: 1px solid #000; padding: 2px; box-sizing: border-box; background-color: #fff; box-shadow: 2px 2px 5px rgba(0,0,0,0.1);">
                            <img src="{{ Storage::url($registration->student_photo) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Pas Foto">
                        </div>
                    @else
                        <div style="width: 3cm; height: 4cm; border: 1.5px dashed #777777; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #fafafa; font-size: 10px; color: #555555; text-align: center; padding: 4px; box-sizing: border-box; font-family: sans-serif;">
                            <span style="font-weight: bold;">PAS FOTO</span>
                            <span style="font-size: 9px; margin-top: 2px;">3 x 4 cm</span>
                        </div>
                    @endif
                </div>

                <!-- Sisi Kanan: Tanda Tangan Panitia -->
                <div style="width: 50%; text-align: right;">
                    <p style="margin: 0 0 4px 0; font-size: 13px;">Lamongan, {{ \Carbon\Carbon::parse($registration->created_at)->translatedFormat('d F Y') }}</p>
                    <p style="margin: 0 0 65px 0; font-weight: bold; font-size: 13px;">Panitia Penerimaan Santri Baru,</p>
                    
                    <p style="margin: 0; font-weight: bold; text-decoration: underline; font-size: 13px; text-transform: uppercase;">PANITIA PSB NURUL KHIDMAH</p>
                    <p style="margin: 2px 0 0 0; font-size: 11px; color: #333333; font-family: monospace;">RefID: PSB-{{ $registration->created_at->format('Ymd') }}-{{ sprintf('%04d', $registration->id) }}</p>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Pemicu Cetak Otomatis -->
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 600); // Penundaan singkat agar aset gambar termuat
        });
    </script>
</body>
</html>
