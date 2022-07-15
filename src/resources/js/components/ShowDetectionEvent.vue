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
        <div class="control">
          <div class="tags has-addons">
            <span class="tag">Automations Run</span>
            <span class="tag is-primary">{{ automationsRun.length }}</span>
          </div>
        </div>
        <div class="control" v-if="automationsFailed.length > 0">
          <div class="tags has-addons">
            <span class="tag">Automations Failed</span>
            <span class="tag is-danger">{{ automationsFailed.length }}</span>
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
          <span class="has-text-weight-bold">All Predictions</span>
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
            :class="prediction.isRelevant ? 'has-text-weight-bold' : 'has-text-grey is-italic'"
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
        <b-button
          v-if="event"
          tag="a"
          :href="`/api/events/${event.id}/img`"
          size="is-small"
          icon-left="download"
          >Download Image</b-button
        >
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
          <b-table-column label="Matched Profile" field="name" v-slot="props">
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
              <div v-else-if="props.row.is_masked" class="control">
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
              <div v-else-if="props.row.is_zone_ignored" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="ban"
                    ><b-tooltip
                      multiline
                      type="is-warning"
                      :label="
                        `${selectedPrediction.object_class} objects are ignored in this area.`
                      "
                      >Ignored</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
              <div v-else-if="props.row.is_confidence_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="percent"
                    ><b-tooltip multiline type="is-warning">
                      <template v-slot:content>
                        AI confidence is below the configured threshold of
                        {{ props.row.min_confidence | percentage }}.
                      </template>
                      Below Confidence</b-tooltip
                    ></b-tag
                  >
                </b-taglist>
              </div>
              <div v-else-if="props.row.is_smart_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="filter">
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
              <div v-else-if="props.row.is_size_filtered" class="control">
                <b-taglist>
                  <b-tag type="is-warning" icon="compress">
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
          <b-table-column v-if="selectedPrediction" field="name" v-slot="props">
            <b-dropdown aria-role="list" class="is-pulled-right" position="is-bottom-left">
              <template #trigger>
                <b-icon icon="ellipsis-v"></b-icon>
              </template>
              <b-dropdown-item
                aria-role="listitem"
                @click="ignorePrediction(props.row, selectedPrediction)"
              >
                <b-icon icon="ban"></b-icon>
                Ignore Prediction
              </b-dropdown-item>
            </b-dropdown>
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
import IgnorePredictionForm from './IgnorePredictionForm.vue';

const Facade = require('facade.js');

export default {
  name: 'ShowDetectionEvent',
  props: ['id'],
  components: {
    IgnorePredictionForm,
  },
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
        return this.event.ai_predictions
          .map((p) => ({
            ...p,
            isRelevant: p.detection_profiles.some((d) => d.is_relevant),
          }))
          .sort((a, b) => {
            if (a.isRelevant && !b.isRelevant) {
              return -1;
            }
            if (!a.isRelevant && b.isRelevant) {
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
      if (!this.selectedPrediction && this.event && this.event.pattern_matched_profiles) {
        return this.event.pattern_matched_profiles.map((prof) => ({
          ...prof,
          is_relevant: this.event.ai_predictions.some(
            (pred) =>
              // eslint-disable-next-line implicit-arrow-linebreak
              pred.detection_profiles.some((p) => p.is_relevant && p.id === prof.id),
            // eslint-disable-next-line function-paren-newline
          ),
        }));
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
    automationsRun() {
      if (this.event && this.event.automationResults) {
        return this.event.automationResults.filter((a) => a.is_error);
      }
      return [];
    },
    automationsFailed() {
      if (this.event && this.event.automationResults) {
        return this.event.automationResults.filter((a) => a.is_error);
      }
      return [];
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
          // const filteredColor = localStorage.darkMode === 'true' ? '#f2cb1d' : '#ffe08a';
          const filteredColor = 'grey';
          rects.push(
            new Facade.Rect({
              x: prediction.x_min,
              y: prediction.y_min,
              width: prediction.x_max - prediction.x_min,
              height: prediction.y_max - prediction.y_min,
              lineWidth: 0.00625 * this.imageWidth,
              strokeStyle: prediction.isRelevant ? relevantColor : filteredColor,
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
    ignorePrediction(profile, aiPrediction) {
      this.$buefy.modal.open({
        parent: this,
        hasModalCard: true,
        fullScreen: false,
        component: IgnorePredictionForm,
        props: {
          profile,
          aiPrediction,
        },
      });
    },
  },
};
</script>
