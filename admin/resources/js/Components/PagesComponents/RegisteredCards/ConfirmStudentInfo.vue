<template>
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
            <button class="btn" @click="handleCancel">Cancel</button>
            <button class="btn btn-primary" @click="handleConfirm">
                Continue
            </button>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

// Define props to receive studentID, modalStudentInfo, and cardExists from parent
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
