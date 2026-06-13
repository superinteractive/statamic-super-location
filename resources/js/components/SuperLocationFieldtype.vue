<template>
    <div class="super-location-fieldtype">
        <div class="super-location-card">
            <div class="super-location-map-shell">
                <div
                    ref="mapCanvas"
                    class="super-location-map"
                    role="img"
                    aria-label="Location map"
                ></div>

                <div v-if="!mapReady" class="super-location-map-loading">
                    {{ __("statamic-super-location::messages.loading_map") }}
                </div>
            </div>

            <div class="super-location-search">
                <template v-if="hasSelection">
                    <div class="super-location-selected">
                        <div class="super-location-selected-copy">
                            <strong v-text="selectionLabel"></strong>
                            <span v-text="selectionSubLabel"></span>
                        </div>

                        <button
                            v-if="!isReadOnly"
                            type="button"
                            class="super-location-clear"
                            :aria-label="__('statamic-super-location::messages.remove_location')"
                            @click="clearSelection"
                        >
                            <svg aria-hidden="true" viewBox="0 0 20 20" focusable="false">
                                <path
                                    fill="currentColor"
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z"
                                />
                            </svg>
                        </button>
                    </div>
                </template>

                <template v-else>
                    <Input
                        v-model="searchQuery"
                        type="search"
                        autocomplete="off"
                        :disabled="isReadOnly"
                        :read-only="isReadOnly"
                        :placeholder="__('statamic-super-location::messages.search_placeholder')"
                        @update:model-value="handleSearchInput"
                    />

                    <small v-if="searchError" class="super-location-error" v-text="searchError"></small>

                    <div v-if="shouldShowSuggestions" class="super-location-suggestions">
                        <div v-if="isSearching" class="super-location-suggestion-status">
                            <span class="super-location-spinner" aria-hidden="true"></span>
                            {{ __("statamic-super-location::messages.searching") }}
                        </div>

                        <template v-if="!isSearching && suggestions.length">
                            <button
                                v-for="option in suggestions"
                                :key="option.id"
                                type="button"
                                class="super-location-suggestion"
                                @click="selectSuggestion(option)"
                            >
                                <span v-text="option.primary"></span>
                                <small v-text="option.secondary"></small>
                            </button>
                        </template>

                        <p v-if="!isSearching && !suggestions.length" class="super-location-empty">
                            {{ __("statamic-super-location::messages.no_results") }}
                        </p>
                    </div>
                </template>
            </div>

            <div v-if="hasSelection" class="super-location-details">
                <div class="super-location-field">
                    <label class="publish-field-label" v-text="__('statamic-super-location::messages.street')"></label>
                    <Input :model-value="draftValue.street_line || ''" disabled read-only />
                </div>

                <div class="super-location-field-grid">
                    <div class="super-location-field">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.postal_code')"></label>
                        <Input :model-value="draftValue.postal_code || ''" disabled read-only />
                    </div>

                    <div class="super-location-field">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.city')"></label>
                        <Input :model-value="draftValue.city || ''" disabled read-only />
                    </div>
                </div>

                <div class="super-location-field-grid">
                    <div class="super-location-field">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.country')"></label>
                        <Input :model-value="draftValue.country || ''" disabled read-only />
                    </div>

                    <div class="super-location-field">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.state')"></label>
                        <Input :model-value="draftValue.state || ''" disabled read-only />
                    </div>
                </div>

                <div class="super-location-field-grid">
                    <div class="super-location-field">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.latitude')"></label>
                        <Input :model-value="draftValue.latitude || ''" disabled read-only />
                    </div>

                    <div class="super-location-field">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.longitude')"></label>
                        <Input :model-value="draftValue.longitude || ''" disabled read-only />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Fieldtype } from '@statamic/cms';
import { Input } from '@statamic/cms/ui';
import { ensureLeafletLoaded, createDefaultMarkerIcon } from '../support/leaflet';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly } = Fieldtype.use(emit, props);

defineExpose(expose);

const emptyLocation = () => ({
    street_line: null,
    postal_code: null,
    city: null,
    state: null,
    country: null,
    latitude: null,
    longitude: null,
});

const normalizeValue = (value) => {
    if (value && typeof value === 'object' && !Array.isArray(value)) {
        return { ...emptyLocation(), ...value };
    }

    return emptyLocation();
};

