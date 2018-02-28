<template>
    <div class="card">
        <div class="card-header">{{ name }}</div>
        <div class="card-body messagerie__body">
            <message :message="message" v-for="message in messages" :key="message.id" :user="user"/>

            <form>
                <div class="form-group">
                    <textarea :class="{'form-control': true, 'is-invalid': errors['content']}"
                              v-model="content" placeholder="Entrez votre message..."
                              @keypress.enter="sendMessage"></textarea>
                    <div class="invalid-feedback" v-if="errors['content']">{{ errors['content'].join(',') }}</div>
                </div>
            </form>

            <div class="messagerie__loading" v-if="loading">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import message from './MessageComponent';
    import {mapGetters} from 'vuex';

    export default {
        data() {
            return {
                content: '',
                errors: {},
                loading: false,
                $message: null
            }
        },
        computed: {
            ...mapGetters(['user']),
            messages: function () {
                return this.$store.getters.messages(this.$route.params.id)
            },
            name: function () {
                return this.$store.getters.conversation(this.$route.params.id).name
            },
            count: function () {
                return this.$store.getters.conversation(this.$route.params.id).count
            }
        },
        mounted() {
            this.loadMessages();
            this.$message = this.$el.querySelector('.messagerie__body');
        },
        watch: {
            '$route.params.id': function () {
                this.loadMessages();
            }
        },
        components: {message},
        methods: {
            async onScroll() {
                if (this.$message.scrollTop === 0) {
                    this.loading = true;
                    this.$message.removeEventListener('scroll', this.onScroll());
                    let previousHeight = this.$message.scrollHeight;
                    await this.$store.dispatch('loadPreviousMessages', this.$route.params.id);
                    this.$nextTick(() => this.$message.scrollTop = this.$message.scrollHeight - previousHeight);
                    if (this.messages < this.count) {
                        this.$message.addEventListener('scroll', this.onScroll());
                    }
                    this.loading = false;
                }
            },
            scrollBot() {
                this.$nextTick(() => {
                    this.$message.scrollTop = this.$message.scrollHeight;
                })
            },
            loadMessages: async function () {
                await this.$store.dispatch('loadMessages', this.$route.params.id);
                this.scrollBot();
                if (this.messages < this.count) {
                    this.$message.addEventListener('scroll', this.onScroll());
                }
            },
            sendMessage: async function (e) {
                if (!e.shiftKey) {
                    this.loading = true;
                    this.errors = {};
                    e.preventDefault();

                    try {
                        await this.$store.dispatch('sendMessage', {
                            content: this.content,
                            userId: this.$route.params.id
                        });
                        this.content = '';
                        this.scrollBot();
                    } catch (e) {
                        if (e.errors)
                            this.errors = e.errors;
                        else
                            console.error(e);
                    }
                }
                this.loading = false;
            }
        }
    }
</script>