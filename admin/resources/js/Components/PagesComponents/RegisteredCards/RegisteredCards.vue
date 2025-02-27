<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- SearchBar Component -->
        <SearchBar v-model="searchQuery" :loading="loading" @search="fetchCards(1)" @reset="resetSearch"
            @add-card="openModal" />

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Card Table -->
        <DaisyTable :data="registeredCards.data" :currentPage="registeredCards.current_page"
            :lastPage="registeredCards.last_page" @change-page="fetchCards" />

        <!-- Add Card Modal -->
        <DaisyModal ref="modalRef" title="Add Card">
            <template #default>
                <CardForm :form="cardForm" @submit="saveCard" @cancel="closeModal" />
            </template>
        </DaisyModal>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { io } from "socket.io-client";
import SearchBar from "./SearchBar.vue";
import CardForm from "./CardForm.vue";
import DaisyTable from "@/Components/DaisyTable.vue";
import DaisyModal from "@/Components/DaisyModal.vue";

// Props passed from the server
const props = defineProps({
    registeredCards: Object,
    search: {
        type: String,
        default: "",
    },
});

// Local state for search and notifications
const searchQuery = ref(props.search);
const loading = ref(false);
const successMessage = ref("");

// Use a form with fields matching the CardForm (studentId and uid)
const cardForm = useForm({ studentId: "", uid: "" });

// Socket.io instance
let socket = null;

// Reference to the modal component
const modalRef = ref(null);

// Connect to the socket.io server and define event handlers
onMounted(() => {
    socket = io("http://localhost:3000");

    socket.on("connect", () => {
        console.log("Connected to Socket.io server");
    });

    // When an NFC card is scanned, update the form fields
    socket.on("cardScanned", (data) => {
        console.log("Card scanned event received", data);
        cardForm.studentId = data.studentId;
        cardForm.uid = data.uid;
    });

    // Listen for NFC status messages from the server
    socket.on("nfcStatus", (message) => {
        successMessage.value = message;
    });

    // Listen for NFC errors
    socket.on("nfcError", (error) => {
        successMessage.value = "NFC Error: " + error;
    });

    // When a card is successfully registered via NFC, show a confirmation
    socket.on("cardRegistered", (card) => {
        successMessage.value = "✅ Card Registered Successfully!";
    });

    socket.on("disconnect", () => {
        console.log("Disconnected from server");
    });
});

// Disconnect the socket when the component unmounts
onUnmounted(() => {
    if (socket) {
        socket.disconnect();
    }
});

// Open the modal to add a new card
function openModal() {
    modalRef.value.showModal();
}

// Close the modal
function closeModal() {
    modalRef.value.closeModal();
}

// Save the card: submit the form data and emit a socket event after DB storage
function saveCard() {
    cardForm.post("/registered-cards", {
        preserveState: true,
        onSuccess: () => {
            closeModal();
            successMessage.value = "Card added successfully!";
            // Emit an event to the socket server after a successful DB operation
            socket.emit("dbStored", cardForm.data);
            setTimeout(() => (successMessage.value = ""), 4000);
            cardForm.reset();
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 0) {
                successMessage.value = "Error adding card. Try again!";
                setTimeout(() => (successMessage.value = ""), 4000);
            }
        },
    });
}

// Fetch cards (with pagination and search support)
function fetchCards(page) {
    loading.value = true;
    router.get(
        `/registered-cards`,
        { page: page, search: searchQuery.value || "" },
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            },
        }
    );
    console.log(searchQuery.value);
}

// Reset the search and fetch the first page
function resetSearch() {
    searchQuery.value = "";
    fetchCards(1);
}
</script>