const mapCanvas = ref(null);
const mapInstance = ref(null);
const markerInstance = ref(null);
const markerIcon = ref(null);
const mapReady = ref(false);
const defaultCenter = ref([0, 0]);
const draftValue = ref(normalizeValue(props.value));
const searchQuery = ref('');
const suggestions = ref([]);
const searchTimeout = ref(null);
const searchError = ref(null);
const isSearching = ref(false);
const searchController = ref(null);

const meta = computed(() => props.meta || {});
const tiles = computed(() => meta.value.tiles || {});
const search = computed(() => meta.value.search || {});
const defaults = computed(() => meta.value.defaults || {});
const searchLanguage = computed(() => search.value.language || 'en');

const hasSelection = computed(() => {
    return Boolean(
        (draftValue.value.street_line && draftValue.value.city)
        || (draftValue.value.latitude && draftValue.value.longitude)
    );
});

const shouldShowSuggestions = computed(() => {
    if (isReadOnly.value || hasSelection.value) {
        return false;
    }

    const hasQuery = searchQuery.value && searchQuery.value.length >= 3;

    return (hasQuery && (isSearching.value || suggestions.value.length > 0))
        || (isSearching.value && Boolean(searchQuery.value));
});

const selectionLabel = computed(() => {
    const primary = draftValue.value.street_line || formatPreview(draftValue.value);

    return primary || __('statamic-super-location::messages.location_selected');
});

const selectionSubLabel = computed(() => {
    const label = [
        draftValue.value.postal_code,
        draftValue.value.city,
        draftValue.value.country,
    ].filter(Boolean).join(', ');

    return label || __('statamic-super-location::messages.coordinates_captured');
});

watch(() => props.value, (value) => {
    draftValue.value = normalizeValue(value);
    searchQuery.value = formatPreview(draftValue.value);
    syncMapFromValue({ pan: false });
}, { deep: true });

onMounted(() => {
    searchQuery.value = formatPreview(draftValue.value);
    initializeMap();
});

onBeforeUnmount(() => {
    clearTimeout(searchTimeout.value);
    abortSearch();

    if (markerInstance.value) {
        markerInstance.value.off();
    }

    if (mapInstance.value) {
        mapInstance.value.remove();
    }
});

const commitValue = (nextValue) => {
    draftValue.value = normalizeValue(nextValue);
    update({ ...draftValue.value });
};

const patchValue = (patch) => {
    commitValue({ ...draftValue.value, ...patch });
};

function initializeMap() {
    ensureLeafletLoaded()
        .then(() => {
            if (!mapCanvas.value || mapInstance.value) {
                return;
            }

            const defaultLatitude = Number(defaults.value.latitude ?? 0);
            const defaultLongitude = Number(defaults.value.longitude ?? 0);
            const latitude = Number(draftValue.value.latitude ?? defaultLatitude);
            const longitude = Number(draftValue.value.longitude ?? defaultLongitude);
            const zoom = Number(defaults.value.zoom ?? 2);

            defaultCenter.value = [defaultLatitude, defaultLongitude];

            mapInstance.value = window.L.map(mapCanvas.value, {
                center: [latitude, longitude],
                zoom,
                zoomControl: !isReadOnly.value,
                dragging: !isReadOnly.value,
                scrollWheelZoom: !isReadOnly.value,
            });

            window.L.tileLayer(tiles.value.url, {
                attribution: tiles.value.attribution,
                maxZoom: tiles.value.max_zoom || 19,
            }).addTo(mapInstance.value);

            markerIcon.value = createDefaultMarkerIcon();
            mapReady.value = true;

            syncMapFromValue({ pan: false });
            invalidateMapSize();
        })
        .catch(() => {
            searchError.value = __('statamic-super-location::messages.map_error');
        });
}

function syncMapFromValue(options = { pan: true }) {
    if (!mapInstance.value || !draftValue.value.latitude || !draftValue.value.longitude) {
        return;
    }

    const lat = Number(draftValue.value.latitude);
    const lng = Number(draftValue.value.longitude);

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return;
    }

    if (!markerInstance.value) {
        markerInstance.value = window.L.marker([lat, lng], {
            draggable: !isReadOnly.value,
            icon: markerIcon.value || undefined,
        }).addTo(mapInstance.value);

        markerInstance.value.on('moveend', () => {
            if (isReadOnly.value) {
                return;
            }

            const position = markerInstance.value.getLatLng();

            patchValue({
                latitude: position.lat.toFixed(6),
                longitude: position.lng.toFixed(6),
            });
        });
    } else {
        markerInstance.value.setLatLng([lat, lng]);
    }

    if (options.pan !== false) {
        mapInstance.value.setView([lat, lng]);
    }

    invalidateMapSize();
}

