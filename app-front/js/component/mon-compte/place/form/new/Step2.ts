import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';

export default defineComponent({
  components: {
    Button,
  },
  computed: {
    ...mapState([
      'beingCreatedPlace',
    ]),
  },
});
