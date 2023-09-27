<template>
  <b-collapse
      tag="nav"
      id="sidebar"
      class="col-md-auto sticky-top bg-dark"
      horizontal>
    <div class="items sticky-top d-flex flex-md-column flex-row flex-nowrap align-items-center text-center">
      <div class="top-items">
        <ul class="nav nav-pills nav-flush flex-md-column flex-row flex-nowrap mb-auto mx-auto">
          <li class="first nav-item">
            <img v-b-toggle.sidebar src="@/images/logo.svg" class="logo" alt="SynoX" width="32" height="32"
                 title="SynoX" aria-expanded="false" aria-controls="sidebar" aria-hidden="true"/>
            <span class="title">SynoX</span>
          </li>

          <router-link
              v-for="route in routes"
              :key="route.path"
              :to="route.path"
              v-slot="{href, navigate, isActive}"
              custom>
            <li class="nav-item" :class="[isActive && 'active']">
              <a class="nav-link" :title="route.name" :href="href" @click="navigate">
                <AppIcon :name="route.meta.icon"/>
                <span class="title">{{ route.name }}</span>
                <b-badge
                    v-if="route.meta.badge && route.meta.badge.id in badges && badges[route.meta.badge.id]"
                    v-bind="route.meta.badge"
                    pill>
                  {{ badges[route.meta.badge.id] }}
                </b-badge>
              </a>
            </li>
          </router-link>
        </ul>
      </div>

      <div class="bottom-items">
        <div class="last nav-item">
          <a href="https://github.com/demorfi/synox" target="_blank" class="nav-link" title="Github">
            <AppIcon name="github"/>
            <span class="title fs-8">
              <AppIcon name="code-slash"/> with <AppIcon name="heart-fill"/> in Siberia
            </span>
          </a>
        </div>
      </div>

    </div>
  </b-collapse>
</template>

<script>
import {mapGetters} from 'vuex';
import AppIcon from '@/components/AppIcon.vue';

export default {
  components: {
    AppIcon
  },

  data: () => ({
    badges: {
      search  : 0,
      packages: 0
    }
  }),

  watch: {
    getPackagesEnabled: {
      immediate: true,
      handler()
      {
        this.badges.packages = this.getPackagesEnabled.length;
      }
    }
  },

  computed: {
    ...mapGetters('packages', ['getPackagesEnabled']),
    routes()
    {
      return this.$router.getRoutes();
    }
  }
}
</script>

<style lang="scss">
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';

$sidebar-closed-width: 4rem;
$sidebar-opened-width: 8rem;
$sidebar-color-links: rgb(169, 169, 169);
$sidebar-color-links-active: rgb(255, 255, 255);
$sidebar-color-active-selected: rgb(212, 108, 0);
$sidebar-bg-active-selected: rgba(62, 62, 62, .8);

#sidebar {
  padding: 0;

  svg {
    vertical-align: sub;
  }

  &.collapse:not(.show) {
    display: block;
  }

  &.collapsing {
    transition: none !important;
    width: 100% !important;
  }

  .items {
    transition: 0s;
  }

  .nav-link {
    color: $sidebar-color-links;
    padding: .7rem 1rem;
    position: relative;

    &:hover,
    &:focus {
      color: $sidebar-color-links-active;
    }

    .badge {
      display: inline-block;
      position: absolute;
      font-size: .5rem;
      top: 1rem;
      right: 0;
      transform: translate(-25%, -50%);
    }
  }

  .nav-item {
    position: relative;

    &.first {
      color: $sidebar-color-links-active;
      padding: .5rem 1rem;
    }

    &.active {
      background-color: $sidebar-bg-active-selected;

      .nav-link {
        color: $sidebar-color-links-active;
      }

      &::before {
        height: .4rem;
        right: 0;
        background-color: $sidebar-color-active-selected;
        content: '';
        top: 0;
        left: 0;
        position: absolute;
      }
    }

    .title {
      display: none;
    }
  }

  .top-items {
    margin-right: auto;
  }

  @include media-breakpoint-up(md) {
    &.collapsing {
      transition: inherit !important;
      width: inherit !important;
    }

    .nav-item {
      &.first {
        img {
          cursor: pointer;
        }
      }
    }

    .items {
      transition: .3s;
      width: $sidebar-closed-width;
      overflow: hidden;
      min-height: 100vh;

      > div {
        width: 100%;
      }
    }

    .top-items {
      padding-top: .5rem;
      margin-bottom: auto;
      margin-right: inherit;
    }

    .nav-item:not(.last) {
      margin-bottom: 1.5rem;

      &.active::before {
        width: .4rem;
        top: 0;
        bottom: 0;
        height: inherit;
        right: inherit;
      }
    }

    &.show {
      .nav-item {
        &.first {
          display: block;
          margin-bottom: 1rem;

          span {
            display: block;
          }
        }
      }

      .items {
        width: calc($sidebar-closed-width + $sidebar-opened-width);
      }

      .nav-link {
        display: grid;
        align-items: center;
        column-gap: 1rem;
        grid-template-columns: max-content max-content;

        .title {
          display: inline-block;
        }
      }
    }
  }
}
</style>