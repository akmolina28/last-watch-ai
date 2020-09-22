<template>
    <div class="component-container">
        <h1 class="title">Web Request Configs</h1>

        <table class="table pb-5">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Url</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="webRequestConfigs.length === 0" class="py-4 is-italic">
                    No configs set up yet. Add one below.
                </tr>
                <tr v-for="config in webRequestConfigs">
                    <td>{{ config.name }}</td>
                    <td>{{ config.url }}</td>
                </tr>
            </tbody>
        </table>
        <div class="box is-6">
            <p class="subtitle">New Config</p>
            <form @submit="checkForm" method="POST" action="/webRequests" ref="webRequestForm">

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
                    <label class="label" for="url">URL</label>

                    <div class="control">
                        <input
                            v-model="url"
                            class="input"
                            type="text"
                            name="url"
                            id="url">
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
        name: "WebRequestConfigs",

        data() {
            return {
                webRequestConfigs: [],
                name: '',
                url: ''
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData() {
                axios.get('/api/webRequest').then(response => {
                    this.webRequestConfigs  = response.data.data;
                });
            },

            submitForm: function () {
                let formData = new FormData(this.$refs.webRequestForm);
                axios.post('/api/webRequest', formData)
                    .then(response => {
                        this.webRequestConfigs.push(response.data.data);
                        this.name = '';
                        this.url = '';
                    })
                    .catch(err => {
                        console.log(err.response);
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
