<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\SiteSettingsController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tentang-kami', function () {
    return view('about');
})->name('about');

Route::get('/pendidikan', function () {
    $fees     = \App\Models\EducationFee::orderBy('name')->get();
    $programs = \App\Models\EducationProgram::active()->orderBy('order')->orderBy('name')->get();
    return view('education', compact('fees', 'programs'));
})->name('education');

Route::get('/fasilitas', function () {
    $facilities = \App\Models\Facility::all();
    return view('facilities', compact('facilities'));
})->name('facilities');

Route::get('/berita', function () {
    $allNews = \App\Models\News::orderBy('published_at', 'desc')->latest()->get();
    $mainNews = $allNews->first();
    $otherNews = $allNews->skip(1);
    return view('news.index', compact('mainNews', 'otherNews'));
})->name('news.index');

Route::get('/berita/{slug}', function ($slug) {
    $news = \App\Models\News::where('slug', $slug)->firstOrFail();
    return view('news.show', compact('news'));
})->name('news.show');

Route::get('/pendaftaran/{program?}', [\App\Http\Controllers\PublicRegistrationController::class, 'create'])->name('register.create');
Route::get('/pendaftaran', [\App\Http\Controllers\PublicRegistrationController::class, 'create'])->name('registration');
Route::post('/pendaftaran', [\App\Http\Controllers\PublicRegistrationController::class, 'store'])->name('registration.store');

