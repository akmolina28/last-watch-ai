<template>
  <div class="component-container">
    <title-header>
      <template v-slot:title>
        Folder Copy
      </template>
      <template v-slot:subtitle>
        Copy image files to a local folder
      </template>
    </title-header>

    <b-table :data="folderCopyConfigs">
      <template #empty>
        No automations set up yet. Use the form below.
      </template>
      <b-table-column field="name" label="Name" v-slot="props">
        {{ props.row.name }}
      </b-table-column>
      <b-table-column field="copy_to" label="Copy To" v-slot="props">
        {{ props.row.copy_to }}
      </b-table-column>
      <b-table-column field="copy_to" label="Overwrite?" v-slot="props">
        <b-icon v-if="props.row.overwrite" icon="check"></b-icon>
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
      <form @submit="checkForm" method="POST" action="/automations/folderCopy" ref="folderCopyForm">
        <div class="field">
          <label class="label" for="name">Name</label>

          <div class="control">
            <input v-model="name" class="input" type="text" name="name" id="name" />
          </div>
        </div>

        <div class="field">
          <label class="label" for="copy_to">Copy To</label>

          <div class="control">
            <input v-model="copy_to" class="input" type="text" name="copy_to" id="copy_to" />
          </div>
        </div>

        <div class="field">
          <label class="label" for="overwrite">Overwrite</label>

          <div class="control">
            <input
              v-model="overwrite"
              class="checkbox"
              type="checkbox"
              name="overwrite"
              id="overwrite"
            />
          </div>
        </div>

        <div class="field is-grouped">
          <div class="control">
            <button class="button is-link" type="submit">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FolderCopyConfigs',

  data() {
    return {
      folderCopyConfigs: [],
      name: '',
      copy_to: '',
      overwrite: false,
    };
  },

  mounted() {
    this.getData();
  },

  methods: {
    getData() {
      axios.get('/api/automations/folderCopy').then((response) => {
        const configs = response.data.data;
        for (let i = 0; i < configs.length; i += 1) {
          configs[i].isDeleting = false;
        }
        this.folderCopyConfigs = configs;
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
        .delete(`/api/automations/folderCopy/${config.id}`)
        .then(() => {
          this.folderCopyConfigs = this.folderCopyConfigs.filter((c) => c.id !== config.id);
        })
        .catch(() => {
          config.deleting = false;
          this.$buefy.toast.open({
            message: 'Something went wrong. Refresh and try again.',
            type: 'is-danger',
          });
        });
    },

    submitForm() {
      axios
        .post('/api/automations/folderCopy', {
          name: this.name,
          copy_to: this.copy_to,
          overwrite: this.overwrite,
        })
        .then((response) => {
          this.folderCopyConfigs.push(response.data.data);
          this.name = '';
          this.copy_to = '';
          this.overwrite = false;
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
        });
    },

    checkForm(e) {
      if (this.name && this.copy_to) {
        this.submitForm();
      } else {
        this.errors = [];

        if (!this.name) {
          this.errors.push('Name required.');
        }
        if (!this.copy_to) {
          this.errors.push('Copy To required.');
        }

        this.showErrorDialog(this.errors.join(' '));
      }

      e.preventDefault();
    },
  },
};
</script>

<style scoped></style>
