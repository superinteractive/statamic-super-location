<?php

declare(strict_types=1);

function superLocationReadFile(string $path): string
{
    $contents = file_get_contents(__DIR__.'/../../'.$path);

    expect($contents)->toBeString();

    return $contents;
}

it('uses Statamic 6 Vite tooling instead of the Vue 2 plugin', function (): void {
    $package = superLocationReadFile('package.json');
    $vite = superLocationReadFile('vite.config.js');

    expect($package)
        ->toContain('"version": "2.0.0"')
        ->toContain('"@statamic/cms": "file:./vendor/statamic/cms/resources/dist-package"')
        ->not->toContain('@vitejs/plugin-vue2')
        ->and($vite)
        ->toContain("import statamic from '@statamic/cms/vite-plugin';")
        ->toContain('statamic(),')
        ->not->toContain('@vitejs/plugin-vue2')
        ->not->toContain('vue({');
});

it('registers the fieldtype through the Statamic 6 component registry', function (): void {
    $cp = superLocationReadFile('resources/js/cp.js');

    expect($cp)
        ->toContain('Statamic.$components.register(\'super_location-fieldtype\', SuperLocationFieldtype);')
        ->not->toContain('Statamic.component(');
});

it('uses the Statamic 6 fieldtype composable and UI inputs', function (): void {
    $component = superLocationReadFile('resources/js/components/SuperLocationFieldtype.vue');

    expect($component)
        ->toContain("import { Fieldtype } from '@statamic/cms';")
        ->toContain("import { Input } from '@statamic/cms/ui';")
        ->toContain('Fieldtype.use(emit, props)')
        ->toContain('defineExpose(expose)')
        ->not->toContain('mixins: [Fieldtype]')
        ->not->toContain('<text-input')
        ->not->toContain('@input=');
});

it('declares scoped light and dark mode styles', function (): void {
    $css = superLocationReadFile('resources/css/cp.css');

    expect($css)
        ->toContain('@layer addon-utilities')
        ->toContain('.super-location-fieldtype')
        ->toContain('.dark .super-location-fieldtype')
        ->toContain('--super-location-surface')
        ->toContain('--super-location-text');
});

it('does not copy Super Link Tailwind or link-field context workarounds', function (): void {
    $component = superLocationReadFile('resources/js/components/SuperLocationFieldtype.vue');
    $css = superLocationReadFile('resources/css/cp.css');

    expect($component)
        ->not->toContain('class="!')
        ->not->toContain("class='!")
        ->not->toContain('link-fieldtype')
        ->not->toContain("provide('isInLinkField'")
        ->not->toContain('<assets-fieldtype')
        ->not->toContain('<relationship-fieldtype')
        ->not->toContain('@update:meta')
        ->not->toContain('@meta-updated')
        ->and($css)
        ->not->toContain('@apply')
        ->not->toContain("@import 'tailwindcss")
        ->not->toContain('@import "tailwindcss');
});
