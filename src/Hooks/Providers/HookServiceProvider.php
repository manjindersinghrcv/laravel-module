<?php

namespace RCV\Core\Hooks\Providers;

use Illuminate\Support\ServiceProvider;
use RCV\Core\Hooks\HookManager;
use RCV\Core\Hooks\HookRegistry;
use RCV\Core\Hooks\Discovery\ListenerDiscoverer;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Register bindings.
     */
    public function register(): void
    {
        $this->app->singleton(HookRegistry::class, function () {
            return new HookRegistry();
        });

        $this->app->singleton('rcv.core.hooks', function ($app) {
            return new HookManager($app->make(HookRegistry::class));
        });

        $this->app->alias('rcv.core.hooks', HookManager::class);

        $this->loadHelpers();
    }

    /**
     * Boot listeners.
     */
    public function boot(): void
    {
        if (config('rcv-hooks.discovery.enabled', true)) {
            $this->discoverListeners();
        }
    }

    /**
     * Load helper functions file.
     */
    protected function loadHelpers(): void
    {
        $helperPath = __DIR__ . '/../Helpers/helpers.php';
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Trigger auto-discovery.
     */
    protected function discoverListeners(): void
    {
        $discoverer = new ListenerDiscoverer(
            $this->app->make(HookManager::class),
            config('rcv-hooks.discovery', [])
        );

        $discoverer->discover();
    }
}
