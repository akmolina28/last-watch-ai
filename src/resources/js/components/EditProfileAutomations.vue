<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/profiles">Detection Profiles</a></li>
                <li><a :href="'/profiles/' + id">{{ profile.name }}</a></li>
                <li class="is-active"><a href="#" aria-current="page">Automations</a></li>
            </ul>
        </nav>

        <title-header>
            <template v-slot:title>
                Profile Automations
            </template>
            <template v-slot:subtitle>
                Do something when <strong>{{ profile.name }}</strong> is triggered
            </template>
        </title-header>

        <div class="columns">
            <div class="column is-one-third">

                <p v-if="configTypes.length === 0">
                    No automations have been set up yet.
                </p>

                <div v-for="type in configTypes" class="box mb-5" style="overflow-x:auto;">
                    <h4 class="heading is-size-4">{{ type | headerize }}</h4>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subscribe</th>
                                <th>High Priority</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="config in getConfigs(type)">
                                <td><input v-model="config.isSubscribed" type="checkbox" @change="subscribeChange(config)"></td>
                                <td><input v-model="config.isHighPriority" type="checkbox" @change="highPriorityChange(config)"></td>
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
                profile: {}
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
                axios.get(`/api/profiles/${this.id}/automations`).then(response => {
                    let configs = response.data.data;
                    configs.forEach(config => {
                        config.isSubscribed = config.detection_profile_id != null;
                        config.isHighPriority = config.is_high_priority === 1;
                    });
                    this.automationConfigs = configs;
                });
            },

            subscribeChange(config) {
                if (!config.isSubscribed) {
                    config.isHighPriority = false;
                }
                this.updateAutomationConfig(config);
            },

            highPriorityChange(config) {
                if (config.isHighPriority) {
                    config.isSubscribed = true;
                }
                this.updateAutomationConfig(config);
            },

            updateAutomationConfig(config) {
                console.log(config);
                axios.put(`/api/profiles/${this.id}/automations`, {
                    'id': config.id,
                    'type': config.type,
                    'value': config.isSubscribed,
                    'is_high_priority': config.isHighPriority
                })
                    .then(() => {
                        this.$buefy.toast.open({
                            message: 'Automations saved!',
                            type: 'is-success'
                        });
                    })
                    .catch(() => {
                        this.$buefy.toast.open({
                            message: 'Something went wrong. Refresh and try again.',
                            type: 'is-danger'
                        });
                        config.isSubscribed = false;
                        config.isHighPriority = false;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
