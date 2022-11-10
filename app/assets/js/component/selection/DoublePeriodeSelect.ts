import { defineComponent, ref } from 'vue';
import { FeedDataType, getFeedDataTypeColor } from '@/type/FeedData';
import { isArray } from '@vue/shared';
import { SET_SELECTED_PERIODE, SET_SELECTED_PERIODE2 } from '@/store/mutations';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import CalendarValueType from 'primevue/calendar';
import Menu from 'primevue/menu';
import { preselectedPeriode, preselectPeriodMenuItem } from '@/type/Selection';

export default defineComponent({
  name: 'DoublePeriodeSelect',
  components: {
    Button,
    Calendar,
    Menu
  },
  setup() {
    const selectMenu1 = ref()
    const selectMenu2 = ref()

    return {
      selectMenu1,
      selectMenu2
    }
  },
  data() {
    return {
      periode1 : this.$store.state.selection.periode,
      selectMenuItem1: preselectPeriodMenuItem(this.setPreselectedPeriode1),
      periode2 : this.$store.state.selection.periode2,
      selectMenuItem2: preselectPeriodMenuItem(this.setPreselectedPeriode2),
    }
  },
  computed: {
    energie(): FeedDataType|null {
      return this.$store.state.selection.energie
    },
    energieColor1(): string {
      return this.energie ? getFeedDataTypeColor(this.energie, 1) : "inherit"
    },
    energieColor2(): string {
      return this.energie ? getFeedDataTypeColor(this.energie, 2) : "inherit"
    },
  },
  methods: {
    toggleSelectMenu1(event) {
      this.selectMenu1.toggle(event);
    },
    setSelectedPeriode1 (value: CalendarValueType) {
      if (isArray(value) && value[0] && value[1]) {
        this.$store.commit(SET_SELECTED_PERIODE, value)
      }
    },
    setPreselectedPeriode1 (value: string) {
      this.periode1 = preselectedPeriode(value)
      this.$store.commit(SET_SELECTED_PERIODE, this.periode1)
    },
    toggleSelectMenu2(event) {
      this.selectMenu2.toggle(event);
    },
    setSelectedPeriode2 (value: CalendarValueType) {
      if (isArray(value) && value[0] && value[1]) {
        this.$store.commit(SET_SELECTED_PERIODE2, value)
      }
    },
    setPreselectedPeriode2 (value: string) {
      this.periode2 = preselectedPeriode(value)
      this.$store.commit(SET_SELECTED_PERIODE2, this.periode2)
    },
  }
});
