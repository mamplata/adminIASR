<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- SearchBar Component -->
        <SearchBar v-model="searchQuery" :loading="loading" @search="fetchRegisteredCards(1)" @reset="resetSearch()"
            @add-card="openModal" />

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Device Table -->
        <DaisyTable :data="registeredCards.data" :currentPage="registeredCards.current_page"
            :lastPage="registeredCards.last_page" @change-page="fetchRegisteredCards">
            <template #actions="{ row }">
                <div class="flex justify-center">
                    <button @click="confirmDelete(row.id)"
                        class="btn text-white btn-error shadow-lg hover:bg-red-700 mb-2">
                        Delete
                    </button>
                </div>
            </template>
        </DaisyTable>

        <DaisyConfirm :visible="showConfirm" title="Delete Card" message="Are you sure you want to delete this card?"
            @confirm="handleConfirmDelete" @cancel="handleCancelDelete" />

        <!-- DaisyModal Component Usage -->
        <DaisyModal title="Enter Student ID" ref="modalRef">
            <EnterStudentForm v-model="studentID" :isLoading="isLoading" @cancel-registration="cancelRegistration"
                @register-student="registerStudent" />
        </DaisyModal>

        <DaisyModal title="Student Information" ref="modalRef1">
            <ConfirmStudentInfo :studentID="studentID" :modalStudentInfo="modalStudentInfo" :cardExists="cardExists"
                :nfcStatus="nfcStatus" @cancel-registration="cancelRegistration"
                @confirm-registration="confirmRegistration" />
        </DaisyModal>


        <DaisyModal title="Waiting for NFC Tap" ref="modalRef2">
            <NfcScanningState :nfcStatus="nfcStatus" @cancel-registration="cancelRegistration" />
        </DaisyModal>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from '@inertiajs/vue3';
import { io } from "socket.io-client";
import axios from "axios";
import SearchBar from './SearchBar.vue';
import DaisyTable from '@/Components/DaisyTable.vue';
import DaisyModal from '@/Components/DaisyModal.vue';
import EnterStudentForm from "./EnterStudentForm.vue";
import ConfirmStudentInfo from "./ConfirmStudentInfo.vue";
import NfcScanningState from "./NfcScanningState.vue";
import DaisyConfirm from "@/Components/DaisyConfirm.vue";

// Define props passed from the server
const props = defineProps({
    registeredCards: Object,
    search: {
        type: String,
        default: ""
    }
});

// Local state
const searchQuery = ref(props.search);
const loading = ref(false);
const successMessage = ref("");

function fetchRegisteredCards(page) {
    loading.value = true;
    router.get(
        `/registered-cards`,
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
    fetchRegisteredCards(1);
}

// Reactive variables
const studentID = ref("");
const nfcStatus = ref("");
const nfcError = ref("");
const isLoading = ref(false);
let socket = null;

// Modal-related reactive variables
const modalStudentInfo = ref(null); // Holds student info when loaded
const cardExists = ref(false);        // Tracks if a card already exists

// Reference to the DaisyModal component instance
const modalRef = ref(null);
const modalRef1 = ref(null);
const modalRef2 = ref(null);

// Form for NFC registration
const form = useForm({
    studentId: "",
    uid: "",
});

const registrationSuccess = ref(false);

onMounted(() => {
    socket = io("localhost:3000");

    socket.on("connect", () => {
        console.log("Connected to Socket.io server");
    });

    socket.on("nfcStatus", (message) => {
        nfcStatus.value = message;
    });

    socket.on("cardScanned", (data) => {
        isLoading.value = true;
        form.studentId = data.studentId;
        form.uid = data.uid;

        form.post(route("registered-cards.store"), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                registrationSuccess.value = true;
                // No errors, proceed with NFC writing.
                socket.emit("dbStored", data);
                nfcStatus.value = "✅ Database stored successfully!";
            },
            onError: (errors) => {
                registrationSuccess.value = false;
                nfcStatus.value =
                    "❌ Registration Failed: " + Object.values(errors).join(", ");
                socket.emit("registrationFailed", { error: errors });
            },
            onFinish: async () => {
                // Asynchronous delay of 1 second before finalizing
                await new Promise((resolve) => setTimeout(resolve, 2000));
                isLoading.value = false;
                if (registrationSuccess.value) {
                    modalRef2.value.closeModal();
                    // Reset state if needed
                    modalStudentInfo.value = null;
                    studentID.value = "";
                    registrationSuccess.value = false;
                } else {
                    // Registration failed; go back to the confirm student info modal
                    modalRef2.value.closeModal();
                    modalRef1.value.showModal();
                }
            },
        });
    });


    socket.on("studentRegistered", (student) => {
        nfcStatus.value = `✅ Student Registered Successfully and Card Written! Student ID: ${student.studentId} UID: ${student.uid}`;
    });

    socket.on("nfcError", (error) => {
        console.error("NFC Error:", error);
        nfcError.value = error;
    });

    socket.on("disconnect", () => {
        console.log("Disconnected from server");
    });
});

