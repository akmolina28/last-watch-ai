<template>
    <div class="component-wrapper">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li class="is-active"><a href="#" aria-current="page">Detection Profiles</a></li>
            </ul>
        </nav>
        <div>
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Pattern</th>
                    <th>Regex</th>
                    <th>Object Classes</th>
                    <th>Min Confidence</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="profile in laravelData.data">
                        <td>{{ profile.name }}</td>
                        <td>{{ profile.file_pattern }}</td>
                        <td>{{ profile.use_regex }}</td>
                        <td>{{ profile.object_classes.join('|') }}</td>
                        <td>{{ profile.min_confidence }}</td>
                    </tr>
                </tbody>
            </table>

            <pagination :data="laravelData.meta" @pagination-change-page="getData"></pagination>

            <a class="button" href="/profiles/create">New Profile</a>
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
            }
        }
    }
</script>

<style scoped>

</style>
