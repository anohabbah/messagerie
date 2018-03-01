import Vue from 'vue';
import VueRouter from 'vue-router';
import store from './store/store';
import messages from './components/MessagesComponent';

// window.io = require('socket.io-client');
window.Pusher = require('pusher-js');


Vue.use(VueRouter);

Vue.component('messagerie', require('./components/MessagerieComponent'));

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
