<template>
  <Dialog
    header="Gérer la connexion à votre compteur Gazpar"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-text-center p-mb-4">
      <img class="img-fluid" src="/image/logo-grdf.png" alt="Logo de GRDF">
    </div>

    <p>
      PCE du compteur Gazpar actuellement associé&nbsp;: <code>{{ pce }}</code>
    </p>
    <p>
      Vous pouvez vérifier l'état de la connexion entre æneria et votre compteur Gazpar en cliquant sur le bouton ci-dessous&nbsp;:
    </p>
    <div class="p-text-center">
      <Button
        :label="checkingLabel"
        :icon="'pi ' + checkingIcon"
        :disabled="checkingDisabled"
        @click="check()"
        :class="'p-button-rounded p-button-outlined ' + checkingColor"
      />
    </div>

    <Message v-if="checkingError" severity="error" :closable="false">
      <p>
        Il y a eu une erreur lors de la vérification. Veuillez réessayer plus tard. Si l'erreur persiste,
        essayez de ré-associer votre compteur Gazpar.
      </p>
      <div class="p-text-center">
      <Button
        label="Ré-associer mon compteur Gazpar"
        icon="pi pi-arrow-right"
        @click="update()"
        class="p-button-small p-button-rounded p-button-outlined p-button-danger"
      />
      </div>
    </Message>

    <h3>Changer de compteur Gazpar</h3>
    <p>
      Si vous le souhaitez, vous pouvez modifier le compteur Gazpar associé à cette adresse.
    </p>
    <p>
      En cliquant sur ce bouton, vous allez accéder au service <i>Client Connect</i> de GRDF qui vous
      permettera de choisir le compteur Gazpar que vous souhaitez associer à cette adresse.
    </p>
    <div class="p-text-center">
      <Button
        label="Modifier mon compteur Gazpar"
        icon="pi pi-arrow-right"
        @click="update()"
        class="p-button-rounded p-button-secondary"
      />
    </div>

    <template #footer>
      <div class="p-text-center">
        <Button
          label="Fermer"
          icon="pi pi-times"
          @click="closeBasic()"
          class="p-button-text p-button-rounded p-button-secondary"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts" src="./EditGazparForm.ts" />
