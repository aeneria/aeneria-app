
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
      title="Gérer le compteur Gazpar"
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
    <EditGazparForm
      :visible="displayCheckConnectionForm"
      :update="true"
      v-on:toggleVisible="toggleCheckConnectionForm()"
      :place="place"
      :feed="feed"
    />
    <RefreshDataForm
      :visible="displayRefreshDataForm"
      title="Rafraichir les données depuis GRDF"
      v-on:toggleVisible="toggleRefreshDataForm()"
      :feed="feed"
    >
      <p>
        Il peut parfois être nécessaire de rafraichir les données d'un flux manuellement : c'est
        à dire forcer la mise à jour des données depuis les serveurs d'Enedis, GrDF et de Météo France.
      </p>
      <Message severity="info" :closable="false" class="p-mb-4 p-mt-1">
        <p>
          Les données d'un compteur Gazpar :
          <ul>
            <li>Ne sont disponibles qu'à partir du jour où vous avez donné votre consentement pour æneria.</li>
            <li>Les dernières données disponibles sont les données d'il y a 3 jours.</li>
          </ul>
        </p>
      </Message>
    </RefreshDataForm>
    <ImportDataForm
      :visible="displayImportDataForm"
      title="Importer un fichier de données GRDF"
      v-on:toggleVisible="toggleImportDataForm()"
      :feed="feed"
    >
      <p>
        GRDF vous propose, depuis votre compte personnel, d'exporter vos données de consommation.
      </p>
      <p>
        Cet export est le moyen le plus simple et le plus efficace pour importer des données en masse sur æneria.
      </p>
      <p>
        Les fichiers générés sont des XLSX qui contiennent des données journalières.
      </p>
      <Message severity="warn" class="p-mb-4 p-mt-1">
        <p>
          Cet import a été prévu pour fonctionner avec les fichiers XLSX issus de GRDF et seulement ces fichiers.
          Si vous essayez d'importer des fichiers provenant d'une autre source, faites-le à vos risques et périls !
        </p>
      </Message>
    </ImportDataForm>
  </div>
</template>

<script lang="ts" src="./FeedGrdf.ts"/>
