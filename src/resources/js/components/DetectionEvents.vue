<template>
    <div class="container">
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

        <div class="is-flex-tablet">
            <div class="mt-2 mr-2 mb-2">
                <b-switch v-model="relevant" @input="getEvents()">
                    Relevant Only
                </b-switch>
            </div>
            <div class="mr-2 mb-2">
                <b-select
                    v-model="filterProfileId"
                    @input="getEvents()"
                    class="is-primary is-outlined"
                    icon="eye">
                    <option :value="null" :key="-1">Any Profile</option>
                    <option
                        v-for="profile in allProfiles"
                        :value="profile.id"
                        :key="profile.id">
                        {{ profile.name }}
                    </option>
                </b-select>
            </div>
            <div class="mr-2 mb-2">
                <b-button type="is-primary" :loading="eventsLoading" @click="getEvents()">
                    <span>
                        Refresh
                    </span>
                    <b-icon icon="sync"></b-icon>
                </b-button>
            </div>
        </div>

        <b-table
            v-if="events"
            hoverable
            mobile-cards
            :data="events"
            :loading="eventsLoading"
            class="mb-3">

            <b-table-column field="thumbnail" v-slot="props">
                <img :src="props.row.thumbnail_path" alt="thumbnail" height="90" width="170"/>
            </b-table-column>

            <b-table-column field="image_file_name" label="Image File" v-slot="props">
                <span class="is-hidden-tablet" style="max-width:210px;overflow-x:hidden;text-overflow:ellipsis;">
                    {{ props.row.image_file_name }}
                </span>
                <span class="is-hidden-mobile">
                    {{ props.row.image_file_name }}
                </span>
            </b-table-column>

            <b-table-column field="occurred_at" label="Occurred" v-slot="props">
                <span :title="props.row.occurred_at | dateStr">
                    {{ props.row.occurred_at | dateStrRelative }}
                </span>
            </b-table-column>

            <b-table-column field="relevant" label="Relevant" v-slot="props">
                <b-icon v-if="props.row.detection_profiles_count > 0" icon="check"></b-icon>
            </b-table-column>

            <b-table-column v-slot="props">
                <router-link :to="`/events/${props.row.id}`">Details</router-link>
            </b-table-column>
        </b-table>

        <pagination :data="meta" @pagination-change-page="getEvents" :limit="5">
        </pagination>
    </div>

</template>

<script>
    export default {
        name: "DetectionEvents",

        data() {
            return {
                events: null,
                eventsLoading: false,
                meta: {},
                relevant: true,
                filterProfileId: null,
                allProfiles: [],
            }
        },

        mounted () {
            this.getData();
        },

        activated () {
            if (!this.events) {
                this.getData();
            }
        },

        methods: {
            getData() {
                this.getEvents();
                this.getProfiles();
            },

            getProfiles() {
                axios.get('/api/profiles')
                    .then(response => {
                        this.allProfiles = response.data.data;
                    });
            },

            getEvents(page = 1) {
                this.eventsLoading = true;
                let url = '/api/events?page=' + page;
                if (this.relevant) {
                    url += '&relevant';
                }
                if (this.filterProfileId) {
                    url += '&profileId=' + this.filterProfileId;
                }
                axios.get(url)
                    .then(response => {
                        this.events = response.data.data;
                        this.meta = response.data.meta;
                    })
                    .finally(() => {
                        this.eventsLoading = false;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
