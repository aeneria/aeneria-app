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
    <div class="p-grid p-mt-2">
      <Card class="p-col-5">
        <template #title>
          <div class="p-d-flex">
            <i class="fas fa-plug p-mr-4"></i>
            <p class="p-my-0">Associer un compteur Linky d'Enedis</p>
          </div>
        </template>

        <template #content>
          <div class="p-text-center p-mb-4">
            <img class="img-fluid" src="/image/logo-enedis.png" alt="Logo d'Enedis">
          </div>

          <p>
            En cliquant sur ce bouton, vous allez accéder à votre compte personnel Enedis où vous pourrez donner votre accord pour qu’Enedis nous transmette vos données.
          </p>

          <Message severity="warn" :closable="false">
            <p>Pour donner votre autorisation, vous devez avoir un compte personnel Enedis.</p>
            <div class="p-text-center">
              <a href="https://mon-compte-particulier.enedis.fr/" class="p-button p-button-rounded p-button-secondary p-button-outlined" target="_blank">Je crée mon compte personnel Enedis</a>
            </div>
            <div class="p-text-center">
              <small class="p-mt-2">(Munissez-vous de votre facture d’électricité pour créer votre espace)</small>
            </div>
          </Message>
        </template>
        <template #footer>
          <div class="p-text-center">
            <Button
              label="Associer votre compteur Linky"
              icon="pi pi-arrow-right"
              @click="$emit('next', 'enedis')"
              class="p-button-rounded p-button-secondary"
            />
          </div>
        </template>
      </Card>
      <div class="p-col-2 p-jc-center p-d-flex p-ai-center">
        <p><strong>OU</strong></p>
      </div>
      <Card class="p-col-5">
        <template #title>
          <div class="p-d-flex">
            <i class="fas fa-burn p-mr-4"></i>
            <p class="p-my-0">Associer un compteur Gazpar de GRDF</p>
          </div>
        </template>
        <template #content>
          <div class="p-text-center p-mb-''">
            <img class="img-fluid" src="/image/logo-grdf.png" alt="Logo de GRDF">
          </div>
          <p>
            En cliquant sur ce bouton, vous allez accéder au service <i>Client Connect</i> de GRDF qui vous permettera de donner votre accord pour que GRDF nous transmette vos données.
          </p>
        </template>
        <template #footer>
          <div class="p-text-center">
            <Button
              label="Associer votre compteur Gazpar"
              icon="pi pi-arrow-right"
              @click="$emit('next', 'grdf')"
              class="p-button-rounded p-button-secondary"
            />
          </div>
        </template>
      </Card>
  </div>
</div>
</template>

<script lang="ts" src="./Step2.ts" />
