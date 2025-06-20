<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Mahasiswa;
use App\Observers\MahasiswaObserver;

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
        // Register observers
        Mahasiswa::observe(MahasiswaObserver::class);
    }
}
