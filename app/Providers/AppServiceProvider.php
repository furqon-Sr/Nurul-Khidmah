<?php

namespace App\Providers;

use App\Listeners\LogFailedLogin;
use App\Listeners\LogSuccessfulLogin;
use App\Models\Absensi;
use App\Models\EducationProgram;
use App\Models\Nilai;
use App\Models\PageContent;
use App\Policies\AbsensiPolicy;
use App\Policies\NilaiPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        $this->configureDefaults();

        // Registrasi Policies Keamanan Akademik (Grade & Attendance Locking)
        Gate::policy(Nilai::class, NilaiPolicy::class);
        Gate::policy(Absensi::class, AbsensiPolicy::class);

        // Daftarkan event listener untuk mencatat login berhasil & gagal (Audit Keamanan)
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Failed::class, LogFailedLogin::class);

        // Global View Composer - runs on every request, data cached for performance
        View::composer('*', function ($view) {
            // Cache only a plain array (not stdClass) to avoid database cache unserialize errors
            $cachedArray = Cache::remember('site_global_data', 3600, function () {
                $result = [
                    'logo_path' => null,
                    'favicon_path' => null,
                    'whatsapp' => null,
                    'footer_text' => null,
                    'email' => null,
                    'address' => null,
                    'contents' => [],
                    'active_programs' => [],
                ];

                try {
                    // Schema::hasTable dihapus — tidak perlu import Schema,
                    // try-catch sudah menangani jika DB belum siap.
                    $allContents = PageContent::all()->keyBy('section_key');

                    $result['logo_path'] = $allContents->get('logo')?->image_path;
                    $result['favicon_path'] = $allContents->get('favicon')?->image_path;
                    $result['whatsapp'] = $allContents->get('contact_phone')?->content_text;
                    $result['footer_text'] = $allContents->get('footer_text')?->content_text;
                    $result['email'] = $allContents->get('contact_email')?->content_text;
                    $result['address'] = $allContents->get('contact_address')?->content_text;
                    $result['staff_login_image_path'] = $allContents->get('staff_login_image')?->image_path;
                    $result['wali_login_image_path'] = $allContents->get('wali_login_image')?->image_path;

                    // Store contents as plain array of arrays for cache safety
                    $result['contents'] = $allContents->map(fn ($c) => [
                        'id' => $c->id,
                        'section_key' => $c->section_key,
                        'content_text' => $c->content_text,
                        'image_path' => $c->image_path,
                    ])->toArray();

                    $result['active_programs'] = EducationProgram::active()
                        ->orderBy('order')
                        ->orderBy('name')
                        ->get(['id', 'name', 'slug', 'description', 'image_path', 'is_registration_open', 'is_coming_soon', 'order'])
                        ->map(fn (EducationProgram $p) => $p->toArray())
                        ->all();

                } catch (\Exception $e) {
                    // DB not ready yet — return empty result
                }

                return $result;
            });

            // Rebuild $settings as stdClass from the cached array (safe, no unserialization of objects)
            $settings = new \stdClass;
            $settings->logo_path = $cachedArray['logo_path'] ?? null;
            $settings->favicon_path = $cachedArray['favicon_path'] ?? null;
            $settings->whatsapp = $cachedArray['whatsapp'] ?? null;
            $settings->footer_text = $cachedArray['footer_text'] ?? null;
            $settings->email = $cachedArray['email'] ?? null;
            $settings->address = $cachedArray['address'] ?? null;
            $settings->staff_login_image_path = $cachedArray['staff_login_image_path'] ?? null;
            $settings->wali_login_image_path = $cachedArray['wali_login_image_path'] ?? null;

            // Rebuild $contents as a Collection keyed by section_key
            $contentsArray = $cachedArray['contents'] ?? [];
            $contents = collect($contentsArray)->map(function ($item) {
                $obj = new \stdClass;
                $obj->id = $item['id'] ?? null;
                $obj->section_key = $item['section_key'] ?? null;
                $obj->content_text = $item['content_text'] ?? null;
                $obj->image_path = $item['image_path'] ?? null;

                return $obj;
            })->keyBy('section_key');

            $navPrograms = collect($cachedArray['active_programs'] ?? [])->map(function ($item) {
                $obj = new \stdClass;
                $obj->id = $item['id'] ?? null;
                $obj->name = $item['name'] ?? '';
                $obj->slug = $item['slug'] ?? '';
                $obj->description = $item['description'] ?? null;
                $obj->image_path = $item['image_path'] ?? null;
                $obj->is_registration_open = (bool) ($item['is_registration_open'] ?? false);
                $obj->is_coming_soon = (bool) ($item['is_coming_soon'] ?? false);

                return $obj;
            });

            $view->with('settings', $settings);
            $view->with('contents', $contents);
            $view->with('navPrograms', $navPrograms);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
