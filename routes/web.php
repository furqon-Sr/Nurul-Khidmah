<?php

use App\Http\Controllers\Admin\AcademicReportController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ClassPromotionController;
use App\Http\Controllers\Admin\ClassRoomController;
use App\Http\Controllers\Admin\EducationFeeController;
use App\Http\Controllers\Admin\ImportDataController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\SantriManagementController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Auth\WaliPortalAuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Pimpinan\PimpinanDashboardController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\Santri\SantriPortalController;
use App\Http\Controllers\SantriRegistrationController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\GradeController;
use App\Models\AdminActivityLog;
use App\Models\EducationFee;
use App\Models\EducationProgram;
use App\Models\Facility;
use App\Models\News;
use App\Models\PageContent;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tentang-kami', function () {
    return view('about');
})->name('about');

Route::get('/pendidikan', function () {
    $fees = EducationFee::orderBy('name')->get();
    $programs = EducationProgram::active()->orderBy('order')->orderBy('name')->get();

    return view('education', compact('fees', 'programs'));
})->name('education');

Route::get('/fasilitas', function () {
    $facilities = Facility::all();

    return view('facilities', compact('facilities'));
})->name('facilities');

Route::get('/berita', function () {
    $allNews = News::orderBy('published_at', 'desc')->latest()->get();
    $mainNews = $allNews->first();
    $otherNews = $allNews->skip(1);

    return view('news.index', compact('mainNews', 'otherNews'));
})->name('news.index');

Route::get('/berita/{slug}', function ($slug) {
    $news = News::where('slug', $slug)->firstOrFail();

    return view('news.show', compact('news'));
})->name('news.show');

Route::get('/pendaftaran/{program?}', [PublicRegistrationController::class, 'create'])->name('register.create');
Route::get('/pendaftaran', [PublicRegistrationController::class, 'create'])->name('registration');
Route::post('/pendaftaran', [PublicRegistrationController::class, 'store'])->name('registration.store');

