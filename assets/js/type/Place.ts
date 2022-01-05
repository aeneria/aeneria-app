import { Feed } from "./Feed"

export interface Place {
  id: number
  name: string
  icon: string
  feedList: Feed[]
}
