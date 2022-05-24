<template>
  <div class="component-container">
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
        <div class="control" v-if="privacyMode">
          <div class="tags has-addons">
            <span class="tag is-danger">Privacy Mode</span>
          </div>
        </div>
        <div v-if="event" class="control">
          <div class="tags has-addons">
            <b-tag icon="clock">
              <b-tooltip :label="occurredAt">
                {{ event.occurred_at | dateStrRelative }}
              </b-tooltip>
            </b-tag>
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

    <div class="is-flex-tablet is-justify-content-space-between mb-5">
      <div class="is-flex-tablet">
        <b-field :label="`Matched Profiles (${patternMatchedProfiles.length})`" class="mr-2">
          <b-select v-model="filterProfileId" class="is-primary is-outlined" icon="eye">
            <option :value="null" :key="-1">Show all predictions</option>
            <option
              v-for="profile in event.pattern_matched_profiles"
              :value="profile.id"
              :key="profile.id"
            >
              {{ profile.name }}
            </option>
          </b-select>
        </b-field>
        <div class="is-hidden-mobile">
          <b-field>
            <template #label>
              <span>&nbsp;</span>
            </template>
            <b-button
              v-if="event && event.prev_event_id"
              tag="a"
              :href="
                `${event.prev_event_id}${filterProfileId ? '?profileId=' + filterProfileId : ''}`
              "
              icon-left="chevron-left"
              type="is-light"
              >Previous</b-button
            >
            <b-button
              v-if="event && event.next_event_id"
              tag="a"
              :href="
                `${event.next_event_id}${filterProfileId ? '?profileId=' + filterProfileId : ''}`
              "
              icon-right="chevron-right"
              type="is-light"
              >Next</b-button
            >
          </b-field>
        </div>
      </div>
    </div>

    <div>
      <div
        class="mb-4"
        v-if="
          event && event.pattern_matched_profiles && event.pattern_matched_profiles.length === 0
        "
      >
        <h5 class="heading is-size-6"><b-icon icon="times"></b-icon> Image Not Processed</h5>
        <p>
          No matching profile(s) found for this file name. Create a new profile to process this
          event.
        </p>
      </div>
      <div v-else>
        <b-tabs
          v-if="filteredPredictions.length > 0"
          type="is-boxed"
          v-model="activeTab"
          :animated="false"
        >
          <b-tab-item
            v-for="prediction in filteredPredictions"
            :key="prediction.id"
            :value="`${prediction.id}`"
          >
            <template #header>
              <b-icon :icon="getIconForPrediction(prediction)"></b-icon>
              <span>
                {{ prediction.confidence | percentage }}
              </span>
            </template>
            <b-field grouped group-multiline>
              <div class="control">
                <b-taglist size="is-small" attached>
                  <b-tag>Object Class</b-tag>
                  <b-tag type="is-primary">{{ prediction.object_class }}</b-tag>
                </b-taglist>
              </div>
              <div class="is-hidden-mobile control">
                <b-taglist attached>
                  <b-tag>Size</b-tag>
                  <b-tag type="is-primary">{{ prediction.area | numberStr }} px²</b-tag>
                </b-taglist>
              </div>
              <div v-if="prediction.is_relevant" class="control">
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
              <div v-else-if="prediction.is_masked" class="control">
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
              <div v-else-if="prediction.is_smart_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="ban"
                    ><b-tooltip
                      multiline
                      type="is-warning"
                      label="
                        Object not relevant because it appeared in the same location previously.
                      "
                      >Smart Filtered</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
              <div v-else-if="prediction.is_size_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="ban">
                    <b-tooltip
                      type="is-warning"
                      multilined
                      :label="
                        // eslint-disable-next-line max-len
                        `Object size is below the minimum configured threshold of ${selectedProfile.min_object_size}.`
                      "
                      >Size Filtered</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
            </b-field>
          </b-tab-item>
        </b-tabs>
      </div>
      <div class="columns">
        <div class="column is-half">
          <canvas id="event-snapshot" ref="event-snapshot" style="width:100%;"></canvas>
        </div>
      </div>

      <div class="content">
        <a v-if="!privacyMode" :href="`/api/events/${event.id}/img`" download>
          <span class="icon">
            <b-icon icon="image"></b-icon>
          </span>
          <span>Download Image File</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

const Facade = require('facade.js');

