import { defineComponent } from 'vue';

export default defineComponent({
  name: 'Erreur',
  props: {
    height: {
      type: Number,
      default: 100,
    },
  },
});
