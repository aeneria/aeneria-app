import { defineComponent } from 'vue';
import ProgressSpinner from 'primevue/progressspinner';

export default defineComponent({
  name: 'Spinner',
  props: {
    height: {
      type: Number,
      default: 100,
    },
  },
  components: {
    ProgressSpinner,
  },
});
