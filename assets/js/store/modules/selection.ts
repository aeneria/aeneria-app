import { DataType, FeedDataType, getFeedDataType, isFeedDataEnergie } from '@/type/FeedData'
import { SET_SELECTED_ENERGIE, SET_SELECTED_GRANULARITE, SET_SELECTED_METEO_DATA, SET_SELECTED_PERIODE, SET_SELECTED_PERIODE2, SET_SELECTED_PLACE, SET_SELECTION } from '../mutations'
import { granulariteList } from '@/type/Granularite'
import { deserializedSelection, Selection, serializedSelection } from '@/type/Selection'
import { Place } from '@/type/Place'
import { Store } from 'vuex'
import { State } from 'vue'
import { INIT_SELECTION } from '../actions'

const lastMonth = new Date();
lastMonth.setMonth(lastMonth.getMonth() -1)
const now = new Date();
const beforelastMonth = new Date();
beforelastMonth.setMonth(beforelastMonth.getMonth() -2)

export const moduleSelection = {
  state: {
      place: null,
      periode: [lastMonth, now],
      periode2: [beforelastMonth, lastMonth],
      energie: null,
      meteoData: null,
      granularite: granulariteList[0],
  } as Selection,
  getters: {
    onlyOneEnergie: (state, getters) => getters.feedDataTypeEnergieList?.length <= 1,
    feedDataTypeEnergieList (state) {
      if (state.place) {
        return getFeedDataTypeEnergieList(state.place)
      }

      return []
    },
    selectedEnergieFeedDataId: (state) => {
      if (!(state.place && state.energie)) {
        return null
      }

      for(const feed of state.place.feedList) {
        for(const feedData of feed.feedDataList) {
          if (state.energie.id == feedData.type) {
            return feedData.id
          }
        }
      }

      return null
    },
    selectedMeteoFeedDataId: (state) => {
      if (!(state.place && state.meteoData)) {
        return null
      }

      return selectedMeteoFeedDataId(state, state.meteoData.id)
    },
    selectedTemperatureFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Temperature),
    selectedDjuFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Dju),
    selectedNebulosityFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Nebulosity),
    selectedRainFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Rain),
    selectedHumidityFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Humidity),
  },
  mutations: {
    [SET_SELECTION] (state, selection) {
      state.place = selection.place
      state.periode = selection.periode
      state.periode2 = selection.periode2
      state.energie = selection.energie
      state.meteoData = selection.meteoData
      state.granularite = selection.granularite
    },
    [SET_SELECTED_PLACE] (state, place) {
      state.place = place
      // @todo faire ça de manière plus fine !
      state.periode = [lastMonth, now]
      state.periode = [beforelastMonth, lastMonth]

      if (place) {
        const energieList = getFeedDataTypeEnergieList(place)
        if (energieList.length) {
          state.energie = energieList[0]
        }
      }
    },
    [SET_SELECTED_ENERGIE] (state, feedDataType) {
      state.energie = feedDataType
    },
    [SET_SELECTED_METEO_DATA] (state, feedDataType) {
      state.meteoData = feedDataType
    },
    [SET_SELECTED_GRANULARITE] (state, granularite) {
      state.granularite = granularite
    },
    [SET_SELECTED_PERIODE] (state, periode) {
      state.periode = periode
    },
    [SET_SELECTED_PERIODE2] (state, periode) {
      state.periode2 = periode
    },
  },
  actions: {
    [INIT_SELECTION] ({state, commit, dispatch, rootState}) {
        const preSelection = window.sessionStorage.getItem('selection')

        if (preSelection) {
          try {
            const toto = deserializedSelection(preSelection)
            commit(SET_SELECTION, toto)
          } catch (e) {
            console.log(e)
          }
        }

        // On sélectionne une place
        if (!state.place) {
          commit(SET_SELECTED_PLACE, rootState.placeList[0] ?? null)
        }

        // On présélectionne les DJU
        if (!state.meteoData) {
          commit(SET_SELECTED_METEO_DATA, getFeedDataType(DataType.Dju))
        }
    }
  }
}

export const persistSelectionPlugin =
(store: Store<State>) => {
  store.subscribe((mutation, state) => {
    if (state.initialized) {
      window.sessionStorage.setItem('selection', serializedSelection(state.selection))
    }
  })
}

function selectedMeteoFeedDataId(state: Selection, dataType: DataType): null|Number {
  if (!(state.place)) {
    return null
  }

  for(const feed of state.place.feedList) {
    for(const feedData of feed.feedDataList) {
      if (dataType == feedData.type) {
        return feedData.id
      }
    }
  }

  return null
}

function getFeedDataTypeEnergieList (place: Place) {
  let ret = new Array<FeedDataType>()

  if (place.feedList) {
    for(const feed of place.feedList) {
      for(const feedData of feed.feedDataList)
      if (isFeedDataEnergie(feedData)) {
        ret.push(getFeedDataType(feedData.type))
      }
    }

    // if (ret.length > 1) {
    //   ret.push(getFeedDataType(DataType.ConsoEnergie))
    // }
  }

  return ret
}
