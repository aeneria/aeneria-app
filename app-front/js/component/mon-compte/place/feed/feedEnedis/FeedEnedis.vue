
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
    <EditLinkyForm
      :visible="displayCheckConnectionForm"
      :update="true"
      v-on:toggleVisible="toggleCheckConnectionForm()"
      :place="place"
      :feed="feed"
    />
    <RefreshDataForm
      :visible="displayRefreshDataForm"
      title="Rafraichir les données depuis Enedis"
      v-on:toggleVisible="toggleRefreshDataForm()"
      :place="place"
      :feed="feed"
    >
      <p>
        Il peut parfois être nécessaire de rafraichir les données d'un flux manuellement : c'est
        à dire forcer la mise à jour des données depuis les serveurs d'Enedis, GrDF et de Météo France.
      </p>
      <Message severity="info" class="p-mb-4 p-mt-1">
        <p>
          Les données d'un compteur Linky :
          <ul>
            <li>Ne sont disponibles qu'à partir du jour où vous avez donné votre consentement pour æneria.</li>
            <li>Les dernières données disponibles sont les données d'hier.</li>
          </ul>
        </p>
      </Message>
    </RefreshDataForm>
    <ImportDataForm
      :visible="displayImportDataForm"
      title="Importer un fichier de données Enedis"
      v-on:toggleVisible="toggleImportDataForm()"
      :place="place"
      :feed="feed"
    >
      <p>
        Enedis vous propose, depuis votre compte personnel, d'exporter vos données de consommation.
      </p>
      <p>
        Cet export est le moyen le plus simple et le plus efficace pour importer des données en masse sur æneria.
      </p>
      <p>
        Les fichiers générés sont des CSV qui peuvent contenir des données journalières ou des données horaires.
        Ces 2 types de fichier sont acceptés par l'import d'æneria.
      </p>
      <Message severity="warn" class="p-mb-4 p-mt-1">
        <p>
          Cet import a été prévu pour fonctionner avec les fichiers CSV issus d'Enedis et seulement ces fichiers.
          Si vous essayez d'importer des fichiers provenant d'une autre source, faites-le à vos risques et périls !
        </p>
      </Message>
      <Message severity="info" class="p-mb-4 p-mt-1">
        <p>
          Les données horaires d'Enedis peuvent être incomplètes (voire erronées). Pour cette raison, lors
          de l'import d'un fichier de données horaires, æneria n'essaye pas de déduire les données
          journalières à partir des données horaires présentes dans le fichier.
        </p>
        <p>
          Ainsi, pour avoir l'ensemble des données dans æeneria (horaires et jounralières), il est nécessaire
          d'importer un fichier de données horaires <strong>et</strong> un fichier de données journalières.
        </p>
      </Message>
    </ImportDataForm>
  </div>
</template>

<script lang="ts" src="./FeedEnedis.ts"/>
