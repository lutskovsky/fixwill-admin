<!-- resources/js/components/ClientDisplayEdit.vue -->
<template>
    <div class="client-display-edit">
        <h2>Client Information</h2>
        <div v-if="client">
            <form @submit.prevent="updateClient">
                <div>
                    <label for="name">Name:</label>
                    <input id="name" v-model="client.name" required type="text"/>
                </div>
                <div>
                    <label for="phone">Phone Number:</label>
                    <input id="phone" v-model="client.phone" required type="text"/>
                </div>
                <!-- Add more fields as necessary -->
                <button type="submit">Update Client</button>
            </form>
        </div>
        <div v-else>
            <p>No client selected.</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ClientDisplayEdit',
    props: {
        clientId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            client: null,
        };
    },
    methods: {
        fetchClient() {
            axios
                .get(`/api/clients/${this.clientId}`)
                .then((response) => {
                    this.client = response.data;
                })
                .catch((error) => {
                    console.error('Error fetching client:', error);
                });
        },
        updateClient() {
            axios
                .put(`/api/clients/${this.clientId}`, this.client)
                .then((response) => {
                    alert('Client updated successfully!');
                })
                .catch((error) => {
                    console.error('Error updating client:', error);
                });
        },
    },
    mounted() {
        this.fetchClient();
    },
    watch: {
        clientId(newId, oldId) {
            if (newId !== oldId) {
                this.fetchClient();
            }
        },
    },
};
</script>

<style scoped>
.client-display-edit {
    border: 1px solid #ccc;
    padding: 20px;
    margin-bottom: 20px;
}

.client-display-edit form div {
    margin-bottom: 10px;
}
</style>
