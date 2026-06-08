<template>
    <div class="sup-space-y-6">
        <div
            class="sup-flex sup-flex-col sup-gap-3 sup-rounded-2xl sup-border sup-border-slate-200/80 sup-bg-white sup-p-5 sup-text-sm sup-shadow-sm sup-ring-1 sup-ring-black/5 dark:sup-border-slate-700 dark:sup-bg-slate-800 dark:sup-ring-white/5"
        >
            <div class="sup-relative sup-overflow-hidden sup-rounded-xl sup-border sup-border-slate-200/60 dark:sup-border-slate-600/60">
                <div ref="mapCanvas" class="sup-w-full sup-rounded-xl" role="img" aria-label="Location map" :style="{ height: mapHeight }"></div>
                <div
                    v-if="!mapReady"
                    class="sup-absolute sup-inset-0 sup-flex sup-items-center sup-justify-center sup-bg-slate-50/90 sup-text-sm sup-font-medium sup-text-slate-500 dark:sup-bg-slate-900/70 dark:sup-text-slate-200"
                >
                    {{ __("statamic-super-location::messages.loading_map") }}
                </div>
            </div>

            <div class="sup-relative">
                <template v-if="hasSelection">
                    <div class="relationship-input">
                        <div class="relationship-input-items sup-space-y-1 sup-outline-none">
                            <div
                                class="item sup-select-none sup-rounded sup-border sup-border-gray-200 sup-bg-white sup-px-3 sup-py-2 sup-text-sm dark:sup-border-slate-600 dark:sup-bg-slate-800"
                            >
                                <div class="item-inner sup-flex sup-items-center">
                                    <span class="little-dot rtl:sup-ml-2 ltr:sup-mr-2 sup-hidden sm:sup-block published"></span>
                                    <div class="sup-flex sup-flex-1 sup-flex-col">
                                        <span class="sup-truncate sup-font-semibold sup-text-gray-900 dark:sup-text-white" v-text="selectionLabel"></span>
                                        <span class="sup-text-xs sup-text-gray-500 dark:sup-text-slate-300" v-text="selectionSubLabel"></span>
                                    </div>
                                    <button
                                        type="button"
                                        class="sup-ml-3 sup-inline-flex sup-items-center sup-justify-center sup-rounded-full sup-border sup-border-transparent sup-bg-gray-200 sup-p-2 sup-text-gray-700 hover:sup-bg-gray-300 dark:sup-bg-slate-700 dark:sup-text-slate-100"
                                        @click="clearSelection"
                                    >
                                        <span class="sup-sr-only">{{ __("statamic-super-location::messages.remove_location") }}</span>
                                        <svg class="sup-h-4 sup-w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <text-input
                        type="search"
                        autocomplete="off"
                        :disabled="isReadOnly"
                        v-model="searchQuery"
                        @input="handleSearchInput"
                        :placeholder="__('statamic-super-location::messages.search_placeholder')"
                    />
                    <small v-if="searchError" class="sup-mt-2 sup-block sup-text-xs text-red" v-text="searchError"></small>

                    <div
                        v-if="shouldShowSuggestions"
                        class="sup-absolute sup-left-0 sup-right-0 sup-z-20 sup-mt-2 sup-overflow-hidden sup-rounded-xl sup-border sup-border-slate-200/80 sup-bg-white sup-text-sm dark:sup-border-slate-600 dark:sup-bg-slate-900"
                    >
                        <div
                            v-if="isSearching"
                            class="sup-flex sup-items-center sup-gap-2 sup-px-4 sup-py-3 sup-text-xs sup-text-slate-500 dark:sup-text-slate-300"
                        >
                            <svg class="sup-h-4 sup-w-4 sup-animate-spin sup-text-blue-500" viewBox="0 0 24 24" fill="none">
                                <circle class="sup-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="sup-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            {{ __("statamic-super-location::messages.searching") }}
                        </div>

                        <template v-if="!isSearching && suggestions.length">
                            <button
                                v-for="option in suggestions"
                                type="button"
                                class="sup-flex sup-w-full sup-flex-col sup-gap-1 sup-px-4 sup-py-3 sup-text-left sup-transition hover:sup-bg-blue-50 focus-visible:sup-bg-blue-100 dark:hover:sup-bg-slate-800"
                                :key="option.id"
                                @click="selectSuggestion(option)"
                            >
                                <span class="sup-font-semibold sup-text-slate-900 dark:sup-text-white" v-text="option.primary"></span>
                                <span class="sup-text-xs sup-text-slate-600 dark:sup-text-slate-300" v-text="option.secondary"></span>
                            </button>
                        </template>

                        <p v-if="!isSearching && !suggestions.length" class="sup-px-4 sup-py-3 sup-text-xs sup-text-slate-500 dark:sup-text-slate-300">
                            {{ __("statamic-super-location::messages.no_results") }}
                        </p>
                    </div>
                </template>
            </div>

            <div v-if="hasSelection" class="sup-space-y-4 sup-border-t sup-border-slate-200/70 sup-pt-4 sup-text-sm dark:sup-border-slate-600/50">
                <div class="field-inner">
                    <label class="publish-field-label" v-text="__('statamic-super-location::messages.street')"></label>
                    <text-input v-model="value.street_line" disabled />
                </div>
                <div class="sup-grid sup-gap-4 md:sup-grid-cols-2">
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.postal_code')"></label>
                        <text-input v-model="value.postal_code" disabled />
                    </div>
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.city')"></label>
                        <text-input v-model="value.city" disabled />
                    </div>
                </div>
                <div class="sup-grid sup-gap-4 md:sup-grid-cols-2">
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.country')"></label>
                        <text-input v-model="value.country" disabled />
                    </div>
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.state')"></label>
                        <text-input v-model="value.state" disabled />
                    </div>
                </div>
                <div class="sup-grid sup-gap-4 md:sup-grid-cols-2">
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.latitude')"></label>
                        <text-input v-model="value.latitude" disabled />
                    </div>
                    <div class="field-inner">
                        <label class="publish-field-label" v-text="__('statamic-super-location::messages.longitude')"></label>
                        <text-input v-model="value.longitude" disabled />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ensureLeafletLoaded, createDefaultMarkerIcon } from "../support/leaflet";

