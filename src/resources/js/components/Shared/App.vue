<template>
  <div class="layout">
    <navigation></navigation>
    <main>
      <div class="container main-container">
        <div class="lead">
          <b-breadcrumb align="is-left">
            <b-breadcrumb-item
              v-for="(breadcrumb, index) in breadcrumbs"
              :key="index"
              tag="router-link"
              :to="breadcrumb.link"
              :active="index == breadcrumbs.length - 1"
              >{{ breadcrumb.name }}</b-breadcrumb-item
            >
          </b-breadcrumb>
          <div class="">
            <transition name="fade" mode="out-in">
              <keep-alive include="DetectionEvents">
                <router-view :key="$route.path"></router-view>
              </keep-alive>
            </transition>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import Navigation from './Navigation.vue';

export default {
  components: {
    Navigation,
  },
  data() {
    return {
      breadcrumbs: this.$route.meta(this.$route).breadcrumbs,
    };
  },
  watch: {
    $route() {
      this.breadcrumbs = this.$route.meta(this.$route).breadcrumbs;
    },
  },
};
</script>

<style lang="scss" scoped></style>
