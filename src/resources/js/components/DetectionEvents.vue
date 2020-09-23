<template>
    <div class="component-container">
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
                        <td>{{ event.occurred_at }}</td>
                        <td>
                            <i v-if="event.detection_profiles_count > 0" class='fas fa-check'></i>
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
                laravelData: {}
            }
        },

        mounted () {
            this.getData();
        },

        methods: {
            getData(page = 1) {
                axios.get(`/api/events?page=${page}`)
                    .then(response => {
                        this.laravelData = response.data;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
