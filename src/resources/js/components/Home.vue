<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li class="is-active"><a href="#" aria-current="page">Home</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Last Watch AI
            </template>
            <template v-slot:subtitle>
                Computer vision-based automations
            </template>
        </title-header>

        <div class="columns" style="margin-left:-0.75rem;">
            <div class="column is-one-third">
                <p class="title is-size-4">Latest Event</p>
                <p class="subtitle is-size-5">{{ event.occurred_at | dateStrRelative }}</p>

                <a :href="`events/${event.id}`">
                    <img alt="Event Image" :src="'storage/' + event.image_file_name"/>
                </a>

                <p class="is-size-5"><strong>Relevant Events Today:</strong> {{ statistics.relevant_events }}</p>
                <p class="is-size-6">Total Events Today: {{ statistics.total_events }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Home",

        data() {
            return {
                event: {},
                statistics: {}
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData() {
                axios.get('/api/events/latest')
                    .then((response) => {
                        this.event = response.data.data;
                    });

                axios.get('/api/statistics')
                    .then((response) => {
                        this.statistics = response.data.data;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
