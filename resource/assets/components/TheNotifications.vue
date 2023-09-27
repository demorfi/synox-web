<template>
  <transition-group
      name="notification"
      tag="div"
      class="notifications">
    <div class="notification"
         v-for="notification in notifications"
         :key="notification.id">
      <NotificationItem
          :type="notification.type"
          :message="notification.message"
          @closed="delNotification(notification.id)"/>
    </div>
  </transition-group>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';
import NotificationItem from '@/components/NotificationItem.vue';

const {mapState, mapMutations} = createNamespacedHelpers('notifications');

export default {
  components: {
    NotificationItem
  },

  computed: {
    ...mapState(['notifications'])
  },

  methods: {
    ...mapMutations(['delNotification'])
  }
}
</script>

<style lang="scss" scoped>
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';

.notifications {
  position: fixed;
  right: 1rem;
  top: 3rem;
  z-index: 100;
  padding: 1rem;

  .notification {
    transition: all .4s ease;
  }

  .notification-leave-active {
    position: fixed;
    white-space: pre;
  }

  .notification-enter-from,
  .notification-leave-to {
    opacity: 0;
    transform: translateY(-30px);
  }

  @include media-breakpoint-up(md) {
    & {
      top: 1rem;
    }
  }
}
</style>