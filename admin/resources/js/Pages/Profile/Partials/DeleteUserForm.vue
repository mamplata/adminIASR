<script setup>
import { ref, computed, nextTick } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import Label from '@/Components/Label.vue';
import Input from '@/Components/Input.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';

const page = usePage();
const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

// Reactive error handling
const deleteError = ref(null);

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value?.focus(), 50);
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            deleteError.value = page.props.errors.error;
        },
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Delete Account
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <Button variant="danger" @click="confirmUserDeletion">
            Delete Account
        </Button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Are you sure you want to delete your account?
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm you would like to permanently delete your account.
                </p>

                <div class="mt-6">
                    <Label for="password" value="Password" class="sr-only" />

                    <Input id="password" ref="passwordInput" v-model="form.password" type="password"
                        class="mt-1 block w-3/4" placeholder="Password" @keyup.enter="deleteUser" />

                    <InputError :message="form.errors.password" class="mt-2" />
                    <!-- Display the error message if it exists -->
                    <div v-if="deleteError" class="mt-4 text-red-600 dark:text-red-500">
                        {{ deleteError }}
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <Button variant="secondary" @click="closeModal">
                        Cancel
                    </Button>

                    <Button variant="danger" class="ml-3" :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing" @click="deleteUser">
                        Delete Account
                    </Button>
                </div>
            </div>
        </Modal>
    </section>
</template>
