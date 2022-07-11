<template>
  <div class="component-container">
    <title-header>
      <template v-slot:title>
        Profile Groups
      </template>
      <template v-slot:subtitle>
        Manage groups for detection profiles
      </template>
    </title-header>
    <div class="columns">
      <div class="column is-one-quarter"></div>
      <div class="column is-half box">
        <b-menu v-if="profileGroups" :accordion="false" :activable="false">
          <b-menu-list>
            <!-- <transition-group name="fade"> -->
            <b-menu-item v-if="profileGroups.length == 0" expanded>
              <template #label="props">
                <span class="is-italic">Add a new group to begin grouping profiles together.</span>
              </template>
            </b-menu-item>
            <b-menu-item v-for="group in profileGroups" :key="group.id" expanded>
              <template #label="props">
                <span class="has-text-weight-bold">{{ group.name }}</span>
                <b-dropdown aria-role="list" class="is-pulled-right" position="is-bottom-left">
                  <template #trigger>
                    <b-icon icon="ellipsis-v"></b-icon>
                  </template>
                  <b-dropdown-item
                    v-if="group.detection_profiles.length != allProfiles.length"
                    aria-role="listitem"
                    @click="group.adding = true"
                  >
                    <b-icon icon="plus"></b-icon>
                    Add Profile
                  </b-dropdown-item>
                  <b-dropdown-item aria-role="listitem" @click="deleteGroup(group)">
                    <b-icon icon="trash"></b-icon>
                    Delete Group
                  </b-dropdown-item>
                </b-dropdown>
              </template>
              <b-menu-item v-for="profile in group.detection_profiles" :key="profile.id">
                <template #label="props">
                  <span>{{ profile.name }}</span>
                  <b-icon
                    class="is-pulled-right"
                    icon="times"
                    @click.native="attachProfileToGroup(group, profile.id, true)"
                  ></b-icon>
                </template>
              </b-menu-item>
              <b-menu-item v-if="group.adding">
                <template #label="props">
                  <b-field grouped>
                    <b-select v-model="group.newProfileId" size="is-small">
                      <option
                        v-for="newProfile in allProfiles.filter(
                          (p) => !group.detection_profiles.some((d) => d.id === p.id),
                        )"
                        :key="newProfile.id"
                        :value="newProfile.id"
                        >{{ newProfile.name }}</option
                      >
                    </b-select>
                    <b-button
                      icon-left="save"
                      size="is-small"
                      class="mr-2"
                      @click="attachProfileToGroup(group, group.newProfileId)"
                    ></b-button>
                    <b-button
                      icon-left="times"
                      size="is-small"
                      @click="group.adding = false"
                    ></b-button>
                  </b-field>
                </template>
              </b-menu-item>
            </b-menu-item>
            <!-- </transition-group> -->
          </b-menu-list>
        </b-menu>
        <hr />
        <div v-if="addingNewGroup">
          <b-field grouped>
            <b-input
              v-model="newGroupName"
              size="is-small"
              expanded
              placeholder="Group name"
            ></b-input>
            <b-button
              icon-left="check"
              class="mr-2"
              size="is-small"
              :loading="newGroupSaving"
              @click="saveNewGroup"
            ></b-button>
            <b-button icon-left="times" size="is-small" @click="addingNewGroup = false"></b-button>
          </b-field>
        </div>
        <b-button
          v-else
          type="is-primary"
          size="is-small"
          icon-left="plus"
          @click="addingNewGroup = true"
          >New Group</b-button
        >
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ProfileGroups',
  data() {
    return {
      profileGroups: null,
      allProfiles: null,
      addingNewGroup: false,
      newGroupName: '',
      newGroupSaving: false,
    };
  },
  mounted() {
    this.loadGroups();
    this.getAllProfiles();
  },
  methods: {
    adding(group) {
      if (this.allProfiles && this.allProfiles.length > 0) {
        if (!group.detection_profiles.some((d) => d.name == null)) {
          group.detection_profiles.push({});
        }
      } else {
        this.$buefy.toast.open({
          message: 'No profiles exist.',
          type: 'is-primary',
        });
      }
    },
    loadGroups() {
      this.getGroupsAjax()
        .then(({ data }) => {
          const groups = data.data;
          for (let i = 0; i < groups.length; i += 1) {
            groups[i].adding = false;
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
    getAllProfiles() {
      this.getProfilesAjax().then(({ data }) => {
        this.allProfiles = data.data;
      });
    },
    getProfilesAjax() {
      return axios.get('/api/profiles');
    },
    deleteGroup(group) {
      this.$buefy.dialog.confirm({
        title: 'Delete Group',
        message: `Delete the group <strong>${group.name}</strong>?`,
        confirmText: 'Delete',
        type: 'is-danger',
        hasIcon: true,
        onConfirm: () => {
          axios
            .delete(`/api/profileGroups/${group.id}`)
            .then(() => {
              this.profileGroups = this.profileGroups.filter((p) => p.id !== group.id);
              this.$buefy.toast.open({
                message: 'Group deleted.',
                type: 'is-primary',
              });
            })
            .catch(() => {
              this.$buefy.toast.open({
                message: 'Something went wrong. Please try again.',
                type: 'is-danger',
              });
            });
        },
      });
    },
    attachProfileToGroup(group, profileId, detach = false) {
      axios
        .put(`/api/profileGroups/${group.id}/attachProfile`, {
          profileId,
          detach,
        })
        .then(() => {
          if (detach) {
            group.detection_profiles = group.detection_profiles.filter((p) => p.id !== profileId);
          } else {
            group.adding = false;
            group.newProfileId = null;
            group.detection_profiles.push(this.allProfiles.filter((p) => p.id === profileId)[0]);
          }
          this.$buefy.toast.open({
            message: 'Group updated.',
            type: 'is-primary',
          });
        })
        .catch(() => {
          this.$buefy.toast.open({
            message: 'Something went wrong. Please try again.',
            type: 'is-danger',
          });
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
            const group = data.data;
            group.adding = false;
            group.detection_profiles = [];
            this.profileGroups.push(group);
            this.addingNewGroup = false;
            this.newGroupName = '';
          })
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
