import { Feed, FeedType } from "./Feed"

export interface Place {
  id: number
  name: string
  icon: string
  feedList: Feed[]
  public: boolean
  allowedUsers: Array<{id: number, username: string}>
  periodeMin: Date|null
  periodeMax: Date|null
  createdAt: Date
  updatedAt: Date
}

export function findFeedByType(place: Place, type: FeedType): Feed|null
{
  return place.feedList.find((feed: Feed) => feed.type === type) ?? null
}
