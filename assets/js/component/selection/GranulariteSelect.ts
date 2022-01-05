import { defineComponent } from 'vue';
import SelectButton, { SelectButtonChangeEvent } from 'primevue/selectbutton';
import {  mapState } from 'vuex';
import { SET_SELECTED_GRANULARITE } from '@/store/mutations';
import { granulariteList } from '@/type/Granularite';

export default defineComponent({
  name: 'GranulariteSelect',
  data() {
    return {
      granulariteList: granulariteList
    }
  },
  components: {
    SelectButton
  },
  computed: {
    ...mapState([
      'selectedGranularite',
    ]),
  },
  methods: {
    setSelectedGranularite (event: SelectButtonChangeEvent) {
      this.$store.commit(SET_SELECTED_GRANULARITE, event.value)
    }
  }
});
