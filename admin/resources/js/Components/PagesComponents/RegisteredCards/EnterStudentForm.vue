<template>
    <div ref="container" tabindex="0" class="max-w-5xl mx-auto px-4 py-6" @keydown.enter="handleEnter">
        <form ref="form" @submit.prevent="handleRegister">
            <div>
                <input type="text" class="input input-bordered dark:text-white w-full mb-2" v-model="studentID"
                    placeholder="Student ID" required />
                <div v-if="nfcStatus" class="mt-2">
                    <span>{{ nfcStatus }}</span>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="mr-4 hover:underline" @click="handleCancel">Cancel</button>
                <button type="submit"
                    class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600"
                    :disabled="isLoading">
                    <span v-if="isLoading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Register</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed, ref, onMounted } from 'vue'

// Define props passed from the parent component
const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    nfcStatus: {
        type: String,
        default: ''
    }
})

// References for container and form elements
const container = ref(null)
const form = ref(null)

onMounted(() => {
    if (container.value) {
        container.value.focus()
    }
})

// Handle Enter key press globally within the form
function handleEnter(event) {
    if (event.target.tagName !== 'TEXTAREA') {
        return
    }
}

// Define events for v-model and component actions
const emit = defineEmits(['update:modelValue', 'cancel-registration', 'register-student'])

// Two-way binding for studentID
const studentID = computed({
    get: () => props.modelValue,
    set: (value) => {
        emit('update:modelValue', value)
    }
})

// Methods for cancel and register actions
function handleCancel() {
    emit('cancel-registration')
}

function handleRegister() {
    emit('register-student', studentID.value)
}
</script>
