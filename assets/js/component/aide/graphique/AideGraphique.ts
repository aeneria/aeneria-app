import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Fieldset from 'primevue/fieldset';
import { mapState } from 'vuex';

export default defineComponent({
  name: 'Aide',
  components: {
    Dialog,
    Button,
    Fieldset,
  },
  props: {
    titre: {
      type: String,
      required: true,
    },
    asDialog: {
      type: Boolean,
      required: false,
      default: true,
    }
  },
  data() {
    return {
        display: false,
    }
  },
  methods: {
    open() {
        this.display = true;
    },
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
  }
});
