import { FeedData } from "./FeedData"

export interface Feed {
  id: number
  name: string
  type: string
  dataProvider: string
  param: any[]
  frequencies: number[]
  feedDataList: FeedData[]
}
