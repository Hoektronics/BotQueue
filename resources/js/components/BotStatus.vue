<template>
  <div class="relative">
    <button v-on:click="expanded = !expanded"
            :class="[status_color]"
            class="relative rounded-full shadow whitespace-no-wrap text-white flex fill-current items-center p-1 pl-2 cursor-pointer z-10">
      {{ status }}
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="h-6">
        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
      </svg>
    </button>
    <button v-if="expanded" class="fixed inset-0 w-full h-full bg-black opacity-50" tabindex="-1"></button>
    <div v-if="expanded" class="mt-2 py-2 w-48 absolute bg-white border shadow-xl rounded-lg">
      <a href="#" class="block px-2 hover:bg-blue-500 hover:text-white">Bring Online</a>
      <a href="#" class="block px-2 hover:bg-blue-500 hover:text-white">Something else</a>
    </div>
  </div>
</template>

<script>
export default {
  name: "BotStatus",
  props: ['status'],
  data: function () {
    return {
      expanded: false
    };
  },
  computed: {
    status_color: function () {
      return {
        'bg-black': this.status === 'offline',
        'bg-green-500': this.status === 'idle',
        'bg-gray-600': this.status === 'job_assigned' || this.status === 'waiting',
        'bg-blue-400': this.status === 'working',
      }
    },
  }
}
</script>

<style scoped>

</style>