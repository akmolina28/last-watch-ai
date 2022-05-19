export default {
  methods: {
    showErrorDialog(message) {
      this.$buefy.dialog.alert({
        message,
        title: 'Error',
        type: 'is-danger',
        hasIcon: true,
        icon: 'times-circle',
        iconPack: 'fa',
        ariaRole: 'alertdialog',
        ariaModal: true,
      });
    },
    deepCloneArray(arr) {
      return JSON.parse(JSON.stringify(arr));
    },
  },
};
