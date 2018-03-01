import Vue from 'vue';
import Vuex from 'vuex';
import Echo from 'laravel-echo';
import Favico from 'favico.js';

Vue.use(Vuex);

const favico = new Favico({animation: 'none'});
const audio = new Audio('./ringtone.m4a');
const title = document.title;
const updateTitle = function (conversations) {
    let unread = Object.values(conversations).reduce((acc, conversation) => conversation.unread + acc, 0);
    if (unread === 0) {
        document.title = title;
        favico.reset();
    } else {
        document.title = `${unread} ${title}`;
        favico.badge(unread);
    }
};

const fetchAPI = async function (url, options = {}) {
    let response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        ...options
    });

    if (response.ok)
        return response.json();
    else {
        throw await response.json();
    }
};

export default new Vuex.Store({
    strict: true,
    state: {
        user: null,
        conversations: [],
        openedConversations: []
    },
    actions: {
        loadConversations: async function ({commit}) {
            let res = await fetchAPI('/api/conversations');
            commit('addConversations', {conversations: res.conversations})
        },
        loadMessages: async function ({commit, getters, state, dispatch}, conversationId) {
            commit('openConversation', parseInt(conversationId, 10));

            if (!getters.conversation(conversationId).loaded) {
                let res = await fetchAPI('/api/conversations/' + conversationId);
                commit('addMessages', {messages: res.messages, id: conversationId, count: res.count});
                commit('markAsRead', conversationId);
            }

            getters.messages(conversationId).forEach((message) => {
                if (message.read_at === null && message.to_id === state.user) {
                    dispatch('markAsRead', message);
                }
            });
            commit('markAsRead', conversationId);
            updateTitle(state.conversations);
        },
        sendMessage: async function ({commit}, {content, userId}) {
            let res = await fetchAPI('/api/conversations/' + userId, {
                method: 'POST',
                body: JSON.stringify({content: content})
            });

            commit('addMessage', {message: res.message, id: userId});
        },
        loadPreviousMessages: async function ({commit, getters}, conversationId) {
            let message = getters.messages(conversationId)[0];
            if (message) {
                let url = '/api/conversations/' + conversationId + '?before=' + message.created_at;
                let res = await fetchAPI(url);
                commit('prependMessages', {messages: res.messages, id: conversationId});
            }
        },
        setUser: function (context, userId) {
            context.commit('setUser', userId);

            new Echo({
                broadcaster: 'pusher',
                key: '743b57682dbc975bcf49',
                cluster: 'ap1',
                encrypted: true
            }).private(`App.User.${userId}`)
                .listen('NewMessage', function (e) {
                    let fromId = e.message.from_id;
                    context.commit('addMessage', {message: e.message, id: fromId});
                    if (!context.state.openedConversations.includes(fromId) || document.hidden) {
                        context.commit('incrementUnread', fromId);
                        audio.play();
                        updateTitle(context.state.conversations)
                    } else {
                        context.dispatch('markAsRead', e.message)
                    }
                });
        },
        markAsRead: function ({commit}, message) {
            fetchAPI(`/api/messages/${message.id}`, {method: 'POST'});
            commit('readMessage', message);
        }
    },
    mutations: {
        readMessage: function (state, message) {
            let conversation = state.conversations[message.from_id];
            if (conversation && conversation.messages) {
                let msg = conversation.messages.find(m => m.id === message.id)
                if (msg) {
                    msg.read_at = (new Date()).toISOString();
                }
            }
        },
        incrementUnread: function (state, conversationId) {
            let conversation = state.conversations[conversationId];

            if (conversation) {
                conversation.unread++;
            }
        },
        openConversation: function (state, conversationId) {
            state.openedConversations = [conversationId];
        },
        setUser: function (state, userId) {
            state.user = userId
        },
        addConversations: function (state, {conversations}) {
            conversations.forEach(function (item) {
                let conversation = state.conversations[item.id] || {messages: [], count: 0};
                conversation = {...conversation, ...item};
                state.conversations = {...state.conversations, ...{[item.id]: conversation}};
            });
        },
        addMessages: function (state, {messages, id, count}) {
            let conversation = state.conversations[id] || {};
            conversation.messages = messages;
            conversation.count = count;
            conversation.loaded = true;
            state.conversations = {...state.conversations, ...{[id]: conversation}};
        },
        prependMessages: function (state, {messages, id}) {
            let conversation = state.conversations[id] || {};
            conversation.messages = [...messages, ...conversation.messages];
            state.conversations = {...state.conversations, ...{[id]: conversation}};
        },
        addMessage: function (state, {message, id}) {
            state.conversations[id].count++;
            state.conversations[id].messages.push(message);
        },
        markAsRead: function (state, id) {
            state.conversations[id].unread = 0;
        }
    },
    getters: {
        user: function (state) {
            return state.user;
        },
        conversations: function (state) {
            return state.conversations;
        },
        conversation: function (state) {
            return function (id) {
                return state.conversations[id] || {};
            }
        },
        messages: function (state) {
            return function (id) {
                let conversation = state.conversations[id];
                if (conversation && conversation.messages)
                    return conversation.messages;
                else
                    return [];
            }
        }
    }
})