import { defineComponent } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import MonthSummary from '@/component/graphique/MonthSummary';
import { formatWithGranularite } from '@/component/graphique/d3-helpers';
import { getGranularite, GranulariteType } from '@/type/Granularite';

export default defineComponent({
  name: 'DashboardHome',
  components: {
    Button,
    Card,
    MonthSummary,
  },
  data() {
    const now = new Date()
    now.setDate(1)
    now.setHours(0,0,0,0)

    const previousMonth = new Date(now)
    previousMonth.setMonth(previousMonth.getMonth() - 1)

    return {
      monthsToDisplay: [now],
      previousMonth: previousMonth,
    }
  },
  computed: {
    place() { return this.$store.state.selection.place },
  },
  methods: {
    addMonth() {
      this.monthsToDisplay.push(new Date(this.previousMonth))
      this.previousMonth.setMonth(this.previousMonth.getMonth() - 1)
    },
    formatMonth(month: Date): string {
      return formatWithGranularite(getGranularite(GranulariteType.Mois), month)
    }

  }
});
