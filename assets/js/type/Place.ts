import { Feed, FeedType } from "./Feed"

export interface Place {
  id: number
  name: string
  icon: string
  feedList: Feed[]
  periodeMin: Date|null
  periodeMax: Date|null
}

export function findFeedByType(place: Place, type: FeedType): Feed|null
{
  return place.feedList.find((feed: Feed) => feed.type === type) ?? null
}
