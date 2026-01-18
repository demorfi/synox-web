<script setup lang="ts">
import {ref, computed, inject} from 'vue';
import {useEntriesStore} from '@/stores/useEntriesStore';
import {prefersSchemeInjectionKey} from '@/stores/keys';

const entriesStore = useEntriesStore();
const props = defineProps({
  type: {
    type: String,
    required: true
  },

  id: {
    type: String,
    required: true
  },

  package: {
    type: String,
    required: true
  },

  category: {
    type: String,
    required: true
  },

  title: {
    type: String,
    required: true
  },

  weight: {
    type: String,
    required: true
  },

  date: {
    type: String,
    required: true
  },

  fetchId: {
    type: String,
    required: true
  },

  description: {
    type: String,
    default: ''
  },

  pageUrl: String,
  seeds: [Number, String],
  peers: [Number, String],
  properties: Object,
  content: Object
});

const inList = ['type', 'id', 'package', 'category', 'weight', 'date', 'seeds', 'peers', 'properties'];
const fetching = ref(false);
const downloading = ref(false);
const showModal = ref(false);

const pScheme = inject(prefersSchemeInjectionKey);
const background = computed(() => {
  let hash = 0;
  for (let i = 0; i < props.package.length; i++) {
    hash = props.package.charCodeAt(i) + ((hash << i) - hash);
  }

  const hsla = hash % 360;
  return {
    background: `linear-gradient(90deg,rgba(255,255,255,0) 60%,hsla(${hsla},50%,40%,.4) 100%)`
  };
});

const fetched = computed(() => {
  return entriesStore.get(props.id, props.fetchId)
      || props.content;
});

const listProps = computed(() => {
  const list = [];
  for (let [propName, propValue] of Object.entries(props)) {
    if (inList.includes(propName) && propValue !== undefined && propValue !== null) {
      const type = 'text';
      if (propValue instanceof Object) {
        for (let [name, value] of Object.entries(propValue)) {
          list.push({name, value, type});
        }
      } else {
        let name = propName.charAt(0).toUpperCase() + propName.slice(1);
        list.push({name, value: propValue, type});
      }
    }
  }

  // Add magnet link info
  if (props.content !== null && props.content?.magnet !== null) {
    list.push({name: 'Magnet', value: props.content.magnet, type: 'link'});
  }
  return list;
});

const fetch = (packageId, fetchId) => {
  fetching.value = true;
  entriesStore.fetch(packageId, fetchId, props.content)
      .finally(() => fetching.value = false);
};

const show = () => showModal.value = true;

const download = (fetched) => {
  downloading.value = true;
  entriesStore.download(fetched.name, fetched.typeId, fetched.baseName)
      .finally(() => downloading.value = false);
};
</script>

<template>
  <BCard no-body :bg-variant="pScheme.color" footer-class="fs-8" class="border-0 border-bottom shadow-sm">
    <BCardBody :style="background">
      <BCardTitle :text="title" class="mb-0" tag="h5">
        <a v-if="pageUrl" :href="pageUrl" target="_blank">{{ title }}</a>
      </BCardTitle>
      <BCardSubtitle v-if="description" class="mt-2" :text="description"/>
    </BCardBody>

    <template #footer>
      <BRow>
        <BCol cols="4" md="2">
          <BRow v-if="!!fetchId">
            <BCol cols="12">
              <BButton v-if="!fetched || !fetched?.available" @click="fetch(id, fetchId)"
                       size="sm" :variant="'outline-' + pScheme.invert" class="m-2">
                <BSpinner v-show="fetching" type="grow" small/>
                Fetch
              </BButton>

              <BButton v-else-if="!!fetched?.content" @click="show" size="sm" variant="outline-primary" class="m-2">
                Show
                <Suspense>
                  <BModal size="sm" ok-title="Close" ok-variant="secondary" cancel-title="Download"
                          cancel-variant="primary" v-model="showModal" :title="title" :ok-only="!fetched?.path"
                          @cancel="download(fetched)" centered>
                    <pre>{{ fetched.content }}</pre>
                  </BModal>
                </Suspense>
              </BButton>

              <BButton v-else-if="!!fetched?.path" @click="download(fetched)" size="sm"
                       variant="outline-success" class="m-2">
                <BSpinner v-show="downloading" type="grow" small/>
                Download
              </BButton>
            </BCol>
          </BRow>
        </BCol>

        <BCol cols="8" md="10">
          <BRow>
            <dl v-for="(info, index) in listProps" :key="index" class="col-6 col-md-4 col-lg-3 mb-0">
              <dt>{{ info.name }}</dt>
              <dd>
                <template v-if="info.type === 'text'">{{ info.value }}</template>
                <template v-else-if="info.type === 'link'">
                  <a :href="info.value" target="_blank">Link</a>
                </template>
              </dd>
            </dl>
          </BRow>
        </BCol>
      </BRow>
    </template>
  </BCard>
</template>

<style scoped>
pre {
  white-space: pre-line;
}
</style>