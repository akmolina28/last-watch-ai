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
        <div class="control">
          <div class="tags has-addons" @click="highlightAllPredictions">
            <div class="tag">AI Predictions</div>
            <a
              href="javascript:void(0);"
              :class="`tag is-primary ${predictions.length === 0 ? 'is-light' : ''}`"
            >
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

    <div class="is-flex-tablet is-justify-content-space-between">
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
      <b-field class="is-hidden-mobile">
        <template #label>
          <span>&nbsp;</span>
        </template>
        <b-tag v-if="occurredAt" type="is-light" size="is-large">{{ occurredAt }}</b-tag>
      </b-field>
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
      <div>
        <canvas id="event-snapshot" ref="event-snapshot" style="width:100%;"></canvas>
      </div>
      <div class="buttons">
        <b-button
          v-for="prediction in highlightedPredictions"
          :class="getClassForPrediction(prediction)"
          :key="prediction.id"
          @click="soloHighlightPrediction(prediction)"
        >
          <b-icon :icon="getIconForPrediction(prediction)"></b-icon>
          <span>
            {{ prediction.confidence | percentage }}
          </span>
        </b-button>
      </div>

      <div class="content">
        <a :href="`/api/events/${event.id}/img`" download>
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
      highlightPredictions: [],
      soloPrediction: null,
      highlightMask: null,
      filterProfileId: parseInt(this.profileId, 10) || null,
    };
  },

  mounted() {
    this.getData();
  },

  computed: {
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
    highlightedPredictions() {
      let predictions = [];

      if (this.selectedProfile) {
        predictions = this.getPredictionsForProfile(this.selectedProfile);
      } else {
        predictions = this.deepCloneArray(this.predictions);

        for (let i = 0; i < predictions.length; i += 1) {
          predictions[i].is_masked = 0;
          predictions[i].is_smart_filtered = 0;
          predictions[i].is_size_filtered = 0;
        }
      }

      return predictions;
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
        return moment(moment.utc(this.event.occurred_at)).local();
      }
      return null;
    },
  },

  watch: {
    highlightedPredictions() {
      this.draw();
    },
    soloPrediction() {
      this.draw();
    },
    filterProfileId() {
      this.soloPrediction = null;
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
    soloHighlightPrediction(prediction) {
      if (this.soloPrediction && this.soloPrediction.id === prediction.id) {
        this.soloPrediction = null;
      } else {
        this.soloPrediction = prediction;
      }
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
    getClassForPrediction(prediction) {
      if (prediction) {
        let c = '';

        if (prediction.is_masked) {
          c = 'is-danger';
        } else if (prediction.is_smart_filtered) {
          c = 'is-warning';
        } else if (prediction.is_size_filtered) {
          c = 'is-warning';
        } else {
          c = 'is-primary';
        }

        let isLight = false;
        if (this.soloPrediction) {
          isLight = prediction.id !== this.soloPrediction.id;
        }

        if (isLight) {
          c += ' is-light';
        }

        return c;
      }
      return '';
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

        if (this.profileId) {
          const selectedProfile = this.event.pattern_matched_profiles.filter(
            // eslint-disable-next-line eqeqeq
            (p) => p.id == this.profileId,
          )[0];
          this.toggleSelectedProfile(selectedProfile);
        } else {
          this.draw();
        }
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

    clearSelectedProfile() {
      this.event.pattern_matched_profiles.forEach((p) => {
        if (p.isSelected) this.toggleSelectedProfile(p);
      });
    },

    highlightAllPredictions() {
      this.soloPrediction = null;
      this.highlightMask = null;
      this.clearSelectedProfile();

      let predictions = [];

      if (this.highlightPredictions.length > 0) {
        this.clearHighlightedPredictions();
      } else {
        predictions = this.deepCloneArray(this.predictions);

        for (let i = 0; i < predictions.length; i += 1) {
          predictions[i].is_masked = 0;
          predictions[i].is_smart_filtered = 0;
          predictions[i].is_size_filtered = 0;
        }
      }

      this.highlightPredictions = predictions;

      this.draw();
    },

    toggleSelectedProfile(profile) {
      this.soloPrediction = null;
      profile.isSelected = !profile.isSelected;

      this.event.pattern_matched_profiles.forEach((p) => {
        if (p.id !== profile.id) p.isSelected = false;
      });

      if (profile.isSelected) {
        this.highlightPredictions = this.getPredictionsForProfile(profile);
        // this.highlightPredictions = this.sortPredictions(this.getPredictionsForProfile(profile));
        this.highlightMask = profile.use_mask ? `${profile.slug}.png` : null;
      } else {
        this.clearHighlightedPredictions();
      }
      this.draw();
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
      if (this.highlightMask) {
        mask = new Facade.Image(`/storage/masks/${this.highlightMask}`, {
          x: this.imageWidth / 2,
          y: this.imageHeight / 2,
          height: this.imageHeight,
          width: this.imageWidth,
          anchor: 'center',
        });
      }

      const rects = [];
      let predictions = [];
      let showTip = false;
      let tipText = null;

      if (this.soloPrediction) {
        showTip = true;
        let text = this.soloPrediction.object_class;
        text += ` | ${this.$options.filters.percentage(this.soloPrediction.confidence)} confidence`;
        if (this.soloPrediction.is_masked) text += ' | masked';
        if (this.soloPrediction.is_smart_filtered) text += ' | smart filtered';
        if (this.soloPrediction.is_size_filtered) text += ' | size filtered';
        tipText = new Facade.Text(text, {
          x: 10,
          y: this.imageHeight - 10,
          fontFamily:
            'BlinkMacSystemFont, -apple-system, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", "Helvetica", "Arial", sans-serif',
          fontSize: 30,
          scale: this.imageWidth / 640,
          fillStyle: '#fff',
          strokeStyle: '#000',
          lineWidth: 1,
          anchor: 'bottom/left',
        });
        predictions.push(this.soloPrediction);
      } else {
        predictions = this.highlightedPredictions;
      }

      if (predictions.length > 0) {
        predictions.forEach((prediction) => {
          const color = '#7957d5';
          rects.push(
            new Facade.Rect({
              x: prediction.x_min,
              y: prediction.y_min,
              width: prediction.x_max - prediction.x_min,
              height: prediction.y_max - prediction.y_min,
              lineWidth: 0.00625 * this.imageWidth,
              strokeStyle:
                prediction.is_masked || prediction.is_smart_filtered || prediction.is_size_filtered
                  ? 'gray'
                  : color,
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

        if (showTip) {
          this.addToStage(tipText);
        }
      });
    },
  },
};
</script>

<style scoped></style>
