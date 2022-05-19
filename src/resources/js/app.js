import axios from 'axios';
import Buefy from 'buefy';
import moment from 'moment';
import Vue from 'vue';
import VueClipboard from 'vue-clipboard2';
import VueRouter from 'vue-router';

import App from './components/Shared/App.vue';
import mixins from './mixins';
import router from './routes';
import TitleHeader from './components/TitleHeader.vue';

window._ = require('lodash');
require('typeface-nunito');
const VuePaginator = require('laravel-vue-bulma-paginator');
require('./filters');

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.moment = moment;

window.Vue = Vue;
Vue.use(VueRouter);
Vue.use(Buefy, {
  defaultIconPack: 'fas',
});
Vue.use(VueClipboard);

Vue.component('pagination', VuePaginator);
Vue.component('title-header', TitleHeader);

Vue.mixin(mixins);

// eslint-disable-next-line no-new
new Vue({
  el: '#app',
  router,
  components: {
    App,
  },
});
