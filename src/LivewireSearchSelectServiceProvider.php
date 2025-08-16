<?php

namespace AMABK\LivewireSearchSelect;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use AMABK\LivewireSearchSelect\SearchSelect;

class LivewireSearchSelectServiceProvider extends ServiceProvider
{
    /**
     * Register services: bind config, merge defaults, etc.
     */
    public function register(): void
    {
        // Merge package config so users can override only what they need
        $this->mergeConfigFrom(__DIR__ . '/../config/livewire-search-select.php', 'livewire-search-select');
    }

    /**
     * Bootstrap services: load views, register Livewire components, offer publishes.
     */
    public function boot(): void
    {
        // 1) Views available as: view('livewire-search-select::search-select')
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-search-select');

        // 2) (Optional) Publishable resources
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/livewire-search-select'),
        ], 'livewire-search-select-views');

        $this->publishes([
            __DIR__ . '/../config/livewire-search-select.php' => config_path('livewire-search-select.php'),
        ], 'livewire-search-select-config');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/livewire-search-select'),
        ], 'livewire-search-select-assets');

        // 3) Livewire component registration
        //    Usage in Blade: <livewire:search-select ... />
        Livewire::component('search-select', SearchSelect::class);

        // 4) (Optional) Load migrations if your package ships them
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // And optionally allow publishing migrations:
        // $this->publishes([
        //     __DIR__ . '/../database/migrations' => database_path('migrations'),
        // ], 'livewire-search-select-migrations');
    }
}
