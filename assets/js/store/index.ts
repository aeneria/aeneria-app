import { queryPlaces } from '@/api/configuration'
import { DataType, FeedDataType, getFeedDataType, isFeedDataEnergie } from '@/type/FeedData'
import { getGranularite, GranulariteType } from '@/type/Granularite'
import { Place } from '@/type/Place'
import { State } from 'vue'
import { createStore } from 'vuex'
import { INIT_PLACE_LIST, UPDATE_SELECTED_PLACE } from './actions'
import { SET_PLACE_LIST, SET_SELECTED_ENERGIE, SET_SELECTED_GRANULARITE, SET_SELECTED_PERIODE, SET_SELECTED_PLACE } from './mutations'

const lastMonth = new Date('2020-02-09');
lastMonth.setMonth(lastMonth.getMonth() -1)
const now = new Date('2021-10-04');

export const store = createStore({
  state: {
    placeList: [] as Place[],
    selectedPlace: null as null|Place,
    selectedPeriode: [lastMonth, now],
    selectedEnergie: null as null|FeedDataType,
    selectedGranularite: getGranularite(GranulariteType.Jour),
  } as State,
  getters: {
    onlyOnePlace: (state) => state.placeList.length <= 1,
    onlyOneEnergie: (state, getters) => getters.feedDataTypeEnergieList?.length <= 1,
    feedDataTypeEnergieList (state) {
      let ret = [] as FeedDataType[]

      if (state.selectedPlace && state.selectedPlace.feedList) {
        for(const feed of state.selectedPlace.feedList) {
          for(const feedData of feed.feedDataList)
          if (isFeedDataEnergie(feedData)) {
            ret.push(getFeedDataType(feedData.type))
          }
        }

        if (ret.length > 1) {
          ret.push(getFeedDataType(DataType.ConsoEnergie))
        }
      }

      return ret
    },
    selectedEnergieFeedDataId: (state) => {
      if (!(state.selectedPlace && state.selectedEnergie)) {
        return null
      }

      for(const feed of state.selectedPlace.feedList) {
        for(const feedData of feed.feedDataList) {
          if (state.selectedEnergie.id == feedData.type) {
            return feedData.id
          }
        }
      }

      return null
    },
    selectedTemperatureFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Temperature),
    selectedDjuFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Dju),
    selectedNebulosityFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Nebulosity),
    selectedRainFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Rain),
    selectedHumidityFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Humidity),
  },
  mutations: {
    [SET_PLACE_LIST] (state, placeList) {
      state.placeList = placeList
    },
    [SET_SELECTED_PLACE] (state, place) {
      state.selectedPlace = place
    },
    [SET_SELECTED_ENERGIE] (state, feedDataType) {
      state.selectedEnergie = feedDataType
    },
    [SET_SELECTED_GRANULARITE] (state, granularite) {
      state.selectedGranularite = granularite
    },
    [SET_SELECTED_PERIODE] (state, periode) {
      state.selectedPeriode = periode
    },
  },
  actions: {
    [INIT_PLACE_LIST] ({commit, dispatch, getters, state}) {
      queryPlaces().then(placeList => {
        commit(SET_PLACE_LIST, placeList)

        // On sélectionne une place
        dispatch(UPDATE_SELECTED_PLACE, state.placeList[0] ?? null)
      })
    },
    [UPDATE_SELECTED_PLACE] ({commit, dispatch, getters, state}, place) {
      commit(SET_SELECTED_PLACE, place)

      if (place) {
        let energie = getFeedDataType(getters.feedDataTypeEnergieList[0].id)
        // let energie = undefined;
        // switch (getters.feedDataTypeEnergieList.length) {
        //   case 0:
        //     energie = null
        //     break
        //   case 1:
        //     energie = getFeedDataType(getters.feedDataTypeEnergieList[0].id)
        //     break
        //   default:
        //     energie = getFeedDataType(DataType.ConsoEnergie)
        // }

        commit(SET_SELECTED_ENERGIE, energie)
      }
    }
  }
})

function selectedMeteoFeedDataId(state: State, dataType: DataType): null|Number {
  if (!(state.selectedPlace)) {
    return null
  }

  for(const feed of state.selectedPlace.feedList) {
    for(const feedData of feed.feedDataList) {
      if (dataType == feedData.type) {
        return feedData.id
      }
    }
  }

  return null
}