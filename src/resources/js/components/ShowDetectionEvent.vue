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
                <canvas id="event-snapshot" ref="event-snapshot" style="width: 640px; height: 480px"></canvas>
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

        computed: {
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
            }
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
                let canvas = document.getElementById('event-snapshot');
                canvas.width = this.imageWidth;
                canvas.height = this.imageHeight;

                let stage = new Facade(document.querySelector('#event-snapshot')),
                    image = new Facade.Image('/storage/' + this.event.image_file_name, {
                        x: this.imageWidth / 2,
                        y: this.imageHeight / 2,
                        height: this.imageHeight,
                        width: this.imageWidth,
                        anchor: 'center'
                    });

                let mask = null;
                if (mask_name) {
                    mask = new Facade.Image('/storage/masks/' + mask_name, {
                        x: this.imageWidth / 2,
                        y: this.imageHeight / 2,
                        height: this.imageHeight,
                        width: this.imageWidth,
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

                stage.clear();

                stage.addToStage(image);

                if (mask) stage.addToStage(mask);

                for (let i = 0; i < rects.length; i++) {
                    stage.addToStage(rects[i]);
                }


            }
        }
    }
</script>

<style scoped>

</style>
