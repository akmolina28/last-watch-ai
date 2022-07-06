<template>
  <div style="position:absolute;left:0;right:0;top:0;min-height:100%;">
    <canvas id="canvas" style="width:100%" />
    <a
      v-if="prevEventId"
      style="position:absolute;top:0;left:0;width:20%;height:100%;"
      @click="goToPrev()"
    >
      <b-icon
        v-if="!hideUI"
        size="is-large"
        icon="chevron-left"
        style="position:absolute;left:0;top:45%"
      ></b-icon>
    </a>
    <a
      @click="hideUI = !hideUI"
      style="position:absolute;left:20%;top:0;height:100%;width:60%;"
    ></a>
    <a
      v-if="nextEventId"
      :style="`position:absolute;top:0;right:0;width:20%;height:100%;`"
      @click="goToNext()"
    >
      <b-icon
        v-if="!hideUI"
        size="is-large"
        icon="chevron-right"
        style="position:absolute;right:0;top:45%"
      ></b-icon>
    </a>
    <div style="clear:both;"></div>
  </div>
</template>

<script>
import axios from 'axios';

const Facade = require('facade.js');

export default {
  name: 'EventViewer',
  props: ['event', 'profile', 'group'],
  data() {
    return {
      loading: true,
      detectionEvent: null,
      hideUI: false,
    };
  },
  mounted() {
    this.load(this.event, this.profile, this.group);
  },
  computed: {
    eventId() {
      if (this.detectionEvent) {
        return this.detectionEvent.id;
      }
      return null;
    },
    imageFile() {
      return this.detectionEvent ? this.detectionEvent.image_file_path : '';
    },
    imageWidth() {
      if (this.detectionEvent) {
        return this.detectionEvent.image_width;
      }
      return 0;
    },
    imageHeight() {
      if (this.detectionEvent) {
        return this.detectionEvent.image_height;
      }
      return 0;
    },
    nextEventId() {
      if (this.detectionEvent) {
        return this.detectionEvent.next_event_id;
      }
      return null;
    },
    prevEventId() {
      if (this.detectionEvent) {
        return this.detectionEvent.prev_event_id;
      }
      return null;
    },
    highlightPredictions() {
      if (this.detectionEvent) {
        // eslint-disable-next-line arrow-body-style
        return this.detectionEvent.ai_predictions.filter((pred) => {
          return pred.detection_profiles.some(
            (prof) => prof.is_relevant && (!this.profile || prof.slug === this.profile),
          );
        });
      }
      return null;
    },
  },
  watch: {
    hideUI() {
      this.draw();
    },
  },
  methods: {
    load(event, profile, group) {
      this.loading = true;
      this.getDataAjax(event, profile, group).then((response) => {
        this.detectionEvent = response.data.data;
        this.hideUI = false;
        this.loading = false;
        this.pushRoute(this.detectionEvent.id, this.profile, this.group);
        this.draw();
      });
    },
    getDataAjax(event, profile, group) {
      return axios.get('/api/events/viewer', {
        params: {
          event,
          profile,
          group,
        },
      });
    },
    goToNext() {
      if (this.nextEventId) {
        this.load(this.nextEventId, this.profile, this.group);
      }
    },
    goToPrev() {
      if (this.prevEventId) {
        this.load(this.prevEventId, this.profile, this.group);
      }
    },
    pushRoute(event, profile, group) {
      this.$router
        .replace({
          name: 'EventViewer',
          query: {
            event,
            profile,
            group,
          },
        })
        .catch(() => {});
    },
    draw() {
      const canvas = document.getElementById('canvas');
      canvas.width = this.imageWidth;
      canvas.height = this.imageHeight;

      const stage = new Facade(document.querySelector('#canvas'));
      const image = new Facade.Image(this.imageFile, {
        x: this.imageWidth / 2,
        y: this.imageHeight / 2,
        height: this.imageHeight,
        width: this.imageWidth,
        anchor: 'center',
      });

      const rects = [];

      if (!this.hideUI && this.highlightPredictions.length > 0) {
        this.highlightPredictions.forEach((prediction) => {
          const relevantColor = localStorage.darkMode === 'true' ? 'rgb(0, 91, 161)' : '#7957d5';
          // const filteredColor = localStorage.darkMode === 'true' ? '#f2cb1d' : '#ffe08a';
          rects.push(
            new Facade.Rect({
              x: prediction.x_min,
              y: prediction.y_min,
              width: prediction.x_max - prediction.x_min,
              height: prediction.y_max - prediction.y_min,
              lineWidth: 0.00625 * this.imageWidth,
              strokeStyle: relevantColor,
              fillStyle: 'rgba(0, 0, 0, 0)',
            }),
          );
        });
      }

      // eslint-disable-next-line func-names, space-before-function-paren
      stage.draw(function() {
        this.clear();

        this.addToStage(image);

        for (let i = 0; i < rects.length; i += 1) {
          this.addToStage(rects[i]);
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped></style>
