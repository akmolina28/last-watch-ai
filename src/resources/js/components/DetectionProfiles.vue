<template>
  <div class="component-wrapper">
    <title-header>
      <template v-slot:title>
        Detection Profiles
      </template>
      <template v-slot:subtitle>
        Configure the AI to run when certain image files are created
      </template>
    </title-header>

    <div class="mb-3">
      <a class="button is-primary" href="/profiles/create">
        <span>New Profile</span>
        <span class="icon">
          <b-icon icon="plus"></b-icon>
        </span>
      </a>
    </div>

    <b-table
      hoverable
      mobile-cards
      :data="isEmpty ? [] : profiles"
      :loading="profilesLoading"
      class="mb-3"
    >
      <b-table-column field="name" label="Profile Name" v-slot="props">
        {{ props.row.name }}
      </b-table-column>

      <b-table-column field="file_pattern" label="File Pattern" v-slot="props">
        {{ props.row.file_pattern }}
      </b-table-column>

      <b-table-column field="use_regex" label="Regex" v-slot="props">
        <b-icon v-if="props.row.use_regex" icon="check"></b-icon>
      </b-table-column>

      <b-table-column field="use_smart_filter" label="Smart Filter" v-slot="props">
        <b-icon v-if="props.row.use_smart_filter" icon="check"></b-icon>
      </b-table-column>

      <b-table-column field="object_classes" label="Object Classes" v-slot="props">
        {{ props.row.is_negative ? 'NEG: ' : '' }}{{ props.row.object_classes.join(' | ') }}
      </b-table-column>

      <b-table-column field="min_confidence" label="Min Confidence" v-slot="props">
        {{ props.row.min_confidence }}
      </b-table-column>

      <b-table-column field="min_object_size" label="Min Size" v-slot="props">
        {{ props.row.min_object_size }}
      </b-table-column>

      <b-table-column field="status" label="Status" v-slot="props">
        <b-dropdown aria-role="list">
          <button
            :class="
              'button is-primary' +
                (props.row.status === 'disabled' ? ' is-light' : '') +
                (props.row.isStatusUpdating ? ' is-loading' : '')
            "
            slot="trigger"
            slot-scope="{ statusDropdownActive }"
          >
            <span>{{ props.row.status | menuize }}</span>
            <b-icon :icon="statusDropdownActive ? 'chevron-up' : 'chevron-down'"></b-icon>
          </button>

          <b-dropdown-item aria-role="listitem" @click="updateStatus(props.row, 'enabled')">
            <span class="icon has-text-success">
              <b-icon icon="circle"></b-icon>
            </span>
            Enabled
          </b-dropdown-item>
          <hr class="dropdown-divider" />
          <b-dropdown-item aria-role="listitem" @click="updateStatus(props.row, 'disabled')">
            <span class="icon has-text-grey">
              <b-icon icon="ban"></b-icon>
            </span>
            Disabled
          </b-dropdown-item>
          <hr class="dropdown-divider" />
          <b-dropdown-item aria-role="listitem" @click="showSchedulerModal(props.row)">
            <span class="icon has-text-primary">
              <b-icon icon="clock"></b-icon>
            </span>
            As Scheduled
          </b-dropdown-item>
        </b-dropdown>
      </b-table-column>
      <b-table-column label="" v-slot="props">
        <b-dropdown aria-role="list" position="is-bottom-left">
          <template #trigger="{ active }">
            <b-button type="is-primary is-outlined" icon-right="cog" />
          </template>

          <router-link :to="`/profiles/${props.row.id}/edit`">
            <b-dropdown-item aria-role="listitem">
              <b-icon icon="edit"></b-icon>
              <span>
                Edit Profile
              </span>
            </b-dropdown-item>
          </router-link>

          <a href="javascript:void(0);">
            <b-dropdown-item aria-role="listitem" @click="copySlug(props.row)">
              <b-icon icon="copy"></b-icon>
              <span>
                Copy API Slug
              </span>
            </b-dropdown-item>
          </a>

          <router-link :to="`/profiles/${props.row.id}/automations`">
            <b-dropdown-item aria-role="listitem">
              <b-icon icon="robot"></b-icon>
              <span>
                Profile Automations
              </span>
            </b-dropdown-item>
          </router-link>
        </b-dropdown>
      </b-table-column>
      <b-table-column field="delete" label="" v-slot="props">
        <button
          @click="deleteProfile(props.row)"
          :class="'button is-danger is-outlined' + (props.row.isDeleting ? ' is-loading' : '')"
        >
          <span class="icon is-small">
            <b-icon icon="trash"></b-icon>
          </span>
        </button>
      </b-table-column>
    </b-table>

    <b-modal
      v-model="isModalActive"
      trap-focus
      has-modal-card
      :destroy-on-hide="false"
      aria-role="dialog"
      aria-modal
    >
      <template #default="props">
        <scheduler-modal v-bind="modalProps" @close="props.close" @save="saveSchedule">
        </scheduler-modal>
      </template>
    </b-modal>
  </div>
