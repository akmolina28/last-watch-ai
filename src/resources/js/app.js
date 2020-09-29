require('./bootstrap');

import router from './routes.js';

Vue.component('pagination', require('laravel-vue-bulma-paginator'));

import TitleHeader from './components/TitleHeader';
Vue.component('title-header', TitleHeader);

new Vue({
    el: '#app',
    router
});
