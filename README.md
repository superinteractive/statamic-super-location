# Super Location Field

Super Location is a Statamic addon that provides a “Super Location” fieldtype backed by OpenStreetMap search. It includes an autocomplete search bar, configurable defaults, and a publishable config file.

## Features

- Autocomplete search powered by the public OpenStreetMap Nominatim API.
- Interactive Leaflet map with a single draggable marker.
- Automatically fills street, postal code, city, country, state, latitude, and longitude.
- Returns a structured array that you can use directly in Antlers, Blade, or PHP.

## Installation

Install the addon with Composer:

```bash
composer require superinteractive/statamic-super-location
```

Publish the config when you need to adjust map defaults, tile providers, or search settings:

```bash
php artisan vendor:publish --tag="statamic-super-location-config"
```

## Configuration

The publishable config file lives at `config/statamic/super-location.php`. You can customize the default map center, zoom level, and the endpoints that are used for fetching tiles and search results.

## Building Assets

The Control Panel assets are powered by Vite + Tailwind. During development, run:

```bash
cd Packages/statamic-super-location
npm install
npm run dev
```

For distribution, build the assets so the compiled files end up in `resources/dist/build`:

```bash
npm run build
```

## Documentation

See `docs/usage.md` for implementation notes and templating tips.
