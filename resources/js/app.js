import './bootstrap';
import Alpine from 'alpinejs';
import { initDashboardCharts } from './dashboard';
import { offcanvasCrud } from './offcanvasCrud';

window.Alpine = Alpine;
window.offcanvasCrud = offcanvasCrud;

document.addEventListener('alpine:init', () => {
    Alpine.data('offcanvasCrud', (config) => offcanvasCrud(config));
});

window.globalSearch = () => ({
    query: '',
    open: false,
    loading: false,
    results: [],
    async search() {
        if (this.query.length < 2) {
            this.open = false;
            this.results = [];
            return;
        }

        this.open = true;
        this.loading = true;

        try {
            const response = await window.axios.get('/search', {
                params: { q: this.query },
            });

            this.results = response.data.results ?? [];
        } catch (error) {
            this.results = [];
        } finally {
            this.loading = false;
        }
    },
    goTo(url) {
        window.location.href = url;
    },
});

Alpine.start();

document.addEventListener('DOMContentLoaded', initDashboardCharts);
