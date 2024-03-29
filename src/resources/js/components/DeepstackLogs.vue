<template>
  <div class="component-container">
    <title-header>
      <template v-slot:title>
        Deepstack Logs
      </template>
      <template v-slot:subtitle>
        AI results and performance
      </template>
    </title-header>

    <b-table hoverable mobile-cards :data="logs" :loading="loading" class="mb-3">
      <b-table-column field="is_error" v-slot="props">
        <b-icon v-if="props.row.is_error" type="is-danger" icon="exclamation-triangle"></b-icon>
        <b-icon v-else-if="props.row.returned_at" type="is-success" icon="check"></b-icon>
        <b-icon v-else icon="circle-notch" custom-class="fa-spin"></b-icon>
      </b-table-column>
      <b-table-column field="called_at" label="Started At" v-slot="props">
        {{ props.row.called_at | dateStrRelative }}
      </b-table-column>

      <b-table-column field="detection_event_id" label="Event" v-slot="props">
        <a :href="`/events/${props.row.detection_event_id}`">{{ props.row.detection_event_id }}</a>
      </b-table-column>

      <b-table-column field="run_time_seconds" label="Run Time" v-slot="props">
        {{ props.row.run_time_seconds | runTime }}
      </b-table-column>

      <b-table-column field="input_file" label="Input File" v-slot="props">
        {{ props.row.input_file }}
      </b-table-column>

      <b-table-column field="response_json" label="Response" v-slot="props">
        <a v-if="props.row.response_json" @click="showResponse(props.row.response_json)"
          >Response</a
        >
      </b-table-column>
    </b-table>

    <pagination :data="meta" @pagination-change-page="getData" :limit="5"> </pagination>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DeepstackLogs',

  data() {
    return {
      logs: [],
      meta: {},
      loading: true,
    };
  },

  mounted() {
    this.getData();
  },

  filters: {
    runTime(value) {
      if (value && value > 0) {
        return `${value}s`;
      }
      if (value === 0) {
        return '<1s';
      }
      return '';
    },
  },

  methods: {
    getData(page = 1) {
      this.loading = true;

      axios
        .get(`/api/deepstackLogs?page=${page}`)
        .then((response) => {
          this.logs = response.data.data;
          this.meta = response.data.meta;
          this.isEmpty = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },

    showResponse(responseJson) {
      const prettyJson = this.prettyJson(responseJson);

      this.$buefy.dialog.alert({
        title: 'Deepstack Response',
        message: `<pre>${prettyJson}</pre>`,
        confirmText: 'Close',
      });
    },

    prettyJson(jsonString) {
      const jsonObj = JSON.parse(jsonString);
      return JSON.stringify(jsonObj, null, 2);
    },
  },
};
</script>

<style scoped></style>
