<template>
    <div class="p-6 max-w-lg mx-auto rounded-lg uppercase">
        <!-- Primary Information Section -->
        <section class="flex flex-col items-center justify-around gap-4">
            <!-- Student Image -->
            <div v-if="modalStudentInfo && modalStudentInfo.image" class="image flex-shrink-0">
                <img :src="modalStudentInfo.image" alt="Student Image"
                    class="w-40 h-40 rounded-full object-cover shadow-lg" />
            </div>

            <!-- Essential Details -->
            <div class="flex flex-col text-center">
                <div v-if="modalStudentInfo">
                    <span>{{ modalStudentInfo.fName }} {{ modalStudentInfo.lName }}</span>
                </div>
                <div>
                    <span class="text-gray-500 text-sm">
                        {{ modalStudentInfo ? modalStudentInfo.studentId : studentID }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Additional Information Section -->
        <section class="mt-4 text-justify gap-4 space-y-3">
            <div v-if="modalStudentInfo">
                <strong>Program: </strong>
                <span>{{ modalStudentInfo.program }}</span>
            </div>
            <div v-if="modalStudentInfo">
                <strong>Department: </strong>
                <span>{{ modalStudentInfo.department }}</span>
            </div>
            <div v-if="modalStudentInfo">
                <strong>Year Level: </strong>
                <span>{{ modalStudentInfo.yearLevel }}</span>
            </div>
            <div>
                <strong>Status: </strong>
                <span v-if="cardExists" class="text-warning">Card already exists.</span>
                <span v-else class="text-success">New registration.</span>
            </div>
            <div v-if="nfcStatus" class="mt-2 normal-case">
                <span>{{ nfcStatus }}</span>
            </div>
        </section>

        <!-- Action Buttons -->
        <footer class="mt-6 flex justify-end space-x-2">
            <button class="mr-4 hover:underline" @click="handleCancel">Cancel</button>
            <button class="btn btn-primary" @click="handleConfirm">Continue</button>
        </footer>
    </div>

</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

// Define props to receive studentID, modalStudentInfo, cardExists, and nfcStatus from parent
const props = defineProps({
    studentID: {
        type: String,
        default: ''
    },
    modalStudentInfo: {
        type: Object,
        default: null
    },
    cardExists: {
        type: Boolean,
        default: false
    },
    nfcStatus: {
        type: String,
        default: ''
    }
})

// Define emitted events for cancellation and confirmation actions
const emit = defineEmits(['cancel-registration', 'confirm-registration'])

// Emit cancel and confirm events
function handleCancel() {
    emit('cancel-registration')
}

function handleConfirm() {
    emit('confirm-registration')
}
</script>

<style scoped></style>
