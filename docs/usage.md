# Super Location Field Usage

## Blueprint Setup

Add a new field within your blueprint and select the **Super Location** fieldtype. You can optionally override:

- Default latitude and longitude used to center the map when no selection has been made.
- Default zoom level for the Leaflet map.
- Result language used by the autocomplete search endpoint.

## Template Output

The stored value is a keyed array. Example:

```yaml
location:
  street_line: "Damrak 1"
  postal_code: "1012 LG"
  city: "Amsterdam"
  state: "North Holland"
  country: "Netherlands"
  latitude: "52.373169"
  longitude: "4.890659"
```

You can render the result using Antlers or Blade:

```antlers
<address class="not-prose">
    {{ location:street_line }}<br>
    {{ location:postal_code }} {{ location:city }}<br>
    {{ location:country }}
</address>
```

```blade
{{ $entry->location['latitude'] }}, {{ $entry->location['longitude'] }}
```

## Map + Search Behavior

- The search field debounces requests to the configured search endpoint to reduce rate-limit pressure.
- Selecting a suggestion moves the map marker, fills the sub-fields, and keeps the map center aligned with the selected coordinates.
- Dragging the marker updates latitude and longitude.
- Clearing a selection removes the marker and resets the stored value shape to the fieldtype defaults.
- In read-only mode, search, clearing, map dragging, and scroll-wheel zoom are disabled.

## Control Panel Themes

The fieldtype uses scoped CSS variables under `.super-location-fieldtype` and `.dark .super-location-fieldtype`. It does not depend on project Tailwind classes, so it follows Statamic 6 light and dark Control Panel modes without leaking styles into other fields.
