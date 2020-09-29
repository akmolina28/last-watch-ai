<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/automations">Automations</a></li>
                <li class="is-active"><a href="#" aria-current="page">SMB/CIFS Copy</a></li>
            </ul>
        </nav>

        <title-header>
            <template v-slot:title>
                SMB/CIFS Copy
            </template>
            <template v-slot:subtitle>
                Upload images to a Samba share
            </template>
        </title-header>

        <div style="overflow-x:auto;" class="mb-3">
            <table class="table pb-5">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Service Name</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Remote Dest</th>
                        <th>Overwrite</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="smbCifsCopyConfigs.length === 0" class="py-4 is-italic">
                        No configs set up yet. Add one below.
                    </tr>
                    <tr v-for="config in smbCifsCopyConfigs">
                        <td>{{ config.name }}</td>
                        <td>{{ config.servicename }}</td>
                        <td>{{ config.user }}</td>
                        <td>{{ config.password }}</td>
                        <td>{{ config.remote_dest }}</td>
                        <td>
                            <i v-if="config.overwrite" class="fas fa-check"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box is-6">
            <p class="subtitle">New Config</p>
            <form @submit="checkForm" method="POST" action="/automations/smbCifsCopy" ref="smbCifsCopyForm">

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
                    <label class="label" for="servicename">Service Name</label>

                    <div class="control">
                        <input
                            v-model="servicename"
                            class="input"
                            type="text"
                            name="servicename"
                            id="servicename"
                            placeholder="//192.168.1.123/share">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="user">User Name</label>

                    <div class="control">
                        <input
                            v-model="user"
                            class="input"
                            type="text"
                            name="user"
                            id="user">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="password">Password</label>

                    <div class="control">
                        <input
                            v-model="password"
                            class="input"
                            type="text"
                            name="password"
                            id="password">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="remote_dest">Remote Dest</label>

                    <div class="control">
                        <input
                            v-model="remote_dest"
                            class="input"
                            type="text"
                            name="remote_dest"
                            id="remote_dest"
                            placeholder="/path/to/dest">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="overwrite">Overwrite</label>

                    <div class="control">
                        <input
                            v-model="overwrite"
                            class="checkbox"
                            type="checkbox"
                            name="overwrite"
                            id="overwrite">
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
        name: "SmbCifsCopyConfigs",

        data() {
            return {
                smbCifsCopyConfigs: [],
                name: '',
                servicename: '',
                user: '',
                password: '',
                remote_dest: '',
                overwrite: false
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData() {
                axios.get('/api/automations/smbCifsCopy').then(response => {
                    this.smbCifsCopyConfigs  = response.data.data;
                });
            },

            submitForm: function () {
                axios.post('/api/automations/smbCifsCopy', {
                    name: this.name,
                    servicename: this.servicename,
                    user: this.user,
                    password: this.password,
                    remote_dest: this.remote_dest,
                    overwrite: this.overwrite
                })
                    .then(response => {
                        this.smbCifsCopyConfigs.push(response.data.data);
                        this.name = '';
                        this.servicename = '';
                        this.user = '';
                        this.password = '';
                        this.remote_dest = '';
                        this.overwrite = false;
                    })
                    .catch(err => {
                        console.log(err.response);
                    })
            },

            checkForm: function (e) {
                if (this.name &&
                    this.servicename &&
                    this.user &&
                    this.password &&
                    this.remote_dest) {

                    this.submitForm();
                }

                else {
                    this.errors = [];

                    if (!this.name) {
                        this.errors.push('Name required.');
                    }
                    if (!this.servicename) {
                        this.errors.push('Service Name required.');
                    }
                    if (!this.user) {
                        this.errors.push('User Name required.');
                    }
                    if (!this.password) {
                        this.errors.push('Password required.');
                    }
                    if (!this.remote_dest) {
                        this.errors.push('Remote Dest required.');
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