export default {
    mixins: [Fieldtype],

    data() {
        return {
            mapInstance: null,
            markerInstance: null,
            markerIcon: null,
            mapReady: false,
            mapHeight: "18rem",
            defaultCenter: null,
            searchQuery: "",
            suggestions: [],
            searchTimeout: null,
            searchError: null,
            isSearching: false,
            searchController: null,
        };
    },

    computed: {
        hasSelection() {
            return Boolean((this.value?.street_line && this.value?.city) || (this.value?.latitude && this.value?.longitude));
        },

        shouldShowSuggestions() {
            if (this.isReadOnly) {
                return false;
            }

            const hasQuery = this.searchQuery && this.searchQuery.length >= 3;

            if (this.hasSelection) {
                return false;
            }

            return (hasQuery && (this.isSearching || this.suggestions.length > 0)) || (this.isSearching && !!this.searchQuery);
        },

        selectionLabel() {
            const primary = this.value?.street_line || this.formatPreview(this.value);

            return primary || this.__("statamic-super-location::messages.location_selected");
        },

        selectionSubLabel() {
            const label = [this.value?.postal_code, this.value?.city, this.value?.country].filter(Boolean).join(", ");

            return label || this.__("statamic-super-location::messages.coordinates_captured");
        },

        searchLanguage() {
            return this.meta?.search?.language || "en";
        },
    },

    watch: {
        value: {
            deep: true,
            handler(newValue) {
                this.update(newValue);
            },
        },
        "value.latitude": function () {
            this.syncMapFromValue();
        },
        "value.longitude": function () {
            this.syncMapFromValue();
        },
        hasSelection() {
            this.invalidateMapSize();
        },
    },

    created() {
        this.ensureValueObject();
    },

    mounted() {
        this.searchQuery = this.formatPreview(this.value);
        this.initializeMap();
    },

    methods: {
        ensureValueObject() {
            if (this.value && typeof this.value === "object") {
                return;
            }

            this.value = {
                street_line: null,
                postal_code: null,
                city: null,
                state: null,
                country: null,
                latitude: null,
                longitude: null,
            };
        },

        initializeMap() {
            ensureLeafletLoaded()
                .then(() => {
                    const defaults = this.meta.defaults || {};
                    const latitude = Number(this.value.latitude ?? defaults.latitude);
                    const longitude = Number(this.value.longitude ?? defaults.longitude);
                    this.defaultCenter = [Number(defaults.latitude ?? 0), Number(defaults.longitude ?? 0)];

                    this.mapInstance = window.L.map(this.$refs.mapCanvas, {
                        center: [latitude, longitude],
                        zoom: defaults.zoom ?? 2,
                        zoomControl: !this.isReadOnly,
                        dragging: !this.isReadOnly,
                        scrollWheelZoom: !this.isReadOnly,
                    });

                    window.L.tileLayer(this.meta.tiles.url, {
                        attribution: this.meta.tiles.attribution,
                        maxZoom: this.meta.tiles.max_zoom || 19,
                    }).addTo(this.mapInstance);

                    this.markerIcon = createDefaultMarkerIcon();
                    this.mapReady = true;
                    this.syncMapFromValue({ pan: false });
                    this.invalidateMapSize();
                })
                .catch(() => {
                    this.searchError = this.__("statamic-super-location::messages.map_error");
                });
        },

        syncMapFromValue(options = { pan: true }) {
            if (!this.mapInstance || !this.value.latitude || !this.value.longitude) {
                return;
            }

            const lat = Number(this.value.latitude);
            const lng = Number(this.value.longitude);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            if (!this.markerInstance) {
                this.markerInstance = window.L.marker([lat, lng], {
                    draggable: !this.isReadOnly,
                    icon: this.markerIcon || undefined,
                }).addTo(this.mapInstance);

                this.markerInstance.on("moveend", () => {
                    if (this.isReadOnly) {
                        return;
                    }

                    const pos = this.markerInstance.getLatLng();
                    this.value.latitude = pos.lat.toFixed(6);
                    this.value.longitude = pos.lng.toFixed(6);
                });
            } else {
                this.markerInstance.setLatLng([lat, lng]);
            }

            if (options.pan !== false) {
                this.mapInstance.setView([lat, lng]);
            }

            this.invalidateMapSize();
        },

        formatPreview(value) {
            if (!value) {
                return "";
            }

            return [value.street_line, value.city, value.country].filter(Boolean).join(", ");
        },

        handleSearchInput() {
            if (this.isReadOnly) {
                return;
            }

            this.searchError = null;
            clearTimeout(this.searchTimeout);

            if (!this.searchQuery || this.searchQuery.length < 3) {
                this.abortSearch();
                this.isSearching = false;
                this.suggestions = [];
                return;
            }

            this.searchTimeout = setTimeout(() => this.performSearch(), 400);
        },

        clearSelection() {
            this.abortSearch();
            this.isSearching = false;
            this.searchQuery = "";
            this.suggestions = [];
            this.searchError = null;

            Object.assign(this.value, {
                street_line: null,
                postal_code: null,
                city: null,
                state: null,
                country: null,
                latitude: null,
                longitude: null,
            });

            if (this.markerInstance && this.mapInstance) {
                this.mapInstance.removeLayer(this.markerInstance);
                this.markerInstance = null;
            }

            if (this.mapInstance && this.defaultCenter) {
                this.mapInstance.setView(this.defaultCenter);
            }

            this.invalidateMapSize();
        },

        abortSearch() {
            if (this.searchController) {
                this.searchController.abort();
                this.searchController = null;
            }
        },

        performSearch() {
            const endpoint = this.meta.search.endpoint;
            const params = new URLSearchParams({
                ...this.meta.search.default_parameters,
                q: this.searchQuery,
            });

            if (this.searchLanguage) {
                params.set("accept-language", this.searchLanguage);
            }

            this.abortSearch();
            this.isSearching = true;

            this.searchController = new AbortController();

            fetch(`${endpoint}?${params.toString()}`, {
                headers: {
                    Accept: "application/json",
                    "Accept-Language": this.searchLanguage,
                },
                signal: this.searchController.signal,
            })
                .then((response) => response.json())
                .then((results) => {
                    this.suggestions = (results || []).map((result) => this.mapResultToSuggestion(result));
                })
                .catch((error) => {
                    if (error.name === "AbortError") {
                        return;
                    }

                    this.searchError = this.__("statamic-super-location::messages.search_error");
                })
                .finally(() => {
                    this.isSearching = false;
                });
        },

        selectSuggestion(option) {
            const address = option.raw.address || {};

            this.abortSearch();
            this.isSearching = false;

            this.searchQuery = [option.primary, option.secondary].filter(Boolean).join(", ");
            this.suggestions = [];

            this.value.street_line = this.formatStreet(address) || option.raw.name || option.primary || null;
            this.value.postal_code = address.postcode || null;
            this.value.city = address.city || address.town || address.village || null;
            this.value.state = address.state || address.county || null;
            this.value.country = address.country || address.country_code?.toUpperCase() || null;
            this.value.latitude = Number(option.raw.lat).toFixed(6);
            this.value.longitude = Number(option.raw.lon).toFixed(6);

            this.syncMapFromValue();
            this.invalidateMapSize();
        },

        mapResultToSuggestion(result) {
            const address = result.address || {};

            return {
                id: result.place_id,
                primary: this.formatPrimaryLabel(address, result),
                secondary: this.formatSecondaryLabel(address, result),
                raw: result,
            };
        },

        formatPrimaryLabel(address, result) {
            const street = address.road || address.pedestrian || address.path || "";
            const number = address.house_number || "";
            const name = result.name || address.neighbourhood || address.suburb || "";

            const primary = [street, number].filter(Boolean).join(" ").trim();

            return primary || name || result.display_name;
        },

        formatSecondaryLabel(address, result) {
            const locality = address.city || address.town || address.village || address.hamlet || "";
            const region = address.state || address.county || "";
            const postal = address.postcode || "";
            const country = address.country || "";

            const parts = [locality, region, postal, country].filter(Boolean);

            if (!parts.length) {
                return result.display_name;
            }

            return parts.join(", ");
        },

        formatStreet(address) {
            return [address.road, [address.house_number, address.subunit].filter(Boolean).join(" ").trim(), address.neighbourhood, address.suburb]
                .filter(Boolean)
                .join(" ")
                .replace(/\s+/g, " ")
                .trim();
        },

        invalidateMapSize() {
            if (!this.mapInstance) {
                return;
            }

            this.$nextTick(() => {
                this.mapInstance.invalidateSize();
            });
        },
    },
};
</script>
