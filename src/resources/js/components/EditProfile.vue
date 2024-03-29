<template>
  <div class="form-wrapper" style="position:relative">
    <b-loading :is-full-page="false" v-model="profileLoading"></b-loading>
    <form @submit.prevent="processForm">
      <b-field label="Name">
        <b-input
          v-model="name"
          placeholder="Unique name"
          name="name"
          :disabled="isEditing"
          required
        ></b-input>
      </b-field>

      <b-field label="File Pattern" class="" grouped>
        <b-input
          v-model="file_pattern"
          placeholder="Pattern match string"
          name="file_pattern"
          expanded
          required
        ></b-input>
        <b-switch v-model="use_regex">Use Regex</b-switch>
      </b-field>

      <b-field class="mb-6">
        <b-switch v-model="privacy_mode">Privacy Mode</b-switch>
      </b-field>

      <p class="heading is-size-4">AI Settings</p>

      <b-field label="Relevant Objects">
        <b-select required multiple native-size="8" v-model="object_classes">
          <option v-for="objectClass in allObjectClasses" :value="objectClass">{{
            objectClass
          }}</option>
        </b-select>
        <div class="ml-2">
          <b-switch v-model="is_negative">Negative Relevance</b-switch>
        </div>
      </b-field>

      <b-field label="AI Confidence">
        <b-input v-model="min_confidence" type="number" :min="0.01" :max="1" :step="0.01"></b-input>
      </b-field>

      <b-field class="mb-6">
        <b-slider v-model="min_confidence" :min="0" :max="1" :step="0.01"> </b-slider>
      </b-field>

      <p class="heading is-size-4">Advanced Filtering</p>

      <label class="label">Mask File</label>

      <div class="box" v-if="display_mask">
        <img :src="`/storage/masks/${slug}.png`" :alt="`${slug}.png`" />
        <a href="javascript:void(0);" @click="removeMask"
          ><b-icon icon="times"></b-icon> Remove Mask</a
        >
      </div>

      <b-field class="file is-primary" :class="{ 'has-name': !!mask }">
        <b-upload v-model="mask" class="file-label" @input="inputMask">
          <span class="file-cta">
            <b-icon class="file-icon" icon="upload"></b-icon>
            <span class="file-label">Click to upload</span>
          </span>
          <span class="file-name" v-if="mask">
            {{ mask.name }}
          </span>
        </b-upload>
      </b-field>

      <b-field label="Smart Filter" grouped>
        <b-switch v-model="use_smart_filter"> </b-switch>

        <b-field grouped label="Precision" label-position="on-border">
          <b-input
            v-model="smart_filter_precision"
            type="number"
            :min="0.01"
            :max="0.99"
            :step="0.01"
            name="smart_filter_precision"
            :disabled="!use_smart_filter"
          >
          </b-input>
        </b-field>
      </b-field>

      <b-field class="mb-6">
        <b-slider
          v-model="smart_filter_precision"
          :min="0.01"
          :max="0.99"
          :step="0.01"
          :disabled="!use_smart_filter"
        >
        </b-slider>
      </b-field>

      <b-field label="Minimum Object Size">
        <b-input v-model="min_object_size" type="number"></b-input>
      </b-field>

      <b-field position="is-right" grouped>
        <b-button type="is-primary" :loading="isSaving" icon-left="save" native-type="submit">
          Save and Continue
        </b-button>
      </b-field>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DetectionProfileForm',
  props: ['id'],
  data() {
    return {
      name: '',
      file_pattern: '',
      min_confidence: 0.45,
      use_regex: false,
      privacy_mode: false,
      use_smart_filter: false,
      smart_filter_precision: 0.65,
      min_object_size: null,
      mask: null,
      object_classes: [],
      allObjectClasses: [],
      isSaving: false,
      is_negative: false,
      use_mask: false,
      display_mask: false,
      slug: '',
      profileLoading: false,
    };
  },

  created() {
    axios.get('/api/objectClasses').then(({ data }) => {
      this.allObjectClasses = data;
    });

    if (this.isEditing) {
      this.profileLoading = true;
      axios
        .get(`/api/profiles/${this.id}/edit`)
        .then(({ data }) => {
          const profile = data.data;
          this.$emit('profile-loaded', profile);

          this.name = profile.name;
          this.file_pattern = profile.file_pattern;
          this.min_confidence = parseFloat(profile.min_confidence);
          this.min_object_size = parseFloat(profile.min_object_size);
          this.use_regex = profile.use_regex;
          this.privacy_mode = profile.privacy_mode;
          this.object_classes = profile.object_classes;
          this.use_smart_filter = profile.use_smart_filter;
          this.smart_filter_precision = parseFloat(profile.smart_filter_precision);
          this.is_negative = profile.is_negative;
          this.slug = profile.slug;

          if (profile.use_mask) {
            this.use_mask = true;
            this.display_mask = true;
          }
        })
        .catch(() => {
          this.$buefy.toast.open({
            message: 'Something went wrong. Please try again.',
            type: 'is-danger',
          });
        })
        .finally(() => {
          this.profileLoading = false;
        });
    }
  },

  computed: {
    isEditing() {
      return this.id != null;
    },
    pageTitle() {
      return this.id != null ? 'Edit Profile' : 'Create Profile';
    },
  },

  methods: {
    removeMask() {
      this.use_mask = false;
      this.display_mask = false;
    },

    inputMask() {
      this.use_mask = true;
      this.display_mask = false;
    },

    handleSubmitErrors(e) {
      let msg = e.response.data.message;

      if (e.response.data.errors) {
        msg += ` ${e.response.data.errors[Object.keys(e.response.data.errors)[0]][0]}`;
      }

      this.$buefy.toast.open({
        duration: 10000,
        message: msg,
        type: 'is-danger',
      });
    },

    processForm() {
      if (this.isSaving) {
        return;
      }

      this.isSaving = true;

      const formData = new FormData();

      formData.append('id', this.id);
      formData.append('name', this.name);
      formData.append('file_pattern', this.file_pattern);
      formData.append('min_confidence', this.min_confidence);
      formData.append('use_regex', this.use_regex);
      formData.append('privacy_mode', this.privacy_mode);
      formData.append('use_smart_filter', this.use_smart_filter);
      formData.append('smart_filter_precision', this.smart_filter_precision);
      if (this.min_object_size) {
        formData.append('min_object_size', this.min_object_size);
      }
      formData.append('mask', this.mask);
      formData.append('object_classes', JSON.stringify(this.object_classes));
      formData.append('is_negative', this.is_negative);
      formData.append('use_mask', this.use_mask);

      if (this.isEditing) {
        formData.append('_method', 'PATCH');
        axios
          .post(`/api/profiles/${this.id}`, formData, {
            headers: {
              enctype: 'multipart/form-data',
            },
          })
          .then(() => {
            this.$emit('profile-saved', this.id);
            this.$buefy.toast.open({
              message: 'Profile saved.',
              type: 'is-primary',
            });
          })
          .catch((e) => {
            this.handleSubmitErrors(e);
          })
          .finally(() => {
            this.isSaving = false;
          });
      } else {
        axios
          .post('/api/profiles', formData, {
            headers: {
              enctype: 'multipart/form-data',
            },
          })
          .then((response) => {
            const { id } = response.data.data;
            this.$emit('profile-saved', id);
            this.$buefy.toast.open({
              message: 'Profile saved.',
              type: 'is-primary',
            });
          })
          .catch((e) => {
            this.handleSubmitErrors(e);
          })
          .finally(() => {
            this.isSaving = false;
          });
      }
    },
  },
};
</script>

<style scoped></style>
