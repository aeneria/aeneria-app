import { defineComponent } from 'vue';
import { granulariteList } from '@/type/Granularite';
import { SET_SELECTED_GRANULARITE } from '@/store/mutations';
import SelectButton, { SelectButtonChangeEvent } from 'primevue/selectbutton';
import Dropdown from 'primevue/dropdown';

export default defineComponent({
  name: 'GranulariteSelect',
  components: {
    SelectButton,
    Dropdown,
  },
  props: {
    isMobile: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      granulariteList: granulariteList,
    }
  },
  computed: {
    granularite() { return this.$store.state.selection.granularite },
  },
  methods: {
    setSelectedGranularite (event: SelectButtonChangeEvent) {
      this.$store.commit(SET_SELECTED_GRANULARITE, event.value)
    }
  }
});
