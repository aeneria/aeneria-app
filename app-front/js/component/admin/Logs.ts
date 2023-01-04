import { defineComponent } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Dropdown from 'primevue/dropdown';
import Column from 'primevue/column';
import {  queryLogs } from '@/api/admin';
import { timeFormat } from 'd3';
import { FilterMatchMode } from 'primevue/api';

export default defineComponent({
  name: 'Admin',
  components: {
    Button,
    Column,
    DataTable,
    Dropdown,
  },
  data() {
    return {
      logs: null as null|Array<any>,
      filters: {
        'severity': {value: null, matchMode: FilterMatchMode.EQUALS}
      },
      severityOptions: [
        "fa-solid fa-bomb",
        "fa-solid fa-triangle-exclamation",
        "fa-solid fa-circle-info",
        "fa-solid fa-bugs",
      ],
    }
  },
  methods: {
    loadLogs() {
      queryLogs().then(data => {
        // DataTable veut des objets en entré
        this.logs = data.map(e => {
          return {
            severity: this.getLogSeverity(e),
            date: this.getLogDate(e),
            message: this.getLogMessage(e),
          }
        }).reverse()
      })
    },
    getLogSeverity(log: string): string {
      if (log.includes(".CRITICAL")) {
        return "fa-solid fa-bomb"
      } else if(log.includes(".ERROR")) {
        return "fa-solid fa-triangle-exclamation"
      } else if(log.includes(".INFO")) {
        return "fa-solid fa-circle-info"
      } else if(log.includes(".DEBUG")) {
        return "fa-solid fa-bugs"
      }

      return ''
    },
    getLogSeverityLabel(icon: string): string {

      if (icon == this.severityOptions[0]) {
        return 'Critique'
      } else if (icon == this.severityOptions[1]) {
        return 'Erreur'
      } else if (icon == this.severityOptions[2]) {
        return 'Info'
      } else if (icon == this.severityOptions[3]) {
        return 'Debug'
      }

      return ''
    },
    getLogRowClass(log: any): string {
      return this.getLogSeverityLabel(log.severity).toLowerCase()
    },
    getLogDate(log: string): string {
      let date = new Date(log.substring(1, 33))
      return timeFormat("%d/%m/%Y %H:%M:%S")(date)
      // return date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()
    },
    getLogMessage(log: string): string {
      return log.substring(35)
    },
  },
});
