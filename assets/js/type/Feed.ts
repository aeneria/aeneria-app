import { FeedData } from "./FeedData"

export enum DataProvider {
  linky = 'LINKY',
  enedisDataConnect = 'ENEDIS_DATA_CONNECT',
  grdfAdict = 'GRDF_ADICT',
  meteoFrance = 'METEO_FRANCE',
  fake = 'FAKE',
}

export enum FeedType {
  electricity = 'ELECTRICITY',
  gaz = 'GAZ',
  meteo = 'METEO',
}

export interface Feed {
  id: number
  name: string
  type: string
  dataProvider: DataProvider
  param: any[]
  frequencies: number[]
  feedDataList: FeedData[]
}

export function feedLabelLong(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'Compte Enedis&nbsp;: ' + feed.param['LOGIN']
    case DataProvider.enedisDataConnect :
      return 'Linky&nbsp;: PDL&nbsp;-&nbsp;'+ feed.param['ADDRESS'].usagePointId
    case DataProvider.grdfAdict :
      return 'Gazpar&nbsp;: PCE&nbsp;-&nbsp;'+ feed.param['PCE']
    case DataProvider.meteoFrance :
      return ''+ feed.param['CITY']
    case DataProvider.fake :
      return 'FakeProvider&nbsp;: '+ feed.type
  }
}

export function feedLabelShort(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'Compteur Enedis'
    case DataProvider.enedisDataConnect :
      return 'Compteur Linky'
    case DataProvider.grdfAdict :
      return 'Compteur Gazpar'
    case DataProvider.meteoFrance :
      return 'Météo France'
    case DataProvider.fake :
      return 'FakeProvider'
  }
}

export function feedIcon(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'fas fa-tachometer-alt'
    case DataProvider.enedisDataConnect :
      return 'fas fa-plug'
    case DataProvider.grdfAdict :
      return 'fas fa-burn'
    case DataProvider.meteoFrance :
      return 'fas fa-cloud-sun'
    case DataProvider.fake :
      return 'fas fa-code'
  }
}

export function feedDescription(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'Compteur Enedis associé'
    case DataProvider.enedisDataConnect :
      return 'Compteur Linky associé'
    case DataProvider.grdfAdict :
      return 'Compteur Gazpar associé'
    case DataProvider.meteoFrance :
      return 'Station météo associée'
    case DataProvider.fake :
      return 'Fake'
  }
}