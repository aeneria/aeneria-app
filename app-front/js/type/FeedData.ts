export interface FeedData {
  id: number
  type: DataType
}

export interface FeedDataType {
  id: DataType
  label: string
  unite: string
  coefficientNormalisateur: number
  precision: number
  color: string
  colors: string[]
  hasHourlyData: boolean
  icon: string
}

export enum DataType {
  ConsoEnergie = 'CONSO_ENERGIE',
  ConsoElec = 'CONSO_ELEC',
  ConsoGaz = 'CONSO_GAZ',
  Temperature = 'TEMPERATURE',
  TemperatureMin = 'TEMPERATURE_MIN',
  TemperatureMax = 'TEMPERATURE_MAX',
  Dju = 'DJU',
  Pressure = 'PRESSURE',
  Humidity = 'HUMIDITY',
  Nebulosity = 'NEBULOSITY',
  Rain = 'RAIN',
}

export const feedDataTypeList = new Array<FeedDataType>(
  {
    id: DataType.ConsoEnergie,
    label: "Énergie totale",
    unite: 'kWhep',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#dba225',
    colors: [
      '#feff9d',
      '#ffee79',
      '#fbde5e',
      '#f5ce48',
      '#edbf38',
      '#e4b02c',
      '#dba225',
      '#d09422',
      '#c68622',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-sigma',
  },
  {
    id: DataType.ConsoElec,
    label: "Électricité",
    unite: 'kWh',
    coefficientNormalisateur: 1.8,
    precision: 1,
    color: '#dba225',
    colors: [
      '#feff9d',
      '#ffee79',
      '#fbde5e',
      '#f5ce48',
      '#edbf38',
      '#e4b02c',
      '#dba225',
      '#d09422',
      '#c68622',
    ],
    hasHourlyData: true,
    icon: 'fa-solid fa-bolt',
  },
  {
    id: DataType.ConsoGaz,
    label: "Gaz",
    unite: 'kWh',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#2171b5',
    colors: [
      '#f7fbff',
      '#deebf7',
      '#c6dbef',
      '#9ecae1',
      '#6baed6',
      '#4292c6',
      '#2171b5',
      '#08519c',
      '#08306b',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-fire-flame-simple',
  },
  {
    id: DataType.Temperature,
    label: "Température",
    unite: '°C',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#fdae61',
    colors: [
      '#4575b4',
      '#74add1',
      '#abd9e9',
      '#e0f3f8',
      '#ffffbf',
      '#fee090',
      '#fdae61',
      '#f46d43',
      '#d73027',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-temperature-half',
  },
  {
    id: DataType.TemperatureMin,
    label: "Température minimale",
    unite: '°C',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#4575b4',
    colors: [
      '#4575b4',
      '#74add1',
      '#abd9e9',
      '#e0f3f8',
      '#ffffbf',
      '#fee090',
      '#fdae61',
      '#f46d43',
      '#d73027',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-temperature-empty',
  },
  {
    id: DataType.TemperatureMax,
    label: "Température maximale",
    unite: '°C',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#d73027',
    colors: [
      '#4575b4',
      '#74add1',
      '#abd9e9',
      '#e0f3f8',
      '#ffffbf',
      '#fee090',
      '#fdae61',
      '#f46d43',
      '#d73027',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-temperature-full',
  },
  {
    id: DataType.Dju,
    label: "Degrés Jour Unifié",
    unite: 'DJU',
    coefficientNormalisateur: 1,
    precision: 0,
    color: '#039BE5',
    colors: [
      '#B3E5FC',
      '#81D4FA',
      '#64B5F6',
      '#4FC3F7',
      '#29B6F6',
      '#03A9F4',
      '#039BE5',
      '#0288D1',
      '#0277BD',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-temperature-half',
  },
  {
    id: DataType.Pressure,
    label: "Pression",
    unite: 'hPa',
    coefficientNormalisateur: 0,
    precision: 1,
    color: '#dba225',
    colors: [
      '#feff9d',
      '#ffee79',
      '#fbde5e',
      '#f5ce48',
      '#edbf38',
      '#e4b02c',
      '#dba225',
      '#d09422',
      '#c68622',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-gauge-high',
  },
  {
    id: DataType.Humidity,
    label: "Humidité",
    unite: '%',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#41b6c4',
    colors: [
      '#f03b20',
      '#feb24c',
      '#ffeda0',
      '#ffffcc',
      '#c7e9b4',
      '#7fcdbb',
      '#41b6c4',
      '#2c7fb8',
      '#253494',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-droplet',
  },
  {
    id: DataType.Nebulosity,
    label: "Nébulosité",
    unite: '%',
    coefficientNormalisateur: 1,
    precision: 0,
    color: '#749198',
    colors: [
      '#20CFFE',
      '#48C4EB',
      '#5BBAD9',
      '#67AFC7',
      '#6EA5B7',
      '#729BA7',
      '#749198',
      '#758889',
      '#747E7C',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-cloud',
  },
  {
    id: DataType.Rain,
    label: "Précipiations",
    unite: 'mm/m²',
    coefficientNormalisateur: 1,
    precision: 1,
    color: '#2b8cbe',
    colors: [
      '#f7fcf0',
      '#e0f3db',
      '#ccebc5',
      '#a8ddb5',
      '#7bccc4',
      '#4eb3d3',
      '#2b8cbe',
      '#0868ac',
      '#084081',
    ],
    hasHourlyData: false,
    icon: 'fa-solid fa-cloud-showers-heavy',
  },
)

export function getFeedDataType(type: DataType): FeedDataType
{
  return feedDataTypeList.find((element: FeedDataType) => element.id === type) ?? feedDataTypeList[0]
}

export function getFeedDataTypeColor(feedDataType: FeedDataType, color: 1|2): string
{
  return color == 1 ? feedDataType.colors[4] : feedDataType.colors[8]
}

export function isFeedDataEnergie(feedData: FeedData): boolean
{
  return [DataType.ConsoElec, DataType.ConsoGaz].includes(feedData.type)
}