Route::get('/kontak', [ContactController::class, 'index'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

Route::get('/biaya-pendidikan/download', function () {
    $pdfContent = PageContent::where('section_key', 'education_fee_pdf')->first();
    if ($pdfContent && $pdfContent->image_path && Storage::disk('public')->exists($pdfContent->image_path)) {
        $ext = pathinfo($pdfContent->image_path, PATHINFO_EXTENSION) ?: 'pdf';
        $filename = 'Biaya_Pendidikan_Nurul_Khidmah.'.$ext;

        return Storage::disk('public')->download($pdfContent->image_path, $filename);
    }

    return abort(404, 'Dokumen biaya pendidikan belum diunggah.');
})->name('education.download-fee');

// Pintu 1 (Publik & Ramah Wali Santri): Login terisolasi khusus Role Wali Santri via NIS atau Email
Route::get('/portal-wali', [WaliPortalAuthController::class, 'showLoginForm'])->name('wali.login');
Route::post('/portal-wali', [WaliPortalAuthController::class, 'login'])->name('wali.login.store');

// Pintu 2 (Internal Staf Yayasan, Pengurus Akademik, Dewan Guru, & Pimpinan)
Route::get('/portal-staf', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('staff.login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->post('/logout', function (Request $request) {
    if (auth()->check()) {
        AdminActivityLog::log('logout', 'Logout dari sistem ('.auth()->user()->roleLabel().')');
    }

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// 1. Modul Konten Website Yayasan (Super Admin & Staf TU / Pengurus Akademik)
Route::middleware(['auth', 'role:super_admin,pengurus_akademik,admin'])->group(function () {
    Route::resource('admin/news', NewsController::class)->names('admin.news');
    Route::resource('admin/facilities', FacilityController::class)->names('admin.facilities');

    Route::get('admin/settings', [SiteSettingsController::class, 'index'])->name('settings.index');
    Route::post('admin/settings', [SiteSettingsController::class, 'update'])->name('settings.update');

    Route::get('admin/flush-cache', function () {
        Cache::forget('site_global_data');

        return redirect()->back()->with('success', 'Cache berhasil dibersihkan. Data terbaru akan dimuat dari database.');
    })->name('admin.flush-cache');
});

// 2. Modul Informasi Program & Biaya Pendidikan (Super Admin & Staf TU / Pengurus Akademik)
Route::middleware(['auth', 'role:super_admin,pengurus_akademik,admin'])->group(function () {
    Route::resource('admin/fees', EducationFeeController::class)->names('admin.fees');
    Route::resource('admin/programs', ProgramController::class)->names('admin.programs');
    Route::post('admin/programs/{program}/toggle', [ProgramController::class, 'toggle'])->name('admin.programs.toggle');
});

// 3. Modul Master Data SIAKAD, PPDB, & Kenaikan Kelas (Khusus Super Admin & Pengurus Akademik, plus Kepala Sekolah view-only)
Route::middleware(['auth', 'role:super_admin,pengurus_akademik,kepala_sekolah'])->group(function () {
    Route::resource('admin/classes', ClassRoomController::class)->names('admin.classes');
    Route::resource('admin/santri', SantriManagementController::class)->names('admin.santri');
    Route::resource('admin/schedules', ScheduleController::class)->names('admin.schedules');
});

Route::middleware(['auth', 'role:super_admin,pengurus_akademik'])->group(function () {
    Route::resource('admin/academic-years', AcademicYearController::class)->names('admin.academic-years');
    Route::post('admin/academic-years/{academicYear}/toggle', [AcademicYearController::class, 'toggleActive'])->name('admin.academic-years.toggle');

    Route::resource('admin/subjects', SubjectController::class)->names('admin.subjects');
    Route::resource('admin/teachers', TeacherController::class)->names('admin.teachers');

    // Kenaikan Kelas & Kelulusan Kolektif Lintas Tahun Ajaran
    Route::get('admin/promotions', [ClassPromotionController::class, 'index'])->name('admin.promotions.index');
    Route::post('admin/promotions', [ClassPromotionController::class, 'store'])->name('admin.promotions.store');

    // Pendaftar PPDB
    Route::get('admin/registrations', [SantriRegistrationController::class, 'index'])->name('admin.registrations.index');
    Route::post('admin/registrations/bulk-status', [SantriRegistrationController::class, 'bulkStatus'])->name('admin.registrations.bulk-status');
    Route::get('admin/registrations/{registration}/edit', [SantriRegistrationController::class, 'edit'])->name('admin.registrations.edit');
    Route::get('admin/registrations/{registration}', [SantriRegistrationController::class, 'show'])->name('admin.registrations.show');
    Route::put('admin/registrations/{registration}', [SantriRegistrationController::class, 'update'])->name('admin.registrations.update');
    Route::put('admin/registrations/{registration}/status', [SantriRegistrationController::class, 'updateStatus'])->name('admin.registrations.update-status');
    Route::get('admin/registrations/{registration}/pdf', [SantriRegistrationController::class, 'downloadPdf'])->name('admin.registrations.pdf');
    Route::delete('admin/registrations/{registration}', [SantriRegistrationController::class, 'destroy'])->name('admin.registrations.destroy');

    // Data Migration & Background Queue (Import Santri & Pengajar)
    Route::get('admin/imports', [ImportDataController::class, 'index'])->name('admin.imports.index');
    Route::post('admin/imports/santri', [ImportDataController::class, 'uploadSantri'])->name('admin.imports.santri');
    Route::post('admin/imports/pengajar', [ImportDataController::class, 'uploadPengajar'])->name('admin.imports.pengajar');
    Route::get('admin/imports/template/{type}', [ImportDataController::class, 'downloadTemplate'])->name('admin.imports.template');
    Route::get('admin/imports/status', [ImportDataController::class, 'status'])->name('admin.imports.status');
    Route::get('admin/imports/{batch}', [ImportDataController::class, 'show'])->whereNumber('batch')->name('admin.imports.show');
    Route::post('admin/imports/{batch}/confirm', [ImportDataController::class, 'confirm'])->whereNumber('batch')->name('admin.imports.confirm');
    Route::post('admin/imports/{batch}/cancel', [ImportDataController::class, 'cancel'])->whereNumber('batch')->name('admin.imports.cancel');
    Route::get('admin/imports/{batch}/errors', [ImportDataController::class, 'errors'])->whereNumber('batch')->name('admin.imports.errors');
});

// 4. Modul Akademik Guru & Pemantauan Kepatuhan (Super Admin, Pengurus Akademik, Kepala Sekolah, & Tenaga Pengajar)
Route::middleware(['auth', 'role:super_admin,pengurus_akademik,kepala_sekolah,tenaga_pengajar'])->group(function () {
    Route::get('teacher/attendance', [AttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::get('teacher/attendance/create', [AttendanceController::class, 'create'])->name('teacher.attendance.create');
    Route::post('teacher/attendance', [AttendanceController::class, 'store'])->name('teacher.attendance.store');

    Route::get('teacher/grades', [GradeController::class, 'index'])->name('teacher.grades.index');
    Route::get('teacher/grades/{schedule}/edit', [GradeController::class, 'edit'])->name('teacher.grades.edit');
    Route::put('teacher/grades/{schedule}', [GradeController::class, 'update'])->name('teacher.grades.update');
});

// 5. Portal Wali Santri (Super Admin, Pengurus Akademik & Kepala Sekolah Mode Pantau, & Wali Santri)
Route::middleware(['auth', 'role:super_admin,pengurus_akademik,kepala_sekolah,santri'])->group(function () {
    Route::get('santri/dashboard', [SantriPortalController::class, 'dashboard'])->name('santri.dashboard');
    Route::get('wali/dashboard', [SantriPortalController::class, 'dashboard'])->name('wali.dashboard');

    Route::get('santri/attendance', [SantriPortalController::class, 'attendance'])->name('santri.attendance');
    Route::get('wali/attendance', [SantriPortalController::class, 'attendance'])->name('wali.attendance');

    Route::get('santri/grades', [SantriPortalController::class, 'grades'])->name('santri.grades');
    Route::get('wali/grades', [SantriPortalController::class, 'grades'])->name('wali.grades');

    Route::get('santri/rapor-pdf', [SantriPortalController::class, 'downloadRaporPdf'])->name('santri.rapor-pdf');
    Route::get('wali/rapor-pdf', [SantriPortalController::class, 'downloadRaporPdf'])->name('wali.rapor-pdf');
    Route::get('santri/rapor/download', [SantriPortalController::class, 'downloadTruePdf'])->name('santri.rapor.download');
    Route::get('wali/rapor/download', [SantriPortalController::class, 'downloadTruePdf'])->name('wali.rapor.download');
    Route::match(['get', 'post'], 'wali/switch-santri/{santri}', function (Santri $santri) {
        if (auth()->user()->isSantri()) {
            if ($santri->user_id !== auth()->id()) {
                abort(403, 'Anda tidak memiliki hak akses data santri ini.');
            }
            session(['active_wali_santri_id' => $santri->id]);

            return redirect()->back(fallback: route('wali.dashboard'))->with('success', "Berhasil beralih ke data pemantauan ananda: {$santri->nama_lengkap}");
        }
        abort(403);
    })->name('wali.switch-santri');
});

// 6. Dashboard Eksekutif Pimpinan Pesantren (Khusus Super Admin & Pimpinan — FR-13)
Route::middleware(['auth', 'role:super_admin,pimpinan'])->group(function () {
    Route::get('pimpinan/dashboard', [PimpinanDashboardController::class, 'index'])->name('pimpinan.dashboard');
    Route::get('pimpinan/laporan-pdf', [PimpinanDashboardController::class, 'exportPdf'])->name('pimpinan.export-pdf');
});

// 7. Pusat Laporan Akademik, Rekap Leger & Validasi Rapor (Super Admin, Pengurus Akademik, Kepala Sekolah, & Tenaga Pengajar/Wali Kelas)
Route::middleware(['auth', 'role:super_admin,pengurus_akademik,kepala_sekolah,tenaga_pengajar'])->group(function () {
    Route::get('admin/reports', [AcademicReportController::class, 'index'])->name('admin.reports.index');
    Route::post('admin/reports/wali-notes/{kelas}', [AcademicReportController::class, 'updateWaliNotes'])->name('admin.reports.wali-notes');
    Route::get('admin/reports/attendance-pdf', [AcademicReportController::class, 'exportAttendancePdf'])->name('admin.reports.attendance-pdf');
    Route::get('admin/reports/attendance-pdf/download', [AcademicReportController::class, 'downloadAttendancePdf'])->name('admin.reports.attendance.download');
    Route::get('admin/reports/grades-pdf', [AcademicReportController::class, 'exportGradesPdf'])->name('admin.reports.grades-pdf');
    Route::get('admin/reports/grades-pdf/download', [AcademicReportController::class, 'downloadGradesPdf'])->name('admin.reports.grades.download');
    Route::get('admin/reports/santri-rapor-pdf/{santri}', [AcademicReportController::class, 'exportSantriRaporPdf'])->name('admin.reports.santri-rapor-pdf');
    Route::get('admin/reports/santri-rapor-pdf/{santri}/download', [AcademicReportController::class, 'downloadSantriRaporPdf'])->name('admin.reports.santri-rapor.download');
});

// Alur Persetujuan Rapor (Grade Locking & Immutability: Khusus Kepala Sekolah, Pimpinan & Super Admin)
Route::middleware(['auth', 'role:super_admin,kepala_sekolah,pimpinan'])->group(function () {
    Route::post('admin/reports/approve', [AcademicReportController::class, 'approveReport'])->name('admin.reports.approve');
    Route::post('admin/reports/approve-santri/{santri}', [AcademicReportController::class, 'approveSantri'])->name('admin.reports.approve-santri');
    Route::post('admin/reports/approve-class/{kelas}', [AcademicReportController::class, 'approveClass'])->name('admin.reports.approve-class');
});

// 8. Manajemen Sistem & Keamanan (Khusus Super Admin)
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::patch('admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('admin/users/{user}/avatar', [AdminUserController::class, 'updateAvatar'])->name('admin.users.update-avatar');
    Route::patch('admin/users/{user}/toggle-verification', [AdminUserController::class, 'toggleVerification'])->name('admin.users.toggle-verification');

    Route::get('admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
});

require __DIR__.'/settings.php';
