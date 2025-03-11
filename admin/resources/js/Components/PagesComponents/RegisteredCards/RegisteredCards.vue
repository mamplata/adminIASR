<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- SearchBar Component -->
        <SearchBar v-model="searchQuery" :loading="loading" @search="fetchRegisteredCards(1)" @reset="resetSearch()"
            @add-card="openModal" />

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
                :nfcStatus="nfcStatus" @register-student="registerStudent" />
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
import { useForm, router, usePage } from '@inertiajs/vue3';
import { io } from "socket.io-client";
import axios from "axios";
import { useToast } from 'vue-toastification';

import SearchBar from './SearchBar.vue';
import DaisyTable from '@/Components/Daisy/DaisyTable.vue';
import DaisyModal from '@/Components/Daisy/DaisyModal.vue';
import EnterStudentForm from "./EnterStudentForm.vue";
import ConfirmStudentInfo from "./ConfirmStudentInfo.vue";
import NfcScanningState from "./NfcScanningState.vue";
import DaisyConfirm from "@/Components/Daisy/DaisyConfirm.vue";

// Define props passed from the server
const props = defineProps({
    registeredCards: Object,
    search: {
        type: String,
        default: ""
    }
});

const { props: page } = usePage();
const toast = useToast();

// Local state
const searchQuery = ref(page.search || "");
const loading = ref(false);

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
}

function resetSearch() {
    searchQuery.value = "";
    fetchRegisteredCards(1);
}

// Reactive variables
const studentID = ref("");
const semester = ref("");
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
const isModal2Open = ref(false);

onMounted(() => {
    socket = io(import.meta.env.VITE_SOCKET_URL);

    socket.on("connect", () => {
        console.log("Connected to Socket.io server");
    });

    socket.on("nfcStatus", (message) => {
        nfcStatus.value = message;
    });

    socket.on("registrationFailed", async (data) => {
        if (isModal2Open.value) {
            nfcStatus.value = "❌ " + data.message;
            await new Promise((resolve) => setTimeout(resolve, 2000));
            modalRef2.value.closeModal();
            modalRef1.value.showModal();
            isModal2Open.value = false;
            registrationSuccess.value = false;
        }
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
                toast.success("Card registered successfully!");
            },
            onError: (errors) => {
                registrationSuccess.value = false;
                nfcStatus.value =
                    "❌ Registration Failed: " + Object.values(errors).join(", ");
            },
            onFinish: async () => {
                // Asynchronous delay of 1 second before finalizing
                await new Promise((resolve) => setTimeout(resolve, 2000));
                isLoading.value = false;
                isModal2Open.value = false;
                if (registrationSuccess.value) {
                    modalRef2.value.closeModal();
                    // Reset state if needed
                    modalStudentInfo.value = null;
                    studentID.value = "";
                    registrationSuccess.value = false;
                    isModal2Open.value = true;
                } else {
                    // Registration failed; go back to the confirm student info modal
                    modalRef2.value.closeModal();
                    modalRef1.value.showModal();
                }
            },
        });
    });


    socket.on("studentRegistered", (student) => {
        nfcStatus.value = `Student Registered Successfully and Card Written! Student ID: ${student.studentId} UID: ${student.uid}`;
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
    semester.value = "";
    nfcStatus.value = "";
    nfcError.value = "";
    modalRef.value.showModal();
};

const processStudentData = async (studentData) => {
    // Extract the first digit (2 from "2nd")
    const semesterNumber = studentData.last_enrolled_at.match(/\d+/)[0];
    // Extract the last two digits of the year (24 from "2024")
    const year = studentData.last_enrolled_at.match(/\d{4}/)[0].slice(2);
    semester.value = semesterNumber + year;

    console.log(semester.value);

    // Proceed to check if the card exists **after** the student form is successfully stored
    try {
        const checkCardResponse = await axios.get(route("registered-cards.checkStudentID"), {
            params: { studentId: studentID.value },
        });
        cardExists.value = checkCardResponse.data.exists;
    } catch (cardError) {
        console.error("Error checking card existence:", cardError);
    }

    // Proceed with closing and opening modals
    modalRef.value.closeModal();
    modalRef1.value.showModal();
    nfcStatus.value = "";
    nfcError.value = "";
};


const registerStudent = async () => {
    if (!studentID.value) {
        alert("Please enter a Student ID first.");
        return;
    }

    try {
        // Check if the student exists locally
        const checkStudentResponse = await axios.get(route("student-infos.check"), {
            params: { studentId: studentID.value },
        });

        // If student not found locally, fetch from external API
        if (checkStudentResponse.data.error) {

            const fetchResponse = await axios.get(route("students.fetch", { studentId: studentID.value }));
            const studentData = fetchResponse.data.student;

            modalStudentInfo.value = studentData;

            // Prepare the form data
            const studentForm = useForm({
                studentId: studentData.studentId,
                fName: studentData.fName,
                lName: studentData.lName,
                program: studentData.program,
                department: studentData.department,
                yearLevel: studentData.yearLevel,
                image: studentData.image,
                last_enrolled_at: studentData.last_enrolled_at
            });

            // Submit the form to store student info
            await studentForm.post(route("student-infos.store"), {
                onSuccess: async () => {
                    // Call the new function to handle processing
                    await processStudentData(studentData);
                },
                onError: (errors) => {
                    nfcStatus.value = "❌ " + (errors.error || "An error occurred while saving student data.");
                }
            });

        } else {
            modalStudentInfo.value = checkStudentResponse.data.student;

            // Call the new function to handle processing for locally found student
            await processStudentData(modalStudentInfo.value);

        }

    } catch (error) {
        // General error handling
        console.error("An unexpected error occurred:", error);
        nfcStatus.value = "❌ " + (error.response?.data?.error || "An unexpected error occurred.");
    }
};


// Called when the user confirms registration after reviewing student info.
const confirmRegistration = () => {
    modalRef1.value.closeModal();
    modalRef2.value.showModal();
    isModal2Open.value = true;
    nfcStatus.value = "";
    nfcError.value = "";
    let studentInfo = { studentID: studentID.value, semester: semester.value };
    socket.emit("registerStudent", studentInfo);
    nfcStatus.value = "⏳ Waiting for NFC tap...";
};

// Cancels the registration process.
const cancelRegistration = () => {
    modalRef.value.closeModal();
    modalRef1.value.closeModal();
    modalRef2.value.closeModal();
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
            toast.success("Card deleted successfully!");
        },
        onError: () => {
            toast.error("Error deleting card. Please try again!");
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
