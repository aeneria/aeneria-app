import { defineComponent } from 'vue';
import Card from 'primevue/card';
import { mapState } from 'vuex';

export default defineComponent({
  name: 'About',
  components: {
    Card,
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
  }
});
