<?php

declare(strict_types=1);

use SuperInteractive\SuperLocation\Fieldtypes\SuperLocationFieldtype;

it('provides a stable structured default value', function (): void {
    $fieldtype = new SuperLocationFieldtype;

    expect($fieldtype->defaultValue())->toBe([
        'street_line' => null,
        'postal_code' => null,
        'city' => null,
        'state' => null,
        'country' => null,
        'latitude' => null,
        'longitude' => null,
    ]);
});

it('formats the index preview from stored location data', function (): void {
    $fieldtype = new SuperLocationFieldtype;

    $preview = $fieldtype->preProcessIndex([
        'street_line' => 'Damrak 1',
        'postal_code' => '1012 LG',
        'city' => 'Amsterdam',
        'state' => 'North Holland',
        'country' => 'Netherlands',
        'latitude' => '52.373169',
        'longitude' => '4.890659',
    ]);

    expect($preview)->toBe('Damrak 1, 1012 LG, Amsterdam, Netherlands');
});

it('returns null for non-array index values', function (): void {
    $fieldtype = new SuperLocationFieldtype;

    expect($fieldtype->preProcessIndex(null))->toBeNull()
        ->and($fieldtype->preProcessIndex('Amsterdam'))->toBeNull();
});
