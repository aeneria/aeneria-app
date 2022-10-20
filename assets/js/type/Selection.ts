import { FeedDataType } from "./FeedData"
import { Granularite } from "./Granularite"
import { Place } from "./Place"

export interface Selection {
  place: null|Place
  periode: [null|Date, null|Date]
  periode2: [null|Date, null|Date]
  energie: null|FeedDataType
  meteoData: null|FeedDataType
  granularite: null|Granularite
}

export const preselectPeriodMenuItem = (command: CallableFunction) => {
  return [
    {
      label: 'Semaine en cours',
      command: () => command('current-week')
    },
    {
      label: 'Semaine précédente',
      command: () => command('last-week')
    },
    {
      separator: true
    },
    {
      label: 'Mois en cours',
      command: () => command('current-month')
    },
    {
      label: 'Mois précédent',
      command: () => command('last-month')
    },
    {
      label: 'Les 3 derniers mois',
      command: () => command('last-3-months')
    },
    {
      label: 'Les 6 derniers mois',
      command: () => command('last-6-months')
    },
    {
      separator: true
    },
    {
      label: 'Année en cours',
      command: () => command('current-year')
    },
    {
      label: 'Année précédente',
      command: () => command('last-year')
    },
    {
      label: 'Année glissante',
      command: () => command('sliding-year')
    },
    {
      separator: true
    },
    {
      label: 'Tout',
      command: () => command('all')
    },
  ]
}

export const preselectedPeriode = (value: string) => {
  const now = new Date()
  const startDate = new Date()
  startDate.setHours(0)
  startDate.setMinutes(0)
  const endDate = new Date()
  endDate.setHours(0)
  endDate.setMinutes(0)

  switch (value) {
    case 'current-week' :
      startDate.setDate(now.getDate() - (now.getDay() - 1))
      endDate.setDate(now.getDate() - 1)
      break
    case 'last-week' :
      startDate.setDate(now.getDate() - (now.getDay() + 6))
      endDate.setDate(now.getDate() - now.getDay())
      break
    case 'current-month' :
      startDate.setDate(1)
      endDate.setDate(now.getDate() - 1)
      break
    case 'last-month' :
      startDate.setDate(1)
      startDate.setMonth(now.getMonth() - 1)
      endDate.setDate(0)
      break
    case 'last-3-months' :
      startDate.setDate(1)
      startDate.setMonth(now.getMonth() - 3)
      endDate.setDate(now.getDate() - 1)
      break
    case 'last-6-months' :
      startDate.setDate(1)
      startDate.setMonth(now.getMonth() - 6)
      endDate.setDate(now.getDate() - 1)
      break
    case 'current-year' :
      startDate.setDate(1)
      startDate.setMonth(0)
      endDate.setDate(now.getDate() - 1)
      break
    case 'last-year' :
      startDate.setDate(1)
      startDate.setMonth(0)
      startDate.setFullYear(now.getFullYear() - 1)
      endDate.setMonth(0)
      endDate.setDate(0)
      break
    case 'sliding-year' :
      startDate.setDate(1)
      startDate.setMonth(now.getMonth() - 11)
      endDate.setDate(now.getDate() - 1)
      break
    case 'all' :
      // @todo
      break
  }

  return [startDate, endDate] as [Date|null, Date|null]
}
