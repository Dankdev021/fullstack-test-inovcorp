<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import ToastContainer from './components/ToastContainer.vue'
</script>

<template>
  <div class="min-h-screen bg-surface">
    <AppHeader />
    <RouterView v-slot="{ Component }">
      <Transition name="page" mode="out-in">
        <component :is="Component" />
      </Transition>
    </RouterView>
    <ToastContainer />
  </div>
</template>

<style scoped>
.page-enter-active,
.page-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