</template>

<script>
import axios from 'axios';

const SchedulerModal = {
  props: ['profile'],
  template: `
            <form action="">
                <div class="modal-card" style="width: auto">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Set Schedule</p>
                        <button
                            type="button"
                            class="delete"
                            @click="$emit('close')"/>
                    </header>
                    <section class="modal-card-body">
                        <h5 class="title is-size-5">{{ profile.name }}</h5>
                        <b-field label="Start Time">
                            <b-timepicker
                                inline
                                v-model="profile.startTime"
                                hour-format="24">
                            </b-timepicker>
                        </b-field>

                        <b-field label="End Time" class="mb-10">
                            <b-timepicker
                                inline
                                v-model="profile.endTime"
                                hour-format="24">
                            </b-timepicker>
                        </b-field>

                    </section>
                    <footer class="modal-card-foot">
                        <button class="button" type="button" @click="$emit('close')">Close</button>
                        <button class="button is-primary" @click.prevent="save()">Save</button>
                    </footer>
                </div>
            </form>
        `,
  methods: {
    save() {
      this.$emit('save', this.profile);
      this.$emit('close');
    },
  },
};
export default {
  name: 'DetectionProfiles',
  components: {
    SchedulerModal,
  },
  data() {
    return {
      isEmpty: true,
      profilesLoading: false,
      profiles: [],
      meta: {},
      isModalActive: false,
      modalProps: {
        profile: {},
      },
    };
  },

  mounted() {
    this.getData();
  },

  filters: {
    menuize(value) {
      if (value === 'enabled') return 'Enabled';
      if (value === 'disabled') return 'Disabled';
      if (value === 'as_scheduled') return 'As Scheduled';
      return value;
    },
  },

  methods: {
    copySlug(profile) {
      this.$copyText(profile.slug).then(
        () => {
          this.$buefy.toast.open({
            message: 'Slug copied!',
            type: 'is-success',
          });
        },
        () => {
          this.$buefy.toast.open({
            message: "Can't copy slug",
            type: 'is-danger',
          });
        },
      );
    },

    showSchedulerModal(profile) {
      this.modalProps.profile = profile;
      this.isModalActive = true;
    },

    getEndTime(profile) {
      if (profile.start_time) {
        return Date.parse(`2020-01-01 ${profile.start_time}`);
      }
      return null;
    },

    statusButtonClass(profile) {
      let c = 'button';

      if (profile.status === 'active') {
        c += ' is-primary';
      }

      return c;
    },

    getData() {
      this.profilesLoading = true;
      axios
        .get('/api/profiles')
        .then((response) => {
          const profiles = response.data.data;
          for (let i = 0; i < profiles.length; i += 1) {
            profiles[i].isDeleting = false;
            profiles[i].isStatusUpdating = false;
            profiles[i].startTime = profiles[i].start_time
              ? new Date(`2020-01-01 ${profiles[i].start_time}`)
              : null;
            profiles[i].endTime = profiles[i].end_time
              ? new Date(`2020-01-01 ${profiles[i].end_time}`)
              : null;
          }
          this.profiles = profiles;
          this.meta = response.data.meta;
          this.isEmpty = false;
          this.profilesLoading = false;
        })
        .catch(() => {
          this.profilesLoading = false;
        });
    },

    deleteProfile(profile) {
      profile.isDeleting = true;
      axios
        .delete(`/api/profiles/${profile.id}`)
        .then(() => {
          this.profiles = this.profiles.filter((p) => p.id !== profile.id);
        })
        .catch(() => {
          profile.isDeleting = false;
        });
    },

    getTime(date) {
      if (!date) return '';

      const hour = date.getHours();
      const hourStr = hour < 10 ? `0${hour}` : `${hour}`;

      const minute = date.getMinutes();
      const minStr = minute < 10 ? `0${minute}` : `${minute}`;

      return `${hourStr}:${minStr}`;
    },

    saveSchedule(profile) {
      this.updateStatus(profile, 'as_scheduled');
    },

    updateStatus(profile, status) {
      profile.isStatusUpdating = true;

      axios
        .put(`/api/profiles/${profile.id}/status`, {
          status,
          start_time: this.getTime(profile.startTime),
          end_time: this.getTime(profile.endTime),
        })
        .then(() => {
          profile.isStatusUpdating = false;
          profile.status = status;
        })
        .catch(() => {
          profile.isStatusUpdating = false;
        });
    },
  },
};
</script>

<style scoped>
.modal .animation-content .modal-card {
  overflow: visible !important;
}

.modal-card-body {
  overflow: visible !important;
}
</style>
