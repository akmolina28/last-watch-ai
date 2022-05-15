<template>
  <div class="layout">
    <navigation></navigation>
    <main>
      <div class="container main-container">
        <div class="lead">
          <transition name="fade" mode="out-in">
            <keep-alive include="DetectionEvents">
              <router-view></router-view>
            </keep-alive>
          </transition>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
  import Navigation from "./Navigation.vue"
  export default {
    components: {
      Navigation,
    },
  }
</script>

<style lang="scss" scoped>

</style>