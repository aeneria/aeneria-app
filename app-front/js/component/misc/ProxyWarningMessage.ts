import { defineComponent } from 'vue';
import Message from 'primevue/message';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

export default defineComponent({
  components: {
    Button,
    Dialog,
    Message,
  },
  props: {
    provider: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      showDialog: false,
    }
  },
  methods: {
  }
});
