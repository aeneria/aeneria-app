import { defineComponent } from 'vue';
import Dropdown, { DropdownChangeEvent } from 'primevue/dropdown';
import { mapGetters } from 'vuex';
import { SET_SELECTED_ENERGIE } from '@/store/mutations';

export default defineComponent({
  name: 'EnergieSelect',
  components: {
    Dropdown
  },
  computed: {
    energie() { return this.$store.state.selection.energie },
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
