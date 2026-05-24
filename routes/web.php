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
        return \Illuminate\Support\Facades\Storage::disk('public')->download($pdfContent->image_path, 'Biaya_Pendidikan_Nurul_Khidmah.pdf');
    }
    return abort(404, 'Dokumen biaya pendidikan belum diunggah.');
})->name('education.download-fee');

use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::resource('admin/news', NewsController::class)->names('admin.news');
    Route::resource('admin/facilities', FacilityController::class)->names('admin.facilities');
    Route::resource('admin/fees', \App\Http\Controllers\Admin\EducationFeeController::class)->names('admin.fees');
    Route::resource('admin/programs', \App\Http\Controllers\Admin\ProgramController::class)->names('admin.programs');
    Route::post('admin/programs/{program}/toggle', [\App\Http\Controllers\Admin\ProgramController::class, 'toggle'])->name('admin.programs.toggle');

    // Pendaftar Routes
    Route::get('admin/registrations', [\App\Http\Controllers\SantriRegistrationController::class, 'index'])->name('admin.registrations.index');
    Route::post('admin/registrations/bulk-status', [\App\Http\Controllers\SantriRegistrationController::class, 'bulkStatus'])->name('admin.registrations.bulk-status');
    Route::get('admin/registrations/{registration}', [\App\Http\Controllers\SantriRegistrationController::class, 'show'])->name('admin.registrations.show');
    Route::put('admin/registrations/{registration}/status', [\App\Http\Controllers\SantriRegistrationController::class, 'updateStatus'])->name('admin.registrations.update-status');
    Route::get('admin/registrations/{registration}/pdf', [\App\Http\Controllers\SantriRegistrationController::class, 'downloadPdf'])->name('admin.registrations.pdf');

    // Manual cache flush route (for debugging/forced refresh)
    Route::get('admin/flush-cache', function () {
        \Illuminate\Support\Facades\Cache::forget('site_global_data');
        return redirect()->back()->with('success', 'Cache berhasil dibersihkan. Data terbaru akan dimuat dari database.');
    })->name('admin.flush-cache');
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

    // One-time setup: create missing tables directly via Schema
    Route::get('admin/run-setup', function () {
        $results = [];

        if (! \Illuminate\Support\Facades\Schema::hasTable('education_programs')) {
            \Illuminate\Support\Facades\Schema::create('education_programs', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_registration_open')->default(false);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
            $results[] = '✅ Tabel education_programs berhasil dibuat.';
        } else {
            $results[] = '✅ Tabel education_programs sudah ada.';
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('education_fees')) {
            \Illuminate\Support\Facades\Schema::create('education_fees', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('level')->nullable();
                $table->unsignedBigInteger('amount')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
            $results[] = '✅ Tabel education_fees berhasil dibuat.';
        } else {
            $results[] = '✅ Tabel education_fees sudah ada.';
        }

        \Illuminate\Support\Facades\Cache::forget('site_global_data');

        return response('<html><body style="font-family:sans-serif;padding:20px;background:#f0fdf4">
            <h2 style="color:#166534;">🛠️ Setup Selesai</h2>
            <ul>' . implode('', array_map(fn($r) => "<li style='margin:8px 0;color:#15803d;font-size:15px;'>$r</li>", $results)) . '</ul>
            <br><a href="/admin/programs" style="display:inline-block;padding:10px 20px;background:#16a34a;color:white;border-radius:8px;text-decoration:none;font-weight:bold;">→ Kembali ke Program Pendidikan</a>
        </body></html>');
    })->name('admin.run-setup');
});

require __DIR__ . '/settings.php';
