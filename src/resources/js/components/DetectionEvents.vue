<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li class="is-active"><a href="#" aria-current="page">Detection Events</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Detection Events
            </template>
            <template v-slot:subtitle>
                Search through all AI-tested images
            </template>
        </title-header>

        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <select v-model="filterOption" class="select" @change="filter(filterOption)">
                        <option value="all">All</option>
                        <option value="relevant">Relevant</option>
                        <option value="profile">Specific Profile</option>
                    </select>
                </div>
                <div class="level-item">
                    <select v-model="filterProfileId" v-if="filterOption === 'profile'" class="select" @change="getData()">
                        <option></option>
                        <option v-for="profile in allProfiles" :key="profile.id" :value="profile.id">
                            {{ profile.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
        <div id="wrapper">
            <table class="table">
                <thead>
                <tr>
                    <th>Matched File</th>
                    <th>Occurred</th>
                    <th>Relevant</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="event in laravelData.data" @click="$router.push(`/events/${event.id}`)" :key="event.id">
                        <td>{{ event.image_file_name }}</td>
                        <td :title="event.occurred_at | dateStr">
                            {{ event.occurred_at | dateStrRelative }}
                        </td>
                        <td>
                            <b-icon v-if="event.detection_profiles_count > 0" icon="check"></b-icon>
                        </td>
                    </tr>
                </tbody>
            </table>

            <pagination :data="laravelData.meta" @pagination-change-page="getData" :limit="5">
            </pagination>

        </div>
    </div>


</template>

<script>
    export default {
        name: "DetectionEvents",

        data() {
            return {
                laravelData: {},
                filterOption: 'relevant',
                filterProfileId: null,
                allProfiles: []
            }
        },

        mounted () {
            this.getProfiles();
            this.getData();
        },

        filters: {
            dateStr(value) {
                return moment.utc(value).local();
            },
            dateStrRelative(value) {
                return moment.utc(value).fromNow();
            }
        },

        methods: {
            filter(filterOption) {
                if (filterOption === 'all' || filterOption === 'relevant') {
                    this.filterProfileId = null;
                    this.getData();
                }
            },

            getProfiles() {
                axios.get('/api/profiles')
                    .then(response => {
                        this.allProfiles = response.data.data;
                    });
            },

            getData(page = 1) {
                let url = '/api/events?page=' + page;
                if (this.filterOption === 'relevant') {
                    url += '&relevant';
                }
                else if (this.filterProfileId) {
                    url += '&profileId=' + this.filterProfileId;
                }
                axios.get(url)
                    .then(response => {
                        this.laravelData = response.data;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