function formatPreview(value) {
    if (!value) {
        return '';
    }

    return [value.street_line, value.city, value.country].filter(Boolean).join(', ');
}

function handleSearchInput() {
    if (isReadOnly.value) {
        return;
    }

    searchError.value = null;
    clearTimeout(searchTimeout.value);

    if (!searchQuery.value || searchQuery.value.length < 3) {
        abortSearch();
        isSearching.value = false;
        suggestions.value = [];
        return;
    }

    searchTimeout.value = setTimeout(() => performSearch(), 400);
}

function clearSelection() {
    if (isReadOnly.value) {
        return;
    }

    abortSearch();
    isSearching.value = false;
    searchQuery.value = '';
    suggestions.value = [];
    searchError.value = null;

    commitValue(emptyLocation());

    if (markerInstance.value && mapInstance.value) {
        mapInstance.value.removeLayer(markerInstance.value);
        markerInstance.value = null;
    }

    if (mapInstance.value && defaultCenter.value) {
        mapInstance.value.setView(defaultCenter.value, Number(defaults.value.zoom ?? 2));
    }

    invalidateMapSize();
}

function abortSearch() {
    if (searchController.value) {
        searchController.value.abort();
        searchController.value = null;
    }
}

function performSearch() {
    const endpoint = search.value.endpoint;

    if (!endpoint) {
        searchError.value = __('statamic-super-location::messages.search_error');
        return;
    }

    const params = new URLSearchParams(search.value.default_parameters || {});
    params.set('q', searchQuery.value);

    if (searchLanguage.value) {
        params.set('accept-language', searchLanguage.value);
    }

    abortSearch();
    isSearching.value = true;

    const controller = new AbortController();
    searchController.value = controller;

    fetch(`${endpoint}?${params.toString()}`, {
        headers: {
            Accept: 'application/json',
            'Accept-Language': searchLanguage.value,
        },
        signal: controller.signal,
    })
        .then((response) => response.json())
        .then((results) => {
            suggestions.value = (results || []).map((result) => mapResultToSuggestion(result));
        })
        .catch((error) => {
            if (error.name === 'AbortError') {
                return;
            }

            searchError.value = __('statamic-super-location::messages.search_error');
        })
        .finally(() => {
            if (searchController.value === controller) {
                isSearching.value = false;
                searchController.value = null;
            }
        });
}

function selectSuggestion(option) {
    const address = option.raw.address || {};

    abortSearch();
    isSearching.value = false;
    searchQuery.value = [option.primary, option.secondary].filter(Boolean).join(', ');
    suggestions.value = [];

    commitValue({
        street_line: formatStreet(address) || option.raw.name || option.primary || null,
        postal_code: address.postcode || null,
        city: address.city || address.town || address.village || null,
        state: address.state || address.county || null,
        country: address.country || address.country_code?.toUpperCase() || null,
        latitude: Number(option.raw.lat).toFixed(6),
        longitude: Number(option.raw.lon).toFixed(6),
    });

    syncMapFromValue();
    invalidateMapSize();
}

function mapResultToSuggestion(result) {
    const address = result.address || {};

    return {
        id: result.place_id,
        primary: formatPrimaryLabel(address, result),
        secondary: formatSecondaryLabel(address, result),
        raw: result,
    };
}

function formatPrimaryLabel(address, result) {
    const street = address.road || address.pedestrian || address.path || '';
    const number = address.house_number || '';
    const name = result.name || address.neighbourhood || address.suburb || '';
    const primary = [street, number].filter(Boolean).join(' ').trim();

    return primary || name || result.display_name;
}

function formatSecondaryLabel(address, result) {
    const locality = address.city || address.town || address.village || address.hamlet || '';
    const region = address.state || address.county || '';
    const postal = address.postcode || '';
    const country = address.country || '';
    const parts = [locality, region, postal, country].filter(Boolean);

    if (!parts.length) {
        return result.display_name;
    }

    return parts.join(', ');
}

function formatStreet(address) {
    return [
        address.road,
        [address.house_number, address.subunit].filter(Boolean).join(' ').trim(),
        address.neighbourhood,
        address.suburb,
    ]
        .filter(Boolean)
        .join(' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function invalidateMapSize() {
    if (!mapInstance.value) {
        return;
    }

    nextTick(() => {
        mapInstance.value.invalidateSize();
    });
}
</script>
