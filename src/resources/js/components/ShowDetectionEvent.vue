<template>
    <div class="component-container">
        <div class="columns">
            <div class="column is-one-third">
                <ul class="menu-list">
                    <li v-for="profile in profiles" @click="selectProfile(profile)">
                        <a :class="profile.id === selectedProfile.id ? 'is-active' : ''">
                            <h6 class="heading is-size-6">{{ profile.name }}</h6>
                            <p class="is-italic">{{ profile.file_pattern }}</p>
                            <ul>
                                <li v-for="prediction in getPredictions(profile)">{{ prediction.object_class }}</li>
                            </ul>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="column is-two-thirds">
                <canvas id="event-snapshot" ref="event-snapshot" height="480" width="640"></canvas>
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
                predictions: [],
                profiles: [],
                selectedProfile: {}
            }
        },

        mounted () {
            this.getData();
        },

        methods: {
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

                        this.draw(this.event.ai_predictions);
                    });
            },

            getPredictions(profile) {
                let predictions = [];

                for (let i = 0; i < this.event.ai_predictions.length; i++) {
                    let prediction = this.event.ai_predictions[i];
                    for (let j = 0; j < prediction.detection_profiles.length; j++) {
                        if (prediction.detection_profiles[j].id === profile.id) {
                            predictions.push(prediction);
                            break;
                        }
                    }
                }

                return predictions;
            },

            selectProfile(profile) {
                this.selectedProfile = profile;

                let predictions = this.getPredictions(profile);
                this.draw(predictions, profile.use_mask ? profile.slug + '.png' : null);
            },

            draw(predictions, mask_name) {
                let stage = new Facade(document.querySelector('#event-snapshot')),
                    image = new Facade.Image('/storage/' + this.event.image_file_name, {
                        x: stage.width() / 2,
                        y: stage.height() / 2,
                        height: 480,
                        width: 640,
                        anchor: 'center'
                    });

                let mask = null;
                if (mask_name) {
                    mask = new Facade.Image('/storage/masks/' + mask_name, {
                        x: stage.width() / 2,
                        y: stage.height() / 2,
                        height: 480,
                        width: 640,
                        anchor: 'center'
                    });
                }

                let rects = [];
                predictions.forEach(prediction => {
                    rects.push(new Facade.Rect({
                        x: prediction.x_min,
                        y: prediction.y_min,
                        width: prediction.x_max - prediction.x_min,
                        height: prediction.y_max - prediction.y_min,
                        lineWidth: 4,
                        strokeStyle: 'red',
                        fillStyle: 'rgba(0, 0, 0, 0)'
                    }));
                });

                // stage.resizeForHDPI();

                // stage.context.webkitImageSmoothingEnabled = false;

                stage.draw(function () {

                    this.clear();

                    this.addToStage(image);

                    if (mask) this.addToStage(mask);

                    for (let i = 0; i < rects.length; i++) {
                        this.addToStage(rects[i]);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>
