<template>
  <div class="component-container">
    <p class="title">Edit automations</p>

    <div class="subtitle">{{ profile.name }}</div>

    <div class="columns">
      <div class="column is-one-third">
        <div v-if="automationSuccess" class="notification is-primary">
          <button class="delete" @click="automationSuccess = false"></button>
          automation updated!
        </div>

        <div v-if="webRequestConfigs.length > 0" class="box">
          <p class="subtitle">Web Request</p>
          <table class="table">
            <thead>
              <tr>
                <th>Subscribe</th>
                <th>Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="config in webRequestConfigs">
                <td>
                  <input
                    :checked="isSubscribed(config)"
                    type="checkbox"
                    @change="updateautomation($event, config.id, 'webRequest')"
                  />
                </td>
                <td>{{ config.name }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="folderCopyConfigs.length > 0" class="box">
          <p class="subtitle">Folder Copy</p>
          <table class="table">
            <thead>
              <tr>
                <th>Subscribe</th>
                <th>Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="config in folderCopyConfigs">
                <td>
                  <input
                    :checked="isSubscribed(config)"
                    type="checkbox"
                    @change="updateautomation($event, config.id, 'folderCopy')"
                  />
                </td>
                <td>{{ config.name }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="telegramConfigs.length > 0" class="box">
          <p class="subtitle">Telegram</p>
          <table class="table">
            <thead>
              <tr>
                <th>Subscribe</th>
                <th>Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="config in telegramConfigs">
                <td>
                  <input
                    :checked="isSubscribed(config)"
                    type="checkbox"
                    @change="updateautomation($event, config.id, 'telegram')"
                  />
                </td>
                <td>{{ config.name }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="smbCifsCopyConfigs.length > 0" class="box">
          <p class="subtitle">SMB/CIFS Copy</p>
          <table class="table">
            <thead>
              <tr>
                <th>Subscribe</th>
                <th>Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="config in smbCifsCopyConfigs">
                <td>
                  <input
                    :checked="isSubscribed(config)"
                    type="checkbox"
                    @change="updateautomation($event, config.id, 'smbCifsCopy')"
                  />
                </td>
                <td>{{ config.name }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EditProfileautomations',

  props: ['id'],

  data() {
    return {
      profile: {},
      telegramConfigs: [],
      webRequestConfigs: [],
      folderCopyConfigs: [],
      smbCifsCopyConfigs: [],
      automationSuccess: false,
    };
  },

  computed: {
    profileId() {
      return parseInt(this.id, 10);
    },
  },

  mounted() {
    this.getData();
  },

  methods: {
    getData() {
      axios.get(`/api/profiles/${this.id}`).then((response) => {
        this.profile = response.data.data;
      });
      axios.get('/api/telegram').then((response) => {
        this.telegramConfigs = response.data.data;
      });
      axios.get('/api/webRequest').then((response) => {
        this.webRequestConfigs = response.data.data;
      });
      axios.get('/api/folderCopy').then((response) => {
        this.folderCopyConfigs = response.data.data;
      });
      axios.get('/api/smbCifsCopy').then((response) => {
        this.smbCifsCopyConfigs = response.data.data;
      });
    },

    isSubscribed(config) {
      let subscribed = false;
      config.detection_profiles.forEach((p) => {
        if (p.id === this.profileId) subscribed = true;
      });
      return subscribed;
    },

    updateautomation(event, id, type) {
      this.automationSuccess = false;
      const { checked } = event.target;

      const formData = new FormData();
      formData.append('id', id);
      formData.append('type', type);
      formData.append('value', checked);

      axios
        .post(`/api/profiles/${this.id}/automations`, formData)
        .then(() => {
          this.automationSuccess = true;
        })
        .catch(() => {
          event.target.checked = !checked;
        });
    },
  },
};
</script>

<style scoped></style>
