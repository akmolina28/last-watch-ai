<template>
    <div class="component-container">
        <p class="title">Edit Subscriptions</p>

        <div class="subtitle">{{ profile.name }}</div>

        <div class="columns">
            <div class="column is-one-third">

                <div v-if="subscriptionSuccess" class="notification is-primary">
                    <button class="delete" @click="subscriptionSuccess = false"></button>
                    Subscription updated!
                </div>

                <div v-if="webRequestConfigs.length > 0" class="box">
                    <p class="subtitle">Web Request</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Subscribe</th>
                            <th>Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="config in webRequestConfigs">
                            <td><input :checked="isSubscribed(config)" type="checkbox" @change="updateSubscription($event, config.id, 'webRequest')"></td>
                            <td>{{ config.name }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="folderCopyConfigs.length > 0" class="box">
                    <p class="subtitle">Folder Copy</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Subscribe</th>
                            <th>Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="config in folderCopyConfigs">
                            <td><input :checked="isSubscribed(config)" type="checkbox" @change="updateSubscription($event, config.id, 'folderCopy')"></td>
                            <td>{{ config.name }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="telegramConfigs.length > 0" class="box">
                    <p class="subtitle">Telegram</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subscribe</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="config in telegramConfigs">
                                <td><input :checked="isSubscribed(config)" type="checkbox" @change="updateSubscription($event, config.id, 'telegram')"></td>
                                <td>{{ config.name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "EditProfileSubscriptions",

        props: ['id'],

        data() {
            return {
                profile: {},
                telegramConfigs: [],
                webRequestConfigs: [],
                folderCopyConfigs: [],
                subscriptionSuccess: false
            }
        },

        computed: {
            profileId() {
                return parseInt(this.id);
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData() {
                axios.get(`/api/profiles/${this.id}`).then(response => this.profile = response.data.data);
                axios.get('/api/telegram').then(response => this.telegramConfigs = response.data.data);
                axios.get('/api/webRequest').then(response => this.webRequestConfigs = response.data.data);
                axios.get('/api/folderCopy').then(response => this.folderCopyConfigs = response.data.data);
            },

            isSubscribed(config) {
                let subscribed = false;
                config.detection_profiles.forEach(p => {
                    if (p.id === this.profileId) subscribed = true;
                });
                return subscribed;
            },

            updateSubscription(event, id, type) {
                this.subscriptionSuccess = false;
                let checked = event.target.checked;

                let formData = new FormData();
                formData.append('id', id);
                formData.append('type', type);
                formData.append('value', checked);

                axios.post(`/api/profiles/${this.id}/subscriptions`, formData)
                    .then(() => this.subscriptionSuccess = true)
                    .catch(err => {
                        event.target.checked = !checked;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
