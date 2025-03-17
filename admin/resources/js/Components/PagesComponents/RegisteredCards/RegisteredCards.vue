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

        <!-- 1. NFC Scanning Modal (shown first) -->
        <DaisyModal title="Waiting for NFC Tap" ref="modalRef">
            <NfcScanningState :nfcStatus="nfcStatus" @cancel-registration="cancelRegistration" />
        </DaisyModal>

        <!-- 2. Student ID Input Modal -->
        <DaisyModal title="Enter Student ID" ref="modalRef1">
            <EnterStudentForm v-model="studentID" :isLoading="isLoading" @cancel-registration="cancelRegistration"
                :nfcStatus="nfcStatus" @register-student="registerStudent" />
        </DaisyModal>

        <!-- 3. Confirmation Modal -->
        <DaisyModal title="Student Information" ref="modalRef2">
            <ConfirmStudentInfo :studentID="studentID" :modalStudentInfo="modalStudentInfo" :cardExists="cardExists"
                :nfcStatus="nfcStatus" @cancel-registration="cancelRegistration"
                @confirm-registration="confirmRegistration" :isEnrolled="isEnrolled" :renew="renew" />
        </DaisyModal>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import { io } from "socket.io-client";
import axios from "axios";
import { useToast } from "vue-toastification";

import SearchBar from "./SearchBar.vue";
import DaisyTable from "@/Components/Daisy/DaisyTable.vue";
import DaisyModal from "@/Components/Daisy/DaisyModal.vue";
import EnterStudentForm from "./EnterStudentForm.vue";
import ConfirmStudentInfo from "./ConfirmStudentInfo.vue";
import NfcScanningState from "./NfcScanningState.vue";
import DaisyConfirm from "@/Components/Daisy/DaisyConfirm.vue";

