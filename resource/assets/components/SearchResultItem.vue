<template>
  <b-card
      no-body
      bg-variant="light"
      footer-class="fs-8"
      class="border-0 border-bottom shadow-sm">
    <b-card-body :style="background">
      <b-card-title
          :text="title"
          class="mb-0"
          tag="h5">
        <a
            v-if="pageUrl"
            :href="pageUrl"
            target="_blank">
          {{ title }}
        </a>
      </b-card-title>
      <b-card-subtitle
          v-if="description"
          class="mt-2"
          :text="description"/>
    </b-card-body>
    <template #footer>
      <b-row>
        <b-col
            cols="4"
            md="2">
          <b-row v-if="!!fetchId">
            <b-col cols="12">
              <b-button
                  v-if="!fetched || !fetched?.available"
                  @click="fetch(id, fetchId)"
                  size="sm"
                  variant="outline-dark"
                  class="m-2">
                <b-spinner
                    v-show="fetching"
                    type="grow"
                    small/>
                Fetch
              </b-button>
              <b-button
                  v-else-if="!!fetched?.content"
                  @click="show"
                  size="sm"
                  variant="outline-primary"
                  class="m-2">
                Show
                <suspense>
                  <b-modal
                      v-model="showModal"
                      :title="title"
                      :ok-only="!fetched?.path"
                      size="sm"
                      ok-title="Close"
                      ok-variant="secondary"
                      cancel-title="Download"
                      cancel-variant="primary"
                      centered
                      @cancel="download(fetched)">
                    <pre>{{ fetched.content }}</pre>
                  </b-modal>
                </suspense>
              </b-button>
              <b-button
                  v-else-if="!!fetched?.path"
                  @click="download(fetched)"
                  size="sm"
                  variant="outline-success"
                  class="m-2">
                <b-spinner
                    v-show="downloading"
                    type="grow"
                    small/>
                Download
              </b-button>
            </b-col>
          </b-row>
        </b-col>
        <b-col
            cols="8"
            md="10">
          <b-row>
            <dl
                v-for="(info, index) in listProps"
                :key="index"
                class="col-6 col-md-4 col-lg-3 mb-0">
              <dt>{{ info.name }}</dt>
              <dd>
                <template
                    v-if="info.type === 'text'">
                  {{ info.value }}
                </template>
                <template
                    v-else-if="info.type === 'link'">
                  <a
                      :href="info.value"
                      target="_blank">
                    Link
                  </a>
                </template>
              </dd>
            </dl>
          </b-row>
        </b-col>
      </b-row>
    </template>
  </b-card>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';

const {mapGetters, mapActions} = createNamespacedHelpers('content');

export default {
  props: {
    type: {
      type    : String,
      required: true,
      inList  : true
    },

    id: {
      type    : String,
      required: true,
      inList  : false
    },

    package: {
      type    : String,
      required: true,
      inList  : true
    },

    category: {
      type    : String,
      required: true,
      inList  : true
    },

    title: {
      type    : String,
      required: true
    },

    weight: {
      type    : String,
      required: true,
      inList  : true
    },

    date: {
      type    : String,
      required: true,
      inList  : true
    },

    fetchId: {
      type    : String,
      required: true
    },

    pageUrl: {
      type    : String,
      required: false
    },

    description: {
      type    : String,
      required: false,
      default : ''
    },

    seeds: {
      type    : [Number, String],
      required: false,
      inList  : true
    },

    peers: {
      type    : [Number, String],
      required: false,
      inList  : true
    },

    properties: {
      type    : Object,
      required: false,
      inList  : true
    },

    content: {
      type     : Object,
      required : false
    }
  },

  data: () => ({
    fetching   : false,
    downloading: false,
    showModal  : false,
  }),

  computed: {
    ...mapGetters(['getEntry']),
    background()
    {
      let hash = 0;
      for (let i = 0; i < this.package.length; i++) {
        hash = this.package.charCodeAt(i) + ((hash << i) - hash);
      }

      const hsla = hash % 360;
      return {
        background: `linear-gradient(90deg,rgba(255,255,255,0) 60%,hsla(${hsla},50%,40%,.4) 100%)`
      };
    },

    fetched()
    {
      return this.getEntry({packageId: this.id, fetchId: this.fetchId}) || this.content;
    },

    listProps()
    {
      let list = [];
      for (let prop in this.$options.props) {
        let setting = this.$options.props[prop],
            value = this.$props[prop];

        if ('inList' in setting && setting.inList && value !== undefined && value !== null) {
          const type = 'text';
          if (value instanceof Object) {
            for (let sValue in value) {
              list.push({name: sValue, value: value[sValue], type});
            }
          } else {
            let name = prop.charAt(0).toUpperCase() + prop.slice(1);
            list.push({name, value, type});
          }
        }
      }

      // Add magnet link info
      if (this.content !== null && this.content?.magnet !== null) {
        list.push({name: 'Magnet', value: this.content.magnet, type: 'link'});
      }
      return list;
    }
  },

  methods: {
    ...mapActions(['fetchEntry', 'downloadEntry']),
    fetch(packageId, fetchId)
    {
      this.fetching = true;
      this.fetchEntry({packageId, fetchId, params: this.content})
          .finally(() => this.fetching = false);
    },

    show()
    {
      this.showModal = true;
    },

    download(fetched)
    {
      this.downloading = true;
      this.downloadEntry(fetched)
          .finally(() => this.downloading = false);
    }
  }
}
</script>

<style scoped>
pre
{
  white-space : pre-line;
}
</style>