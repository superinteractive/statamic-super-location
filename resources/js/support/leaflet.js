const LEAFLET_VERSION = '1.9.4';
const LEAFLET_JS = `https://unpkg.com/leaflet@${LEAFLET_VERSION}/dist/leaflet.js`;
const LEAFLET_CSS = `https://unpkg.com/leaflet@${LEAFLET_VERSION}/dist/leaflet.css`;
const LEAFLET_IMG_BASE = `https://unpkg.com/leaflet@${LEAFLET_VERSION}/dist/images/`;

const injectStylesheet = (href) => {
    if (document.querySelector(`link[data-super-location="${href}"]`)) {
        return;
    }

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    link.dataset.superLocation = href;
    document.head.appendChild(link);
};

const injectScript = (src) => new Promise((resolve, reject) => {
    const existing = document.querySelector(`script[data-super-location="${src}"]`);
    if (existing && window.L) {
        resolve();
        return;
    }

    const script = existing || document.createElement('script');
    script.src = src;
    script.async = true;
    script.dataset.superLocation = src;
    script.onload = () => resolve();
    script.onerror = (error) => reject(error);

    if (!existing) {
        document.head.appendChild(script);
    }
});

let leafletPromise = null;

export const ensureLeafletLoaded = () => {
    if (window.L?.map) {
        return Promise.resolve();
    }

    if (!leafletPromise) {
        injectStylesheet(LEAFLET_CSS);
        leafletPromise = injectScript(LEAFLET_JS);
    }

    return leafletPromise;
};

export const createDefaultMarkerIcon = () => {
    if (!window.L?.icon) {
        return null;
    }

    return window.L.icon({
        iconUrl: `${LEAFLET_IMG_BASE}marker-icon.png`,
        iconRetinaUrl: `${LEAFLET_IMG_BASE}marker-icon-2x.png`,
        shadowUrl: `${LEAFLET_IMG_BASE}marker-shadow.png`,
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        shadowSize: [41, 41],
        shadowAnchor: [12, 41],
    });
};
