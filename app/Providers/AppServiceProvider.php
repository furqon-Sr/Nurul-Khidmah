<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\PageContent;

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
        $this->configureDefaults();

        // Daftarkan event listener untuk mencatat login
        Event::listen(Login::class, \App\Listeners\LogSuccessfulLogin::class);

        // Global View Composer - runs on every request, data cached for performance
        View::composer('*', function ($view) {
            // Cache only a plain array (not stdClass) to avoid database cache unserialize errors
            $cachedArray = Cache::remember('site_global_data', 3600, function () {
                $result = [
                    'logo_path'    => null,
                    'favicon_path' => null,
                    'whatsapp'     => null,
                    'footer_text'  => null,
                    'email'        => null,
                    'address'      => null,
                    'contents'     => [],
                ];

                try {
                    // Schema::hasTable dihapus — tidak perlu import Schema,
                    // try-catch sudah menangani jika DB belum siap.
                    $allContents = PageContent::all()->keyBy('section_key');

                    $result['logo_path']    = $allContents->get('logo')?->image_path;
                    $result['favicon_path'] = $allContents->get('favicon')?->image_path;
                    $result['whatsapp']     = $allContents->get('contact_phone')?->content_text;
                    $result['footer_text']  = $allContents->get('footer_text')?->content_text;
                    $result['email']        = $allContents->get('contact_email')?->content_text;
                    $result['address']      = $allContents->get('contact_address')?->content_text;

                    // Store contents as plain array of arrays for cache safety
                    $result['contents'] = $allContents->map(fn($c) => [
                        'id'           => $c->id,
                        'section_key'  => $c->section_key,
                        'content_text' => $c->content_text,
                        'image_path'   => $c->image_path,
                    ])->toArray();

                } catch (\Exception $e) {
                    // DB not ready yet — return empty result
                }

                return $result;
            });

            // Rebuild $settings as stdClass from the cached array (safe, no unserialization of objects)
            $settings = new \stdClass();
            $settings->logo_path    = $cachedArray['logo_path']    ?? null;
            $settings->favicon_path = $cachedArray['favicon_path'] ?? null;
            $settings->whatsapp     = $cachedArray['whatsapp']     ?? null;
            $settings->footer_text  = $cachedArray['footer_text']  ?? null;
            $settings->email        = $cachedArray['email']        ?? null;
            $settings->address      = $cachedArray['address']      ?? null;

            // Rebuild $contents as a Collection keyed by section_key
            $contentsArray = $cachedArray['contents'] ?? [];
            $contents = collect($contentsArray)->map(function ($item) {
                $obj = new \stdClass();
                $obj->id           = $item['id']           ?? null;
                $obj->section_key  = $item['section_key']  ?? null;
                $obj->content_text = $item['content_text'] ?? null;
                $obj->image_path   = $item['image_path']   ?? null;
                return $obj;
            })->keyBy('section_key');

            $view->with('settings', $settings);
            $view->with('contents', $contents);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
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
