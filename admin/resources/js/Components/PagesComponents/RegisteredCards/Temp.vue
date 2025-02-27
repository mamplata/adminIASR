<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { io } from "socket.io-client";
import axios from "axios";

const studentID = ref("");
const nfcStatus = ref("");
const nfcError = ref("");
const isLoading = ref(false);
let socket = null;

// Modal-related reactive variables
const showModal = ref(false);
const modalStudentInfo = ref(null); // to store fetched or local student info
const cardExists = ref(false);        // to track if a card already exists

// Form for NFC registration (remains the same)
const form = useForm({
    studentId: '',
    uid: '',
});

onMounted(() => {
    socket = io("http://localhost:3000");

    socket.on("connect", () => {
        console.log("Connected to Socket.io server");
    });

    socket.on("nfcStatus", (message) => {
        nfcStatus.value = message;
    });

    socket.on("cardScanned", async (data) => {
        isLoading.value = true;
        form.studentId = data.studentId;
        form.uid = data.uid;

        form.post(route('registered-cards.store'), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                // No errors, proceed with NFC writing.
                socket.emit("dbStored", data);
                nfcStatus.value = "✅ Database stored successfully!";
            },
            onError: (errors) => {
                // Validation errors, e.g., UID already taken.
                nfcStatus.value = "❌ Registration Failed: " + Object.values(errors).join(", ");
                socket.emit("registrationFailed", { error: errors });
            },
            onFinish: () => {
                setTimeout(() => {
                    isLoading.value = false;
                }, 1000);
            },
        });
    });

    // Listen for the final success event after the card is written.
    socket.on("studentRegistered", (student) => {
        nfcStatus.value = "✅ Student Registered Successfully and Card Written!";
    });

    socket.on("nfcError", (error) => {
        console.error("NFC Error:", error);
        nfcError.value = error;
    });

    socket.on("disconnect", () => {
        console.log("Disconnected from server");
    });
});

// Opens the modal in its initial (student input) state.
const openStudentModal = () => {
    // Reset modal state so it shows the student ID input first.
    modalStudentInfo.value = null;
    studentID.value = "";
    showModal.value = true;
};

// Called when the user enters a Student ID and clicks "Tap Your Card Now".
const registerStudent = async () => {
    if (!studentID.value) {
        alert("Please enter a Student ID first.");
        return;
    }

    try {
        // Check for local student info.
        const checkStudentResponse = await axios.get(route('student-infos.check'), {
            params: { studentId: studentID.value }
        });

        if (checkStudentResponse.data.error) {
            console.log("Student not found locally. Fetching from external API...");
            // Fetch from external API.
            const fetchResponse = await axios.get(route('students.fetch', { studentId: studentID.value }));
            const studentData = fetchResponse.data.student;
            modalStudentInfo.value = studentData;

            // Store the fetched student info.
            await router.post(route('student-infos.store'), {
                studentId: studentData.studentId,
                fName: studentData.fname,
                lName: studentData.lname,
                program: studentData.program,
                department: studentData.department,
                yearLevel: studentData.yearLevel
            });

            console.log("Student info stored successfully.");
        } else {
            console.log("Student info found locally.");
            modalStudentInfo.value = checkStudentResponse.data.student;
        }

        // Check if a registered card already exists for this student.
        const checkCardResponse = await axios.get(route('registered-cards.checkStudentID'), {
            params: { studentId: studentID.value }
        });
        cardExists.value = checkCardResponse.data.exists;
        // Once student info is loaded, the modal content updates.
    } catch (error) {
        console.error("Error verifying student info:", error);
        nfcStatus.value = "❌ Error verifying student info!";
    }
};

// Called when the user confirms registration after reviewing student info.
const confirmRegistration = () => {
    showModal.value = false;
    socket.emit("registerStudent", studentID.value);
    nfcStatus.value = "⏳ Waiting for NFC tap...";
};

// Cancels the registration process.
const cancelRegistration = () => {
    showModal.value = false;
    nfcStatus.value = "Registration cancelled.";
};

onUnmounted(() => {
    if (socket) {
        socket.disconnect();
    }
});

// Props from the server
const props = defineProps({
    registeredCards: Object,
});
</script>

<template>
    <div class="p-6 text-gray-900">
        <div class="container dark:text-white">
            <!-- Button to open the modal -->
            <button class="btn btn-primary mb-4" @click="openStudentModal">
                Register Student
            </button>

            <!-- Modal for Student Registration -->
            <input type="checkbox" id="modal" class="modal-toggle" v-model="showModal" />
            <div class="modal dark:text-black" v-if="showModal">
                <div class="modal-box">
                    <!-- When no student info is loaded, show the Student ID input -->
                    <div v-if="!modalStudentInfo">
                        <h3 class="font-bold text-lg">Enter Student ID</h3>
                        <div class="py-2">
                            <input type="text" class="input input-bordered dark:text-black w-full mb-2"
                                v-model="studentID" placeholder="Student ID" />
                        </div>
                        <div class="modal-action">
                            <button class="btn" @click="cancelRegistration">Cancel</button>
                            <button class="btn btn-primary" @click="registerStudent" :disabled="isLoading">
                                Tap Your Card Now
                            </button>
                        </div>
                    </div>
                    <!-- Once student info is loaded, show the details and confirmation options -->
                    <div v-else>
                        <h3 class="font-bold text-lg">Student Information</h3>
                        <div class="py-2">
                            <p>
                                <strong>Student ID:</strong> {{ modalStudentInfo.studentId || studentID }}
                            </p>
                            <p>
                                <strong>Name:</strong> {{ modalStudentInfo.fName }} {{ modalStudentInfo.lName }}
                            </p>
                            <p>
                                <strong>Program:</strong> {{ modalStudentInfo.program }}
                            </p>
                            <p>
                                <strong>Status:</strong>
                                <span v-if="cardExists" class="text-warning"> Card already exists.</span>
                                <span v-else class="text-success"> New registration.</span>
                            </p>
                        </div>
                        <div class="modal-action">
                            <button class="btn" @click="cancelRegistration">Cancel</button>
                            <button class="btn btn-primary" @click="confirmRegistration">
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Animation -->
            <div v-if="isLoading" class="loading-container my-4">
                <div class="loading-spinner"></div>
                <p>Processing registration...</p>
            </div>

            <!-- Status Messages -->
            <p v-if="nfcStatus" class="my-2">{{ nfcStatus }}</p>
            <p v-if="nfcError" class="my-2 text-error">{{ nfcError }}</p>

            <!-- Registered Students List -->
            <h2 class="mt-6 text-xl">Registered Students</h2>
            <ul class="list-disc ml-6">
                <li v-for="card in registeredCards.data" :key="card.id">
                    <strong>{{ card.studentId }}</strong> - NFC UID: {{ card.uid }}
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.loading-container {
    text-align: center;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 5px solid #ccc;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
