<template>
  <div class="component-container">
    <title-header>
      <template v-slot:title>
        Last Watch AI
      </template>
      <template v-slot:subtitle>
        Computer vision-based automations
      </template>
      <template v-slot:meta>
        <div class="control">
          <div class="tags has-addons">
            <span class="tag">Relevant Events Today</span>
            <span
              :class="'tag is-success' + (statistics.relevant_events === 0 ? ' is-light' : '')"
              >{{ statistics.relevant_events }}</span
            >
          </div>
        </div>
        <div class="control">
          <div class="tags has-addons">
            <span class="tag">Total Events Processed Today</span>
            <span class="tag is-primary is-light">{{ statistics.total_events }}</span>
          </div>
        </div>
        <div class="control">
          <div class="tags has-addons">
            <span class="tag">Automation Errors Today</span>
            <a
              href="/errors"
              :class="'tag is-danger' + (statistics.total_errors === 0 ? ' is-light' : '')"
              >{{ statistics.total_errors }}</a
            >
          </div>
        </div>
      </template>
    </title-header>

    <div v-if="event" class="columns" style="margin-left:-0.75rem;">
      <div class="column is-one-third">
        <p class="title is-size-4">Latest Event - {{ event.occurred_at | dateStrRelative }}</p>
        <p class="subtitle is-size-6">{{ event.image_file_name }}</p>

        <a :href="`events/${event.id}`">
          <img alt="Event Image" :src="event.image_file_path" />
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Home',

  data() {
    return {
      event: null,
      statistics: {},
    };
  },

  mounted() {
    this.getData();
  },

  methods: {
    getData() {
      axios
        .get('/api/events/latest')
        .then((response) => {
          this.event = response.data.data;
        })
        .catch((error) => {
          this.showErrorDialog(error);
        });

      axios.get('/api/statistics').then((response) => {
        this.statistics = response.data.data;
      });
    },
  },
};
</script>

<style scoped></style>
