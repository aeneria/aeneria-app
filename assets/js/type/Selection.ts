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