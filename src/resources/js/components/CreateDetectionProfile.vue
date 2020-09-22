<template>
    <div class="component-wrapper">
        <h1 class="heading has-text-weight-bold is-size-4">New Detection Profile</h1>

        <form @submit="checkForm" method="POST" action="/profiles" enctype="multipart/form-data" ref="profileForm">

            <div class="field">
                <label class="label" for="name">Name</label>

                <div class="control">
                    <input
                        v-model="name"
                        class="input"
                        type="text"
                        name="name"
                        id="name">
                </div>
            </div>

            <div class="field">
                <label class="label" for="file_pattern">File Pattern</label>

                <div class="control">
                    <input
                        v-model="file_pattern"
                        class="input"
                        type="text"
                        name="file_pattern"
                        id="file_pattern">
                </div>
            </div>

            <div class="field mb-5">
                <label class="label" for="use_regex">Use Regex</label>

                <div class="control">
                    <input v-model="use_regex" type="checkbox" name="use_regex" id="use_regex">
                </div>
            </div>

            <h2 class="heading has-text-weight-bold is-size-5">Relevance</h2>

            <div class="field">
                <label class="label" for="object_classes[]">Object Classes</label>

                <div class="control select is-multiple">
                    <select v-model="objectClasses" name="object_classes[]" multiple>
                        <option v-for="objectClass in allObjectClasses" :value="objectClass">{{ objectClass }}</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label class="label" for="mask">Mask File</label>

                <div class="file">
                    <label class="file-label">
                        <input @change="evt=>mask = evt.target.files[0]" class="file-input" type="file" name="mask" accept="image/x-png">
                        <span class="file-cta">
                                <span class="file-icon">
                                    <i class="fas fa-upload"></i>
                                </span>
                                <span class="file-label">
                                    Choose a file…
                                </span>
                            </span>
                    </label>
                </div>
            </div>


            <div class="field">
                <label class="label" for="min_confidence">Minimum Confidence</label>

                <div class="control">
                    <input
                        v-model="min_confidence"
                        class="input"
                        type="text"
                        name="min_confidence"
                        id="min_confidence">
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button class="button is-link" type="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        name: "CreateDetectionProfile",
        data() {
            return {
                name: '',
                file_pattern: '',
                min_confidence: 0.45,
                use_regex: false,
                mask: {},
                objectClasses: [],
                errors: [],
                allObjectClasses: [],
                telegramConfigs: []
            }
        },

        created() {
            axios.get('/api/objectClasses').then(({data}) => this.allObjectClasses = data);
            axios.get('/api/telegram').then(({data}) => this.telegramConfigs = data);
        },

        methods:{
            submitForm: function () {
                let formData = new FormData(this.$refs.profileForm);
                axios.post('/api/profiles', formData).then(response => {
                    let id = response.data.data.id;
                    this.$router.push(`/profiles/${id}/subscriptions`);
                });
            },
            checkForm: function (e) {
                let confidenceMin = 0;
                let confidenceMax = 1;

                if (this.name &&
                    this.file_pattern &&
                    this.min_confidence &&
                    this.min_confidence >= confidenceMin &&
                    this.min_confidence < confidenceMax &&
                    this.objectClasses.length > 0) {

                    this.submitForm();
                }

                else {
                    this.errors = [];

                    if (!this.name) {
                        this.errors.push('Name required.');
                    }
                    if (!this.file_pattern) {
                        this.errors.push('File Pattern required.')
                    }
                    if (!this.min_confidence) {
                        this.errors.push('Age required.');
                    }
                    if (!(this.min_confidence >= confidenceMin && this.min_confidence < confidenceMax)) {
                        this.errors.push('Min Confidence must be between 0 and 1.');
                    }
                    if (this.objectClasses.length === 0) {
                        this.errors.push('Must select at least one Object Class.');
                    }

                    alert(this.errors.join(' '));
                }

                e.preventDefault();
            }
        }
    }
</script>

<style scoped>

</style>
