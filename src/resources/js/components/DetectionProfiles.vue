<template>
    <div class="component-wrapper">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li class="is-active"><a href="#" aria-current="page">Detection Profiles</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Detection Profiles
            </template>
            <template v-slot:subtitle>
                Configure the AI to run when certain image files are created
            </template>
        </title-header>

        <div class="mb-3">
            <a class="button is-info" href="/profiles/create">New Profile</a>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Pattern</th>
                    <th>Regex</th>
                    <th>Object Classes</th>
                    <th>Min Confidence</th>
                    <th>Automations</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="profile in laravelData.data">
                        <td>{{ profile.name }}</td>
                        <td>{{ profile.file_pattern }}</td>
                        <td>{{ profile.use_regex }}</td>
                        <td>{{ profile.object_classes.join('|') }}</td>
                        <td>{{ profile.min_confidence }}</td>
                        <td><router-link :to="`/profiles/${profile.id}/automations`">Automations</router-link></td>
                        <td>
                            <div class="dropdown is-hoverable">
                                <div class="dropdown-trigger">
                                    <button class="button" aria-haspopup="true" aria-controls="dropdown-menu2">
                                        <span>{{ profile.status }}</span>
                                        <span class="icon is-small">
                                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                                        </span>
                                    </button>
                                </div>
                                <div class="dropdown-menu" role="menu">
                                    <div class="dropdown-content">
                                        <a class="dropdown-item" @click="updateStatus(profile, 'active')">
                                            <p><strong>Active</strong></p>
                                        </a>
                                        <hr class="dropdown-divider">
                                        <a class="dropdown-item" @click="updateStatus(profile, 'inactive')">
                                            <p><strong>Inactive</strong></p>
                                        </a>
                                        <hr class="dropdown-divider">
                                        <a class="dropdown-item">
                                            <p><strong>As Scheduled</strong></p>
                                            <p>Under development</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <pagination :data="laravelData.meta" @pagination-change-page="getData"></pagination>

        </div>
    </div>
</template>

<script>
    export default {
        name: "DetectionProfiles",

        data() {
            return {
                laravelData: {}
            }
        },

        mounted () {
            this.getData();
        },

        methods: {
            getData(page = 1) {
                axios.get(`/api/profiles?page=${page}`)
                    .then(response => {
                        this.laravelData = response.data;
                    });
            },

            updateStatus(profile, status) {
                axios.put(`/api/profiles/${profile.id}/status`, {
                    'status': status
                })
                    .then(response => {
                        profile.status = status;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
