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
