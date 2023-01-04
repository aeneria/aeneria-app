import { defineComponent } from 'vue';
import Aide from './AideGraphique';

export default defineComponent({
  name: 'AidePapillon',
  props: {
    asDialog: {
      type: Boolean,
      required: false,
      default: true,
    }
  },
  components: {
    Aide,
  }
})