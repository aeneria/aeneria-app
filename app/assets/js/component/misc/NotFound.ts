import { defineComponent } from 'vue';
import Card from 'primevue/card';
import { mapState } from 'vuex';
import { RouterLink } from 'vue-router';

export default defineComponent({
  name: 'NotFound',
  components: {
    Card,
    RouterLink,
  },
  data() {
    const icons = ['fa-dizzy', 'fa-grimace', 'fa-flushed', 'fa-meh-rolling-eyes', 'fa-sad-cry', 'fa-sad-tear']
    return {
      icon: icons[Math.floor(Math.random() * icons.length)]
    }
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
  }
});
