/* eslint-disable global-require */
import VueRouter from 'vue-router';

const routes = [
  {
    name: 'Home',
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
    name: 'DetectionProfiles',
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
    name: 'CreateDetectionProfile',
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
    name: 'EditDetectionProfile',
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
    name: 'EditProfileAutomations',
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
    name: 'DetectionEvents',
    path: '/events',
    component: require('./components/DetectionEvents.vue').default,
    props: (route) => ({ ...route.query, ...route.params }),
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
    name: 'EventViewer',
    path: '/events/viewer',
    component: require('./components/EventViewer.vue').default,
    props: (route) => ({ ...route.query, ...route.params }),
    meta: () => ({
      hideNavbar: true,
    }),
  },
  {
    name: 'ShowDetectionEvent',
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
    name: 'FolderCopyConfigs',
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
    name: 'SmbCifsCopyConfigs',
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
    name: 'TelegramConfigs',
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
    name: 'WebRequestConfigs',
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
    name: 'MqttPublishConfigs',
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
    name: 'Errors',
    path: '/errors/',
    component: require('./components/Errors.vue').default,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'AutomationErrors',
          link: '/errors',
        },
      ],
    }),
  },
  {
    name: 'DeepstackLogs',
    path: '/deepstackLogs/',
    component: require('./components/DeepstackLogs.vue').default,
    meta: () => ({
      breadcrumbs: [
        {
          name: 'Home',
          link: '/',
        },
        {
          name: 'Deepstack Logs',
          link: '/deepstackLogs',
        },
      ],
    }),
  },
];

export default new VueRouter({
  mode: 'history',
  linkExactActiveClass: 'is-active',
  routes,
  scrollBehavior: (to, from, savedPosition) => {
    if (to.name === 'DetectionEvents' && savedPosition) {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve(savedPosition);
        }, 250);
      });
    }
    if (to.name === from.name) {
      return {};
    }
    return { x: 0, y: 0 };
  },
});
