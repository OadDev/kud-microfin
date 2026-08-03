<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        // Hostinger (like most shared hosts) terminates HTTPS upstream and
        // forwards to PHP over plain HTTP internally, so $request->isSecure()
        // reports false unless the proxy is trusted -- without this,
        // route()/url() silently generate http:// links (still reachable,
        // but the wrong link Laravel itself signs/expects, and the kind of
        // thing that reads as "buttons not working" once anything depends
        // on the scheme matching). This app only ever runs on one fixed
        // HTTPS domain in production, so just force the scheme outright
        // rather than maintaining a trusted-proxy IP list.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Cart badge needs to be available in the customer shell's header on
        // every page (not just Shop), so it's a composer rather than
        // something each CustomerPanel controller has to remember to pass.
        View::composer('components.customer-layout', function ($view) {
            $user = Auth::user();
            $cartCount = 0;
            if ($user && $user->role === 'customer' && $user->customer) {
                $cartCount = (int) $user->customer->cartItems()->sum('quantity');
            }
            $view->with('cartCount', $cartCount);
        });
    }
}
