<template>
  <div class="component-container">
    <title-header>
      <template v-slot:title>
        Web Request
      </template>
      <template v-slot:subtitle>
        Make Http GET requests
      </template>
    </title-header>

    <b-table :data="webRequestConfigs">
      <template #empty>
        No automations set up yet. Use the form below.
      </template>
      <b-table-column field="name" label="Name" v-slot="props">
        {{ props.row.name }}
      </b-table-column>
      <b-table-column field="url" label="Url" v-slot="props">
        {{ props.row.url }}
      </b-table-column>
      <b-table-column field="is_post" label="Post" v-slot="props">
        <b-icon v-if="props.row.is_post" icon="check"></b-icon>
      </b-table-column>
      <b-table-column field="body_json" label="Body" v-slot="props">
        <span :title="props.row.body_json">{{ props.row.body_json | truncate }}</span>
      </b-table-column>
      <b-table-column field="profile_count" label="Used By" v-slot="props">
        {{ props.row.detection_profiles.length }} profile(s)
      </b-table-column>
      <b-table-column field="delete" label="Delete" v-slot="props">
        <b-button
          icon-right="trash"
          type="is-danger is-outlined"
          :loading="props.row.isDeleting"
          @click="deleteClick(props.row)"
        >
        </b-button>
      </b-table-column>
    </b-table>

    <div class="box is-6">
      <p class="subtitle">New Config</p>

      <form @submit.prevent="processForm">
        <b-field label="Name">
          <b-input v-model="name" placeholder="Unique name" name="name" required></b-input>
        </b-field>

        <div class="px-5 py-5 mb-3 has-background-light">
          <h5 class="title is-size-5">Substitution Variables</h5>
          <ul>
            <li v-for="(description, replacement) in replacements">
              <strong>{{ replacement }}</strong> - {{ description }}
            </li>
          </ul>
        </div>

        <b-field label="Headers (json)">
          <b-input v-model="headersJson" name="headersJson" type="textarea"></b-input>
        </b-field>

        <b-field label="URL">
          <b-input v-model="url" placeholder="http://some.site/api" name="url" required></b-input>
        </b-field>

        <b-field label="POST">
          <b-switch v-model="isPost" @input="changePost" />
        </b-field>

        <b-field label="Body (json)">
          <b-input v-model="bodyJson" name="bodyJson" type="textarea" :disabled="!isPost"></b-input>
        </b-field>

        <b-button type="is-primary" :loading="isSaving" icon-left="save" native-type="submit">
          Save Profile
        </b-button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'WebRequestConfigs',

  data() {
    return {
      webRequestConfigs: [],
      replacements: [],
      name: '',
      url: '',
      isPost: false,
      bodyJson: '',
      headersJson: '',
      isSaving: false,
    };
  },

  mounted() {
    this.getData();
  },

  filters: {
    truncate(value) {
      const val = value ?? '';
      let ret = val.substring(0, 19);
      if (val.length > 19) {
        ret += '...';
      }
      return ret;
    },
  },

  methods: {
    getData() {
      axios.get('/api/automations/webRequest').then((response) => {
        const configs = response.data.data;
        for (let i = 0; i < configs.length; i += 1) {
          configs[i].isDeleting = false;
        }
        this.webRequestConfigs = configs;
      });

      axios.get('/api/automations/replacements').then((response) => {
        this.replacements = response.data.data;
      });
    },

    deleteClick(config) {
      if (config.detection_profiles.length > 0) {
        this.$buefy.dialog.confirm({
          title: 'Confirm Delete',
          message: `Deleting this automation will automatically unsubscribe ${config.detection_profiles.length} Detection Profile(s).`,
          confirmText: 'Delete',
          type: 'is-danger',
          hasIcon: true,
          onConfirm: () => this.delete(config),
        });
      } else {
        this.delete(config);
      }
    },

    delete(config) {
      config.isDeleting = true;

      axios
        .delete(`/api/automations/webRequest/${config.id}`)
        .then(() => {
          this.webRequestConfigs = this.webRequestConfigs.filter((c) => c.id !== config.id);
        })
        .catch(() => {
          config.deleting = false;
          this.$buefy.toast.open({
            message: 'Something went wrong. Refresh and try again.',
            type: 'is-danger',
          });
        });
    },

    changePost() {
      this.bodyJson = '';
    },

    processForm() {
      const formData = {
        name: this.name,
        url: this.url,
        is_post: this.isPost,
        body_json: this.bodyJson,
        headers_json: this.headersJson,
      };

      this.isSaving = true;
      axios
        .post('/api/automations/webRequest', formData)
        .then((response) => {
          this.webRequestConfigs.push(response.data.data);
          this.name = '';
          this.url = '';
          this.isPost = false;
          this.bodyJson = '';
          this.headersJson = '';
        })
        .catch((error) => {
          const { errors } = error.response.data;
          if (errors && errors.name) {
            this.$buefy.toast.open({
              message: errors.name[0],
              type: 'is-danger',
            });
          } else {
            this.$buefy.toast.open({
              message: 'Something went wrong. Refresh and try again.',
              type: 'is-danger',
            });
          }
        })
        .finally(() => {
          this.isSaving = false;
        });
    },

    checkForm(e) {
      if (this.name && this.url) {
        this.submitForm();
      } else {
        this.errors = [];

        if (!this.name) {
          this.errors.push('Name required.');
        }
        if (!this.url) {
          this.errors.push('URL required.');
        }

        this.showErrorDialog(this.errors.join(' '));
      }

      e.preventDefault();
    },
  },
};
</script>

<style scoped></style>
