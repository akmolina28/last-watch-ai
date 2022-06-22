<template>
  <div class="component-container">
    <b-loading v-model="loading" :is-full-page="false"></b-loading>
    <title-header>
      <template v-slot:title>
        Detection Event
      </template>
      <template v-slot:subtitle>
        {{ imageFileName }}
      </template>
      <template v-slot:meta>
        <div class="control">
          <div class="tags has-addons">
            <span class="tag">Relevant</span>
            <span v-if="relevant" class="tag is-success">Yes</span>
            <span v-else class="tag is-success is-light">No</span>
          </div>
        </div>
        <div class="control" v-if="privacyMode">
          <div class="tags has-addons">
            <span class="tag is-danger">Privacy Mode</span>
          </div>
        </div>
      </template>
    </title-header>
    <b-tabs
      v-if="predictions.length > 0"
      v-model="activeTab"
      type="is-boxed"
      :animated="false"
      class="mb-0"
    >
      <b-tab-item value="all">
        <template #header>
          <span class="has-text-weight-bold">All</span>
        </template>
        <div v-if="event">
          <b-field grouped group-multiline>
            <div class="control">
              <b-taglist size="is-small" attached>
                <b-tag>Total Predictions</b-tag>
                <b-tag type="is-primary">{{ predictions.length }}</b-tag>
              </b-taglist>
            </div>
            <div class="control">
              <b-taglist attached>
                <b-tag>Relevant Profiles</b-tag>
                <b-tag type="is-primary">{{ relevantProfileIds.length }}</b-tag>
              </b-taglist>
            </div>
          </b-field>
        </div>
      </b-tab-item>
      <b-tab-item
        v-for="prediction in predictions"
        :key="prediction.id"
        :value="`${prediction.id}`"
      >
        <template #header>
          <span
            :class="
              prediction.detection_profiles.length
                ? 'has-text-weight-bold'
                : 'has-text-grey is-italic'
            "
            >{{ prediction.object_class }}</span
          >
        </template>
        <div v-if="selectedPrediction">
          <b-field grouped group-multiline>
            <div class="control">
              <b-taglist size="is-small" attached>
                <b-tag>Object Class</b-tag>
                <b-tag type="is-primary">{{ prediction.object_class }}</b-tag>
              </b-taglist>
            </div>
            <div class="control">
              <b-taglist attached>
                <b-tag>AI Confidence</b-tag>
                <b-tag type="is-primary">{{ prediction.confidence | percentage }}</b-tag>
              </b-taglist>
            </div>
            <div class="control is-hidden-mobile">
              <b-taglist attached>
                <b-tag>Size</b-tag>
                <b-tag type="is-primary">{{ prediction.area | numberStr }} px²</b-tag>
              </b-taglist>
            </div>
          </b-field>
        </div>
      </b-tab-item>
    </b-tabs>
    <div class="columns">
      <div class="column is-half">
        <canvas id="event-snapshot" ref="event-snapshot" style="width:100%;"></canvas>
      </div>
      <div class="column is-half">
        <b-table
          v-if="detectionProfiles"
          :data="detectionProfiles"
          :selected.sync="selectedProfile"
          focusable
          sort-icon="menu-up"
          :default-sort="['is_relevant', 'desc']"
        >
          <b-table-column label="Profile" field="name" v-slot="props">
            {{ props.row.name }}
          </b-table-column>
          <b-table-column label="Relevance" field="is_relevant" v-slot="props">
            <b-field grouped group-multiline>
              <div v-if="props.row.is_relevant" class="control">
                <b-taglist>
                  <b-tag type="is-success" icon="check">
                    <b-tooltip
                      multiline
                      type="is-success"
                      :label="`Object meets all conditions for the selected profile.`"
                      >Relevant</b-tooltip
                    >
                  </b-tag>
                </b-taglist>
              </div>
              <div v-if="props.row.is_masked" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="ban"
                    ><b-tooltip
                      multiline
                      type="is-warning"
                      label="Object not relevant because most of it is behind the mask."
                      >Masked</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
              <div v-if="props.row.is_smart_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="ban">
                    <b-tooltip
                      multiline
                      type="is-warning"
                      label="
                        Object not relevant because it appeared in the same location previously.
                      "
                      >Smart Filter</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
              <div v-if="props.row.is_size_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="ban">
                    <b-tooltip
                      type="is-warning"
                      multilined
                      :label="
                        // eslint-disable-next-line max-len
                        `Object size is below the minimum configured threshold of ${props.row.min_object_size}.`
                      "
                      >Too Small</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
            </b-field>
          </b-table-column>
          <template #empty>
            <span><i>No matched profiles for this prediction.</i></span>
          </template>
        </b-table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

const Facade = require('facade.js');

