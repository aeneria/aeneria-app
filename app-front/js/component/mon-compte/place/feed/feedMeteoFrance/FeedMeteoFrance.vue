
<template>
  <div class="feed-detail">
    <span :class="icon + ' p-mr-2 icon'" :title="description"></span>
    <i
      v-if="feed.fetchError > 5"
      v-tooltip="'Il semble qu\'il y ait des erreurs avec ce compteur. Vérifiez son fonctionnement en cliquant sur le bouton en bout de ligne.'"
      class="pi pi-exclamation-triangle p-mr-1"
      style="color: #c63737;"
    ></i>
    <span v-html="label"></span>
    <Button
      icon="pi pi-ellipsis-v"
      title="Gérer le compteur Linky"
      @click="toggleMenuEdition"
      aria-haspopup="true"
      :aria-controls="'overlay_menu_edition_' + feed.id"
      class="p-button-xs p-button-rounded p-button-secondary p-button-icon-only p-ml-1"
    />
    <Menu
      :id="'overlay_menu_edition_' + feed.id"
      ref="menuEdition"
      :model="menuEditionItems"
      :popup="true"
    />
    <EditMeteoForm
      :visible="displayCheckConnectionForm"
      v-on:toggleVisible="toggleCheckConnectionForm()"
      :place="place"
    />
    <RefreshDataForm
      :visible="displayRefreshDataForm"
      title="Rafraichir les données depuis MétéoFrance"
      v-on:toggleVisible="toggleRefreshDataForm()"
      :place="place"
      :feed="feed"
    >
      <p>
        Il peut parfois être nécessaire de rafraichir les données d'un flux manuellement : c'est
        à dire forcer la mise à jour des données depuis les serveurs d'Enedis, GrDF et de Météo France.
      </p>
      <Message severity="info" :closable="false" class="p-mb-4 p-mt-1">
        <p>
          Les données Météo France ne peuvent être récupérées que 2 semaines en arrière.
        </p>
      </Message>
    </RefreshDataForm>
  </div>
</template>

<script lang="ts" src="./FeedMeteoFrance.ts"/>
