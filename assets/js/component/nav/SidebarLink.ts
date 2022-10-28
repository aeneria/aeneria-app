import { defineComponent } from 'vue';
import { RouterLink } from 'vue-router';
import Button from 'primevue/button';

export default defineComponent({
  name: 'SidebarLink',
  components: {
    RouterLink,
    Button
  },
  props: {
    to: {
      type: String,
      required: true,
    },
    icon: {
      type: String,
      required: true,
    },
    titre: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    isMobile: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    goTo() {
      this.$router.push({name: this.to})
    }
  },
});
