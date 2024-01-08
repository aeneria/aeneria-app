<template>
  <Dialog
    header="Associer un compteur Linky d'Enedis"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-text-center p-mb-4">
      <img class="img-fluid" src="/image/logo-enedis.png" alt="Logo d'Enedis">
    </div>

    <p>
      À l'étape suivante, vous allez être rediriger vers votre compte personnel
      Enedis où vous pourrez donner votre accord pour qu’Enedis nous transmette
      vos données.
    </p>

    <Message severity="info" :closable="false">
      <p>Pour donner votre autorisation, vous devez avoir un compte personnel Enedis.</p>
      <div class="p-text-center">
        <a
        class="p-button p-button-rounded p-button-sm p-button-outlined p-button-info"
        href="https://mon-compte-particulier.enedis.fr/"
          target="_blank"
        >
          Je crée mon compte personnel Enedis
        </a>
      </div>
      <div class="p-text-center">
        <small class="p-mt-2">(Munissez-vous d'une facture d’électricité pour créer votre espace)</small>
      </div>
    </Message>

    <Message severity="info" :closable="false">
      <p>
        Pour avoir accès à l'ensemble des graphiques proposés par æneria, nous vous conseillons d'activer
        la collecte horaire.
      </p>
      <p>
        Depuis votre compte client Enedis, rendez-vous dans l'ongler "Gérer l’accès à mes données" puis activez&nbsp;:
        <ul>
          <li>L'enregistrement de la consommation horaire</li>
          <li>La collecte de la consommation horaire</li>
        </ul>
      </p>
    </Message>

    <ProxyWarningMessage
      v-if="$store.state.configuration?.proxyForEnedis"
      provider="Enedis"
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
          label="Associer un compteur Linky"
          icon="pi pi-arrow-right"
          @click="post()"
          class="p-button-rounded p-button-secondary"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts" src="./AddLinkyForm.ts" />
