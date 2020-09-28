<template>
    <div class="component-container">
        <p class="title">Edit Automations</p>

        <div class="subtitle">{{ profile.name }}</div>

        <div class="columns">
            <div class="column is-one-third">

                <div v-if="automationSuccess" class="notification is-primary">
                    <button class="delete" @click="automationSuccess = false"></button>
                    automation updated!
                </div>

                <div v-for="type in configTypes" class="mb-5">
                    <h4 class="heading is-size-4">{{ type | headerize }}</h4>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subscribe</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="config in getConfigs(type)">
                                <td><input :checked="config.detection_profile_id != null" type="checkbox" @change="updateAutomationConfig($event, config.id, config.type)"></td>
                                <td>{{ config.name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <a class="button" href="/profiles">Done</a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "EditProfileAutomations",

        props: ['id'],

        data() {
            return {
                automationConfigs: [],
                profile: {},
                automationSuccess: false
            }
        },

        computed: {
            profileId() {
                return parseInt(this.id);
            },
            configTypes() {
                let types = [];
                this.automationConfigs.forEach(function (automationConfig) {
                    if (!types.includes(automationConfig.type)) {
                        types.push(automationConfig.type);
                    }
                });
                return types;
            }
        },

        filters: {
            headerize(value) {
                let ret = value ?? '';
                if (ret.indexOf('_config') > 0) {
                    ret = ret.substring(0, ret.indexOf('_config'));
                }
                ret = ret.replace(/_/g, ' ');
                return ret;
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getConfigs(type) {
                return this.automationConfigs.filter(c => c.type === type);
            },

            getData() {
                axios.get(`/api/profiles/${this.id}`).then(response => this.profile = response.data.data);
                axios.get(`/api/profiles/${this.id}/automations`).then(response => this.automationConfigs = response.data.data);
            },

            updateAutomationConfig(event, id, type) {
                this.automationSuccess = false;
                let checked = event.target.checked;

                let formData = new FormData();
                formData.append('id', id);
                formData.append('type', type);
                formData.append('value', checked);

                axios.post(`/api/profiles/${this.id}/automations`, formData)
                    .then(() => this.automationSuccess = true)
                    .catch(err => {
                        event.target.checked = !checked;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
