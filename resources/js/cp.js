import '../css/cp.css';
import SuperLocationFieldtype from './components/SuperLocationFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('super_location-fieldtype', SuperLocationFieldtype);
});
