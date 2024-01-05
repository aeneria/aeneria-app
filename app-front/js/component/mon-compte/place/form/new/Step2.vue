<template>
  <div class="parameter p-d-block">
    <p>Maintenant, ajoutez un compteur Linky ou Gazpar à votre nouvelle adresse&nbsp;:</p>
    <p>
      En France, <a href="https://www.enedis.fr/profil" target="_blank">Enedis</a> gère le réseau d’électricité jusqu’au compteur d’électricité et
      <a href="https://www.grdf.fr" target="_blank">GRDF</a> gère le réseau de gaz jusqu’au compteur de gaz.
    </p>
    <p>
      Pour permettre à æneria d'alimenter ses tableaux de bord avec vos consommations d'énergie, vous devez autoriser Enedis et/ou GRDF à nous
      transmettre les données de votre(vos) compteur(s) communicant(s).
    </p>
    <div class="p-d-flex p-jc-around p-my-4">
      <Button
        label="Associer un compteur Linky"
        icon="fas fa-plug"
        @click="$emit('next', 'enedis')"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
      <Button
        label="Associer un compteur Gazpar"
        icon="fas fa-burn"
        @click="$emit('next', 'grdf')"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </div>
    <div class="p-d-flex p-jc-end">
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="$emit('cancel')"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Étape précédente"
        icon="pi pi-arrow-left"
        @click="$emit('previous')"
        class="p-button-text p-button-rounded p-button-secondary"
      />
    </div>
  </div>
</template>

<script lang="ts" src="./Step2.ts" />
