import { Store } from 'vuex'
import { Configuration } from './Configuration';
import { FeedDataType } from './FeedData';
import { Granularite } from './Granularite';
import { Place } from './Place';
import { Utilisateur } from './Utilisateur';

declare module '@vue/runtime-core' {
  interface State {
    configuration: null|Configuration
    utilisateur: null|Utilisateur
    hasNoPlace: null|boolean
    placeList: Place[]
    selectedPlace: null|Place
    selectedPeriode: [Date, Date]
    selectedEnergie: null|FeedDataType
    selectedMeteoData: null|FeedDataType
    selectedGranularite: null|Granularite
  }

  // provide typings for `this.$store`
  interface ComponentCustomProperties {
    $store: Store<State>
  }
}
