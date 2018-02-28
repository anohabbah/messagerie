import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

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
        conversations: []
    },
    actions: {
        loadConversations: async function ({commit}) {
            let res = await fetchAPI('/api/conversations');
            commit('addConversations', {conversations: res.conversations})
        },
        loadMessages: async function ({commit, getters}, conversationId) {
            if (!getters.conversation(conversationId).loaded) {
                let res = await fetchAPI('/api/conversations/' + conversationId);
                commit('addMessages', {messages: res.messages, id: conversationId, count: res.count});
                commit('markAsRead', conversationId);
            }
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
        }
    },
    mutations: {
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