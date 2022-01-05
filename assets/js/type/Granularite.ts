export enum GranulariteType {
  Jour = 'jour',
  Semaine = 'semaine',
  Mois = 'mois',
  Annee = 'annee',
}

export enum RepartitionColonne {
  Date = 'date',
  Hour = 'hour',
  WeekDay = 'weekDay',
  Week = 'week',
  Month = 'month',
  Year = 'year',
}

export enum Frequence {
  Hour = "HOUR",
  Day = "DAY",
  Week = "WEEK",
  Month = "MONTH",
  Year = "YEAR",
}

export interface Granularite {
  type: GranulariteType,
  label: string,
  frequence: Frequence,
}

export const granulariteList = [
  {
    type: GranulariteType.Jour,
    label: 'Jour',
    frequence: Frequence.Day
  },
  {
    type: GranulariteType.Semaine,
    label: 'Semaine',
    frequence: Frequence.Week
  },
  {
    type: GranulariteType.Mois,
    label: 'Mois',
    frequence: Frequence.Month
  },
  {
    type: GranulariteType.Annee,
    label: 'Année',
    frequence: Frequence.Year
  },
] as Granularite[]

export function getGranularite(type: GranulariteType): Granularite
{
  return granulariteList.find((element: Granularite) => element.type === type) ?? granulariteList[0]
}

