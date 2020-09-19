import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        redirect: '/profiles'
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
        path: '/events/',
        component: require('./components/DetectionEvents').default,
        props: true
    },
    {
        path: '/events/:id',
        component: require('./components/ShowDetectionEvent').default,
        props: true
    }
]

export default new VueRouter({
    mode: 'history',
    linkExactActiveClass: 'is-active',
    routes
});
