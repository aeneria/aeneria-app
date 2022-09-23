<template>
  <Dialog
    header="Rafraichir des données depuis la source"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <Message severity="info" :closable="false" class="p-mb-4 p-mt-1">
      <p>
        Avant de commencer à rafraichir des données, lisez ces quelques informations :
        <ul>
          <li>Les données Météo France ne peuvent être récupérées que 2 semaines en arrière.</li>
          <li>Les données d'un compteur Linky :
            <ul>
              <li>Ne sont disponibles qu'à partir du jour où vous avez donné votre consentement pour æneria.</li>
              <li>Les dernières données disponibles sont les données d'hier.</li>
            </ul>
          </li>
          <li>Les données d'un compteur Gazpar :
            <ul>
              <li>Ne sont disponibles qu'à partir du jour où vous avez donné votre consentement pour æneria.</li>
              <li>Les dernières données disponibles sont les données d'il y a 3 jours.</li>
            </ul>
          </li>
        </ul>
      </p>
    </Message>
    <template v-for="feed of place.feedList" :key="feed.id">
      <RefreshFeedDataForm
        :place="place"
        :feed="feed"
        v-on:toggleVisible="closeBasic"
      />
    </template>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="closeBasic"
        class="p-button-text p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./RefreshDataForm.ts" />
