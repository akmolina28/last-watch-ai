<template>
    <div class="component-container">
        <div class="columns">
            <div class="column is-one-third">
                <h1 class="heading">Predictions</h1>
                <h1 class="heading">Predictions</h1>
                <table class="table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Class</th>
                            <th>Confidence</th>
                            <th>Relevance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prediction, index) in event.ai_predictions"
                            @click="highlightPrediction(prediction)"
                            :class="prediction.highlight ? 'is-selected' : ''">
                            <td>{{ index + 1 }}</td>
                            <td>{{ prediction.object_class }}</td>
                            <td>{{ prediction.confidence }}</td>
                            <td>
                                <i v-if="prediction.detection_profiles.length > 0" class="fas fa-check"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="column is-two-thirds">
                <canvas id="event-snapshot" ref="event-snapshot" height="480" width="640"></canvas>
                <div v-for="profile in highlightedProfiles">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Relevant Profile</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ profile.name }}</td>
                                <td><button v-if="profile.use_mask" class="button">Show Mask</button></td>
                            </tr>
                        </tbody>
                    </table>
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
                event: {}
            }
        },

        mounted () {
            this.getData();
        },

        computed: {
            highlightedProfiles() {
                let profiles = []
                if (this.event && this.event.ai_predictions) {
                    profiles = this.event.ai_predictions.filter(p => p.highlight);
                }
                return profiles;
            }
        },

        methods: {
            getData() {
                axios.get(`/api/events/${this.id}`)
                    .then(response => {
                        this.event = response.data.data;
                        this.draw(this.event.ai_predictions);
                    });
            },

            highlightPrediction(prediction) {
                this.event.ai_predictions.forEach(p => p.highlight = false);
                prediction.highlight = true;

                this.draw([prediction]);
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
                if (typeof mask_name !== 'undefined') {
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
