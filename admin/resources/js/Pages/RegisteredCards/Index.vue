<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import RegisteredCards from '@/Components/PagesComponents/RegisteredCards/RegisteredCards.vue';
</script>

<template>
    <div>
        <AuthenticatedLayout title="Registered Cards">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Registered Cards
                    </h2>
                </div>
            </template>

            <!-- Passing the users prop to the UserManagement component -->
            <RegisteredCards :registeredCards="registeredCards" />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: { registeredCards: Object },
};
</script>
