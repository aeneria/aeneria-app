import { defineComponent, ref } from 'vue';
import Calendar from 'primevue/calendar';
import CalendarValueType from 'primevue/calendar';
import Button from 'primevue/button';
import { SET_SELECTED_PERIODE } from '@/store/mutations';
import { isArray } from '@vue/shared';
import Menu from 'primevue/menu';

export default defineComponent({
  name: 'PeriodeSelect',
  components: {
    Calendar,
    Button,
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
      periode : [
        new Date(this.$store.state.selectedPeriode[0]),
        new Date(this.$store.state.selectedPeriode[1]),
      ],
      selectMenuItem: [
        {
          label: 'Semaine en cours',
          command: () => this.setPreselectedPeriode('current-week')
        },
        {
          label: 'Semaine précédente',
          command: () => this.setPreselectedPeriode('last-week')
        },
        {
          separator: true
        },
        {
          label: 'Mois en cours',
          command: () => this.setPreselectedPeriode('current-month')
        },
        {
          label: 'Mois précédent',
          command: () => this.setPreselectedPeriode('last-month')
        },
        {
          label: 'Les 3 derniers mois',
          command: () => this.setPreselectedPeriode('last-3-months')
        },
        {
          label: 'Les 6 derniers mois',
          command: () => this.setPreselectedPeriode('last-6-months')
        },
        {
          separator: true
        },
        {
          label: 'Année en cours',
          command: () => this.setPreselectedPeriode('current-year')
        },
        {
          label: 'Année précédente',
          command: () => this.setPreselectedPeriode('last-year')
        },
        {
          label: 'Année glissante',
          command: () => this.setPreselectedPeriode('sliding-year')
        },
        {
          separator: true
        },
        {
          label: 'Tout',
          command: () => this.setPreselectedPeriode('all')
        },
      ],
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
      const now = new Date()
      const startDate = new Date()
      startDate.setHours(0)
      startDate.setMinutes(0)
      const endDate = new Date()
      endDate.setHours(0)
      endDate.setMinutes(0)

      switch (value) {
        case 'current-week' :
          startDate.setDate(now.getDate() - (now.getDay() - 1))
          endDate.setDate(now.getDate() - 1)
          break
        case 'last-week' :
          startDate.setDate(now.getDate() - (now.getDay() + 6))
          endDate.setDate(now.getDate() - now.getDay())
          break
        case 'current-month' :
          startDate.setDate(1)
          endDate.setDate(now.getDate() - 1)
          break
        case 'last-month' :
          startDate.setDate(1)
          startDate.setMonth(now.getMonth() - 1)
          endDate.setDate(0)
          break
        case 'last-3-months' :
          startDate.setDate(1)
          startDate.setMonth(now.getMonth() - 3)
          endDate.setDate(now.getDate() - 1)
          break
        case 'last-6-months' :
          startDate.setDate(1)
          startDate.setMonth(now.getMonth() - 6)
          endDate.setDate(now.getDate() - 1)
          break
        case 'current-year' :
          startDate.setDate(1)
          startDate.setMonth(0)
          endDate.setDate(now.getDate() - 1)
          break
        case 'last-year' :
          startDate.setDate(1)
          startDate.setMonth(0)
          startDate.setFullYear(now.getFullYear() - 1)
          endDate.setMonth(0)
          endDate.setDate(0)
          break
        case 'sliding-year' :
          startDate.setDate(1)
          startDate.setMonth(now.getMonth() - 11)
          endDate.setDate(now.getDate() - 1)
          break
        case 'all' :
          // @todo
          break
      }

      this.periode = [startDate, endDate]
      this.$store.commit(SET_SELECTED_PERIODE, this.periode)
    },
  }
});
