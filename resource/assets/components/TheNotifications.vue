<script setup lang="ts">
import {computed} from 'vue';
import {useStore} from 'vuex';
import NotificationItem from '@/components/items/Notification.vue';

const store = useStore();
const notifications = computed(() => store.state.notifications.notifications);
const isEmpty = computed(() => !Object.keys(notifications.value).length);

const delNotification = (id) => store.commit('notifications/delNotification', id);
</script>

<template>
  <Teleport to="body">
    <TransitionGroup name="notification" tag="div" class="notifications" :class="{'is-empty': isEmpty}">
      <NotificationItem v-for="notification in notifications" :key="notification.id" :variant="notification.type"
                        :message="notification.message" @closed="delNotification(notification.id)"/>
    </TransitionGroup>
  </Teleport>
</template>

<style lang="scss" scoped>
@import 'bootstrap/scss/functions';
@import 'bootstrap/scss/variables';
@import 'bootstrap/scss/mixins';

.notifications {
  position:    fixed;
  right:       1rem;
  top:         3rem;
  z-index:     2000;
  padding:     1rem;
  white-space: pre;

  .notification-move,
  .notification-enter-active,
  .notification-leave-active {
    transition: all .5s ease;
  }

  .notification-enter-from,
  .notification-leave-to {
    opacity:   0;
    transform: translateX(2rem);
  }

  &:not(.is-empty) {
    .notification-leave-active {
      position: absolute;
    }
  }

  @include media-breakpoint-up(md) {
    & {
      top: 1rem;
    }
  }
}
</style>