import { defineComponent } from 'vue';
import { required, email } from "@vuelidate/validators";
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputSwitch from 'primevue/inputswitch';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import useVuelidate from '@vuelidate/core';
import { postUserAdd } from '@/api/admin';

export default defineComponent({
  name: 'UserAddForm',
  components: {
    Button,
    Dialog,
    InputText,
    InputSwitch,
    Password,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
    },
  },
  data() {
    return {
      email: null as null|string,
      password: null as null|string,
      active: null as null|boolean,
      admin: null as null|boolean,
      submitted: false
    }
  },
  validations() {
    return {
      email: {
        required,
        email,
      },
      password: {
        required,
      },
    }
  },
  methods: {
    confirmEdition(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      postUserAdd(
        this.email,
        this.password,
        this.active,
        this.admin
      ).then(() => {
        this.$emit('toggleVisible')
        this.$emit('refreshUserList')
      })
    },
    cancelEdition() {
      this.$emit('toggleVisible')
    },
  },
});
