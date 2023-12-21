import { defineComponent, PropType } from 'vue';
import { mapState } from 'vuex';
import { Place } from '@/type/Place';
import { PLACE_EDIT } from '@/store/actions';
import { queryUsers } from '@/api/configuration';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import ToggleButton from 'primevue/togglebutton';

export default defineComponent({
  name: 'EditForm',
  components: {
    Button,
    Dialog,
    InputText,
    MultiSelect,
    ToggleButton,
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
      name: this.place.name,
      public: this.place.public,
      allowedUsers: this.place.allowedUsers,
      userListOption: null as null | Array<{id: number, username: string}>,
      tooltipPublicPlace: {
        value: `Lorsqu'une adresse est publique, tous les utilisateurs peuvent la voir. ` +
          `Cela ne concerne que les utilisateurs connectés, les données de l'adresse ne ` +
          `sont pas accessible sans compte æneria.`,
        escape: true,
      }
    }
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
  },
  mounted() {
    queryUsers().then(data => this.userListOption = data)
  },
  validations() {
    return {
      name: {
        required,
      },
    }
  },
  methods: {
    closeBasic() {
      this.name = this.place.name
      this.$emit('toggleVisible')
    },
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(PLACE_EDIT, {
        placeId: this.place.id,
        name: this.name,
        allowedUsers: this.allowedUsers,
        public: this.public,
      })
      this.$emit('toggleVisible')
    },
  },
  emits: ['toggleVisible'],
});
