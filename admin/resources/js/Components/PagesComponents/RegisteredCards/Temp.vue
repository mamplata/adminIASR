<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { io } from "socket.io-client";
import axios from "axios";
import DaisyModal from "@/Components/DaisyModal.vue";

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
    socket = io("http://localhost:3000");

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
                    modalRef.value.closeModal();
                    // Reset state if needed
                    modalStudentInfo.value = null;
                    studentID.value = "";
                    registrationSuccess.value = false;
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

// Props from the server (if any)
const props = defineProps({
    registeredCards: Object,
});
</script>

<template>
    <div class="p-6 text-gray-900">
        <div class="container dark:text-white">
            <!-- Button to open the modal -->
            <button class="btn btn-primary mb-4" @click="openModal">
                Register Student
            </button>

            <!-- DaisyModal Component Usage -->
            <DaisyModal title="Student Registration" ref="modalRef">
                <!-- Stage 1: Student ID Input -->
                <div>
                    <h3 class="font-bold text-lg">Enter Student ID</h3>
                    <div class="py-2">
                        <input type="text" class="input input-bordered dark:text-black w-full mb-2" v-model="studentID"
                            placeholder="Student ID" />
                    </div>
                    <div class="modal-action">
                        <button class="btn" @click="cancelRegistration">Cancel</button>
                        <button class="btn btn-primary" @click="registerStudent" :disabled="isLoading">
                            Tap Your Card Now
                        </button>
                    </div>
                </div>
            </DaisyModal>

            <DaisyModal title="Student Registration" ref="modalRef1">
                <!-- Stage 2: Student Info and Confirmation -->
                <div>
                    <h3 class="font-bold text-lg">Student Information</h3>
                    <div class="py-2">
                        <p>
                            <strong>Student ID:</strong>
                            {{ modalStudentInfo ? modalStudentInfo.studentId : studentID }}
                        </p>
                        <p v-if="modalStudentInfo">
                            <strong>Name:</strong> {{ modalStudentInfo.fName }} {{ modalStudentInfo.lName }}
                        </p>
                        <p v-if="modalStudentInfo">
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
            </DaisyModal>


            <DaisyModal title="Student Registration" ref="modalRef2">
                <!-- Stage 3: Scanning State -->
                <div>
                    <h3 class="font-bold text-lg">Waiting for NFC Tap</h3>
                    <div class="py-2 flex flex-col items-center">
                        <div class="loading-spinner"></div>
                        <p class="mt-2">{{ nfcStatus }}</p>

                    </div>
                    <div class="modal-action">
                        <button class="btn" @click="cancelRegistration">Cancel</button>
                    </div>
                </div>
            </DaisyModal>
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
