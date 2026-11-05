<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Enforce HTTPS in production or behind SSL reverse proxy
        if (app()->environment('production') || str_starts_with((string) config('app.url', ''), 'https://') || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\User::observe(\App\Observers\ActivityObserver::class);
        \App\Models\SdaPersonel::observe(\App\Observers\ActivityObserver::class);
        \App\Models\KatalogPelanggaran::observe(\App\Observers\ActivityObserver::class);
        \App\Models\ReguPatroli::observe(\App\Observers\ActivityObserver::class);
        \App\Models\PenertibanK3::observe(\App\Observers\ActivityObserver::class);
        \App\Models\Satlinmas::observe(\App\Observers\ActivityObserver::class);
        \App\Models\SdaKegiatan::observe(\App\Observers\ActivityObserver::class);
        \App\Models\SdaPustaka::observe(\App\Observers\ActivityObserver::class);
        \App\Models\SatpolKegiatan::observe(\App\Observers\ActivityObserver::class);
        \App\Models\Pengaduan::observe(\App\Observers\ActivityObserver::class);
        \App\Models\Disposisi::observe(\App\Observers\ActivityObserver::class);
        \App\Models\KegiatanLinmas::observe(\App\Observers\ActivityObserver::class);
        \App\Models\PenertibanTrantibum::observe(\App\Observers\ActivityObserver::class);
        \App\Models\PerdaPerbup::observe(\App\Observers\ActivityObserver::class);
        \App\Models\PenegakanPerada::observe(\App\Observers\ActivityObserver::class);
    }
}