// Define props passed from the server
const props = defineProps({
    registeredCards: Object,
    search: {
        type: String,
        default: "",
    },
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
            },
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

// New reactive variable to hold the scanned card UID
const scannedCardUID = ref("");

// Modal-related reactive variables
const modalStudentInfo = ref(null); // Holds student info when loaded
const cardExists = ref(false); // Tracks if a card already exists
const isEnrolled = ref(true); // Tracks enrollment status
const renew = ref(false); // Tracks card if renew or replacement

// References to DaisyModal component instances
const modalRef = ref(null); // NFC scanning modal
const modalRef1 = ref(null); // Student ID modal
const modalRef2 = ref(null); // Confirmation modal

// useForm for saving card registration
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

    // When a card is scanned, store its UID, then check if it already exists.
    socket.on("cardScanned", async (data) => {
        scannedCardUID.value = data.uid;
        nfcStatus.value = null;

        try {
            // Step 1: Check if the card is registered and enrolled
            const checkResponse = await axios.get(route("registered-cards.checkCard"), {
                params: { uid: scannedCardUID.value },
            });

            modalRef.value.closeModal();

            if (checkResponse.data.exists) {
                studentID.value = checkResponse.data.studentId;
                modalStudentInfo.value = checkResponse.data.studentInfo;

                renew.value = true;
                cardExists.value = false; // Skip "Card already exists" status.

                // Step 2: Extract semester and year safely
                if (modalStudentInfo.value?.last_enrolled_at) {
                    const match = modalStudentInfo.value.last_enrolled_at.match(/(1st|2nd)\s(\d{4})/);
                    if (match) {
                        const semesterNumber = match[1] === "1st" ? "1" : "2";
                        const year = match[2].slice(2); // Extract last two digits of the year
                        semester.value = semesterNumber + year;
                    } else {
                        semester.value = "N/A"; // Fallback if format is incorrect
                    }
                } else {
                    semester.value = "N/A"; // Fallback for missing data
                }

                // Step 3: Use updated enrollment status
                isEnrolled.value = checkResponse.data.isEnrolled;
                nfcStatus.value = checkResponse.data.message;
                modalRef2.value.showModal();
            } else {
                cardExists.value = false;
                modalRef1.value.showModal(); // Card not found modal
            }
        } catch (error) {
            console.error("Error checking card existence:", error);
            modalRef1.value.showModal(); // Handle API errors
        }
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
// Clears scannedCardUID, then shows the NFC scanning modal and emits "startScan".
const openModal = () => {
    modalStudentInfo.value = null;
    studentID.value = "";
    semester.value = "";
    nfcStatus.value = "";
    nfcError.value = "";
    scannedCardUID.value = "";
    cardExists.value = false;
    isEnrolled.value = true;
    modalRef.value.showModal();
    socket.emit("startScan");
};

// Once student data is fetched/verified, process it and move to confirmation.
// Extracts the semester from studentData, checks for an existing card (if needed),
// then transitions to the Confirmation modal.
const processStudentData = async (studentData) => {
    const semesterNumber = studentData.last_enrolled_at.match(/\d+/)[0];
    const year = studentData.last_enrolled_at.match(/\d{4}/)[0].slice(2);
    semester.value = semesterNumber + year;

    console.log(semester.value);

    // For cases where card is not pre-registered,
    // you may optionally re-check using studentID if needed.
    try {
        const checkCardResponse = await axios.get(route("registered-cards.checkStudentID"), {
            params: { studentId: studentID.value },
        });

        if (checkCardResponse.data.exists) {
            // No card registered yet.
            renew.value = false;
            cardExists.value = true;
        }
    } catch (error) {
        console.error("Error checking card existence:", error);
    }


    modalRef1.value.closeModal();
    modalRef2.value.showModal();
    nfcStatus.value = "";
    nfcError.value = "";
};

// Called when the student clicks "Register" after entering their Student ID manually.
const registerStudent = async () => {
    if (!studentID.value) {
        alert("Please enter a Student ID first.");
        return;
    }

    try {
        // Step 1: Check student data (handles local & external fetch automatically)
        const checkStudentResponse = await axios.get(route("student-infos.check"), {
            params: { studentId: studentID.value },
        });

        if (checkStudentResponse.data.error) {
            nfcStatus.value = "❌ " + checkStudentResponse.data.error;
            return;
        }

        // Step 2: Use the fetched student data
        modalStudentInfo.value = checkStudentResponse.data.student;

        // If the data came from external (i.e. it's new or outdated locally), then store it.
        if (checkStudentResponse.data.from_external) {
            // Prepare and submit form to store updated student info.
            const studentForm = useForm({
                studentId: modalStudentInfo.value.studentId,
                fName: modalStudentInfo.value.fName,
                lName: modalStudentInfo.value.lName,
                program: modalStudentInfo.value.program,
                department: modalStudentInfo.value.department,
                yearLevel: modalStudentInfo.value.yearLevel,
                image: modalStudentInfo.value.image,
                last_enrolled_at: modalStudentInfo.value.last_enrolled_at,
            });

            await studentForm.post(route("student-infos.store"), {
                onSuccess: async () => {
                    await processStudentData(modalStudentInfo.value);
                },
                onError: (errors) => {
                    nfcStatus.value =
                        "❌ " +
                        (errors.error || "An error occurred while saving student data.");
                },
            });
        } else {
            // Data is already stored and up-to-date; simply process it.
            await processStudentData(modalStudentInfo.value);
        }
    } catch (error) {
        console.error("An unexpected error occurred:", error);
        nfcStatus.value =
            "❌ " +
            (error.response?.data?.error || "An unexpected error occurred.");
    }
};



// Called when the user confirms registration in the Confirmation modal.
// Emits "confirmRegistration" with studentID, semester, and scanned UID,
// and then saves the card info using useForm.
const confirmRegistration = async () => {
    modalRef2.value.closeModal();

    let studentInfo = {
        studentID: studentID.value,
        semester: semester.value,
        uid: scannedCardUID.value,
    };

    // Emit event to NFC server.
    socket.emit("confirmRegistration", studentInfo);
    nfcStatus.value = "⏳ Writing to NFC card...";

    // Use useForm to save card info to the database.
    form.studentId = studentID.value;
    form.uid = scannedCardUID.value;

    form.post(route("registered-cards.store"), {
        onSuccess: () => {
            toast.success("Card saved to database successfully!");
        },
        onError: (errors) => {
            toast.error("Error saving card to database.");
            console.error("Database save error:", errors);
        },
    });
};

const cancelRegistration = () => {
    modalRef.value.closeModal();
    modalRef1.value.closeModal();
    modalRef2.value.closeModal();
    socket.emit("cancelScan"); // Tell the server to stop scanning
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