// Opens the modal and resets its state.
const openModal = () => {
    modalStudentInfo.value = null;
    studentID.value = "";
    nfcStatus.value = "";
    nfcError.value = "";
    modalRef.value.showModal();
};

// Called when the user enters a Student ID and clicks "Tap Your Card Now".
const registerStudent = async () => {
    if (!studentID.value) {
        alert("Please enter a Student ID first.");
        return;
    }

    try {
        const checkStudentResponse = await axios.get(route("student-infos.check"), {
            params: { studentId: studentID.value },
        });

        if (checkStudentResponse.data.error) {
            console.log("Student not found locally. Fetching from external API...");
            const fetchResponse = await axios.get(
                route("students.fetch", { studentId: studentID.value })
            );
            const studentData = fetchResponse.data.student;
            modalStudentInfo.value = studentData;

            await router.post(route("student-infos.store"), {
                studentId: studentData.studentId,
                fName: studentData.fname,
                lName: studentData.lname,
                program: studentData.program,
                department: studentData.department,
                yearLevel: studentData.yearLevel,
                semester: studentData.semester,
                image: studentData.image
            });

            console.log("Student info stored successfully.");
        } else {
            console.log("Student info found locally.");
            modalStudentInfo.value = checkStudentResponse.data.student;
        }

        const checkCardResponse = await axios.get(
            route("registered-cards.checkStudentID"),
            {
                params: { studentId: studentID.value },
            }
        );
        cardExists.value = checkCardResponse.data.exists;
        modalRef.value.closeModal();
        modalRef1.value.showModal();
    } catch (error) {
        console.error("Error verifying student info:", error);
        nfcStatus.value = "❌ Error verifying student info!";
    }
};

// Called when the user confirms registration after reviewing student info.
const confirmRegistration = () => {
    modalRef1.value.closeModal();
    modalRef2.value.showModal();
    socket.emit("registerStudent", studentID.value);
    nfcStatus.value = "⏳ Waiting for NFC tap...";
};

// Cancels the registration process.
const cancelRegistration = () => {
    modalRef.value.closeModal();
    modalRef1.value.closeModal();
    modalRef2.value.closeModal();
    nfcStatus.value = "Registration cancelled.";
};

onUnmounted(() => {
    if (socket) {
        socket.disconnect();
    }
});

const cardToDelete = ref(null);
const showConfirm = ref(false);

function confirmDelete(cardId) {
    cardToDelete.value = cardId;
    showConfirm.value = true;
}

function handleConfirmDelete() {
    router.delete(`/registered-cards/${cardToDelete.value}`, {
        onSuccess: () => {
            successMessage.value = "Card deleted successfully!";
            setTimeout(() => { successMessage.value = ""; }, 4000);
        },
        onError: () => {
            successMessage.value = "Error deleting card. Please try again!";
            setTimeout(() => { successMessage.value = ""; }, 4000);
        },
    });
    cardToDelete.value = null;
    showConfirm.value = false;
}

function handleCancelDelete() {
    cardToDelete.value = null;
    showConfirm.value = false;
}


</script>
