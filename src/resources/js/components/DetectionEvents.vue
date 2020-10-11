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
                    <div class="field">
                        <b-switch v-model="relevant" @input="getData()">
                            Relevant Only
                        </b-switch>
                    </div>
                </div>

                <div class="level-item">
                    <b-select
                        v-model="filterProfileId"
                        @input="getData()"
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

                <div class="level-item">
                    <button class="button" :disabled="eventsLoading" @click="getData()">
                        <span>
                            Refresh
                        </span>
                        <b-icon icon="sync"></b-icon>
                    </button>
                </div>
            </div>
        </div>

        <b-table
            hoverable
            :selected.sync="selected"
            mobile-cards
            :data="isEmpty ? [] : events"
            :loading="eventsLoading"
            @click="rowClick"
            class="mb-3">

            <b-table-column field="image_file_name" label="Image File" v-slot="props">
                {{ props.row.image_file_name }}
            </b-table-column>

            <b-table-column field="occurred_at" label="Occurred" v-slot="props">
                <span :title="props.row.occurred_at | dateStr">
                    {{ props.row.occurred_at | dateStrRelative }}
                </span>
            </b-table-column>

            <b-table-column field="relevant" label="Relevant" v-slot="props">
                <b-icon v-if="props.row.detection_profiles_count > 0" icon="check"></b-icon>
            </b-table-column>

        </b-table>

        <pagination :data="meta" @pagination-change-page="getData" :limit="5">
        </pagination>
    </div>


</template>

<script>
    export default {
        name: "DetectionEvents",

        data() {
            return {
                events: [],
                eventsLoading: false,
                meta: {},
                relevant: true,
                filterProfileId: null,
                allProfiles: [],
                isEmpty: true,
                selected: {}
            }
        },

        mounted () {
            this.getProfiles();
            this.getData();
        },

        methods: {
            rowClick(event) {
                window.open(`/events/${event.id}`, '_blank');
            },

            getProfiles() {
                axios.get('/api/profiles')
                    .then(response => {
                        this.allProfiles = response.data.data;
                    });
            },

            getData(page = 1) {
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
                        this.isEmpty = false;
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
