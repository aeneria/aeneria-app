<template>
  <Dialog
    header="Associer un compteur Gazpar de GRDF"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-text-center p-mb-4">
      <img class="img-fluid" src="/image/logo-grdf.png" alt="Logo de GRDF">
    </div>

    <p>
      À l'étape suivante, vous allez être rediriger vers service <i>Client Connect</i>
      de GRDF qui vous permettera de donner votre accord pour que GRDF nous transmette
      vos données.
    </p>

    <Message severity="info" :closable="false">
      <p>
        Pour qu'æneria fonctionne correctement, à l'étape suivante, donnez votre consentement
        pour les données suivantes&nbsp;:
        <ul>
          <li>
            Mes données de consommation gaz&nbsp;: oui
            <ul>
              <li>Période&nbsp;: choisissez la période la plus grande possible</li>
              <li>Autoriser l'accès à mes données publiées&nbsp;: oui</li>
              <li>Autoriser l'accès à mes données informatives&nbsp;: oui</li>
            </ul>
          </li>
          <li>Mes données techniques&nbsp;: oui</li>
        </ul>
      </p>
    </Message>

    <ProxyWarningMessage
      v-if="$store.state.configuration?.proxyForGrdf"
      provider="GRDF"
    />

    <template #footer>
      <div class="p-text-center p-mt-2">
        <Button
          label="Annuler"
          icon="pi pi-times"
          @click="closeBasic()"
          class="p-button-text p-button-rounded p-button-secondary"
        />
        <Button
          label="Associer un compteur Gazpar"
          icon="pi pi-arrow-right"
          @click="post()"
          class="p-button-rounded p-button-secondary"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts" src="./AddGazparForm.ts" />
