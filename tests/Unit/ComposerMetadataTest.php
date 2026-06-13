<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>
 *
 * @throws JsonException
 */
function superLocationComposerMetadata(): array
{
    $json = file_get_contents(__DIR__.'/../../composer.json');

    expect($json)->toBeString();

    $composer = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    expect($composer)->toBeArray();

    return $composer;
}

it('declares Statamic 6 compatible package metadata and discovery', function (): void {
    $composer = superLocationComposerMetadata();

    expect($composer['name'])->toBe('superinteractive/statamic-super-location')
        ->and($composer['type'])->toBe('statamic-addon')
        ->and($composer)->not->toHaveKey('version')
        ->and($composer['require']['php'])->toBe('^8.3')
        ->and($composer['require']['illuminate/support'])->toBe('^12.0 || ^13.0')
        ->and($composer['require']['statamic/cms'])->toBe('^6.0')
        ->and($composer['require-dev']['orchestra/testbench'])->toBe('^10.8 || ^11.0')
        ->and($composer['require-dev']['pestphp/pest'])->toBe('^3.8 || ^4.0')
        ->and($composer['require-dev']['pestphp/pest-plugin-laravel'])->toBe('^3.0 || ^4.0')
        ->and($composer['require-dev']['phpunit/phpunit'])->toBe('^11.5 || ^12.5 || ^13.0')
        ->and($composer['autoload']['psr-4'])->toHaveKey('SuperInteractive\\SuperLocation\\')
        ->and($composer['autoload-dev']['psr-4'])->toHaveKey('SuperInteractive\\SuperLocation\\Tests\\')
        ->and($composer['extra']['laravel']['providers'])->toBe([
            'SuperInteractive\\SuperLocation\\ServiceProvider',
        ])
        ->and($composer['extra']['statamic']['name'])->toBe('Super Location Field');
});

it('declares publish-ready author and support metadata', function (): void {
    $composer = superLocationComposerMetadata();

    expect($composer['authors'][0]['email'])->toBe('hello@superinteractive.com')
        ->and($composer['support']['issues'])->toBe('https://github.com/superinteractive/statamic-super-location/issues')
        ->and($composer['support']['source'])->toBe('https://github.com/superinteractive/statamic-super-location')
        ->and($composer)->not->toHaveKey('minimum-stability')
        ->and($composer)->not->toHaveKey('prefer-stable');
});
