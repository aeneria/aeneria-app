import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import { PLACE_EDIT_NOM } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'EditNomForm',
  components: {
    Button,
    Dialog,
    InputText,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
    place: {
      type: Object as PropType<Place>,
      required: true
    },
  },
  data() {
    return {
      submitted: false,
      newName: '',
    }
  },
  validations() {
    return {
      newName: {
        required,
      },
    }
  },
  methods: {
    closeBasic() {
      this.newName = ''
      this.$emit('toggleVisible')
    },
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(PLACE_EDIT_NOM, {
        placeId: this.place.id,
        newName: this.newName,
      })
      this.newName = ''
      this.$emit('toggleVisible')
    },
  },
  emits: ['toggleVisible'],
});
