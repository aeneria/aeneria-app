import { DataDoubleRepartition, DataPoint, DataRepartition } from "@/type/DataValue";
import { Frequence, RepartitionColonne } from "@/type/Granularite";
import { queryData } from "@/utils";

export function queryDataPoint(
  feedDataId: number,
  frequence: Frequence,
  debut: Date,
  fin: Date,
): Promise<Array<DataPoint>> {
  return queryData(`/api/data/point/${feedDataId}/${frequence}/${formatDate(debut)}/${formatDate(fin)}`)
  .then ((data) => {
    if (!data) {
      return null
    }
    return JSON.parse(data, (key, value) => {
      if (key === 'date' && typeof value === 'object') {
        return new Date(value.date)
      }
      return value
    })
  })
}

export function queryRepartition(
  feedDataId: number,
  frequence: Frequence,
  colonne: RepartitionColonne,
  debut: Date,
  fin: Date,
): Promise<Array<DataRepartition>> {
  return queryData(`/api/data/repartition/${feedDataId}/${frequence}/${colonne}/${formatDate(debut)}/${formatDate(fin)}`)
  .then ((data) => {
    return JSON.parse(data, (key, value) => {
      return key === 'value' ? parseFloat(value) : value
    })
  })
}

export function queryDoubleRepartition(
  feedDataId: number,
  frequence: Frequence,
  colonneX: RepartitionColonne,
  colonneY: RepartitionColonne,
  debut: Date,
  fin: Date,
): Promise<Array<DataDoubleRepartition>> {
  return queryData(`/api/data/double_repartition/${feedDataId}/${frequence}/${colonneX}/${colonneY}/${formatDate(debut)}/${formatDate(fin)}`)
  .then ((data) => {
    return JSON.parse(data, (key, value) => {
      return key === 'value' ? parseFloat(value) : value
    })
  })
}

export function queryCroisement(
  feedDataIdX: number,
  feedDataIdY: number,
  frequence: Frequence,
  debut: Date,
  fin: Date,
): Promise<number> {
  return queryData(`/api/data/croisement/${feedDataIdX}/${feedDataIdY}/${frequence}/${formatDate(debut)}/${formatDate(fin)}`)
  .then(data => data.data as number)
}

export function querySomme(
  feedDataId: number,
  frequence: Frequence,
  debut: Date,
  fin: Date,
): Promise<number> {
  return queryData(`/api/data/somme/${feedDataId}/${frequence}/${formatDate(debut)}/${formatDate(fin)}`)
  .then(data => data.data as number)
}

export function queryNombreInferieur(
  feedDataId: number,
  valeur: number,
  frequence: Frequence,
  debut: Date,
  fin: Date,
): Promise<number> {
  return queryData(`/api/data/min/${feedDataId}/${frequence}/${formatDate(debut)}/${formatDate(fin)}`)
  .then(data => data.data as number)
}


function formatDate(date: Date): string {
  return date.getFullYear() + '-' + ('00' + (date.getMonth() + 1)).slice(-2) + '-' + ('00' + date.getDate()).slice(-2)
}
