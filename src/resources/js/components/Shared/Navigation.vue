<template>
  <b-navbar wrapper-class="container" class="is-spaced has-shadow">
    <template slot="brand">
      <b-navbar-item tag="router-link" :to="{ path: '/' }">
        <span class="heading is-size-4 mb-0">Last Watch</span>
      </b-navbar-item>
    </template>
    <template slot="start">
      <b-navbar-item href="/profiles">
        <span class="icon has-text-primary">
          <b-icon icon="eye"></b-icon>
        </span>
        <span>
          Detection Profiles
        </span>
      </b-navbar-item>
      <b-navbar-item href="/events">
        <span class="icon has-text-primary">
          <b-icon icon="images"></b-icon>
        </span>
        <span>
          Detection Events
        </span>
      </b-navbar-item>

      <b-dropdown aria-role="menu">
        <a class="navbar-item" slot="trigger" role="button">
          <span>Automations</span>
          <b-icon icon="chevron-down"></b-icon>
        </a>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/automations/folderCopy" class="dropdown-item">
            <div class="media">
              <b-icon class="media-left fa-lg" icon="copy"></b-icon>
              <div class="media-content">
                <h3>Folder Copy</h3>
                <small>Copy image files to a local folder</small>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/automations/smbCifsCopy" class="dropdown-item">
            <div class="media">
              <b-icon class="media-left fa-lg" icon="upload"></b-icon>
              <div class="media-content">
                <h3>SMB/CIFS Copy</h3>
                <small>Upload images to Samba share</small>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/automations/telegram" class="dropdown-item">
            <div class="media">
              <b-icon pack="fab" class="media-left fa-lg" icon="telegram-plane"></b-icon>
              <div class="media-content">
                <h3>Telegram</h3>
                <small>Send images to a Telegram bot</small>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/automations/webRequest" class="dropdown-item">
            <div class="media">
              <b-icon class="media-left fa-lg" icon="globe-americas"></b-icon>
              <div class="media-content">
                <h3>Web Request</h3>
                <small>Make Http GET/POST requests</small>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/automations/mqttPublish" class="dropdown-item">
            <div class="media">
              <b-icon class="media-left fa-lg" icon="project-diagram"></b-icon>
              <div class="media-content">
                <h3>MQTT Publish</h3>
                <small>Send MQTT messages</small>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>
      </b-dropdown>
    </template>

    <template slot="end">
      <b-navbar-item @click="toggleDarkMode">
        <transition name="rotate" mode="out-in">
          <b-icon v-if="darkMode" icon="moon" :key="1"></b-icon>
          <b-icon v-if="!darkMode" icon="sun" :key="0"></b-icon>
        </transition>
      </b-navbar-item>
      <b-dropdown aria-role="menu" class="is-right">
        <a class="navbar-item" slot="trigger" role="button">
          <b-icon icon="list"></b-icon>
        </a>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/errors" class="dropdown-item">
            <div class="media">
              <b-icon class="media-left fa-lg" icon="bug"></b-icon>
              <div class="media-content">
                <h3>Error Logs</h3>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>

        <b-dropdown-item has-link aria-role="menuitem">
          <router-link to="/deepstackLogs" class="dropdown-item">
            <div class="media">
              <b-icon class="media-left fa-lg" icon="brain"></b-icon>
              <div class="media-content">
                <h3>Deepstack Logs</h3>
              </div>
            </div>
          </router-link>
        </b-dropdown-item>
      </b-dropdown>
    </template>
  </b-navbar>
</template>

<script>
export default {
  name: 'Navigation',
  data() {
    return {
      darkMode: false,
    };
  },
  mounted() {
    if (localStorage.darkMode) {
      this.darkMode = localStorage.darkMode === 'true';
      this.updateRoot();
    }
  },
  watch: {
    darkMode(newValue) {
      localStorage.darkMode = newValue;
    },
  },
  methods: {
    toggleDarkMode() {
      this.darkMode = !this.darkMode;
      this.updateRoot();
    },
    updateRoot() {
      const root = document.getElementsByTagName('html')[0];
      root.className = this.darkMode ? 'dark-theme' : 'light-theme';
    },
  },
};
</script>

<style scoped lang="scss">
@keyframes rotateOut {
  0% {
    opacity: 0;
    transform: scale(0) rotate(180deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}
@keyframes rotateIn {
  0% {
    opacity: 0;
    transform: scale(0) rotate(-180deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}
.rotate-enter-active {
  animation: rotateIn 0.2s;
}
.rotate-leave-active {
  animation: rotateOut 0.2s reverse;
}
</style>
