<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
      <!-- Search Input -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-2 mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search announcements"
          class="input input-bordered w-full dark:bg-gray-700 dark:text-white col-span-2"
        />
        <select v-model="selectedDepartment" class="input input-bordered mb-2 dark:bg-gray-700 dark:text-white">
          <option value="" disabled>Select Department</option>
          <option v-for="department in searchDepartments" :key="department" :value="department">
            {{ department }}
          </option>
        </select>
        <button
          @click="fetchAnnouncements(1)"
          :disabled="loading"
          class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2"
        >
          <span v-if="loading" class="loading loading-spinner loading-sm"></span>
          <span v-else>Search</span>
        </button>
      </div>
  
      <!-- Add Announcement Button -->
      <button @click="openModal" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
        Add Announcement
      </button>
  
      <!-- Success Notification -->
      <transition name="fade">
        <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
          <span>{{ successMessage }}</span>
        </div>
      </transition>
  
      <!-- Table of Announcements -->
      <DaisyTable
        :data="announcements.data"
        :currentPage="announcements.current_page"
        :lastPage="announcements.last_page"
        @change-page="fetchAnnouncements"
      />
  
      <!-- Modal for Creating an Announcement -->
      <DaisyModal ref="modal" title="Add Announcement">
        <template #default>
          <form @submit.prevent="saveAnnouncement">
            <!-- Publisher -->
            <label class="label">Publisher</label>
            <input
              v-model="announcementForm.publisher"
              type="text"
              class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white"
              required
            />
  
            <!-- Department Dropdown -->
            <label class="label">Department</label>
            <select
              v-model="announcementForm.department"
              class="input w-full input-bordered mb-2 dark:bg-gray-700 dark:text-white"
            >
              <option value="" disabled>Select Department</option>
              <option v-for="dept in departments" :key="dept" :value="dept">
                {{ dept }}
              </option>
            </select>
  
            <!-- Type (text/image) -->
            <label class="label">Type</label>
            <select
              v-model="announcementForm.type"
              class="select select-bordered w-full mb-2 dark:bg-gray-700 dark:text-white"
              required
            >
              <option disabled value="">-- Select Announcement Type --</option>
              <option value="text">Text</option>
              <option value="image">Image</option>
            </select>
  
            <!-- Conditionally Render Content Fields -->
            <!-- For text-based announcements -->
            <div v-if="announcementForm.type === 'text'">
              <label class="label">Title</label>
              <input
                v-model="extraContent.title"
                type="text"
                class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white"
                required
              />
  
              <label class="label">Body</label>
              <textarea
                v-model="extraContent.body"
                class="textarea textarea-bordered w-full mb-2 dark:bg-gray-700 dark:text-white"
                required
              ></textarea>
            </div>
  
            <!-- For image-based announcements -->
            <div v-else-if="announcementForm.type === 'image'">
              <label class="label">Upload Image</label>
              <div class="relative flex flex-col items-center mb-2">
                <input
                  type="file"
                  accept="image/*"
                  @change="handleImageUpload"
                  class="hidden"
                  id="fileInput"
                  required
                />
                <label
                  for="fileInput"
                  class="cursor-pointer px-4 py-2 w-full border border-gray-300 rounded-md text-center dark:bg-gray-700 dark:text-white"
                >
                  {{ fileName || 'Choose File' }}
                </label>
              </div>
              <label class="label">Caption</label>
              <input
                v-model="extraContent.caption"
                type="text"
                class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white"
              />
            </div>
  
            <!-- Submit Button with Loading State -->
            <div class="modal-action">
              <button type="button" @click="closeModal" class="btn">Cancel</button>
              <button type="submit" class="btn btn-success text-white hover:bg-[#20714c]">
                <span v-if="announcementForm.processing" class="loading loading-spinner loading-sm"></span>
                <span v-else>Save</span>
              </button>
            </div>
          </form>
        </template>
      </DaisyModal>
    </div>
  </template>
  
  <script>
  import DaisyTable from '@/Components/DaisyTable.vue';
  import DaisyModal from '@/Components/DaisyModal.vue';
  import { useForm, router } from '@inertiajs/vue3';
  
  export default {
    props: {
      announcements: Object,
      searchDepartments: Array,
    },
    data() {
      return {
        searchQuery: "",
        loading: false,
        selectedDepartment: "",
        successMessage: "",
        announcementForm: useForm({
          publisher: "",
          department: "",
          type: "",
        }),
        fileName: '',
        extraContent: {
          title: "",
          body: "",
          image_url: "",
          caption: "",
        },
        departments: ['CCS', 'CAS', 'CHAS', 'COE', 'CBAA', 'COED'],
      };
    },
    methods: {
      openModal() {
        this.resetForm();
        this.$refs.modal.showModal();
      },
      closeModal() {
        this.$refs.modal.closeModal();
      },
      resetForm() {
        this.announcementForm.reset();
        this.extraContent = { title: "", body: "", image_url: "", caption: "" };
        this.fileName = '';
      },
      handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.fileName = file.name;
        const reader = new FileReader();
        reader.onload = () => {
          this.extraContent.image_url = reader.result;
        };
        reader.onerror = (error) => {
          console.error("Error converting image to Base64:", error);
        };
        reader.readAsDataURL(file);
      },
      saveAnnouncement() {
        let contentData = {};
        if (this.announcementForm.type === 'text') {
          contentData = {
            title: this.extraContent.title,
            body: this.extraContent.body,
          };
        } else if (this.announcementForm.type === 'image') {
          contentData = {
            image_url: this.extraContent.image_url,
            caption: this.extraContent.caption,
          };
        }
  
        const payload = {
          publisher: this.announcementForm.publisher,
          department: this.announcementForm.department,
          type: this.announcementForm.type,
          content: contentData,
        };
  
        this.announcementForm.post('/announcements', {
          data: payload,
          onSuccess: () => {
            this.closeModal();
            this.successMessage = "Announcement added successfully!";
            setTimeout(() => (this.successMessage = ""), 4000);
            this.announcementForm.reset();
          },
          onError: () => {
            this.successMessage = "Error adding announcement. Try again!";
          },
        });
      },
      fetchAnnouncements(page) {
        this.loading = true;
        router.get(
          '/announcements',
          {
            page,
            search: this.searchQuery,
            department: this.selectedDepartment,
          },
          {
            onFinish: () => {
              this.loading = false;
            }
          }
        );
      },
    },
    components: {
      DaisyTable,
      DaisyModal,
    },
  };
  </script>
