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
        // Self-adapting 'public' disk location. In the standard Laravel
        // layout, public/ is a subfolder and config/filesystems.php's
        // public_path('uploads') is already correct. In the flattened
        // "whole app in public_html" deploy (see README "Deployment"),
        // public/'s contents were merged up into the app root at build
        // time, so there's no public/ subfolder anymore — point the disk
        // at the app root instead.
        if (! is_dir(base_path('public'))) {
            config([
                'filesystems.disks.public.root' => base_path('uploads'),
                'filesystems.disks.public.url' => rtrim((string) config('app.url'), '/').'/uploads',
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
