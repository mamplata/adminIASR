<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- SearchBar Component -->
        <SearchBar v-model="searchQuery" :loading="loading" @search="fetchUsers(1)" @reset="resetSearch()"
            @add-user="openModal" />

        <!-- User Table -->
        <DaisyTable :data="users.data" :currentPage="users.current_page" :lastPage="users.last_page"
            @change-page="fetchUsers" />

        <!-- Add User Modal -->
        <DaisyModal ref="modalRef" title="Add User">
            <template #default>
                <AddUserForm :form="userForm" @submit="saveUser" @cancel="closeModal" />
            </template>
        </DaisyModal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import SearchBar from './SearchBar.vue';
import AddUserForm from './AddUserForm.vue';
import DaisyTable from '@/Components/Daisy/DaisyTable.vue';
import DaisyModal from '@/Components/Daisy/DaisyModal.vue';
import { useToast } from 'vue-toastification';

const toast = useToast() // Initialize toast

// Define props passed from the server
const props = defineProps({
    users: Object,
    search: {
        type: String,
        default: ""
    }
});

const { props: page } = usePage();

// Local state
const searchQuery = ref(page.search || "");
const loading = ref(false);
const userForm = useForm({ id: null, name: "", email: "", password: "", confirmPassword: "" });

// Reference to the modal component
const modalRef = ref(null);

// Methods
function openModal() {
    modalRef.value.showModal();
}

function closeModal() {
    modalRef.value.closeModal();
}

function saveUser() {
    userForm.post('/users', {
        onSuccess: () => {
            closeModal();
            toast.success('User added successfully!') // Show success toast
            userForm.reset();
        },
        onError: (errors) => {
            // Only show a generic error if there are no field-specific validation errors.
            if (Object.keys(errors).length === 0) {
                toast.error('Error adding user. Try again!') // Show error toast
            }
        }
    });
}


function fetchUsers(page) {
    loading.value = true;
    router.get(
        `/users`,
        { page: page, search: searchQuery.value || "" },
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            }
        }
    );
    console.log(searchQuery.value);
}

function resetSearch() {
    searchQuery.value = "";
    fetchUsers(1);
}
</script>