Route::get('/kontak', [\App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/kontak', [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

Route::get('/biaya-pendidikan/download', function () {
    $pdfContent = \App\Models\PageContent::where('section_key', 'education_fee_pdf')->first();
    if ($pdfContent && $pdfContent->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($pdfContent->image_path)) {
        $ext = pathinfo($pdfContent->image_path, PATHINFO_EXTENSION) ?: 'pdf';
        $filename = 'Biaya_Pendidikan_Nurul_Khidmah.' . $ext;
        return \Illuminate\Support\Facades\Storage::disk('public')->download($pdfContent->image_path, $filename);
    }
    return abort(404, 'Dokumen biaya pendidikan belum diunggah.');
})->name('education.download-fee');

use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->post('/logout', function (Request $request) {
    // Catat logout sebelum session dihancurkan
    if (auth()->check()) {
        \App\Models\AdminActivityLog::log('logout', 'Logout dari panel admin');
    }

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::middleware(['auth', 'role:super_admin,admin,pengurus_akademik'])->group(function () {
    Route::resource('admin/news', NewsController::class)->names('admin.news');
    Route::resource('admin/facilities', FacilityController::class)->names('admin.facilities');
    Route::resource('admin/fees', \App\Http\Controllers\Admin\EducationFeeController::class)->names('admin.fees');
    Route::resource('admin/programs', \App\Http\Controllers\Admin\ProgramController::class)->names('admin.programs');
    Route::post('admin/programs/{program}/toggle', [\App\Http\Controllers\Admin\ProgramController::class, 'toggle'])->name('admin.programs.toggle');

    // Data Master SIAKAD Routes
    Route::resource('admin/academic-years', \App\Http\Controllers\Admin\AcademicYearController::class)->names('admin.academic-years');
    Route::post('admin/academic-years/{academicYear}/toggle', [\App\Http\Controllers\Admin\AcademicYearController::class, 'toggleActive'])->name('admin.academic-years.toggle');

    Route::resource('admin/subjects', \App\Http\Controllers\Admin\SubjectController::class)->names('admin.subjects');
    Route::resource('admin/teachers', \App\Http\Controllers\Admin\TeacherController::class)->names('admin.teachers');
    Route::resource('admin/classes', \App\Http\Controllers\Admin\ClassRoomController::class)->names('admin.classes');
    Route::resource('admin/santri', \App\Http\Controllers\Admin\SantriManagementController::class)->names('admin.santri');
    Route::resource('admin/schedules', \App\Http\Controllers\Admin\ScheduleController::class)->names('admin.schedules');

    // Pendaftar Routes
    Route::get('admin/registrations', [\App\Http\Controllers\SantriRegistrationController::class, 'index'])->name('admin.registrations.index');
    Route::post('admin/registrations/bulk-status', [\App\Http\Controllers\SantriRegistrationController::class, 'bulkStatus'])->name('admin.registrations.bulk-status');
    Route::get('admin/registrations/{registration}', [\App\Http\Controllers\SantriRegistrationController::class, 'show'])->name('admin.registrations.show');
    Route::put('admin/registrations/{registration}/status', [\App\Http\Controllers\SantriRegistrationController::class, 'updateStatus'])->name('admin.registrations.update-status');
    Route::get('admin/registrations/{registration}/pdf', [\App\Http\Controllers\SantriRegistrationController::class, 'downloadPdf'])->name('admin.registrations.pdf');
    Route::delete('admin/registrations/{registration}', [\App\Http\Controllers\SantriRegistrationController::class, 'destroy'])->name('admin.registrations.destroy');

    // Manual cache flush route (for debugging/forced refresh)
    Route::get('admin/flush-cache', function () {
        \Illuminate\Support\Facades\Cache::forget('site_global_data');
        return redirect()->back()->with('success', 'Cache berhasil dibersihkan. Data terbaru akan dimuat dari database.');
    })->name('admin.flush-cache');
});

Route::middleware(['auth', 'role:super_admin,admin,pengurus_akademik,tenaga_pengajar'])->group(function () {
    // Presensi / Absensi Routes
    Route::get('teacher/attendance', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::get('teacher/attendance/create', [\App\Http\Controllers\Teacher\AttendanceController::class, 'create'])->name('teacher.attendance.create');
    Route::post('teacher/attendance', [\App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('teacher.attendance.store');

    // Input Nilai Routes
    Route::get('teacher/grades', [\App\Http\Controllers\Teacher\GradeController::class, 'index'])->name('teacher.grades.index');
    Route::get('teacher/grades/{schedule}/edit', [\App\Http\Controllers\Teacher\GradeController::class, 'edit'])->name('teacher.grades.edit');
    Route::put('teacher/grades/{schedule}', [\App\Http\Controllers\Teacher\GradeController::class, 'update'])->name('teacher.grades.update');
});

// Santri Portal Routes
Route::middleware(['auth', 'role:super_admin,admin,pengurus_akademik,santri'])->group(function () {
    Route::get('santri/dashboard', [\App\Http\Controllers\Santri\SantriPortalController::class, 'dashboard'])->name('santri.dashboard');
    Route::get('santri/attendance', [\App\Http\Controllers\Santri\SantriPortalController::class, 'attendance'])->name('santri.attendance');
    Route::get('santri/grades', [\App\Http\Controllers\Santri\SantriPortalController::class, 'grades'])->name('santri.grades');
    Route::get('santri/rapor-pdf', [\App\Http\Controllers\Santri\SantriPortalController::class, 'downloadRaporPdf'])->name('santri.rapor-pdf');
});

// Pimpinan Dashboard Routes
Route::middleware(['auth', 'role:super_admin,admin,pimpinan'])->group(function () {
    Route::get('pimpinan/dashboard', [\App\Http\Controllers\Pimpinan\PimpinanDashboardController::class, 'index'])->name('pimpinan.dashboard');
});

// Academic Reports & PDF Export Routes
Route::middleware(['auth', 'role:super_admin,admin,pengurus_akademik,pimpinan,tenaga_pengajar'])->group(function () {
    Route::get('admin/reports', [\App\Http\Controllers\Admin\AcademicReportController::class, 'index'])->name('admin.reports.index');
    Route::get('admin/reports/attendance-pdf', [\App\Http\Controllers\Admin\AcademicReportController::class, 'exportAttendancePdf'])->name('admin.reports.attendance-pdf');
    Route::get('admin/reports/grades-pdf', [\App\Http\Controllers\Admin\AcademicReportController::class, 'exportGradesPdf'])->name('admin.reports.grades-pdf');
    Route::get('admin/reports/santri-rapor-pdf/{santri}', [\App\Http\Controllers\Admin\AcademicReportController::class, 'exportSantriRaporPdf'])->name('admin.reports.santri-rapor-pdf');
});

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('admin/settings', [SiteSettingsController::class, 'index'])->name('settings.index');
    Route::post('admin/settings', [SiteSettingsController::class, 'update'])->name('settings.update');

    Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::patch('admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('admin/users/{user}/avatar', [AdminUserController::class, 'updateAvatar'])->name('admin.users.update-avatar');
    Route::patch('admin/users/{user}/toggle-verification', [AdminUserController::class, 'toggleVerification'])->name('admin.users.toggle-verification');

    // Log Aktivitas Admin (hanya bisa dilihat Super Admin)
    Route::get('admin/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
});

require __DIR__ . '/settings.php';


