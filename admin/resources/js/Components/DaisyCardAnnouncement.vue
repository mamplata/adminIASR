<template>
  <dialog ref="modal" class="modal">
    <div class="card modal-box bg-white dark:bg-gray-800 dark:text-white">

      <div class="card-body">
        <div v-if="announcement.type === 'text'" class="mt-2">
          <h4 class="font-semibold text-gray-800 dark:text-white">
            {{ announcement.content.title }}
          </h4>
          <p class="text-gray-600 dark:text-gray-400">
            {{ announcement.content.body }}
          </p>
        </div>
        <div v-else-if="announcement.type === 'image'" class="mt-2">
          <img :src="announcement.content.file_path" :alt="announcement.content.file_name"
            class="rounded-md w-full object-cover" />
        </div>
      </div>
      <div class="card-actions mt-4">
        <button @click="closeCard" class="btn btn-secondary">Close</button>
      </div>
    </div>
  </dialog>
</template>

<script>
export default {
  props: {
    announcement: {
      type: Object,
      required: true
    }
  },
  mounted() {
    // Automatically show the modal when the component mounts
    this.$refs.modal.showModal();
  },
  methods: {
    closeCard() {
      this.$refs.modal.close();
      this.$emit('close');
    }
  }
};
</script>

<style scoped>
.modal::backdrop {
  background: rgba(0, 0, 0, 0.5);
}
</style>
