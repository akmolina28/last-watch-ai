<template>
  <div style="position:absolute;left:0;right:0;top:0;min-height:100%;">
    <img :src="imageFile" style="width:100%" />
    <a
      v-if="prevEventId"
      style="position:absolute;top:0;left:0;width:20%;height:100%;"
      @click="goToPrev()"
    >
      <b-icon size="is-large" icon="chevron-left" style="position:absolute;left:0;top:45%"></b-icon>
    </a>
    <a
      v-if="nextEventId"
      :style="`position:absolute;top:0;right:0;width:20%;height:100%;`"
      @click="goToNext()"
    >
      <b-icon
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

export default {
  name: 'EventViewer',
  props: ['event', 'profile'],
  data() {
    return {
      loading: true,
      detectionEvent: null,
    };
  },
  mounted() {
    this.load(this.event, this.profile);
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
  },
  methods: {
    load(event, profile) {
      this.loading = true;
      this.getDataAjax(event, profile).then((response) => {
        this.detectionEvent = response.data.data;
        this.loading = false;
        this.pushRoute(this.detectionEvent.id, this.profile);
      });
    },
    getDataAjax(event, profile) {
      return axios.get('/api/events/viewer', {
        params: {
          event,
          profile,
        },
      });
    },
    goToNext() {
      if (this.nextEventId) {
        this.load(this.nextEventId, this.profile);
      }
    },
    goToPrev() {
      if (this.prevEventId) {
        this.load(this.prevEventId, this.profile);
      }
    },
    pushRoute(event, profile) {
      this.$router
        .push({
          name: 'EventViewer',
          query: {
            event,
            profile,
          },
        })
        .catch(() => {});
    },
  },
};
</script>

<style lang="scss" scoped></style>
