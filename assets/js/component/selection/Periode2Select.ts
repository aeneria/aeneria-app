import { defineComponent } from 'vue';
import Calendar from 'primevue/calendar';
import CalendarValueType from 'primevue/calendar';
import { SET_SELECTED_PERIODE2 } from '@/store/mutations';
import { isArray } from '@vue/shared';

export default defineComponent({
  name: 'Periode2Select',
  components: {
    Calendar,
  },
  data() {
    return {
      periode2 : this.$store.state.selection.periode2,
    }
  },
  computed: {
    periode() {
      return this.$store.state.selection.periode
    }
  },
  methods: {
    setSelectedPeriode2 (value: CalendarValueType) {
      if (isArray(value) && value[0] && value[1]) {
        this.$store.commit(SET_SELECTED_PERIODE2, value)
      }
    },
  }
});
