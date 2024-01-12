
<template>
  <div>
    <FeedEnedis
      v-if="isElectricityFeed"
      :place="place"
      :feed="feed"
    />
    <FeedGrdf
      v-if="isGazFeed"
      :place="place"
      :feed="feed"
    />
    <FeedMeteoFrance
      v-if="isMeteoFeed"
      :place="place"
      :feed="feed"
    />
  </div>
</template>

<script lang="ts" src="./Feed.ts"/>

<style lang="scss">
.feed-detail {
  .icon {
    width: 1.5rem;
    text-align: center;
  }
}
</style>