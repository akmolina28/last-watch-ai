<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/automations">Automations</a></li>
                <li class="is-active"><a href="#" aria-current="page">Telegram</a></li>
            </ul>
        </nav>

        <title-header>
            <template v-slot:title>
                Telegram
            </template>
            <template v-slot:subtitle>
                Send images to Telegram bots
            </template>
        </title-header>

        <div style="overflow-x:auto;" class="mb-3">
            <table class="table pb-5">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Token</th>
                        <th>Chat ID</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="telegramConfigs.length === 0" class="py-4 is-italic">
                        No bots set up yet. Add one below.
                    </tr>
                    <tr v-for="config in telegramConfigs">
                        <td>{{ config.name }}</td>
                        <td>{{ config.token }}</td>
                        <td>{{ config.chat_id }}</td>
                        <td>{{ config.created_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box is-6">
            <p class="subtitle">New Bot</p>
            <form @submit="checkForm" method="POST" action="/automations/telegram" ref="telegramForm">

                <div class="field">
                    <label class="label" for="name">Name</label>

                    <div class="control">
                        <input
                            v-model="name"
                            class="input"
                            type="text"
                            name="name"
                            id="name">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="token">Token</label>

                    <div class="control">
                        <input
                            v-model="token"
                            class="input"
                            type="text"
                            name="token"
                            id="token">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="chat_id">Chat ID</label>

                    <div class="control">
                        <input
                            v-model="chat_id"
                            class="input"
                            type="text"
                            name="chat_id"
                            id="chat_id">
                    </div>
                </div>

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        name: "TelegramConfigs",

        data() {
            return {
                telegramConfigs: [],
                name: '',
                chat_id: '',
                token: ''
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData() {
                axios.get('/api/automations/telegram').then(response => {
                    this.telegramConfigs  = response.data.data;
                });
            },

            submitForm: function () {
                let formData = new FormData(this.$refs.telegramForm);
                axios.post('/api/automations/telegram', formData)
                    .then(response => {
                        this.telegramConfigs.push(response.data.data);
                        this.name = '';
                        this.chat_id = '';
                        this.token = '';
                    })
                    .catch(err => {
                        console.log(err.response);
                    })
            },

            checkForm: function (e) {
                if (this.name &&
                    this.token &&
                    this.chat_id) {

                    this.submitForm();
                }

                else {
                    this.errors = [];

                    if (!this.name) {
                        this.errors.push('Name required.');
                    }
                    if (!this.token) {
                        this.errors.push('Token required.');
                    }
                    if (!this.chat_id) {
                        this.errors.push('Chat ID required.')
                    }

                    alert(this.errors.join(' '));
                }

                e.preventDefault();
            }
        }
    }
</script>

<style scoped>

</style>
