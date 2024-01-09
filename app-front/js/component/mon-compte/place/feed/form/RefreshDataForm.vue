<template>
  <Dialog
    :header="title"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <slot></slot>
    <!-- <p>
      Il peut parfois être nécessaire de rafraichir les données d'une adresse manuellement : c'est
      à dire forcer la mise à jour des données depuis les serveurs d'Enedis, GrDF et de Météo France.
    </p>
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
    </Message> -->
    <div class="p-d-flex p-flex-column p-ai-center">
      <div class="p-formgroup-inline">
        <div class="p-field">
          <label :for="feed.id" class="">Période</label>
          <Calendar
            :id="feed.id"
            v-model="range"
            selectionMode="range"
            :manualInput="true"
            dateFormat="dd/mm/yy"
            :class="{'p-invalid':v$.$invalid && submitted}"
            placeholder="Sélectionnez une période"
          />
        </div>
      </div>
    </div>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="closeBasic"
        class="p-button-text p-button-secondary"
      />
      <Button
        label="Rafraichir les données"
        icon="pi pi-refresh"
        @click="post(!v$.$invalid)"
        class="p-button-rounded p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./RefreshDataForm.ts" />
