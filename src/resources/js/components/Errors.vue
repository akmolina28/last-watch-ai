<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li class="is-active"><a href="#" aria-current="page">Errors</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Automation Errors
            </template>
            <template v-slot:subtitle>
                Exceptions caught during automation
            </template>
        </title-header>

        <b-table
            hoverable
            mobile-cards
            :data="errors"
            :loading="loading"
            class="mb-3">

            <b-table-column field="automation_config_type" label="Automation Type" v-slot="props">
                {{ props.row.automation_config_type }}
            </b-table-column>

            <b-table-column field="image_file_name" label="Event" v-slot="props">
                {{ props.row.image_file_name }}
            </b-table-column>

            <b-table-column field="response_text" label="Error" v-slot="props">
                {{ props.row.response_text }}
            </b-table-column>

            <b-table-column field="created_at" label="Occurred At" v-slot="props">
                {{ props.row.created_at | dateStrRelative }}
            </b-table-column>

        </b-table>

        <pagination :data="meta" @pagination-change-page="getData" :limit="5">
        </pagination>
    </div>
</template>

<script>
    export default {
        name: "Errors",

        data() {
            return {
                errors: [],
                meta: {},
                loading: true
            }
        },

        mounted() {
            this.getData();
        },

        methods: {
            getData(page = 1) {
                this.loading = true;

                axios.get('/api/errors?page=' + page)
                    .then(response => {
                        this.errors = response.data.data;
                        this.meta = response.data.meta;
                        this.isEmpty = false;
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
