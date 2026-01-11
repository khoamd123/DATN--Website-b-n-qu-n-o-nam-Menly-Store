<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        // Map morph types to fully qualified class names để tránh lỗi Class not found
        Relation::morphMap([
            'ClubJoinRequest' => \App\Models\ClubJoinRequest::class,
        ]);
    }
}
