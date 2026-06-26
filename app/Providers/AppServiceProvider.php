<?php

namespace App\Providers;

use App\Services\NotifikasiService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton agar hasil hitung notifikasi di-cache dalam satu request
        // (View Composer memanggil counter + daftar memakai instance yang sama).
        $this->app->singleton(NotifikasiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sediakan data notifikasi (counter + item teratas) untuk header di
        // semua halaman yang memakai layout utama — ringan & ter-cache per request.
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            if (! $user) {
                $view->with(['notifCount' => 0, 'notifItems' => collect()]);

                return;
            }

            $notifikasi = app(NotifikasiService::class);

            $view->with([
                'notifCount' => $notifikasi->hitungBelumDibaca($user),
                'notifItems' => $notifikasi->ambil($user, 8),
            ]);
        });
    }
}
