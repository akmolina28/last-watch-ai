require('./bootstrap');

import router from './routes.js';

Vue.component('pagination', require('laravel-vue-bulma-paginator'));

import TitleHeader from './components/TitleHeader';
Vue.component('title-header', TitleHeader);

Vue.filter('dateStr', function(value) {
    return moment.utc(value).local();
});

Vue.filter('dateStrRelative', function(value) {
    return moment.utc(value).local().fromNow();
});

Vue.mixin({
    methods: {
        deepCloneArray: function(arr) {
            return JSON.parse(JSON.stringify(arr));
        }
    }
})

new Vue({
    el: '#app',
    router
});