export default {
  name: 'ShowDetectionEvent',
  props: ['id'],
  data() {
    return {
      event: null,
      loading: true,
      activeTab: 'all',
      selectedProfile: null,
    };
  },
  mounted() {
    this.getData();
  },
  computed: {
    relevant() {
      if (this.predictions) {
        return this.predictions.some((p) => p.detection_profiles.some((d) => d.is_relevant));
      }
      return false;
    },
    privacyMode() {
      if (this.event) {
        return this.event.privacy_mode;
      }
      return false;
    },
    imageFileName() {
      if (this.event) {
        return this.event.image_file_name;
      }
      return null;
    },
    predictions() {
      if (this.event) {
        return this.event.ai_predictions.sort((a, b) => {
          const aRelevant = a.detection_profiles.some((p) => p.is_relevant);
          const bRelevant = b.detection_profiles.some((p) => p.is_relevant);
          if (aRelevant && !bRelevant) {
            return -1;
          }
          if (!aRelevant && bRelevant) {
            return 1;
          }
          return b.confidence - a.confidence;
        });
      }
      return [];
    },
    relevantProfileIds() {
      const ids = [];
      if (this.predictions) {
        this.predictions.forEach((p) => {
          p.detection_profiles.forEach((d) => {
            if (d.is_relevant && ids.indexOf(d.id) < 0) {
              ids.push(d.id);
            }
          });
        });
      }
      return ids;
    },
    selectedPredictionId() {
      if (this.activeTab) {
        return parseInt(this.activeTab, 10) || null;
      }
      return null;
    },
    selectedPrediction() {
      if (this.event && this.selectedPredictionId) {
        return this.predictions.find((p) => p.id === this.selectedPredictionId);
      }
      return null;
    },
    detectionProfiles() {
      if (this.selectedPrediction) {
        return this.selectedPrediction.detection_profiles;
      }
      return null;
    },
    imageWidth() {
      if (this.event) {
        return this.event.image_width;
      }
      return 0;
    },
    imageHeight() {
      if (this.event) {
        return this.event.image_height;
      }
      return 0;
    },
    imageFile() {
      return this.event ? this.event.image_file_path : '';
    },
    drawMask() {
      if (this.selectedProfile) {
        return this.selectedProfile.use_mask;
      }
      return false;
    },
    maskFileName() {
      if (this.selectedProfile) {
        return this.selectedProfile.mask_file_name;
      }
      return null;
    },
  },
  watch: {
    selectedPrediction() {
      this.selectedProfile = null;
      this.draw();
    },
    selectedProfile() {
      this.draw();
    },
  },
  methods: {
    getData() {
      this.loading = true;
      this.getEventAjax().then((response) => {
        this.event = response.data.data;
        this.draw();
        this.loading = false;
      });
    },
    getEventAjax() {
      return axios.get(`/api/events/${this.id}`, {
        params: {
          profileId: this.filterProfileId,
        },
      });
    },
    draw() {
      const canvas = document.getElementById('event-snapshot');
      canvas.width = this.imageWidth;
      canvas.height = this.imageHeight;

      const stage = new Facade(document.querySelector('#event-snapshot'));
      const image = new Facade.Image(this.imageFile, {
        x: this.imageWidth / 2,
        y: this.imageHeight / 2,
        height: this.imageHeight,
        width: this.imageWidth,
        anchor: 'center',
      });

      let mask = null;
      if (this.drawMask) {
        mask = new Facade.Image(`/storage/masks/${this.maskFileName}`, {
          x: this.imageWidth / 2,
          y: this.imageHeight / 2,
          height: this.imageHeight,
          width: this.imageWidth,
          anchor: 'center',
        });
      }

      const rects = [];
      let predictions = [];

      if (this.selectedPrediction) {
        predictions.push(this.selectedPrediction);
      } else {
        predictions = this.predictions;
      }

      if (predictions.length > 0) {
        predictions.forEach((prediction) => {
          const relevantColor = localStorage.darkMode === 'true' ? 'rgb(0, 91, 161)' : '#7957d5';
          const filteredColor = localStorage.darkMode === 'true' ? '#f2cb1d' : '#ffe08a';
          rects.push(
            new Facade.Rect({
              x: prediction.x_min,
              y: prediction.y_min,
              width: prediction.x_max - prediction.x_min,
              height: prediction.y_max - prediction.y_min,
              lineWidth: 0.00625 * this.imageWidth,
              strokeStyle:
                prediction.is_masked || prediction.is_smart_filtered || prediction.is_size_filtered
                  ? filteredColor
                  : relevantColor,
              fillStyle: 'rgba(0, 0, 0, 0)',
            }),
          );
        });
      }

      // eslint-disable-next-line func-names, space-before-function-paren
      stage.draw(function() {
        this.clear();

        this.addToStage(image);

        if (mask) this.addToStage(mask);

        for (let i = 0; i < rects.length; i += 1) {
          this.addToStage(rects[i]);
        }
      });
    },
  },
};
</script>
