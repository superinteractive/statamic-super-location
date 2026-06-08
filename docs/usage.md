# Super Location Field Usage

## Blueprint Setup

Add a new field within your blueprint and select the **Super Location** fieldtype. You can optionally override:

- Default latitude / longitude used to center the map when no selection has been made.
- Default zoom level for the Leaflet map.

## Template Output

The stored value is a keyed array. Example:

```yaml
location:
  street_line: "Damrak 1"
  postal_code: "1012 LG"
  city: "Amsterdam"
  state: "North Holland"
  country: "Netherlands"
  latitude: "52.3731692"
  longitude: "4.8906591"
```

You can render the result using Antlers or Blade:

```antlers
<address class="not-prose">
    {{ location:street_line }}<br>
    {{ location:postal_code }} {{ location:city }}<br>
    {{ location:country }}
</address>
```

```php
{{ $entry->location['latitude'] }}, {{ $entry->location['longitude'] }}
```

## Map + Search Behavior

- The search field debounces requests to the Nominatim API to avoid rate-limit issues.
- Selecting a suggestion moves the map marker, fills the sub-fields, and keeps the map’s center aligned with the new coordinates.
- Manual edits to the inputs are also reflected in the field value when the blueprint is saved.
