<?php

namespace App\Providers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use App\Observers\AlatObserver;
use App\Observers\KategoriObserver;
use App\Observers\PeminjamanObserver;
use App\Observers\PengembalianObserver;
use App\Observers\UserObserver;
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
        Alat::observe(AlatObserver::class);
        Kategori::observe(KategoriObserver::class);
        Peminjaman::observe(PeminjamanObserver::class);
        Pengembalian::observe(PengembalianObserver::class);
        User::observe(UserObserver::class);
    }
}
