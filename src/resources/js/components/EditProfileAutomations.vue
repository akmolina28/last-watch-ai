<template>
  <div>
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
              <input v-model="config.value" @change="valueChange(config)" type="checkbox" />
            </td>
            <td>
              <input
                v-model="config.is_high_priority"
                @change="priorityChange(config)"
                type="checkbox"
              />
            </td>
            <td>{{ config.name }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="is-flex is-flex-direction-row-reverse">
      <b-button type="is-primary" icon-left="save" @click="saveAutomationConfigs">Save</b-button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EditProfileAutomations',

  props: ['profileId'],

  data() {
    return {
      automationConfigs: [],
    };
  },

  computed: {
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
    if (this.profileId) {
      this.getData();
    }
  },

  watch: {
    profileId(newValue) {
      if (newValue) {
        this.getData();
      }
    },
  },

  methods: {
    valueChange(config) {
      if (!config.value) {
        config.is_high_priority = false;
      }
    },
    priorityChange(config) {
      if (config.is_high_priority) {
        config.value = true;
      }
    },
    getConfigs(type) {
      return this.automationConfigs.filter((c) => c.type === type);
    },

    getData() {
      axios.get(`/api/profiles/${this.profileId}`).then((response) => {
        this.profile = response.data.data;
      });
      axios.get(`/api/profiles/${this.profileId}/automations`).then((response) => {
        const configs = response.data.data;
        for (let i = 0; i < configs.length; i += 1) {
          configs[i].value = configs[i].detection_profile_id != null;
          configs[i].is_high_priority = configs[i].is_high_priority === 1;
        }
        this.automationConfigs = configs;
      });
    },

    saveAutomationConfigs(config) {
      axios
        .put(`/api/profiles/${this.profileId}/automations`, {
          automations: this.automationConfigs,
        })
        .then(() => {
          this.$buefy.toast.open({
            message: 'Automations saved.',
            type: 'is-primary',
          });
          this.$router.push('/profiles');
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
