<?php

declare(strict_types=1);

namespace SuperInteractive\SuperLocation;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $fieldtypes = [
        Fieldtypes\SuperLocationFieldtype::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/super-location.php', 'statamic.super-location');
    }

    public function bootAddon(): void
    {
        parent::bootAddon();

        $this->publishes([
            __DIR__.'/../config/super-location.php' => config_path('statamic/super-location.php'),
        ], 'statamic-super-location-config');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'statamic-super-location');
    }
}
