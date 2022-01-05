import { defineComponent } from 'vue';
import Calendar from 'primevue/calendar';
import CalendarValueType from 'primevue/calendar';
import Dropdown, { DropdownChangeEvent } from 'primevue/dropdown';
import { SET_SELECTED_PERIODE } from '@/store/mutations';
import { isArray } from '@vue/shared';

export default defineComponent({
  name: 'PeriodeSelect',
  data() {
    return {
      periode : [
        new Date(this.$store.state.selectedPeriode[0]),
        new Date(this.$store.state.selectedPeriode[1]),
      ],
      preSelectList: [
        '6 derniers mois',
        'le mois dernier',
      ]
    }
  },
  components: {
    Calendar,
    Dropdown
  },
  methods: {
    setSelectedPeriode (value: CalendarValueType) {
      if (isArray(value) && value[0] && value[1]) {
        this.$store.commit(SET_SELECTED_PERIODE, value)
      }
    },
    setPreselectedPeriode (date: DropdownChangeEvent) {
      // this.$store.commit(SET_SELECTED_PERIODE, date)
    },
  }
});
