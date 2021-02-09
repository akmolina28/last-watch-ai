<template>
    <div class="component-container">
        <div class="lw-breadcrumb">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/events">Detection Events</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Details</a></li>
                </ul>
            </nav>
            <nav class="lw-prev-next">
                <a v-if="prevEvent" :href="prevEvent.id">
                    ←
                </a>
                <span v-else>
                    ←
                </span>
                <a v-if="nextEvent" :href="nextEvent.id">
                    →
                </a>
                <span v-else>
                  →
                </span>
            </nav>
        </div>
        <title-header>
            <template v-slot:title>
                Detection Event
            </template>
            <template v-slot:subtitle>
                {{ event.image_file_name }}
            </template>
            <template v-slot:meta>
                <div class="control">
                    <div class="tags has-addons">
                        <span class="tag">Relevant</span>
                        <span v-if="relevant" class="tag is-success">Yes</span>
                        <span v-else class="tag is-success is-light">No</span>
                    </div>
                </div>
                <div class="control">
                    <div class="tags has-addons" @click="highlightAllPredictions">
                        <div class="tag">AI Predictions</div>
                        <a href="javascript:void(0);"
                           :class="`tag is-primary ${predictions.length === 0 ? 'is-light' : ''}`">
                            {{ predictions.length }}
                        </a>
                    </div>
                </div>
                <div class="control">
                    <div class="tags has-addons">
                        <span class="tag">Automations Run</span>
                        <a :class="'tag is-primary' + (automations === 0 ? ' is-light' : '')">
                            {{ automations }}
                        </a>
                    </div>
                </div>
                <div v-if="automationErrors > 0" class="control">
                    <div class="tags has-addons">
                        <span class="tag">Automation Errors</span>
                        <a href="/errors" class="tag is-danger">{{ automationErrors }}</a>
                    </div>
                </div>
            </template>
        </title-header>

        <div class="columns reverse-columns" style="margin-left:-0.75rem;">
            <div v-if="!loading" class="column is-one-third">
                <div class="content" :title="event.occurred_at | dateStr">
                    <span class="icon">
                        <b-icon icon="clock"></b-icon>
                    </span>
                    <span>{{ event.occurred_at | dateStrRelative }}</span>
                </div>

                <b-menu class="mb-4" :activable="false">
                    <b-menu-list label="Matched Profiles">
                        <b-menu-item
                            v-for="profile in event.pattern_matched_profiles"
                            @click="toggleSelectedProfile(profile)"
                            :key="profile.key"
                            :disabled="!profile.is_profile_active"
                            :active="profile.isSelected">

                            <template slot="label" slot-scope="props">
                                <p class="heading is-size-6">
                                    <b-icon :icon="getIcon(profile)"></b-icon>
                                    {{ profile.name }}
                                    <span v-if="!profile.is_profile_active">(inactive)</span>
                                </p>
                            </template>
                        </b-menu-item>
                    </b-menu-list>
                </b-menu>

                <div class="content">
                    <a :href="imageFile" download>
                        <span class="icon">
                            <b-icon icon="image"></b-icon>
                        </span>
                        <span>Download Image File</span>
                    </a>
                </div>
            </div>
            <div class="column is-two-thirds">
                <div>
                    <canvas id="event-snapshot" ref="event-snapshot" style="width:100%;"></canvas>
                </div>
                <div class="buttons">
                    <b-button v-for="prediction in highlightPredictions"
                              :class="getClassForPrediction(prediction)"
                              :key="prediction.id"
                              @click="soloHighlightPrediction(prediction)">
                        <b-icon :icon="getIconForPrediction(prediction)"></b-icon>
                        <span>
                            {{ prediction.confidence | percentage }}
                        </span>
                    </b-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    let Facade = require('facade.js');

    export default {
        name: "ShowDetectionEvent",

        props: ['id'],

        data() {
            return {
                event: {},
                nextEvent: null,
                prevEvent: null,
                profiles: [],
                selectedProfile: {},
                highlight: false,
                loading: true,
                highlightPredictions: [],
                soloPrediction: null,
                highlightMask: null
            }
        },

        mounted () {
            this.getData();
        },

        computed: {
            predictions() {
                if (this.event && this.event.ai_predictions) {
                    return this.event.ai_predictions;
                }
                return [];
            },
            automations() {
                if (this.event && this.event.automationResults) {
                    return this.event.automationResults.filter(a => !a.is_error).length;
                }
                return 0;
            },
            automationErrors() {
                if (this.event && this.event.automationResults) {
                    return this.event.automationResults.filter(a => a.is_error).length;
                }
                return 0;
            },
            imageWidth() {
                if (this.event && this.event.image_dimensions) {
                    return parseInt(this.event.image_dimensions.substring(0, this.event.image_dimensions.indexOf('x')));
                }
                return 0;
            },
            imageHeight() {
                if (this.event && this.event.image_dimensions) {
                    return parseInt(this.event.image_dimensions.substring(this.event.image_dimensions.indexOf('x') + 1));
                }
                return 0;
            },
            imageFile() {
                return this.event ? '/storage/' + this.event.image_file_name : '';
            },
            relevant() {
                if (this.event && this.event.ai_predictions) {
                    for (let i = 0; i < this.event.ai_predictions.length; i++) {
                        let prediction = this.event.ai_predictions[i];
                        for (let j = 0; j < prediction.detection_profiles.length; j++) {
                            if (!prediction.detection_profiles[j].is_masked &&
                                !prediction.detection_profiles[j].is_smart_filtered) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            }
        },

        filters: {
            percentage(value) {
                return (value * 100) + "%";
            }
        },

        methods: {
            soloHighlightPrediction(prediction) {
                if (this.soloPrediction && this.soloPrediction.id === prediction.id) {
                    this.soloPrediction = null;
                }
                else {
                    this.soloPrediction = prediction;
                }
                this.draw();
            },

            sortPredictions(predictions) {
                function compare(a, b) {
                    if (a.is_masked && !b.is_masked) {
                        return 1;
                    }
                    if (!a.is_masked && b.is_masked) {
                        return -1;
                    }
                    if (a.is_smart_filtered && !b.is_smart_filtered) {
                        return 1;
                    }
                    if (!a.is_smart_filtered && b.is_smart_filtered) {
                        return -1;
                    }
                    return 0;
                }

                return predictions.sort(compare);
            },
            getClassForPrediction(prediction) {
                if (prediction) {
                    let c = "";

                    if (prediction.is_masked) {
                        c = "is-danger";
                    }
                    else if (prediction.is_smart_filtered) {
                        c = "is-warning";
                    }
                    else {
                        c = "is-primary"
                    }

                    let isLight = false;
                    if (this.soloPrediction) {
                        isLight = prediction.id !== this.soloPrediction.id;
                    }

                    if (isLight) {
                        c += " is-light";
                    }

                    return c;
                }
                return "";
            },
            getIconForPrediction(prediction) {
                if (prediction) {
                    if (prediction.object_class === "person") {
                        return "user";
                    }
                    if (prediction.object_class === "car") {
                        return "car";
                    }
                    if (prediction.object_class === "truck") {
                        return "truck";
                    }
                    if (prediction.object_class === "bus") {
                        return "bus";
                    }
                    if (prediction.object_class === "bicycle") {
                        return "bicycle";
                    }
                    if (prediction.object_class === "motorcycle") {
                        return "motorcycle";
                    }
                    if (prediction.object_class === "plane") {
                        return "plane";
                    }
                    if (prediction.object_class === "train") {
                        return "train";
                    }
                    if (prediction.object_class === "bird") {
                        return "crow";
                    }
                    if (prediction.object_class === "cat") {
                        return "cat";
                    }
                    if (prediction.object_class === "dog") {
                        return "dog";
                    }
                    if (prediction.object_class === "horse") {
                        return "horse";
                    }
                    return "circle";
                }
                return "";
            },
            getData() {
                axios.get(`/api/events/${this.id}`)
                    .then(response => {
                        this.event = response.data.data;

                        this.event.ai_predictions.forEach(ap => {
                            ap.detection_profiles.forEach(dp => {
                                this.profiles.push(dp);
                            });
                        });

                        this.profiles = _.uniqBy(this.profiles, function(p) {
                            return p.id;
                        });

                        this.loading = false;
                        this.draw();
                    });

                axios.get(`/api/events/${this.id}/prev`)
                    .then(response => {
                        this.prevEvent = response.data.data;
                    });

                axios.get(`/api/events/${this.id}/next`)
                    .then(response => {
                        this.nextEvent = response.data.data;
                    });
            },

            hasUnmaskedUnfilteredPredictions(profile) {
                let predictions = this.getPredictionsForProfile(profile);

                for (let i = 0; i < predictions.length; i++) {
                    if (!predictions[i].is_masked && !predictions[i].is_smart_filtered) {
                        return true;
                    }
                }

                return false;
            },

            getPredictionsForProfile(profile) {
                let predictions = [];

                for (let i = 0; i < this.event.ai_predictions.length; i++) {
                    let prediction = this.deepCloneArray(this.event.ai_predictions[i]);
                    for (let j = 0; j < prediction.detection_profiles.length; j++) {
                        if (prediction.detection_profiles[j].id === profile.id) {
                            prediction.is_masked = prediction.detection_profiles[j].is_masked;
                            prediction.is_smart_filtered = prediction.detection_profiles[j].is_smart_filtered;
                            predictions.push(prediction);
                            break;
                        }
                    }
                }

                return predictions;
            },

            clearHighlightedPredictions() {
                this.highlightPredictions = [];
                this.highlightMask = null;
            },

            highlightAllPredictions() {
                this.soloPrediction = null;
                this.highlightMask = null;

                this.event.pattern_matched_profiles.forEach(p => {
                    if (p.isSelected) this.toggleSelectedProfile(p);
                });

                let predictions = [];

                if (this.highlightPredictions.length > 0) {
                    this.clearHighlightedPredictions();
                }
                else {
                    predictions = this.deepCloneArray(this.predictions);

                    for (let i = 0; i < predictions.length; i++) {
                        predictions[i].is_masked = 0;
                        predictions[i].is_smart_filtered = 0;
                    }
                }

                this.highlightPredictions = predictions;

                this.draw();
            },

            toggleSelectedProfile(profile) {
                this.soloPrediction = null;
                profile.isSelected = !profile.isSelected;

                if (profile.isSelected) {
                    this.highlightPredictions = this.sortPredictions(this.getPredictionsForProfile(profile));
                    this.highlightMask = profile.use_mask ? profile.slug + '.png' : null;
                }
                else {
                    this.clearHighlightedPredictions();
                }
                this.draw();
            },

            getIcon(profile) {
                if (!profile.is_profile_active) {
                    return "ban";
                }

                if (this.hasUnmaskedUnfilteredPredictions(profile)) {
                    return 'check';
                }

                return 'times';
            },

            draw() {
                let canvas = document.getElementById('event-snapshot');
                canvas.width = this.imageWidth;
                canvas.height = this.imageHeight;

                let stage = new Facade(document.querySelector('#event-snapshot')),
                    image = new Facade.Image(this.imageFile, {
                        x: this.imageWidth / 2,
                        y: this.imageHeight / 2,
                        height: this.imageHeight,
                        width: this.imageWidth,
                        anchor: 'center'
                    });

                let mask = null;
                if (this.highlightMask) {
                    mask = new Facade.Image('/storage/masks/' + this.highlightMask, {
                        x: this.imageWidth / 2,
                        y: this.imageHeight / 2,
                        height: this.imageHeight,
                        width: this.imageWidth,
                        anchor: 'center'
                    });
                }

                let rects = [];
                let predictions = [];
                let showTip = false;
                let tipText = null;

                if (this.soloPrediction) {
                    showTip = true;
                    let text = this.soloPrediction.object_class;
                    text += ` | ${this.$options.filters.percentage(this.soloPrediction.confidence)} confidence`;
                    if (this.soloPrediction.is_masked) text += ' | masked';
                    if (this.soloPrediction.is_smart_filtered) text += ' | smart filtered';
                    tipText = new Facade.Text(text, {
                        x: 10,
                        y: this.imageHeight - 10,
                        fontFamily: 'BlinkMacSystemFont, -apple-system, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", "Helvetica", "Arial", sans-serif',
                        fontSize: 30,
                        scale: this.imageWidth / 640,
                        fillStyle: '#fff',
                        strokeStyle: '#000',
                        lineWidth: 1,
                        anchor: 'bottom/left'
                    });
                    predictions.push(this.soloPrediction);
                }
                else {
                    predictions = this.highlightPredictions;
                }

                if (predictions.length > 0) {
                    predictions.forEach(prediction => {
                        let color = '#7957d5';
                        rects.push(new Facade.Rect({
                            x: prediction.x_min,
                            y: prediction.y_min,
                            width: prediction.x_max - prediction.x_min,
                            height: prediction.y_max - prediction.y_min,
                            lineWidth: 0.00625 * this.imageWidth,
                            strokeStyle: prediction.is_masked || prediction.is_smart_filtered ? 'gray' : color,
                            fillStyle: 'rgba(0, 0, 0, 0)'
                        }));
                    });
                }

                stage.draw(function () {
                    this.clear();

                    this.addToStage(image);

                    if (mask) this.addToStage(mask);

                    for (let i = 0; i < rects.length; i++) {
                        this.addToStage(rects[i]);
                    }

                    if (showTip) {
                        this.addToStage(tipText);
                    }
                });

            }
        }
    }
</script>

<style scoped>

</style>
