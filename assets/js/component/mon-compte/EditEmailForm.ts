import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';

export default defineComponent({
  name: 'EditEmailForm',
  components: {
    Button,
    Dialog,
    InputText,
    Message,
  },
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    ...mapState([
      'utilisateur',
    ]),
  },
  methods: {
    closeBasic() {
      this.$emit('toggleVisible')
    }
  }
});
