import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { USER_UPDATE_EMAIL } from '@/store/actions';
import { required, email } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";

export default defineComponent({
  name: 'EditEmailForm',
  components: {
    Button,
    Dialog,
    InputText,
    Message,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      submitted: false,
      newEmail: '',
    }
  },
  validations() {
    return {
      newEmail: {
        required,
        email,
      },
    }
  },
  computed: {
    ...mapState([
      'utilisateur',
    ]),
  },
  methods: {
    closeBasic() {
      this.newEmail = ''
      this.$emit('toggleVisible')
    },
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(USER_UPDATE_EMAIL, {
        newEmail: this.newEmail,
      })
      this.$emit('toggleVisible')
    },
  }
});
