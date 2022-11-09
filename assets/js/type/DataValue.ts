export const monthList = [
  "Janvier",
  "Février",
  "Mars",
  "Avril",
  "Mai",
  "Juin",
  "Juillet",
  "Aout",
  "Septembre",
  "Octobre",
  "Novembre",
  "Décembre"
] as [string, string, string, string, string, string, string, string, string, string, string, string]
export const shortMonthList = [
  "Jan.",
  "Fév.",
  "Mars",
  "Avr.",
  "Mai",
  "Juin",
  "Juil.",
  "Aout",
  "Sep.",
  "Oct.",
  "Nov.",
  "Déc."
] as [string, string, string, string, string, string, string, string, string, string, string, string]
export const weekDayList = [
  "Dimanche",
  "Lundi",
  "Mardi",
  "Mercredi",
  "Jeudi",
  "Vendredi",
  "Samedi"
] as [string, string, string, string, string, string, string]
export const shortWeekDayList = [
  "Dim.",
  "Lun.",
  "Mar.",
  "Mer.",
  "Jeu.",
  "Ven.",
  "Sam."
] as [string, string, string, string, string, string, string]
export const minWeekDayList = [
  "D",
  "L",
  "Ma",
  "Me",
  "J",
  "V",
  "S"
] as [string, string, string, string, string, string, string]

export interface DataPoint {
  id: number|string,
  value: number,
  date: Date
}

export interface DataRepartition {
  value: number,
  groupBy: number,
}

export interface DataDoubleRepartition {
  value: number,
  axeX: number,
  axeY: number,
}
