import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Calendar from 'primevue/calendar';
import RefreshFeedDataForm from './RefreshFeedDataForm';
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'RefreshDataForm',
  components: {
    Button,
    Dialog,
    Calendar,
    RefreshFeedDataForm,
    Message,
  },
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
  methods: {
    closeBasic() {
      this.$emit('toggleVisible')
    },
  },
  emits: ['toggleVisible'],
});
