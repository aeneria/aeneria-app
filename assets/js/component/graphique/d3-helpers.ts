import { monthList, shortMonthList, shortWeekDayList, weekDayList } from "@/type/DataValue"
import { Granularite, GranulariteType } from "@/type/Granularite"
import * as d3 from 'd3';

export const d3LocaleDef = {
  dateTime: '%c',
  date: '"%d/%m/%Y"',
  time: "%H:%M:%S",
  periods: ["AM", "PM"],
  days: weekDayList,
  shortDays: shortWeekDayList,
  months: monthList,
  shortMonths: shortMonthList,
} as d3.TimeLocaleDefinition

const d3LocaleObject = d3.timeFormatDefaultLocale(d3LocaleDef)

export const weekDayFormat = (i: number) => shortWeekDayList[(i + 1)%7]
export const monthFormat = (d: Date) => [d.getFullYear(), 'Fév.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'][d.getMonth()]
export const hourFormat = (i: number) => ('00' + i % 24).slice(-2) + 'h'

export const formatMulti = (dateToFormat: Date|d3.NumberValue): string => {
  const date = new Date(dateToFormat.toString())

  return (d3.timeDay(date) < date ? d3LocaleObject.format("%H:%M")
      : d3.timeMonth(date) < date ? (d3.timeWeek(date) < date ? d3LocaleObject.format("%a %d") : d3LocaleObject.format("%b %d"))
      : d3.timeYear(date) < date ? d3LocaleObject.format("%B")
      : d3LocaleObject.format("%Y"))(date)
}

export const formatWithGranularite = (granularite: Granularite, dateToFormat: Date|d3.NumberValue): string => {
  const date = new Date(dateToFormat.toString())

  switch (granularite.type) {
    case GranulariteType.Jour:
      return d3LocaleObject.format("%A %d %B %Y")(date)

    case GranulariteType.Semaine:
      return d3LocaleObject.format("Semaine du %d %B %Y")(date)

    case GranulariteType.Mois:
      return d3LocaleObject.format("%B %Y")(date)

    case GranulariteType.Annee:
      return d3LocaleObject.format("%Y")(date)
  }
}

export const adaptToGranularite = (granularite: Granularite, dateToAdapt: Date): Date => {
  const date = new Date(dateToAdapt)

  switch (granularite.type) {
    case GranulariteType.Jour:
      break

    case GranulariteType.Semaine:
      date.setDate(dateToAdapt.getDate() - dateToAdapt.getDay() + 1)
      break

    case GranulariteType.Mois:
      date.setDate(1)
      break

    case GranulariteType.Annee:
      date.setDate(1)
      date.setMonth(0)
      break
  }

  return date
}