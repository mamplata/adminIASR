<template>
    <div>
        <div class="py-2">
            <input type="text" class="input input-bordered dark:text-black w-full mb-2" v-model="studentID"
                placeholder="Student ID" />
        </div>
        <div class="modal-action">
            <button class="mr-4 hover:underline" @click="handleCancel">Cancel</button>
            <button class="btn btn-primary dark:text-white" @click="handleRegister" :disabled="isLoading">
                Tap Your Card Now
            </button>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue'

// Define any props you might want to pass from the parent
const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})


// Define events including update:modelValue for v-model support
const emit = defineEmits(['update:modelValue', 'cancel-registration', 'register-student'])

// Use a computed property for two-way binding on studentID
const studentID = computed({
    get: () => props.modelValue,
    set: (value) => {
        emit('update:modelValue', value)
    }
})

// Methods to handle button clicks and emit events
function handleCancel() {
    emit('cancel-registration')
}

function handleRegister() {
    emit('register-student', studentID.value)
}
</script>
