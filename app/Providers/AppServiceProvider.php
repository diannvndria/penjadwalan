<?php

namespace App\Providers;

use App\Models\Mahasiswa;
use App\Observers\MahasiswaObserver;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register PDF facade alias
        $loader = AliasLoader::getInstance();
        $loader->alias('PDF', \Barryvdh\DomPDF\Facade\Pdf::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Mahasiswa::observe(MahasiswaObserver::class);
    }
}
