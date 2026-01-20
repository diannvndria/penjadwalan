<?php

namespace App\Providers;

use App\Models\Mahasiswa;
use App\Observers\MahasiswaObserver;
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
        // Register observers
        Mahasiswa::observe(MahasiswaObserver::class);
    }
}
