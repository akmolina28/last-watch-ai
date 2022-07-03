<template>
  <div class="layout">
    <navigation v-if="!hideNavbar"></navigation>
    <main>
      <div class="container main-container">
        <div class="lead">
          <b-breadcrumb v-if="breadcrumbs" align="is-left">
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
      breadcrumbs: this.$route.meta(this.$route).breadcrumbs || null,
      hideNavbar: !!this.$route.meta(this.$route).hideNavbar,
    };
  },
  watch: {
    $route() {
      this.breadcrumbs = this.$route.meta(this.$route).breadcrumbs;
      this.hideNavbar = this.$route.meta(this.$route).hideNavbar;
    },
  },
};
</script>

<style lang="scss" scoped></style>
