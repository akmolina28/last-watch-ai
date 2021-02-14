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

        <b-table :data="smbCifsCopyConfigs">
            <template #empty>
                No automations set up yet. Use the form below.
            </template>
            <b-table-column field="name" label="Name" v-slot="props">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column field="servicename" label="Service Name" v-slot="props">
                {{ props.row.servicename }}
            </b-table-column>
            <b-table-column field="user" label="Username" v-slot="props">
                {{ props.row.user }}
            </b-table-column>
            <b-table-column field="password" label="Password" v-slot="props">
                {{ props.row.password }}
            </b-table-column>
            <b-table-column field="remote_dest" label="Remote Dest" v-slot="props">
                {{ props.row.remote_dest }}
            </b-table-column>
            <b-table-column field="overwrite" label="Overwrite?" v-slot="props">
                <b-icon v-if="props.row.overwrite" icon="check"></b-icon>
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
                    let configs = response.data.data;
                    configs.forEach(c => c.isDeleting = false);
                    this.smbCifsCopyConfigs  = configs;
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

                axios.delete(`/api/automations/smbCifsCopy/${config.id}`)
                    .then(() => {
                        this.smbCifsCopyConfigs = this.smbCifsCopyConfigs.filter(c => {
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
