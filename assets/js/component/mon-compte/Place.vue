
<template>
  <Card class="card-place p-mr-3 p-mb-3">
    <template #title>
     {{ place.name }}
    </template>
    <template #content>

      <div class="p-d-flex p-flex-column p-jc-between">
        <div class="p-p-0 p-d-flex p-flex-wrap p-mb-3">
          <template v-for="feed in place.feedList" :key="feed.id">
            <Chip
              v-if="feed.dataProvider === 'LINKY'"
              class="p-mb-1 p-mr-1"
              :label="'Compte Enedis&nbsp;: ' + feed.param['LOGIN']"
              icon="fas fa-tachometer-alt"
            />
            <Chip
              v-else-if="feed.dataProvider === 'ENEDIS_DATA_CONNECT'"
              class="p-mb-1 p-mr-1"
              :label="'Linky&nbsp;: PDL&nbsp;-&nbsp;'+ feed.param['ADDRESS'].usagePointId"
              icon="fas fa-plug"
            />
            <Chip
              v-else-if="feed.dataProvider === 'GRDF_ADICT'"
              class="p-mb-1 p-mr-1"
              :label="'Gazpar&nbsp;: PCE&nbsp;-&nbsp;'+ feed.param['PCE']"
              icon="fas fa-burn"
            />
            <Chip
              v-else-if="feed.dataProvider === 'METEO_FRANCE'"
              class="p-mb-1 p-mr-1"
              :label="'Météo&nbsp;: '+ feed.param['CITY']"
              icon="fas fa-cloud-sun"
            />
            <Chip
              v-else-if="feed.dataProvider === 'FAKE'"
              class="p-mb-1 p-mr-1"
              :label="'FakeProvider&nbsp;: '+ feed.type"
              icon="fas fa-code"
            />
            <Chip
              v-else
              class="p-mb-1 p-mr-1"
              :label="feed.feedDataProviderType"
              icon="fas fa-code"
            />
          </template>
        </div>
        <Button
          icon="pi pi-pencil"
          title="Gérer l'adresse"
          @click="toggleMenuEdition"
          aria-haspopup="true"
          aria-controls="overlay_menu_edition"
          class="button-place-change p-button-sm p-button-rounded p-button-secondary p-button-icon-only p-mt-auto p-ml-auto"
        />
        <Menu
          id="overlay_menu_edition"
          ref="menuEdition"
          :model="menuEditionItems"
          :popup="true"
        />
      </div>
    </template>
  </Card>
</template>

<script lang="ts" src="./Place.ts"/>

<style lang="scss">
.card-place {
  max-width: 500px;
}
</style>