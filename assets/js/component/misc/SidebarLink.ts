import { defineComponent } from 'vue';
import { RouterLink } from 'vue-router';

export default defineComponent({
  name: 'SidebarLink',
  components: {
    RouterLink,
  },
  props: {
    to: {
      type: String,
      required: true,
    },
    imageBase: {
      type: String,
      required: true,
    },
    titre: {
      type: String,
      required: true,
    },
  } ,
});
