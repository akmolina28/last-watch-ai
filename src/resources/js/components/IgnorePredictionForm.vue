<template>
  <div>
    <form action="">
      <div class="modal-card" style="width: auto">
        <header class="modal-card-head">
          <p class="modal-card-title">Ignore Prediction</p>
          <button type="button" class="delete" @click="$emit('close')" />
        </header>
        <section class="modal-card-body">
          <p class="mb-2 is-italic">
            Ignore similar predictions for a duration of time. Use this when AI repeatedly gives a
            false positive in the same location.
          </p>
          <b-field label="Profile">
            <span>{{ profile.name }}</span>
          </b-field>
          <b-field label="Object Class">
            <span>{{ aiPrediction.object_class }}</span>
          </b-field>
          <b-field label="Duration">
            <b-select v-model="expirationDays">
              <option :value="null">Indefinite</option>
              <option :value="1">1 day</option>
              <option :value="7">1 week</option>
              <option :value="30">1 month</option>
            </b-select>
          </b-field>
        </section>
        <footer class="modal-card-foot">
          <b-button label="Close" @click="$emit('close')" />
          <b-button label="Ignore" @click="saveIgnoreZone" type="is-primary" :loading="saving" />
        </footer>
      </div>
    </form>
    <!-- <b-field label="Expiration">
      <b-select v-model="expirationDays">
        <option :value="null">Indefinite</option>
        <option :value="1">1 day</option>
        <option :value="7">1 week</option>
        <option :value="30">1 month</option>
      </b-select>
    </b-field>
    <b-field>
      <b-button type="is-primary">Ignore Prediction</b-button>
    </b-field> -->
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'IgnorePredictionForm',
  props: {
    profile: {
      type: Object,
      default: null,
    },
    aiPrediction: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      expirationDays: null,
      saving: false,
    };
  },
  methods: {
    saveIgnoreZone() {
      this.saving = true;
      this.saveIgnoreZoneAjax(this.profile.id, this.aiPrediction.id, this.expirationDays)
        .then(() => {
          this.$buefy.toast.open({
            message: 'Ignore zone saved.',
            type: 'is-success',
          });
          this.$emit('close');
        })
        .catch(() => {
          this.$buefy.toast.open({
            message: 'Something went wrong. Please try again.',
            type: 'is-danger',
          });
        })
        .finally(() => {
          this.saving = false;
        });
    },
    saveIgnoreZoneAjax(profileId, predictionId, expirationDays) {
      return axios.put(`/api/profiles/${profileId}/ignoreZone`, {
        ai_prediction_id: predictionId,
        expiration_days: expirationDays,
      });
    },
  },
};
</script>

<style lang="scss" scoped></style>
