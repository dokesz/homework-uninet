<template>
  <div class="dashboard">
    <Sidebar />
    <MainContent />
  </div>
</template>

<script setup>
import Sidebar from '../components/dashboard/Sidebar.vue'
import MainContent from '../components/dashboard/MainContent.vue'
</script>

<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
}

.dashboard {
  display: flex;
  min-height: 100vh;
}
</style>
