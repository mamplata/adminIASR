<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <SearchBar v-model:searchQuery="searchQuery" v-model:selectedDepartment="selectedDepartment"
            v-model:selectedProgram="selectedProgram" :searchDepartments="searchDepartments"
            :searchPrograms="searchPrograms" v-model:startDate="startDate" v-model:endDate="endDate"
            :maxStartDate="maxStartDate" :maxEndDate="maxEndDate" :loading="loading" @search="fetchAnnouncements(1)"
            @reset="resetSearch" @add="openModal('Add')" />

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <AnnouncementTable :announcements="announcements" @change-page="fetchAnnouncements" @view="viewAnnouncement"
            @edit="openModal('Edit', $event)" @delete="confirmDeleteAnnouncement" />

        <DaisyConfirm :visible="showConfirm" title="Delete Announcement"
            message="Are you sure you want to delete this announcement?" @confirm="handleConfirmDelete"
            @cancel="handleCancelDelete" />

        <!-- Conditionally render the announcement card -->
        <DaisyCardAnnouncement v-if="selectedAnnouncement" :announcement="selectedAnnouncement"
            @close="selectedAnnouncement = null" />

        <DaisyModal ref="modalRef" :title="currentAction">
            <template #default>
                <AnnouncementModal :currentAction="currentAction" :announcementForm="announcementForm"
                    :extraContent="extraContent" :departments="departments" :departmentPrograms="departmentPrograms"
                    :minPublicationDate="minPublicationDate" :uploadImage="uploadImage" :fileName="fileName"
                    :isFileRequired="isFileRequired" @cancel="closeModal"
                    @save="isEditing ? updateAnnouncement() : saveAnnouncement()" @image-upload="handleImageUpload" />
            </template>
        </DaisyModal>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import DaisyModal from '@/Components/DaisyModal.vue';
import DaisyCardAnnouncement from '@/Components/DaisyCardAnnouncement.vue';
import DaisyConfirm from '@/Components/DaisyConfirm.vue';
import SearchBar from './SearchBar.vue';
import AnnouncementTable from './AnnouncementTable.vue';
import AnnouncementModal from './AnnouncementModal.vue';

// Define props
const props = defineProps({
    announcements: Object,
    searchDepartments: Array,
    searchPrograms: Object,
    departments: Array,
    departmentPrograms: Object
});

const { props: page } = usePage();

// State variables
const searchQuery = ref(page.search || "");
const loading = ref(false);
const selectedDepartment = ref(page.filterDepartments || "");
const selectedProgram = ref(page.filterPrograms || "");
const successMessage = ref("");

const announcementForm = useForm({
    publisher: "",
    departments: "",
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


const selectedAnnouncement = ref(null);
const currentAction = ref("Add");
const currentRow = ref(null);
const isEditing = ref(false);
const uploadImage = ref("Upload Image");
const hasImage = ref(false);
const showConfirm = ref(false);
const announcementToDelete = ref(null);
const startDate = ref(page.start_date || "");
const endDate = ref(page.end_date || "");

// Reference for the modal component
const modalRef = ref(null);

// Computed properties
const maxStartDate = computed(() => {
    if (endDate.value) {
        return endDate.value;
    }
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();
    if (mm < 10) mm = `0${mm}`;
    if (dd < 10) dd = `0${dd}`;
    return `${yyyy}-${mm}-${dd}`;
});

const maxEndDate = computed(() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();
    if (mm < 10) mm = `0${mm}`;
    if (dd < 10) dd = `0${dd}`;
    return `${yyyy}-${mm}-${dd}`;
});

const minPublicationDate = computed(() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();
    if (mm < 10) mm = `0${mm}`;
    if (dd < 10) dd = `0${dd}`;
    const todayFormatted = `${yyyy}-${mm}-${dd}`;

    if (isEditing.value && announcementForm.publication_date < todayFormatted) {
        return announcementForm.publication_date;
    }
    return todayFormatted;
});

const isFileRequired = computed(() => {
    return announcementForm.type === 'image' && (!isEditing.value || !hasImage.value);
});

// Methods
function openModal(action, row = null) {
    resetForm();
    if (action === 'Edit' && row) {
        isEditing.value = true;
        announcementForm.publisher = row.publisher;
        announcementForm.departments = row.departments;

        const dateObj = new Date(row.publication_date);
        const year = dateObj.getFullYear();
        const month = ("0" + (dateObj.getMonth() + 1)).slice(-2);
        const day = ("0" + dateObj.getDate()).slice(-2);
        announcementForm.publication_date = `${year}-${month}-${day}`;
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
    // Set mode if needed on modalRef
    if (modalRef.value) {
        modalRef.value.mode = action;
        console.log(announcementForm);
        modalRef.value.showModal();
    }
}

function closeModal() {
    isEditing.value = false;
    uploadImage.value = "Upload New Image";
    announcementForm.content = '';
    announcementForm.departments = '';
    if (modalRef.value) {
        modalRef.value.closeModal();
    }
    selectedDepartment.value = '';
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
        onError: (errors) => {
            // Only show a generic error if there are no field-specific validation errors.
            if (Object.keys(errors).length === 0) {
                successMessage.value = "Error adding announcement. Try again!";
                setTimeout(() => (successMessage.value = ""), 4000);
            }
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
        onError: (errors) => {
            // Only show a generic error if there are no field-specific validation errors.
            if (Object.keys(errors).length === 0) {
                successMessage.value = "Error updating announcement. Try again!";
                setTimeout(() => (successMessage.value = ""), 4000);
            }
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
            page,
            search: searchQuery.value,
            departments: selectedDepartment.value,
            programs: selectedProgram.value,
            start_date: startDate.value || null,
            end_date: endDate.value || null,
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
    selectedProgram.value = "";
    startDate.value = "";
    endDate.value = "";
    fetchAnnouncements(1);
}

function viewAnnouncement(row) {
    selectedAnnouncement.value = row;
}
</script>
