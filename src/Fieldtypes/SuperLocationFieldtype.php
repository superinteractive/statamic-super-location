<?php

declare(strict_types=1);

namespace SuperInteractive\SuperLocation\Fieldtypes;

use Statamic\Fields\Fieldtype;

class SuperLocationFieldtype extends Fieldtype
{
    protected $icon = 'location';

    public function defaultValue(): array
    {
        return [
            'street_line' => null,
            'postal_code' => null,
            'city' => null,
            'state' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    public function preload(): array
    {
        $defaults = config('statamic.super-location.defaults');

        return [
            'tiles' => config('statamic.super-location.tiles'),
            'search' => $this->searchConfig(),
            'defaults' => [
                'latitude' => $this->config('default_latitude', $defaults['latitude']),
                'longitude' => $this->config('default_longitude', $defaults['longitude']),
                'zoom' => $this->config('default_zoom', $defaults['zoom']),
            ],
        ];
    }

    public function preProcessIndex($data): ?string
    {
        if (! is_array($data)) {
            return null;
        }

        return collect([
            $data['street_line'] ?? null,
            $data['postal_code'] ?? null,
            $data['city'] ?? null,
            $data['country'] ?? null,
        ])->filter()->implode(', ');
    }

    public function configFieldItems(): array
    {
        return [
            'default_latitude' => [
                'display' => __('Default Latitude'),
                'type' => 'text',
                'input_type' => 'number',
                'width' => 33,
            ],
            'default_longitude' => [
                'display' => __('Default Longitude'),
                'type' => 'text',
                'input_type' => 'number',
                'width' => 33,
            ],
            'default_zoom' => [
                'display' => __('Default Zoom'),
                'type' => 'integer',
                'width' => 33,
            ],
            'language' => [
                'display' => __('Result Language'),
                'type' => 'text',
                'instructions' => __('ISO language code used for autocomplete results.'),
                'width' => 50,
            ],
        ];
    }

    protected function searchConfig(): array
    {
        $search = config('statamic.super-location.search');

        $search['language'] = $this->config('language', $search['language'] ?? config('app.locale'));

        return $search;
    }
}
