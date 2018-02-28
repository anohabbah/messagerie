<template>
    <div>
        <div class="row">
            <div class="col-md-10" :class="isMe ? 'col-md-offset-2 text-right' : ''">
                <p>
                    <strong>{{ name }}</strong>&nbsp;<span class="text-muted">{{ ago }}</span><br>
                    {{ message.content }}
                </p>
            </div>
        </div>
        <hr>
    </div>
</template>

<script>
    import moment from 'moment';
    moment.locale('fr');

    export default {
        props: {
            message: Object,
            user: Number
        },
        computed: {
            isMe: function () {
                return this.user === this.message.from_id;
            },
            name: function () {
                return this.isMe ? 'Moi' : this.message.from.name;
            },
            ago: function() {
                return moment(this.message.created_at).fromNow();
            }
        }
    }
</script>
