<template>
    <div>
        <button @click="fetchUsers">Fetch Users</button>

        <ul v-if="users.length">
            <li v-for="user in users" :key="user.id">
                {{ user.name }} - {{ user.email }}
            </li>
        </ul>
        <div v-else>No users fetched yet.</div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import HTTP from './http';

const users = ref([]);

async function fetchUsers() {
    try {
        const response = await HTTP.get('/api/users');
        users.value = response.data;
        console.log(users.value);
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}
</script>