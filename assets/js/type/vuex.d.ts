import { Store } from 'vuex'
import { FeedDataType } from './FeedData';
import { Granularite } from './Granularite';
import { Place } from './Place';

declare module '@vue/runtime-core' {
  interface State {
    placeList: Place[]
    selectedPlace: null|Place
    selectedPeriode: [Date, Date]
    selectedEnergie: null|FeedDataType
    selectedGranularite: null|Granularite
  }

  // provide typings for `this.$store`
  interface ComponentCustomProperties {
    $store: Store<State>
  }
}
