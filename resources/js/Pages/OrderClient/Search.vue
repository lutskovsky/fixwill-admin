<!-- resources/js/Pages/Clients/Search.vue -->

<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Поиск по клиентам</h1>

        <!-- Search Form -->
        <form class="mb-6" @submit.prevent="submit">
            <div class="flex space-x-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700" for="name">Имя</label>
                    <input
                        id="name"
                        v-model="form.name"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        placeholder="Имя"
                        type="text"
                    />
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700" for="phone">Телефон</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        placeholder="Телефон"
                        type="text"
                    />
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        placeholder="Email"
                        type="email"
                    />
                </div>
            </div>

            <div class="mt-4">
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    type="submit"
                >
                    Искать
                </button>
            </div>
        </form>

        <!-- Search Results -->
        <div v-if="clients.length">
            <table class="min-w-full bg-white border">
                <thead>
                <tr>
                    <th class="py-2 px-4 border">Имя</th>
                    <th class="py-2 px-4 border">Телефоны</th>
                    <th class="py-2 px-4 border">Email</th>
                    <th class="py-2 px-4 border">Адрес</th>
                    <th class="py-2 px-4 border"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="client in clients" :key="client.id" class="hover:bg-gray-100">
                    <td class="py-2 px-4 border">
                        <Link :href="route('client.show', { clientId: client.id })"
                              class="text-blue-500 hover:text-blue-700">
                            {{ client.name }}
                        </Link>
                    </td>
                    <td class="py-2 px-4 border">
                        {{ client.phone.map(phone => phone.substring(0, phone.length - 4) + '****').join(", ") }}
                    </td>
                    <td class="py-2 px-4 border">{{ client.email }}</td>
                    <td class="py-2 px-4 border">{{ client.address }}</td>
                    <td class="py-2 px-4 border">
                        <Link :href="route('client.order.create', { clientId: client.id })" class="text-blue-500 hover:text-blue-700"
                              method="post">
                            Создать новый заказ
                        </Link>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-else-if="submitted">
            <p class="text-gray-500">Ничего не найдено.</p>
        </div>
    </div>
</template>

<script setup>
import {computed, inject} from 'vue';
import {Link, useForm} from '@inertiajs/vue3';

const route = inject('route');

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});


const form = useForm({
    name: props.filters.name || '',
    phone: props.filters.phone || '',
    email: props.filters.email || '',
});

const submitted = computed(() => {
    return form.name || form.phone || form.email;
});

const submit = () => {
    form.post(route('client.search'), {
        preserveScroll: true,
    });
};
</script>

<style scoped>
/* Optional: Add some basic styling */
</style>
