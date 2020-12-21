import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        component: require('./components/Home').default,
    },
    {
        path: '/profiles',
        component: require('./components/DetectionProfiles').default,
        props: true
    },
    {
        path: '/profiles/create',
        component: require('./components/CreateDetectionProfile').default,
        props: true
    },
    {
        path: '/profiles/:id/edit',
        component: require('./components/CreateDetectionProfile').default,
        props: true
    },
    {
        path: '/profiles/:id/automations',
        component: require('./components/EditProfileAutomations').default,
        props: true
    },
    {
        path: '/events/',
        component: require('./components/DetectionEvents').default,
        props: true
    },
    {
        path: '/events/:id',
        component: require('./components/ShowDetectionEvent').default,
        props: true
    },
    {
        path: '/automations/folderCopy/',
        component: require('./components/FolderCopyConfigs').default,
        props: true
    },
    {
        path: '/automations/smbCifsCopy/',
        component: require('./components/SmbCifsCopyConfigs').default,
        props: true
    },
    {
        path: '/automations/telegram/',
        component: require('./components/TelegramConfigs').default,
        props: true
    },
    {
        path: '/automations/webRequest/',
        component: require('./components/WebRequestConfigs').default,
        props: true
    },
    {
        path: '/automations/mqttPublish/',
        component: require('./components/MqttPublishConfigs').default,
        props: true
    },
    {
        path: '/errors/',
        component: require('./components/Errors').default
    },
    {
        path: '/deepstackLogs/',
        component: require('./components/DeepstackLogs').default
    }
]

export default new VueRouter({
    mode: 'history',
    linkExactActiveClass: 'is-active',
    routes
});
