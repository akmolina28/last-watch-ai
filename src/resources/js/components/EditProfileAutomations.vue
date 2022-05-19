<template>
  <div class="component-container">
    <title-header>
      <template v-slot:title>
        Profile Automations
      </template>
      <template v-slot:subtitle>
        Do something when <strong>{{ profile.name }}</strong> is triggered
      </template>
    </title-header>

    <div class="columns">
      <div class="column is-one-third">
        <p v-if="configTypes.length === 0">
          No automations have been set up yet.
        </p>

        <div v-for="type in configTypes" class="box mb-5" style="overflow-x:auto;">
          <h4 class="heading is-size-4">{{ type | headerize }}</h4>

          <table class="table">
            <thead>
              <tr>
                <th>Subscribe</th>
                <th>High Priority</th>
                <th>Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="config in getConfigs(type)">
                <td>
                  <input
                    v-model="config.isSubscribed"
                    type="checkbox"
                    @change="subscribeChange(config)"
                  />
                </td>
                <td>
                  <input
                    v-model="config.isHighPriority"
                    type="checkbox"
                    @change="highPriorityChange(config)"
                  />
                </td>
                <td>{{ config.name }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <a class="button" href="/profiles">Done</a>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EditProfileAutomations',

  props: ['id'],

  data() {
    return {
      automationConfigs: [],
      profile: {},
    };
  },

  computed: {
    profileId() {
      return parseInt(this.id, 10);
    },
    configTypes() {
      const types = [];
      for (let i = 0; i < this.automationConfigs.length; i += 1) {
        if (!types.includes(this.automationConfigs[i].type)) {
          types.push(this.automationConfigs[i].type);
        }
      }
      return types;
    },
  },

  filters: {
    headerize(value) {
      let ret = value ?? '';
      if (ret.indexOf('_config') > 0) {
        ret = ret.substring(0, ret.indexOf('_config'));
      }
      ret = ret.replace(/_/g, ' ');
      return ret;
    },
  },

  mounted() {
    this.getData();
  },

  methods: {
    getConfigs(type) {
      return this.automationConfigs.filter((c) => c.type === type);
    },

    getData() {
      axios.get(`/api/profiles/${this.id}`).then((response) => {
        this.profile = response.data.data;
      });
      axios.get(`/api/profiles/${this.id}/automations`).then((response) => {
        const configs = response.data.data;
        for (let i = 0; i < configs.length; i += 1) {
          configs[i].isSubscribed = configs[i].detection_profile_id != null;
          configs[i].isHighPriority = configs[i].is_high_priority === 1;
        }
        this.automationConfigs = configs;
      });
    },

    subscribeChange(config) {
      if (!config.isSubscribed) {
        config.isHighPriority = false;
      }
      this.updateAutomationConfig(config);
    },

    highPriorityChange(config) {
      if (config.isHighPriority) {
        config.isSubscribed = true;
      }
      this.updateAutomationConfig(config);
    },

    updateAutomationConfig(config) {
      axios
        .put(`/api/profiles/${this.id}/automations`, {
          id: config.id,
          type: config.type,
          value: config.isSubscribed,
          is_high_priority: config.isHighPriority,
        })
        .then(() => {
          this.$buefy.toast.open({
            message: 'Automations saved!',
            type: 'is-success',
          });
        })
        .catch(() => {
          this.$buefy.toast.open({
            message: 'Something went wrong. Refresh and try again.',
            type: 'is-danger',
          });
          config.isSubscribed = false;
          config.isHighPriority = false;
        });
    },
  },
};
</script>

<style scoped></style>
