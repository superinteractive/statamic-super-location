# Super Location Field

Super Location is a Statamic 6 addon that provides a "Super Location" fieldtype backed by OpenStreetMap search. It includes an autocomplete search bar, configurable defaults, a Leaflet map, and scoped Control Panel styles for light and dark mode.

## Compatibility

- `2.x`: Statamic 6, Laravel 12 or 13, PHP 8.3+
- `1.x`: Statamic 5

## Features

- Autocomplete search powered by the public OpenStreetMap Nominatim API.
- Interactive Leaflet map with a single draggable marker.
- Automatically fills street, postal code, city, country, state, latitude, and longitude.
- Renders in Statamic 6 Control Panel light and dark mode.
- Returns a structured array that you can use directly in Antlers, Blade, or PHP.

## Installation

Install the addon with Composer:

```bash
composer require superinteractive/statamic-super-location:^2.0
```

Publish the config when you need to adjust map defaults, tile providers, or search settings:

```bash
php artisan vendor:publish --tag="statamic-super-location-config"
```

## Configuration

The publishable config file lives at `config/statamic/super-location.php`. You can customize the default map center, zoom level, tile provider, and search endpoint.

## Building Assets

The Control Panel assets are powered by Vite and Statamic's `@statamic/cms` package. During development, run:

```bash
npm install
npm run dev
```

For distribution, build the assets so the compiled files end up in `resources/dist/build`:

```bash
npm run build
```

## Documentation

See `docs/usage.md` for implementation notes and templating tips.
