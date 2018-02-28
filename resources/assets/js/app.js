import Vue from 'vue';
import VueRouter from 'vue-router';
import store from './store/store';

Vue.use(VueRouter);

Vue.component('messagerie', require('./components/MessagerieComponent'));
import messages from './components/MessagesComponent';

let $conversations = document.querySelector('#messagerie');

if ($conversations) {
    const routes = [
        {path: '/'},
        {path: '/:id', name: 'conversation', component: messages},
    ];

    const router = new VueRouter({
        mode: 'history',
        routes,
        base: $conversations.dataset.base
    });

    new Vue({
        el: '#messagerie',
        store,
        router
    });
}
