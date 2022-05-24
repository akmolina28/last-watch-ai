import moment from 'moment';
import Vue from 'vue';

Vue.filter('numberStr', (value) => value.toLocaleString('en-US'));

Vue.filter('dateStr', (value) => moment.utc(value).local());

Vue.filter('percentage', (value) => `${Math.round(value * 100)}%`);

Vue.filter(
  'dateStrRelative',
  (value) =>
    // eslint-disable-next-line implicit-arrow-linebreak
    moment
      .utc(value)
      .local()
      .fromNow(),
  // eslint-disable-next-line function-paren-newline
);
