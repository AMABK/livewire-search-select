<?php

namespace AMABK\LivewireSearchSelect;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

/**
 * Package Service Provider: Registers views and Livewire component.
 */
class LivewireSearchSelectServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Make package views available to host app as 'livewire-search-select::'
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-search-select');

        // Register the Livewire component under a unique tag
        Livewire::component('livewire-search-select::search-select', SearchSelect::class);
    }
}
