<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/automations">Automations</a></li>
                <li class="is-active"><a href="#" aria-current="page">Folder Copy</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Folder Copy
            </template>
            <template v-slot:subtitle>
                Copy image files to a local folder
            </template>
        </title-header>

        <div style="overflow-x:auto;" class="mb-3">
            <table class="table pb-5">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Copy To</th>
                        <th>Overwrite</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="folderCopyConfigs.length === 0" class="py-4 is-italic">
                        No configs set up yet. Add one below.
                    </tr>
                    <tr v-for="config in folderCopyConfigs">
                        <td>{{ config.name }}</td>
                        <td>{{ config.copy_to }}</td>
                        <td>
                            <b-icon v-if="config.overwrite" icon="check"></b-icon>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="box is-6">
            <p class="subtitle">New Config</p>
            <form @submit="checkForm" method="POST" action="/automations/folderCopy" ref="folderCopyForm">

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
                    <label class="label" for="copy_to">Copy To</label>

                    <div class="control">
                        <input
                            v-model="copy_to"
                            class="input"
                            type="text"
                            name="copy_to"
                            id="copy_to">
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
        name: "FolderCopyConfigs",

        data() {
            return {
                folderCopyConfigs: [],
                name: '',
                copy_to: '',
                overwrite: false
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData() {
                axios.get('/api/automations/folderCopy').then(response => {
                    this.folderCopyConfigs  = response.data.data;
                });
            },

            submitForm: function () {
                axios.post('/api/automations/folderCopy', {
                    name: this.name,
                    copy_to: this.copy_to,
                    overwrite: this.overwrite
                })
                    .then(response => {
                        this.folderCopyConfigs.push(response.data.data);
                        this.name = '';
                        this.copy_to = '';
                        this.overwrite = false;
                    })
                    .catch(err => {
                        console.log(err.response);
                    })
            },

            checkForm: function (e) {
                if (this.name &&
                    this.copy_to) {

                    this.submitForm();
                }

                else {
                    this.errors = [];

                    if (!this.name) {
                        this.errors.push('Name required.');
                    }
                    if (!this.copy_to) {
                        this.errors.push('Copy To required.');
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
