<template>
  <div class="component-wrapper">
    <title-header>
      <template v-slot:title>{{ edit ? 'Edit' : 'Create' }} Profile</template>
      <template v-slot:subtitle>
        {{ edit ? profileName : 'Configure the file watcher and AI triggers' }}
      </template>
    </title-header>
    <b-steps v-model="activeStep" :has-navigation="false" mobile-mode="compact">
      <b-step-item
        :label="edit ? 'Edit Profile' : 'Add Profile'"
        icon="plus"
        :clickable="allowSkipAhead"
      >
        <div class="columns">
          <div class="column is-one-third"></div>
          <div class="column is-one-third">
            <edit-profile
              @profile-saved="profileSaved"
              :id="profileId"
              @profile-loaded="(p) => (profileName = p.name)"
            ></edit-profile>
          </div>
        </div>
      </b-step-item>
      <b-step-item
        :label="edit ? 'Edit Groups' : 'Add Groups'"
        icon="layer-group"
        :clickable="allowSkipAhead"
      >
        <div class="columns">
          <div class="column is-one-third"></div>
          <div class="column is-one-third">
            <edit-profile-groups
              :profile-id="profileId"
              @groups-saved="activeStep += 1"
            ></edit-profile-groups>
          </div>
        </div>
      </b-step-item>
      <b-step-item
        :label="edit ? 'Automations' : 'Attach Automations'"
        icon="robot"
        :clickable="allowSkipAhead"
      >
        <div class="columns">
          <div class="column is-one-quarter"></div>
          <div class="column is-half">
            <edit-profile-automations :profile-id="profileId"></edit-profile-automations>
          </div>
        </div>
      </b-step-item>
    </b-steps>
  </div>
</template>

<script>
import EditProfile from './EditProfile.vue';
import EditProfileGroups from './EditProfileGroups.vue';
import EditProfileAutomations from './EditProfileAutomations.vue';

export default {
  name: 'EditProfileStep',
  props: ['id'],
  components: {
    EditProfile,
    EditProfileGroups,
    EditProfileAutomations,
  },
  data() {
    return {
      activeStep: 0,
      profileId: parseInt(this.id, 10) || null,
      edit: this.id !== null,
      profileName: ' ',
    };
  },
  computed: {
    allowSkipAhead() {
      return this.profileId != null;
    },
  },
  methods: {
    profileSaved(id) {
      this.profileId = id;
      this.nextStep();
    },
    nextStep() {
      this.activeStep += 1;
    },
  },
};
</script>

<style lang="scss" scoped></style>
