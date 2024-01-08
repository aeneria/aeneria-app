import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import { RouterLink } from 'vue-router';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import Message from 'primevue/message';
import ProxyWarningMessage from '@/component/misc/ProxyWarningMessage';


export default defineComponent({
  components: {
    Button,
    Divider,
    Message,
    RouterLink,
    ProxyWarningMessage,
  },
  computed: {
    ...mapState([
      'beingCreatedPlace',
    ]),
  },
});
