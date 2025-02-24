<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
      <!-- Search Input -->
      <div class="flex mb-2">
        <input v-model="searchQuery" type="text" placeholder="Search publisher"
          class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />
  
        <select v-model="selectedDepartment"
          class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500 mb-2 ml-2">
          <option value="" disabled>Select Department</option>
          <option v-for="department in props.searchDepartments" :key="department" :value="department">
            {{ department }}
          </option>
        </select>
  
        <button @click="fetchAnnouncements(1)" :disabled="loading"
          class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">
          <span v-if="loading" class="loading loading-spinner loading-sm"></span>
          <span v-else>Search</span>
        </button>
        <button @click="resetSearch" class="btn text-white btn-secondary shadow-lg hover:bg-gray-400 ml-2">
          Reset
        </button>
      </div>
  
      <!-- Add Announcement Button -->
      <button @click="openModal('Add')" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
        Add Announcement
      </button>
  
      <!-- Success Notification -->
      <transition name="fade">
        <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
          <span>{{ successMessage }}</span>
        </div>
      </transition>
      <DaisyTable :data="props.announcements.data" :excludedColumns="['id', 'content']"
        :currentPage="props.announcements.current_page" :lastPage="props.announcements.last_page"
        @change-page="fetchAnnouncements">
        <template #actions="{ row }">
          <div class="flex gap-2">
            <button @click="viewAnnouncement(row)"
              class="btn text-white btn-primary shadow-lg hover:bg-blue-700 mb-2">
              View
            </button>
            <button @click="openModal('Edit', row)"
              class="btn text-white btn-warning shadow-lg hover:bg-yellow-600 mb-2">
              Edit
            </button>
            <button @click="confirmDeleteAnnouncement(row.id)"
              class="btn text-white btn-error shadow-lg hover:bg-red-700 mb-2">
              Delete
            </button>
          </div>
        </template>
      </DaisyTable>
  
      <DaisyConfirm :visible="showConfirm" title="Delete Announcement"
        message="Are you sure you want to delete this announcement?" @confirm="handleConfirmDelete"
        @cancel="handleCancelDelete" />
  
      <!-- Conditionally render the announcement card -->
      <DaisyCardAnnouncement v-if="selectedAnnouncement" :announcement="selectedAnnouncement"
        @close="selectedAnnouncement = null" />
  
      <!-- Modal for Creating/Editing an Announcement -->
      <DaisyModal ref="modal" :title="currentAction">
        <template #default>
          <form @submit.prevent="isEditing ? updateAnnouncement() : saveAnnouncement()">
            <!-- Publisher -->
            <label class="label text-gray-900 dark:text-white">Publisher</label>
            <input v-model="announcementForm.publisher" type="text"
              class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
              required />
  
            <!-- Department Dropdown -->
            <label class="label text-gray-900 dark:text-white">Department</label>
            <select v-model="announcementForm.department"
              class="input w-full input-bordered mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
              <option value="" disabled>Select Department</option>
              <option v-for="dept in departments" :key="dept" :value="dept">
                {{ dept }}
              </option>
            </select>
  
            <!-- Publication Date -->
            <label class="label text-gray-900 dark:text-white">Publication Date</label>
            <input v-model="announcementForm.publication_date" type="date"
              class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
              required />
  
            <!-- Type (text/image) -->
            <label class="label text-gray-900 dark:text-white">Type</label>
            <select v-model="announcementForm.type"
              class="select select-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
              required>
              <option disabled value="">-- Select Announcement Type --</option>
              <option value="text">Text</option>
              <option value="image">Image</option>
            </select>
  
            <!-- Conditionally Render Content Fields -->
            <!-- For text-based announcements -->
            <div v-if="announcementForm.type === 'text'">
              <label class="label text-gray-900 dark:text-white">Title</label>
              <input v-model="extraContent.title" type="text"
                class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                required />
  
              <label class="label text-gray-900 dark:text-white">Body</label>
              <textarea v-model="extraContent.body"
                class="textarea textarea-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                required></textarea>
            </div>
  
            <!-- For image-based announcements -->
            <div v-else-if="announcementForm.type === 'image'">
              <label class="label text-gray-900 dark:text-white">{{ uploadImage }}</label>
              <div class="relative flex flex-col items-center mb-2">
                <input type="file" accept="image/*" @change="handleImageUpload" class="hidden"
                  id="fileInput" :required="isFileRequired" />
                <label for="fileInput"
                  class="cursor-pointer px-4 py-2 w-full border border-gray-300 rounded-md text-center bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                  {{ fileName || 'Choose File' }}
                </label>
              </div>
            </div>
  
            <!-- Submit Button with Loading State -->
            <div class="modal-action">
              <button type="button" @click="closeModal"
                class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                Cancel
              </button>
              <button type="submit"
                class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                <span v-if="announcementForm.processing" class="loading loading-spinner loading-sm"></span>
                <span v-else>{{ isEditing ? 'Update' : 'Save' }}</span>
              </button>
            </div>
          </form>
        </template>
      </DaisyModal>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed } from 'vue';
  import { useForm, router } from '@inertiajs/vue3';
  import DaisyTable from '@/Components/DaisyTable.vue';
  import DaisyModal from '@/Components/DaisyModal.vue';
  import DaisyCardAnnouncement from '@/Components/DaisyCardAnnouncement.vue';
  import DaisyConfirm from '../DaisyConfirm.vue';
  
  // Define props
  const props = defineProps({
    announcements: Object,
    searchDepartments: Array,
  });
  
  // Reactive state variables
  const searchQuery = ref("");
  const loading = ref(false);
  const selectedDepartment = ref("");
  const successMessage = ref("");
  
  const announcementForm = useForm({
    publisher: "",
    department: "",
    publication_date: "",
    type: "",
    content: "",
    file: null,
  });
  const fileName = ref('');
  const extraContent = reactive({
    title: "",
    body: "",
  });
  const departments = ['CCS', 'CAS', 'CHAS', 'COE', 'CBAA', 'COED'];
  const selectedAnnouncement = ref(null);
  const currentAction = ref("Add");
  const currentRow = ref(null);
  const isEditing = ref(false);
  const uploadImage = ref("Upload Image");
  const hasImage = ref(false);
  const showConfirm = ref(false);
  const announcementToDelete = ref(null);
  
  // Reference for the modal
  const modal = ref(null);
  
  // Computed property for file requirement
  const isFileRequired = computed(() => {
    return announcementForm.type === 'image' && (!isEditing.value || !hasImage.value);
  });
  
  // Methods
  function openModal(action, row = null) {
    resetForm();
    if (action === 'Edit' && row) {
      isEditing.value = true;
      announcementForm.publisher = row.publisher;
      announcementForm.department = row.department;
      let dateObj = new Date(row.publication_date);
      let formattedDate = dateObj.toISOString().split('T')[0];
      announcementForm.publication_date = formattedDate;
      announcementForm.type = row.type;
      if (row.type === 'text') {
        try {
          extraContent.title = row.content.title;
          extraContent.body = row.content.body;
        } catch (e) {
          console.error('Failed parsing text announcement content', e);
        }
      } else {
        uploadImage.value = "Upload New Image (Optional)";
        hasImage.value = true;
        announcementForm.content = row.content;
      }
      currentRow.value = row;
    } else {
      currentRow.value = null;
    }
    currentAction.value = action + " Announcement";
    if (modal.value) {
      modal.value.mode = action;
      modal.value.showModal();
    }
  }
  
  function closeModal() {
    isEditing.value = false;
    uploadImage.value = "Upload New Image";
    announcementForm.content = '';
    if (modal.value) {
      modal.value.closeModal();
    }
  }
  
  function resetForm() {
    announcementForm.reset();
    extraContent.title = "";
    extraContent.body = "";
    fileName.value = '';
  }
  
  function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    fileName.value = file.name;
    announcementForm.file = file;
  }
  
  function saveAnnouncement() {
    if (announcementForm.type === 'text') {
      announcementForm.content = JSON.stringify({
        title: extraContent.title,
        body: extraContent.body,
      });
    } else if (announcementForm.type === 'image') {
      announcementForm.content = announcementForm.file;
    }
  
    announcementForm.post('/announcements', {
      forceFormData: true,
      onSuccess: () => {
        closeModal();
        successMessage.value = "Announcement added successfully!";
        setTimeout(() => (successMessage.value = ""), 4000);
        announcementForm.reset();
        extraContent.title = "";
        extraContent.body = "";
        fileName.value = '';
      },
      onError: () => {
        successMessage.value = "Error adding announcement. Try again!";
      },
    });
  }
  
  function updateAnnouncement() {
    if (announcementForm.type === 'text') {
      announcementForm.content = JSON.stringify({
        title: extraContent.title,
        body: extraContent.body,
      });
    } else if (announcementForm.type === 'image') {
      if (announcementForm.file != null) {
        announcementForm.content = announcementForm.file;
      }
    }
  
    announcementForm.post(`/announcements/${currentRow.value.id}`, {
      _method: 'PUT',
      forceFormData: true,
      onSuccess: () => {
        closeModal();
        successMessage.value = "Announcement updated successfully!";
        setTimeout(() => (successMessage.value = ""), 4000);
        announcementForm.reset();
        extraContent.title = "";
        extraContent.body = "";
        fileName.value = '';
      },
      onError: () => {
        successMessage.value = "Error updating announcement. Try again!";
      },
    });
  }
  
  function confirmDeleteAnnouncement(announcementId) {
    announcementToDelete.value = announcementId;
    showConfirm.value = true;
  }
  
  function handleConfirmDelete() {
    announcementForm.delete(`/announcements/${announcementToDelete.value}`, {
      onSuccess: () => {
        successMessage.value = "Announcement deleted successfully!";
        setTimeout(() => { successMessage.value = ""; }, 4000);
      },
      onError: () => {
        successMessage.value = "Error deleting announcement. Please try again!";
        setTimeout(() => { successMessage.value = ""; }, 4000);
      },
    });
    announcementToDelete.value = null;
    showConfirm.value = false;
  }
  
  function handleCancelDelete() {
    announcementToDelete.value = null;
    showConfirm.value = false;
  }
  
  function fetchAnnouncements(page) {
    loading.value = true;
    router.get(
      '/announcements',
      {
        page: page,
        search: searchQuery.value,
        department: selectedDepartment.value,
      },
      {
        preserveState: true,
        onFinish: () => {
          loading.value = false;
        }
      }
    );
  }
  
  function resetSearch() {
    searchQuery.value = "";
    selectedDepartment.value = "";
    fetchAnnouncements(1);
  }
  
  function viewAnnouncement(row) {
    selectedAnnouncement.value = row;
  }
  </script>
