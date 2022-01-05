import { defineComponent } from 'vue';
import Dropdown, { DropdownChangeEvent } from 'primevue/dropdown';
import { mapGetters, mapState } from 'vuex';
import { SET_SELECTED_ENERGIE } from '@/store/mutations';

export default defineComponent({
  name: 'EnergieSelect',
  components: {
    Dropdown
  },
  computed: {
    ...mapState([
      'selectedEnergie',
    ]),
    ...mapGetters([
      'onlyOneEnergie',
      'feedDataTypeEnergieList'
    ])
  },
  methods: {
    setSelectedEnergie (event: DropdownChangeEvent) {
      this.$store.commit(SET_SELECTED_ENERGIE, event.value)
    }
  }
});
