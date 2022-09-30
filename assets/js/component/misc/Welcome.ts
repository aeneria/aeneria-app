import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

export default defineComponent({
  name: 'Welcome',
  components: {
    Button,
    Dialog,
  },
  methods: {
    goToNewPLace() {
      this.$router.push({name: 'new-place'})
    },
  }
});
