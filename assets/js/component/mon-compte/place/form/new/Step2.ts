import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import { RouterLink } from 'vue-router';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Message from 'primevue/message';


export default defineComponent({
  name: 'AddCompteur',
  components: {
    Button,
    Card,
    Divider,
    Message,
    RouterLink,
  },
  computed: {
    ...mapState([
      'beingCreatedPlace',
    ]),
  },
});
