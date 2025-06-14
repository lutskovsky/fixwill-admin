<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            <!-- Header Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-800">{{ title }}</h1>
                    <Link v-if="clientId"
                          :href="route('client.order.create', { clientId: clientId })"
                          as="button"
                          class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center gap-2"
                          method="post">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"></path>
                        </svg>
                        Создать новый заказ
                    </Link>
                </div>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash.message"
                 class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 mb-6 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path clip-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          fill-rule="evenodd"></path>
                </svg>
                {{ $page.props.flash.message }}
            </div>

            <!-- Main Form -->
            <form @submit.prevent="submit">
                <input v-model="form.clientId" type="hidden"/>

                <!-- Client Information Card -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"></path>
                        </svg>
                        Информация о клиенте
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                            <input
                                v-model="form.name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Введите имя клиента"
                                type="text"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input
                                v-model="form.email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="email@example.com"
                                type="email"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Адрес</label>
                            <input
                                v-model="form.address"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="Введите адрес"
                                type="text"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Примечание</label>
                            <textarea
                                v-model="form.notes"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none"
                                placeholder="Дополнительная информация о клиенте"
                                rows="3"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    v-model="form.legalEntity"
                                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                    type="checkbox"
                                />
                                <span class="text-sm font-medium text-gray-700">Юридическое лицо</span>
                            </label>

                            <!-- Legal Entity Fields -->
                            <div v-if="form.legalEntity && legalFields.length > 0"
                                 class="mt-4 ml-8 p-4 bg-gray-50 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="field in legalFields" :key="field.id">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{
                                                field.name
                                            }}</label>
                                        <div class="text-gray-900">
                                            <span v-if="field.type !== 0">{{ field.value || '-' }}</span>
                                            <span v-else class="flex items-center gap-2">
                                                <svg v-if="field.value" class="w-5 h-5 text-green-500"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path clip-rule="evenodd"
                                                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                          fill-rule="evenodd"></path>
                                                </svg>
                                                <svg v-else class="w-5 h-5 text-red-500" fill="currentColor"
                                                     viewBox="0 0 20 20">
                                                    <path clip-rule="evenodd"
                                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                          fill-rule="evenodd"></path>
                                                </svg>
                                                {{ field.value ? 'Да' : 'Нет' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Regular Custom Fields -->
                    <div v-if="clientId && regularFields.length > 0" class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="field in regularFields" :key="field.id">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ field.name }}</label>
                                <div class="text-gray-900">
                                    <span v-if="field.type !== 0">{{ field.value || '-' }}</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg v-if="field.value" class="w-5 h-5 text-green-500" fill="currentColor"
                                             viewBox="0 0 20 20">
                                            <path clip-rule="evenodd"
                                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                  fill-rule="evenodd"></path>
                                        </svg>
                                        <svg v-else class="w-5 h-5 text-red-500" fill="currentColor"
                                             viewBox="0 0 20 20">
                                            <path clip-rule="evenodd"
                                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                  fill-rule="evenodd"></path>
                                        </svg>
                                        {{ field.value ? 'Да' : 'Нет' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phone Numbers Card -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"></path>
                        </svg>
                        Телефоны
                    </h2>

                    <!-- Virtual Numbers Selection -->
                    <div v-if="clientId && form.virtualNumbers.length > 0" class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-2">Выберите АОН для звонков:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <label v-for="number in form.virtualNumbers" :key="number.number"
                                   :class="{ 'bg-blue-100': selectedVirtualNumber === number.number }"
                                   class="flex items-center p-2 rounded cursor-pointer hover:bg-blue-100 transition-colors">
                                <input
                                    v-model="selectedVirtualNumber"
                                    :value="number.number"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-2 focus:ring-blue-500"
                                    name="numberGroup"
                                    type="radio"
                                />
                                <div class="ml-2">
                                    <div class="text-sm font-medium text-gray-900">{{ number.number }}</div>
                                    <div class="text-xs text-gray-600">{{ number.description }}</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div v-for="phone in form.phones" :key="phone.encrypted"
                             class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <input
                                :id="phone.encrypted"
                                v-model="phone.text"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                placeholder="7__________"
                                type="text"
                            />
                            <div class="flex items-center gap-2">
                                <button
                                    class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                    title="WhatsApp чат"
                                    type="button"
                                    @click="openWhatsAppChat(phone.text, phone.encrypted)"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </button>
                                <button
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Позвонить"
                                    type="button"
                                    @click.prevent="call(phone.text, phone.encrypted)"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"></path>
                                    </svg>
                                </button>
                                <button
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Удалить"
                                    type="button"
                                    @click.prevent="removePhone(phone)"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button
                            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center gap-2"
                            type="button"
                            @click.prevent="addPhone"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"></path>
                            </svg>
                            Добавить телефон
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button
                        class="px-8 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 flex items-center gap-2"
                        type="submit"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2"></path>
                        </svg>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>

        <!-- WhatsApp Chat Modal -->
        <WhatsAppChatModal
            :encrypted-phone="selectedEncryptedPhone"
            :is-open="whatsAppModalOpen"
            :phone="selectedPhone"
            @close="whatsAppModalOpen = false"
        />
    </div>
</template>

<script>
import {Link, useForm} from '@inertiajs/vue3';
import WhatsAppChatModal from '@/Components/WhatsAppChatModal.vue';
import axios from 'axios';

let id = 0;

export default {
    components: {Link, WhatsAppChatModal},
    props: {
        orderLabel: String,
        clientData: Object,
        clientId: Number,
        phones: Object,
        clientCustomFields: Object,
        customFieldsSettings: Object,
        virtualNumbers: Object,
    },
    data() {
        return {
            selectedVirtualNumber: '',
            newPhone: '',
            whatsAppModalOpen: false,
            selectedPhone: '',
            selectedEncryptedPhone: ''
        }
    },
    computed: {
        title() {
            return this.clientId ? `Клиент #${this.clientId}` : "Новый клиент";
        },
        legalFields() {
            return this.form.customFields.filter(field => field.legal);
        },
        regularFields() {
            return this.form.customFields.filter(field => !field.legal);
        }
    },
    setup(props) {
        let formCustomFields = [];
        for (let field of props.customFieldsSettings) {
            const key = 'f' + field.id;
            field.value = props.clientCustomFields?.[key] || '';
            formCustomFields.push(field);
        }

        const form = useForm({
            clientId: props.clientId || null,
            orderLabel: props.orderLabel || null,
            name: props.clientData?.name || '',
            address: props.clientData?.address || '',
            email: props.clientData?.email || '',
            notes: props.clientData?.notes || '',
            legalEntity: props.clientData?.legalEntity || false,
            customFields: formCustomFields,
            phones: props.phones || [],
            virtualNumbers: props.virtualNumbers || [],
        });

        const submit = () => {
            const clientRoute = props.clientId ? route('client.update', props.clientId) : route('client.create');
            form.post(clientRoute, {
                onError: () => {
                    // Handle errors if needed
                },
            });
        };

        return {
            orderLabel: props.orderLabel,
            form,
            submit
        };
    },

    methods: {
        call(phoneText, encryptedPhone) {
            const virtualNumber = this.selectedVirtualNumber;
            if (!virtualNumber) {
                alert("Пожалуйста, выберите АОН перед звонком");
                return;
            }

            axios.post(route('employee.call'), {
                encryptedPhone: encryptedPhone,
                phoneText: phoneText,
                virtualNumber: virtualNumber
            })
                .then(response => {
                    console.log('Response:', response.data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        },
        addPhone() {
            this.form.phones.push({text: "", encrypted: id++});
        },
        removePhone(phone) {
            this.form.phones = this.form.phones.filter((t) => t !== phone);
        },
        openWhatsAppChat(phoneText, encryptedPhone) {
            this.selectedPhone = phoneText;
            this.selectedEncryptedPhone = encryptedPhone;
            this.whatsAppModalOpen = true;
        }
    },
};
</script>

<style scoped>
/* Remove the fixed width constraint */
form {
    max-width: none !important;
    width: 100% !important;
}
</style>
