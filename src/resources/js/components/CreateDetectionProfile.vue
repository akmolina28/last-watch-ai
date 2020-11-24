<template>
    <div class="component-wrapper">
        <div class="columns">
            <div class="column is-one-third">
                <nav class="breadcrumb" aria-label="breadcrumbs">
                    <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/profiles">Detection Profiles</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Create</a></li>
                    </ul>
                </nav>

                <title-header>
                    <template v-slot:title>
                        {{ pageTitle }}
                    </template>
                    <template v-slot:subtitle>
                        Configure the file watcher and AI
                    </template>
                </title-header>

                <form @submit.prevent="processForm">

                    <b-field label="Name">
                        <b-input v-model="name" placeholder="Unique name" name="name" :disabled="isEditing" required></b-input>
                    </b-field>

                    <b-field label="File Pattern" class="mb-6" grouped>
                        <b-input v-model="file_pattern" placeholder="Pattern match string" name="file_pattern" expanded required></b-input>
                        <b-switch v-model="use_regex">Use Regex</b-switch>
                    </b-field>

                    <p class="heading is-size-4">AI Settings</p>

                    <b-field label="Relevant Objects">
                        <b-select
                            required
                            multiple
                            native-size="8"
                            v-model="object_classes">
                            <option v-for="objectClass in allObjectClasses" :value="objectClass">{{ objectClass }}</option>
                        </b-select>
                        <div class="ml-2">
                            <b-switch v-model="is_negative">Negative Relevance</b-switch>
                        </div>
                    </b-field>

                    <b-field label="AI Confidence">
                        <b-input v-model="min_confidence"
                                 type="number"
                                 :min="0.01"
                                 :max="1"
                                 :step="0.01"></b-input>
                    </b-field>

                    <b-field class="mb-6">
                        <b-slider
                            v-model="min_confidence"
                            :min="0"
                            :max="1"
                            :step="0.01">
                        </b-slider>
                    </b-field>

                    <p class="heading is-size-4">Advanced Filtering</p>

                    <label class="label">Mask File</label>

                    <b-field class="file is-primary" :class="{'has-name': !!mask}">
                        <b-upload v-model="mask" class="file-label">
                            <span class="file-cta">
                                <b-icon class="file-icon" icon="upload"></b-icon>
                                <span class="file-label">Click to upload</span>
                            </span>
                            <span class="file-name" v-if="mask">
                                {{ mask.name }}
                            </span>
                        </b-upload>
                    </b-field>

                    <b-field label="Smart Filter" grouped>
                        <b-switch v-model="use_smart_filter"> </b-switch>

                        <b-field grouped label="Precision" label-position="on-border">
                            <b-input
                                v-model="smart_filter_precision"
                                type="number"
                                :min="0.01"
                                :max="0.99"
                                :step="0.01"
                                name="smart_filter_precision"
                                :disabled="!use_smart_filter">
                            </b-input>
                        </b-field>
                    </b-field>

                    <b-field class="mb-6">
                        <b-slider
                            v-model="smart_filter_precision"
                            :min="0.01"
                            :max="0.99"
                            :step="0.01"
                            :disabled="!use_smart_filter">
                        </b-slider>
                    </b-field>

                    <b-field position="is-right">
                        <a class="button is-primary is-outlined mr-2" type="submit" href="/profiles">Go back</a>

                        <b-button type="is-primary"
                            :loading="isSaving"
                            icon-left="save"
                            native-type="submit">
                            Save Profile
                        </b-button>
                    </b-field>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CreateDetectionProfile",
        props: ['id'],
        data() {
            return {
                name: '',
                file_pattern: '',
                min_confidence: 0.45,
                use_regex: false,
                use_smart_filter: false,
                smart_filter_precision: 0.65,
                mask: null,
                object_classes: [],
                allObjectClasses: [],
                isSaving: false,
                is_negative: false
            }
        },

        created() {
            axios.get('/api/objectClasses').then(({data}) => this.allObjectClasses = data);

            if (this.isEditing) {
                axios.get(`/api/profiles/${this.id}/edit`).then(({data}) => {
                    let profile = data.data;

                    this.name = profile.name;
                    this.file_pattern = profile.file_pattern;
                    this.min_confidence = parseFloat(profile.min_confidence);
                    this.use_regex = profile.use_regex;
                    this.object_classes = profile.object_classes;
                    this.use_smart_filter = profile.use_smart_filter;
                    this.smart_filter_precision = parseFloat(profile.smart_filter_precision);
                    this.is_negative = profile.is_negative;
                });
            }
        },

        computed: {
            isEditing() {
                return this.id != null;
            },
            pageTitle() {
                return this.id != null ? "Edit Profile" : "Create Profile";
            }
        },

        methods: {

            handleSubmitErrors: function(e) {
                let msg = e.response.data.message;

                if (e.response.data.errors) {
                    msg += ' ' + e.response.data.errors[Object.keys(e.response.data.errors)[0]][0];
                }

                this.$buefy.toast.open({
                    duration: 10000,
                    message: msg,
                    type: 'is-danger'
                })
            },

            processForm: function() {
                if (this.isSaving) {
                    return;
                }

                this.isSaving = true;

                let formData = {
                    'id': this.id,
                    'name': this.name,
                    'file_pattern': this.file_pattern,
                    'min_confidence': this.min_confidence,
                    'use_regex': this.use_regex,
                    'use_smart_filter': this.use_smart_filter,
                    'smart_filter_precision': this.smart_filter_precision,
                    'mask': this.mask,
                    'object_classes[]': this.object_classes,
                    'is_negative': this.is_negative
                }

                if (this.isEditing) {
                    axios.patch(`/api/profiles/${this.id}`, formData, {
                        headers: {
                            'enctype': 'multipart/form-data'
                        }
                    }).then(() => {
                        this.$router.push(`/profiles`);

                    }).catch(e => {
                        this.handleSubmitErrors(e);

                    }).finally(() => {
                        this.isSaving = false;
                    });
                }
                else {
                    axios.post('/api/profiles', formData, {
                        headers: {
                            'enctype': 'multipart/form-data'
                        }

                    }).then(response => {
                        let id = response.data.data.id;
                        this.$router.push(`/profiles/${id}/automations`);

                    }).catch(e => {
                        this.handleSubmitErrors(e);

                    }).finally(() => {
                        this.isSaving = false;
                    });
                }

            }
        }
    }
</script>

<style scoped>

</style>
