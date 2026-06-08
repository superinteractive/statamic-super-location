import '../css/cp.css';
import SuperLocationFieldtype from './components/SuperLocationFieldtype.vue';

Statamic.booting(() => {
    Statamic.component('super_location-fieldtype', SuperLocationFieldtype);
});
