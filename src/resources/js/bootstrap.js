window._ = require('lodash');


import Vue from 'vue';
window.Vue = Vue;

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Buefy from 'buefy'

// import { library } from '@fortawesome/fontawesome-svg-core';
// // internal icons
// import { fas } from "@fortawesome/free-solid-svg-icons";
// import { fab } from "@fortawesome/free-brands-svg-icons";
// import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
//
// library.add(fas);
// library.add(fab);
// Vue.component('vue-fontawesome', FontAwesomeIcon);


Vue.use(Buefy, {
    defaultIconPack: "fas"
});

import VueClipboard from 'vue-clipboard2';
Vue.use(VueClipboard);

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import moment from 'moment';
window.moment = moment;

require('typeface-nunito');
