<template>
  <Dialog
    header="Gérer la connexion à votre compteur Linky"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-text-center p-mb-4">
      <img class="img-fluid" src="/image/logo-enedis.png" alt="Logo de Endedis">
    </div>

    <p>
      PDL du compteur Linky actuellement associé&nbsp;: <code>{{ pdl }}</code>
    </p>
    <p>
      Vous pouvez vérifier l'état de la connexion entre æneria et votre compteur Linky en cliquant sur le bouton ci-dessous&nbsp;:
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
        essayez de ré-associer votre compteur Linky.
      </p>
      <div class="p-text-center">
      <Button
        label="Ré-associer mon compteur Linky"
        icon="pi pi-arrow-right"
        @click="update()"
        class="p-button-small p-button-rounded p-button-outlined p-button-danger"
      />
      </div>
    </Message>

    <h3>Changer de compteur Linky</h3>
    <p>
      Si vous le souhaitez, vous pouvez modifier le compteur Linky associé à cette adresse.
    </p>
    <p>
      En cliquant sur ce bouton, vous allez accéder à votre compte personnel Enedis qui vous
      permettera de choisir le compteur Linky que vous souhaitez associer à cette adresse.
    </p>
    <div class="p-text-center">
      <Button
        label="Modifier mon compteur Linky"
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

<script lang="ts" src="./EditLinkyForm.ts" />
