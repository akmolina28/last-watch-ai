<template>
  <div>
    <section class="mb-5 box" style="width:100%">
      <h6 class="title is-6">Profile Groups</h6>
      <div v-if="profileGroups && profileGroups.length > 0" class="mb-5">
        <b-field v-for="group in profileGroups" :key="group.id">
          <b-checkbox v-model="group.checked">{{ group.name }}</b-checkbox>
        </b-field>
      </div>

      <div v-else>
        <p class="is-italic has-text-grey-light mb-5">
          Add a group to categorize this profile, e.g. "Front Door", "Person Events", etc.
        </p>
      </div>

      <section>
        <b-field v-if="!newGroupEditing">
          <b-button icon-left="plus" size="is-small" @click="newGroupEditing = true">
            New Group
          </b-button>
        </b-field>
        <b-field v-else grouped>
          <b-input
            v-model="newGroupName"
            expanded
            size="is-small"
            v-on:keyup.enter="saveNewGroup"
          ></b-input>
          <b-button size="is-small" class="mr-1" @click="saveNewGroup" :loading="newGroupSaving"
            ><b-icon icon="check"></b-icon
          ></b-button>
          <b-button size="is-small" @click="newGroupEditing = false"
            ><b-icon icon="times"></b-icon
          ></b-button>
        </b-field>
      </section>
    </section>
    <div class="is-flex is-flex-direction-row-reverse">
      <b-button :loading="groupsSaving" icon-left="save" type="is-primary" @click="saveGroups">
        Save and Continue</b-button
      >
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EditProfileGroups',
  props: ['profileId'],
  data() {
    return {
      loading: true,
      profileGroups: [],
      newGroupEditing: false,
      newGroupName: '',
      newGroupSaving: false,
      groupsSaving: false,
    };
  },
  mounted() {
    this.loadGroups(this.profileId);
  },
  computed: {
    checkedGroupIds() {
      if (this.profileGroups) {
        return this.profileGroups.filter((g) => g.checked).map((h) => h.id);
      }
      return [];
    },
  },
  methods: {
    loadGroups(profileId) {
      this.getGroupsAjax()
        .then(({ data }) => {
          const groups = data.data;
          for (let i = 0; i < groups.length; i += 1) {
            groups[i].checked = groups[i].detection_profiles.some((d) => d.id === profileId);
          }
          this.profileGroups = groups;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getGroupsAjax() {
      return axios.get('/api/profileGroups');
    },
    saveGroups() {
      this.groupsSaving = true;
      this.saveGroupsAjax(this.profileId)
        .then(() => {
          this.$emit('groups-saved');
          this.$buefy.toast.open({
            message: 'Groups saved.',
            type: 'is-primary',
          });
        })
        .catch(() => {
          this.$buefy.toast.open({
            message: 'Something went wrong. Please try again.',
            type: 'is-danger',
          });
        })
        .finally(() => {
          this.groupsSaving = false;
        });
    },
    saveGroupsAjax(profileId) {
      return axios.put(`/api/profiles/${profileId}/groups`, {
        group_ids: this.checkedGroupIds,
      });
    },
    saveNewGroup() {
      if (!this.newGroupName) {
        this.$buefy.toast.open({
          message: 'Group name is required.',
          type: 'is-danger',
        });
      } else {
        this.newGroupSaving = true;
        this.createGroupAjax(this.newGroupName)
          .then(({ data }) => {
            const newGroup = data.data;
            newGroup.checked = true;
            this.profileGroups.push(data.data);
            this.newGroupEditing = false;
            this.newGroupName = '';
          })
          // eslint-disable-next-line no-unused-vars
          .catch((jqXHR) => {
            const { status } = jqXHR.response;
            let e = jqXHR.message;
            if (status === 422 && jqXHR.response.data.errors) {
              const keys = Object.keys(jqXHR.response.data.errors);
              e = jqXHR.response.data.errors[keys[0]];
            }
            this.$buefy.toast.open({
              message: e,
              type: 'is-danger',
            });
          })
          .finally(() => {
            this.newGroupSaving = false;
          });
      }
    },
    createGroupAjax(name) {
      return axios.post('/api/profileGroups', {
        name,
      });
    },
  },
};
</script>

<style lang="scss" scoped></style>
