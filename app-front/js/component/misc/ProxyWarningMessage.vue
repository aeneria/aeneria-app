<template>
  <Message severity="warn" :closable="false">
    <p>
      æneria est actuellement configuré pour passer par le serveur
      <i>{{ $store.state.configuration?.proxyUrl }}</i>
      pour accéder aux données de {{ provider }}
    </p>
    <Button
      label="En savoir plus"
      icon="pi pi-question-circle"
      @click="showDialog = true"
      class="p-button-rounded p-button-sm p-button-warning p-mt-2"
    />
    <Dialog
      header="Que signifie passer par un serveur proxy ?"
      v-model:visible="showDialog"
      :breakpoints="{'960px': '75vw', '640px': '90vw'}"
      :style="{width: '50vw'}"
      :modal="true"
      :closable="true"
    >
      <p>
        æneria récupère les données de consommation d'énergie à la fois chez Enedis et chez GRDF.

        Pour se faire, le système utilise les APIs mises à disposition par ces 2 structures :
        <ul>
          <li><a href="https://datahub-enedis.fr/data-connect" target="_blank">
            le Data Hub d'Enedis
          </a></li>
          <li><a href="https://sites.grdf.fr/web/portail-api-grdf-adict" target="_blank">
            le portail Grdf Adict
          </a></li>
        </ul>

        Mais pour utiliser ces APIs il est nécessaire d'avoir un compte chez ces 2 plateformes et de signer
        un contrat. Seulement, pour signer ce contrat, il faut être une entreprise, une association ou une
        collectivité locale.
      </p>
      <p>
        Pour permettre à tout le monde d'utiliser æneria, un serveur communautaire a été mis à disposition
        pour qu'une instance d'æneria puisse bénéficier des clés d'API de ce serveur.
      </p>
      <Message severity="warn" :closable="false">
        <p>
          Attention, cela signifie que vos données en provenance de ces APIs (vos données de
          consommation d'énergie) vont transiter par ce serveur proxy.
        </p>
        <p>
          Ayez conscience qu'en passant par {{ $store.state.configuration?.proxyUrl }},
          lorsque vous donnez votre consentement pour accéder aux données, vous donnez votre consentement
          pour {{ $store.state.configuration?.proxyUrl }} et non pour *votre* installation d'æneria.
        </p>
        <p>
          Cela signifie que, techniquement, les personnes qui administrent {{ $store.state.configuration?.proxyUrl }}
          peuvent avoir accès à toutes vos données.
        </p>
        <p><b>
          C'est à vous de voir si vous souhaitez leur faire confiance ou non.
        </b></p>
      </Message>
    </Dialog>
  </Message>
</template>

<script lang="ts" src="./ProxyWarningMessage.ts"/>