export default {
  name: 'ShowDetectionEvent',

  props: ['id', 'profileId'],

  data() {
    return {
      event: {},
      highlight: false,
      loading: true,
      highlightMask: null,
      filterProfileId: parseInt(this.profileId, 10) || null,
      activeTab: null,
    };
  },

  mounted() {
    this.getData();
  },

  computed: {
    privacyMode() {
      if (this.event && this.event.privacy_mode) {
        return true;
      }
      return false;
    },
    selectedProfile() {
      if (this.event && this.event.pattern_matched_profiles) {
        const profiles = this.event.pattern_matched_profiles.filter(
          (p) => p.id === this.filterProfileId,
        );
        if (profiles.length > 0) {
          return profiles[0];
        }
      }
      return null;
    },
    patternMatchedProfiles() {
      if (this.event && this.event.pattern_matched_profiles) {
        return this.event.pattern_matched_profiles;
      }
      return [];
    },
    predictions() {
      if (this.event && this.event.ai_predictions) {
        return this.event.ai_predictions;
      }
      return [];
    },
    automations() {
      if (this.event && this.event.automationResults) {
        return this.event.automationResults.filter((a) => !a.is_error).length;
      }
      return 0;
    },
    automationErrors() {
      if (this.event && this.event.automationResults) {
        return this.event.automationResults.filter((a) => a.is_error).length;
      }
      return 0;
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
    relevant() {
      if (this.event && this.event.ai_predictions) {
        for (let i = 0; i < this.event.ai_predictions.length; i += 1) {
          const prediction = this.event.ai_predictions[i];
          for (let j = 0; j < prediction.detection_profiles.length; j += 1) {
            if (
              !prediction.detection_profiles[j].is_masked &&
              !prediction.detection_profiles[j].is_smart_filtered &&
              !prediction.detection_profiles[j].is_size_filtered
            ) {
              return true;
            }
          }
        }
      }
      return false;
    },
    filteredPredictions() {
      if (this.predictions) {
        let predictions = [];

        if (this.selectedProfile) {
          predictions = this.getPredictionsForProfile(this.selectedProfile);
        } else {
          predictions = this.deepCloneArray(this.predictions);

          for (let i = 0; i < predictions.length; i += 1) {
            predictions[i].is_masked = 0;
            predictions[i].is_smart_filtered = 0;
            predictions[i].is_size_filtered = 0;
            predictions[i].is_relevant = 0;
          }
        }

        return predictions.sort((a, b) => (a.confidence < b.confidence ? 1 : -1));
      }
      return [];
    },
    routeQuery() {
      let query = {};
      if (this.filterProfileId) {
        query = { ...query, profileId: this.filterProfileId };
      }
      return query;
    },
    occurredAt() {
      if (this.event && this.event.occurred_at) {
        return `${moment(moment.utc(this.event.occurred_at)).local()}`;
      }
      return null;
    },
    selectedPrediction() {
      if (this.filteredPredictions && this.activeTab) {
        const predictionId = parseInt(this.activeTab, 10) || null;
        const predictions = this.filteredPredictions.filter((p) => p.id === predictionId);
        return predictions.length > 0 ? predictions[0] : null;
      }
      return null;
    },
  },

  watch: {
    filteredPredictions(newVal) {
      if (newVal.length > 0) {
        this.activeTab = `${newVal[0].id}`;
      }
    },
    selectedPrediction() {
      this.draw();
    },
    filterProfileId() {
      this.updateRoute();
      this.event.prev_event_id = null;
      this.event.next_event_id = null;
      this.getEventAjax().then((response) => {
        const event = response.data.data;

        this.event.prev_event_id = event.prev_event_id;
        this.event.next_event_id = event.next_event_id;
      });
    },
  },

  methods: {
    updateRoute() {
      this.$router.replace({ query: this.routeQuery }).catch(() => {});
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
        if (a.is_size_filtered && !b.is_size_filtered) {
          return 1;
        }
        if (!a.is_size_filtered && b.is_size_filtered) {
          return -1;
        }
        return 0;
      }

      return predictions.sort(compare);
    },
    getIconForPrediction(prediction) {
      if (prediction) {
        if (prediction.object_class === 'person') {
          return 'user';
        }
        if (prediction.object_class === 'car') {
          return 'car';
        }
        if (prediction.object_class === 'truck') {
          return 'truck';
        }
        if (prediction.object_class === 'bus') {
          return 'bus';
        }
        if (prediction.object_class === 'bicycle') {
          return 'bicycle';
        }
        if (prediction.object_class === 'motorcycle') {
          return 'motorcycle';
        }
        if (prediction.object_class === 'plane') {
          return 'plane';
        }
        if (prediction.object_class === 'train') {
          return 'train';
        }
        if (prediction.object_class === 'bird') {
          return 'crow';
        }
        if (prediction.object_class === 'cat') {
          return 'cat';
        }
        if (prediction.object_class === 'dog') {
          return 'dog';
        }
        if (prediction.object_class === 'horse') {
          return 'horse';
        }
        return 'circle';
      }
      return '';
    },
    getData() {
      this.getEventAjax().then((response) => {
        this.event = response.data.data;
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

    hasUnmaskedUnfilteredPredictions(profile) {
      const predictions = this.getPredictionsForProfile(profile);

      for (let i = 0; i < predictions.length; i += 1) {
        if (
          !predictions[i].is_masked &&
          !predictions[i].is_smart_filtered &&
          !predictions[i].is_size_filtered
        ) {
          return true;
        }
      }

      return false;
    },

    getPredictionsForProfile(profile) {
      const predictions = [];

      for (let i = 0; i < this.event.ai_predictions.length; i += 1) {
        const prediction = this.deepCloneArray(this.event.ai_predictions[i]);
        for (let j = 0; j < prediction.detection_profiles.length; j += 1) {
          if (prediction.detection_profiles[j].id === profile.id) {
            prediction.is_masked = prediction.detection_profiles[j].is_masked;
            prediction.is_smart_filtered = prediction.detection_profiles[j].is_smart_filtered;
            prediction.is_size_filtered = prediction.detection_profiles[j].is_size_filtered;
            prediction.is_relevant = prediction.detection_profiles[j].is_relevant;
            predictions.push(prediction);
            break;
          }
        }
      }

      return predictions;
    },

    getIcon(profile) {
      if (!profile.is_profile_active) {
        return 'ban';
      }

      if (this.hasUnmaskedUnfilteredPredictions(profile)) {
        return 'check';
      }

      return 'times';
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
      if (this.selectedProfile.use_mask) {
        mask = new Facade.Image(`/storage/masks/${this.selectedProfile.mask_file_name}`, {
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
        predictions = this.filteredPredictions;
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

<style scoped></style>
