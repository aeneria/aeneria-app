import { FeedDataType } from '@/type/FeedData';
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'Index',
  props: {
    feedDataType: {
      type: Object as PropType<FeedDataType>,
      required: true,
    },
    valeur: {
      type: Number,
      required: true,
    },
    total: {
      type: Number,
      required: false,
    },
    texte: {
      type: String,
      required: false,
    },
    unite: {
      type: String,
      required: false,
    },
  },
});
