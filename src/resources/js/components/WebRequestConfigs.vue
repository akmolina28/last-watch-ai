<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/automations">Automations</a></li>
                <li class="is-active"><a href="#" aria-current="page">Web Request</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Web Request
            </template>
            <template v-slot:subtitle>
                Make Http GET requests
            </template>
        </title-header>

        <div class="responsive-table-wrapper mb-3">
            <table class="table pb-5">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Url</th>
                        <th>Post</th>
                        <th>Body</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="webRequestConfigs.length === 0" class="py-4 is-italic">
                        No configs set up yet. Add one below.
                    </tr>
                    <tr v-for="config in webRequestConfigs">
                        <td>{{ config.name }}</td>
                        <td>{{ config.url }}</td>
                        <td>
                            <b-icon v-if="config.is_post" icon="check"></b-icon>
                        </td>
                        <td>
                            <span :title="config.body_json">{{ config.body_json | truncate }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="box is-6">
            <p class="subtitle">New Config</p>

            <form @submit.prevent="processForm">
                <b-field label="Name">
                    <b-input v-model="name" placeholder="Unique name" name="name" required></b-input>
                </b-field>

                <b-field label="Headers (json)">
                    <b-input v-model="headersJson" name="headersJson" type="textarea"></b-input>
                </b-field>

                <b-field label="URL">
                    <b-input v-model="url" placeholder="http://some.site/api" name="url" required></b-input>
                </b-field>

                <b-field label="POST">
                    <b-switch v-model="isPost" @input="changePost"/>
                </b-field>

                <b-field label="Body (json)">
                    <b-input v-model="bodyJson" name="bodyJson" type="textarea" :disabled="!isPost"></b-input>
                </b-field>

                <b-button type="is-primary"
                          :loading="isSaving"
                          icon-left="save"
                          native-type="submit">
                    Save Profile
                </b-button>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        name: "WebRequestConfigs",

        data() {
            return {
                webRequestConfigs: [],
                name: '',
                url: '',
                isPost: false,
                bodyJson: '',
                headersJson: '',
                isSaving: false
            }
        },

        mounted() {
            this.getData();
        },

        filters: {
            truncate(value) {
                let val = value ?? '';
                let ret = val.substring(0, 19);
                if (val.length > 19) {
                    ret += '...';
                }
                return ret;
            }
        },

        methods: {
            getData() {
                axios.get('/api/automations/webRequest').then(response => {
                    this.webRequestConfigs  = response.data.data;
                });
            },

            changePost() {
                this.bodyJson = '';
            },

            processForm: function () {
                let formData = {
                    'name': this.name,
                    'url': this.url,
                    'is_post': this.isPost,
                    'body_json': this.bodyJson,
                    'headers_json': this.headersJson
                }

                this.isSaving = true;
                axios.post('/api/automations/webRequest', formData)
                    .then(response => {
                        this.webRequestConfigs.push(response.data.data);
                        this.name = '';
                        this.url = '';
                        this.isPost = false;
                        this.bodyJson = '';
                        this.headersJson = '';
                    })
                    .catch(err => {
                        console.log(err.response);
                    })
                    .finally(() => {
                        this.isSaving = false;
                    })
            },

            checkForm: function (e) {
                if (this.name &&
                    this.url) {

                    this.submitForm();
                }

                else {
                    this.errors = [];

                    if (!this.name) {
                        this.errors.push('Name required.');
                    }
                    if (!this.url) {
                        this.errors.push('URL required.');
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
