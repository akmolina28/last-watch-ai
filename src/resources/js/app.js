require('./bootstrap');

import router from './routes.js';

Vue.component('pagination', require('laravel-vue-bulma-paginator'));

new Vue({
    el: '#app',
    router
});
