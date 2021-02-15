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

        <b-table :data="telegramConfigs">
            <template #empty>
                No automations set up yet. Use the form below.
            </template>
            <b-table-column field="name" label="Name" v-slot="props">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column field="token" label="Token" v-slot="props">
                {{ props.row.token }}
            </b-table-column>
            <b-table-column field="chat_id" label="Chat ID" v-slot="props">
                {{ props.row.chat_id }}
            </b-table-column>
            <b-table-column field="profile_count" label="Used By" v-slot="props">
                {{ props.row.detection_profiles.length }} profile(s)
            </b-table-column>
            <b-table-column field="delete" label="Delete" v-slot="props">
                <b-button icon-right="trash"
                          type="is-danger is-outlined"
                          :loading="props.row.isDeleting"
                          @click="deleteClick(props.row)">
                </b-button>
            </b-table-column>
        </b-table>

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
                    let configs = response.data.data;
                    configs.forEach(c => c.isDeleting = false);
                    this.telegramConfigs  = configs;
                });
            },

            deleteClick(config) {
                if (config.detection_profiles.length > 0) {
                    this.$buefy.dialog.confirm({
                        title: 'Confirm Delete',
                        message:
                            `Deleting this automation will automatically unsubscribe ${config.detection_profiles.length} Detection Profile(s).`,
                        confirmText: 'Delete',
                        type: 'is-danger',
                        hasIcon: true,
                        onConfirm: () => this.delete(config)
                    });
                }
                else {
                    this.delete(config);
                }
            },

            delete(config) {
                config.isDeleting = true;

                axios.delete(`/api/automations/telegram/${config.id}`)
                    .then(() => {
                        this.telegramConfigs = this.telegramConfigs.filter(c => {
                            return c.id !== config.id;
                        });
                    })
                    .catch(() => {
                        config.deleting = false;
                        this.$buefy.toast.open({
                            message: 'Something went wrong. Refresh and try again.',
                            type: 'is-danger'
                        });
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
                    .catch(error => {
                        let errors = error.response.data.errors;
                        if (errors && errors.name) {
                            this.$buefy.toast.open({
                                message: errors.name[0],
                                type: 'is-danger'
                            });
                        }
                        else {
                            this.$buefy.toast.open({
                                message: 'Something went wrong. Refresh and try again.',
                                type: 'is-danger'
                            });
                        }
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
