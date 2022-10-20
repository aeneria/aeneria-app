import { defineComponent, ref } from 'vue';
import { isArray } from '@vue/shared';
import { preselectedPeriode, preselectPeriodMenuItem } from '@/type/Selection';
import { SET_SELECTED_PERIODE } from '@/store/mutations';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import CalendarValueType from 'primevue/calendar';
import Menu from 'primevue/menu';

export default defineComponent({
  name: 'PeriodeSelect',
  components: {
    Button,
    Calendar,
    Menu
  },
  setup() {
    const selectMenu = ref()

    return {
      selectMenu
    }
  },
  data() {
    return {
      periode : this.$store.state.selection.periode ?? [new Date(), new Date()],
      selectMenuItem: preselectPeriodMenuItem(this.setPreselectedPeriode),
    }
  },
  methods: {
    toggleSelectMenu(event) {
      this.selectMenu.toggle(event);
    },
    setSelectedPeriode (value: CalendarValueType) {
      if (isArray(value) && value[0] && value[1]) {
        this.$store.commit(SET_SELECTED_PERIODE, value)
      }
    },
    setPreselectedPeriode (value: string) {
      this.periode = preselectedPeriode(value)
      this.$store.commit(SET_SELECTED_PERIODE, this.periode)
    },
  }
});
