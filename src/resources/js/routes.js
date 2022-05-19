/* eslint-disable global-require */
import VueRouter from 'vue-router';

const routes = [
  {
    path: '/',
    component: require('./components/Home.vue').default,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
      ],
    }),
  },
  {
    path: '/profiles',
    component: require('./components/DetectionProfiles.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Detection Profiles',
          link: '/profiles',
        },
      ],
    }),
  },
  {
    path: '/profiles/create',
    component: require('./components/CreateDetectionProfile.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Detection Profiles',
          link: '/profiles',
        },
        {
          name: 'Create',
          link: '/profiles/create',
        },
      ],
    }),
  },
  {
    path: '/profiles/:id/edit',
    component: require('./components/CreateDetectionProfile.vue').default,
    props: true,
    meta: (route) => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Detection Profiles',
          link: '/profiles',
        },
        {
          name: 'Edit',
          link: `/profiles/${route.params.id}/edit`,
        },
      ],
    }),
  },
  {
    path: '/profiles/:id/automations',
    component: require('./components/EditProfileAutomations.vue').default,
    props: true,
    meta: (route) => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Detection Profiles',
          link: '/profiles',
        },
        {
          name: 'Automations',
          link: `/profiles/${route.params.id}/automations`,
        },
      ],
    }),
  },
  {
    path: '/events',
    component: require('./components/DetectionEvents.vue').default,
    props: (route) => ({ ...route.query, ...route.params }),
    name: 'DetectionEvents',
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Detection Events',
          link: '/events',
        },
      ],
    }),
  },
  {
    path: '/events/:id',
    component: require('./components/ShowDetectionEvent.vue').default,
    props: (route) => ({ ...route.query, ...route.params }),
    meta: (route) => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Detection Events',
          link: '/events',
        },
        {
          name: route.params.id,
          link: '/events',
        },
      ],
    }),
  },
  {
    path: '/automations/folderCopy',
    component: require('./components/FolderCopyConfigs.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Automation Configs',
          link: '/automations/folderCopy',
        },
      ],
    }),
  },
  {
    path: '/automations/smbCifsCopy',
    component: require('./components/SmbCifsCopyConfigs.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Automation Configs',
          link: '/automations/smbCifsCopy',
        },
      ],
    }),
  },
  {
    path: '/automations/telegram',
    component: require('./components/TelegramConfigs.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Automation Configs',
          link: '/automations/telegram',
        },
      ],
    }),
  },
  {
    path: '/automations/webRequest',
    component: require('./components/WebRequestConfigs.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Automation Configs',
          link: '/automations/webRequest',
        },
      ],
    }),
  },
  {
    path: '/automations/mqttPublish',
    component: require('./components/MqttPublishConfigs.vue').default,
    props: true,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Automation Configs',
          link: '/automations/mqttPublish',
        },
      ],
    }),
  },
  {
    path: '/errors/',
    component: require('./components/Errors.vue').default,
  },
  {
    path: '/deepstackLogs/',
    component: require('./components/DeepstackLogs.vue').default,
  },
];

export default new VueRouter({
  mode: 'history',
  linkExactActiveClass: 'is-active',
  routes,
});
