import Vue from 'vue';
import VueRouter from 'vue-router';
import VueResource from 'vue-resource';
import App from './App.vue';

import Index from './components/index.vue';
import Mediafire from './components/mediafire.vue';
import Info from './components/info.vue';
import Etc from './components/etc.vue';
import NotFound from './components/not-found.vue';
import TrEmpty from './components/tr-empty.vue';

Vue.use(VueRouter);
Vue.use(VueResource);

// зарегистрируем компоненты глобально
Vue.component('AppTrEmpty', TrEmpty);

var router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/admin', component: Index },
        { path: '/admin/mediafire', component: Mediafire },
        { path: '/admin/info', component: Info },
        { path: '/admin/etc', component: Etc },
        { path: '/admin/*', component: NotFound }
    ]
});

var vm = new Vue({
    el: '#app', // это только у родителя
    http: {
        root: '/admin'
    },
    router,
    render: h => h(App)
});